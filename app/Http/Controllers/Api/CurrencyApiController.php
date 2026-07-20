<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;
use App\Services\ExchangeRateService;

class CurrencyApiController extends Controller
{
    protected $exchangeService;

    public function __construct(ExchangeRateService $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    /**
     * GET /api/currency/rates?base=USD
     * Returns current rates. Fetches live if DB is empty or stale.
     */
    public function rates(Request $request)
    {
        $base  = strtoupper($request->query('base', 'USD'));
        $rates = CurrencyRate::where('base_currency', $base)
            ->where('last_updated_at', '>=', now()->subHours(12))
            ->get();

        if ($rates->isEmpty()) {
            // Fetch live and store
            $liveRates = $this->exchangeService->getRates($base);
            $rates = CurrencyRate::where('base_currency', $base)->get();
        }

        // Attach trend info: compare today vs yesterday's history
        $yesterday = today()->subDay()->toDateString();
        $histMap   = CurrencyRateHistory::where('base_currency', $base)
            ->where('record_date', $yesterday)
            ->pluck('rate', 'target_currency')
            ->toArray();

        $result = $rates->map(function ($r) use ($histMap) {
            $trend      = 'stable';
            $trendPct   = 0;
            $prevRate   = $histMap[$r->target_currency] ?? null;

            if ($prevRate && $prevRate > 0) {
                $diff      = $r->rate - $prevRate;
                $trendPct  = round(($diff / $prevRate) * 100, 3);
                if ($trendPct > 0.05)       $trend = 'up';
                elseif ($trendPct < -0.05)  $trend = 'down';
                else                         $trend = 'stable';
            }

            return [
                'base_currency'   => $r->base_currency,
                'target_currency' => $r->target_currency,
                'rate'            => $r->rate,
                'last_updated_at' => $r->last_updated_at,
                'trend'           => $trend,
                'trend_pct'       => $trendPct,
            ];
        });

        return response()->json($result);
    }

    /**
     * GET /api/currency/convert?from=USD&to=IDR&amount=1000
     */
    public function convert(Request $request)
    {
        $request->validate([
            'from'   => 'required|string|size:3',
            'to'     => 'required|string|size:3',
            'amount' => 'required|numeric|min:0',
        ]);

        $from   = strtoupper($request->from);
        $to     = strtoupper($request->to);
        $amount = (float) $request->amount;

        if ($from === $to) {
            return response()->json([
                'from' => $from, 'to' => $to,
                'amount' => $amount, 'converted_amount' => $amount,
            ]);
        }

        // Convert via USD as pivot
        if ($from === 'USD') {
            $rate      = $this->exchangeService->getRateForCurrency($to, 'USD');
            $converted = $amount * $rate;
        } elseif ($to === 'USD') {
            $rate      = $this->exchangeService->getRateForCurrency($from, 'USD');
            $converted = $rate ? ($amount / $rate) : 0;
        } else {
            $rateFrom  = $this->exchangeService->getRateForCurrency($from, 'USD');
            $rateTo    = $this->exchangeService->getRateForCurrency($to, 'USD');
            $converted = ($rateFrom && $rateTo) ? ($amount / $rateFrom) * $rateTo : 0;
        }

        return response()->json([
            'from'             => $from,
            'to'               => $to,
            'amount'           => $amount,
            'converted_amount' => round($converted, 4),
        ]);
    }

    /**
     * GET /api/currency/history?base=USD&target=IDR&days=30
     * Returns historical rate data for charting.
     */
    public function history(Request $request)
    {
        $base   = strtoupper($request->query('base', 'USD'));
        $target = strtoupper($request->query('target', 'IDR'));
        $days   = (int) $request->query('days', 30);

        $history = CurrencyRateHistory::where('base_currency', $base)
            ->where('target_currency', $target)
            ->where('record_date', '>=', today()->subDays($days)->toDateString())
            ->orderBy('record_date', 'asc')
            ->get(['record_date', 'rate']);

        return response()->json($history);
    }
}
