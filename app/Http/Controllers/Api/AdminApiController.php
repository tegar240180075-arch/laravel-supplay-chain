<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Port;
use App\Models\Country;
use App\Models\Article;
use App\Models\NewsCache;
use App\Models\CurrencyRate;
use App\Models\WeatherData;
use App\Services\RiskScoringService;
use App\Services\ExchangeRateService;

class AdminApiController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    //  USERS
    // ═══════════════════════════════════════════════════════════════

    /** GET /api/admin/users */
    public function listUsers()
    {
        return response()->json(
            User::select('id', 'name', 'email', 'created_at')->orderBy('created_at', 'desc')->get()
        );
    }

    /** DELETE /api/admin/users/{id} */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        // Prevent deleting the only admin
        if (User::count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus satu-satunya pengguna.'], 422);
        }
        $user->delete();
        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  PORTS
    // ═══════════════════════════════════════════════════════════════

    /** GET /api/admin/ports?page=1 */
    public function listPorts(Request $request)
    {
        $ports = Port::with('country')
            ->orderBy('name')
            ->paginate(30);
        return response()->json($ports);
    }

    /** POST /api/admin/ports */
    public function storePort(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'country_code' => 'required|string|size:2',
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
            'type'         => 'nullable|string|max:50',
            'size'         => 'nullable|string|max:50',
        ]);

        $country = Country::where('code', $validated['country_code'])->firstOrFail();

        $port = Port::create([
            'name'       => $validated['name'],
            'country_id' => $country->id,
            'lat'        => $validated['lat'],
            'lng'        => $validated['lng'],
            'type'       => $validated['type'] ?? 'Seaport',
            'size'       => $validated['size'] ?? 'Medium',
        ]);

        return response()->json(['success' => true, 'data' => $port->load('country')], 201);
    }

    /** DELETE /api/admin/ports/{id} */
    public function deletePort($id)
    {
        Port::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  ARTICLES (Analisis Intelijen)
    // ═══════════════════════════════════════════════════════════════

    /** GET /api/admin/articles */
    public function listArticles()
    {
        return response()->json(
            Article::with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    /** POST /api/admin/articles */
    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'status'  => 'nullable|in:published,draft',
        ]);

        $article = Article::create([
            'title'   => $validated['title'],
            'content' => $validated['content'],
            'status'  => $validated['status'] ?? 'published',
            'user_id' => 1, // Default admin
        ]);

        return response()->json(['success' => true, 'data' => $article], 201);
    }

    /** PUT /api/admin/articles/{id} */
    public function updateArticle(Request $request, $id)
    {
        $article   = Article::findOrFail($id);
        $validated = $request->validate([
            'title'   => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'status'  => 'sometimes|in:published,draft',
        ]);
        $article->update($validated);
        return response()->json(['success' => true, 'data' => $article]);
    }

    /** DELETE /api/admin/articles/{id} */
    public function deleteArticle($id)
    {
        Article::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  SYSTEM ACTIONS
    // ═══════════════════════════════════════════════════════════════

    /**
     * POST /api/admin/engine/run
     * Trigger risk update for all countries with existing risk scores.
     */
    public function runRiskEngine(RiskScoringService $riskEngine)
    {
        $countries = \App\Models\Country::whereHas('riskScore')->get();

        if ($countries->isEmpty()) {
            $countries = \App\Models\Country::take(10)->get();
        }

        $success = 0;
        $errors  = 0;
        foreach ($countries as $country) {
            try {
                $riskEngine->calculateRiskForCountry($country);
                $success++;
            } catch (\Exception $e) {
                $errors++;
                \Log::warning("Admin engine run failed for {$country->code}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Risk score diperbarui untuk {$success} negara." . ($errors ? " ({$errors} gagal)" : ''),
            'processed' => $success,
            'errors'    => $errors,
        ]);
    }

    /**
     * POST /api/admin/engine/rates
     * Refresh exchange rates.
     */
    public function refreshRates(ExchangeRateService $exchange)
    {
        try {
            $rates = $exchange->getRates('USD');
            return response()->json([
                'success' => true,
                'message' => count($rates) . ' kurs mata uang diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/admin/cache/clear
     * Clear stale news and weather data.
     */
    public function clearCache()
    {
        $newsDeleted    = NewsCache::where('published_at', '<', now()->subDays(30))->delete();
        $weatherDeleted = WeatherData::where('last_updated_at', '<', now()->subDays(1))->delete();
        $ratesDeleted   = CurrencyRate::where('last_updated_at', '<', now()->subDays(2))->delete();

        return response()->json([
            'success' => true,
            'message' => "Cache dibersihkan: {$newsDeleted} berita lama, {$weatherDeleted} data cuaca, {$ratesDeleted} kurs lama dihapus.",
        ]);
    }

    /** GET /api/admin/stats — system statistics */
    public function stats()
    {
        return response()->json([
            'users'          => User::count(),
            'countries'      => \App\Models\Country::count(),
            'ports'          => \App\Models\Port::count(),
            'news_cached'    => NewsCache::count(),
            'risk_scores'    => \App\Models\RiskScore::count(),
            'economic_rows'  => \App\Models\CountryEconomicData::count(),
            'currency_rates' => CurrencyRate::count(),
            'articles'       => Article::count(),
        ]);
    }
}
