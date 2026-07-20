<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsCache;
use App\Models\Country;
use App\Models\NewsSentiment;
use App\Services\GNewsService;
use App\Services\SentimentAnalysisService;

class NewsApiController extends Controller
{
    protected $gnewsService;
    protected $sentimentService;

    public function __construct(GNewsService $gnewsService, SentimentAnalysisService $sentimentService)
    {
        $this->gnewsService    = $gnewsService;
        $this->sentimentService = $sentimentService;
    }

    /**
     * GET /api/news — global supply chain news
     */
    public function index(Request $request)
    {
        return $this->fetchFromGNews('supply chain OR logistics OR trade OR shipping OR port');
    }

    /**
     * GET /api/news/search?q=...
     */
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));
        if (empty($q)) {
            return $this->index($request);
        }
        return $this->fetchFromGNews($q);
    }

    /**
     * GET /api/news/country/{code}
     */
    public function byCountry($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $query   = $country->name . ' AND (supply chain OR logistics OR trade OR port OR economy)';
        return $this->fetchFromGNews($query);
    }

    /**
     * GET /api/news/sentiment — return recently analyzed sentiments
     */
    public function sentiment()
    {
        $sentiments = NewsSentiment::with('newsCache')->take(50)->get();
        return response()->json($sentiments);
    }

    /**
     * Core: call GNews via service (uses caching), analyze sentiment, return formatted articles.
     */
    private function fetchFromGNews(string $query)
    {
        $apiKey = config('services.gnews.key');

        if (empty($apiKey)) {
            return response()->json([
                [
                    'title'        => 'Konfigurasi API Diperlukan',
                    'description'  => 'Silakan tambahkan GNEWS_API_KEY yang valid di file .env Anda untuk melihat berita nyata.',
                    'url'          => '#',
                    'source_name'  => 'Pesan Sistem',
                    'published_at' => now()->toIso8601String(),
                    'country'      => null,
                    'sentiment'    => ['sentiment_label' => 'Neutral', 'positive_score' => 0, 'negative_score' => 0],
                ],
            ]);
        }

        $articles = $this->gnewsService->fetchByQuery($query);

        if (empty($articles)) {
            return response()->json([
                [
                    'title'        => 'Tidak Ada Berita Ditemukan',
                    'description'  => 'GNews API tidak mengembalikan hasil untuk kueri ini. Mungkin kuota harian (100 req) sudah habis atau kueri tidak menghasilkan hasil.',
                    'url'          => '#',
                    'source_name'  => 'Sistem',
                    'published_at' => now()->toIso8601String(),
                    'country'      => null,
                    'sentiment'    => ['sentiment_label' => 'Neutral', 'positive_score' => 0, 'negative_score' => 0],
                ],
            ], 200);
        }

        $formattedNews = [];
        foreach ($articles as $article) {
            $sentiment = $this->sentimentService->analyzeText(
                $article['title'] ?? '',
                $article['description'] ?? ''
            );

            $formattedNews[] = [
                'title'        => $article['title'],
                'description'  => $article['description'] ?? '',
                'url'          => $article['url'],
                'source_name'  => $article['source']['name'] ?? 'Unknown',
                'published_at' => $article['publishedAt'],
                'image'        => $article['image'] ?? null,
                'country'      => null,
                'sentiment'    => $sentiment,
            ];
        }

        return response()->json($formattedNews);
    }
}
