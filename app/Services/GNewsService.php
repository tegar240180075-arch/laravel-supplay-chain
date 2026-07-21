<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\NewsCache;
use App\Models\Country;
use Carbon\Carbon;

class GNewsService
{
    protected $baseUrl = 'https://gnews.io/api/v4';

    /**
     * Fetch news for a specific country and save to cache table.
     */
    public function fetchNewsForCountry(Country $country)
    {
        $apiKey = config('services.gnews.key');

        if (empty($apiKey)) {
            \Log::warning('GNewsService: GNEWS_API_KEY is not set in .env');
            return [];
        }

        // Enclose country name and multi-word terms in quotes to satisfy GNews syntax requirements
        $query = '"' . $country->name . '" AND (logistics OR economy OR "supply chain" OR trade OR port)';

        // Cache for 2 hours to preserve free-plan quota (100 req/day)
        $cacheKey = 'gnews_country_' . $country->code;
        $articles = Cache::remember($cacheKey, 7200, function () use ($query, $apiKey) {
            // Added retry(3, 1000) to handle 429 Too Many Requests automatically
            $response = Http::timeout(15)->retry(3, 1000)->get("{$this->baseUrl}/search", [
                'q'      => $query,
                'lang'   => 'en',
                'max'    => 10,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                return $response->json()['articles'] ?? [];
            }

            \Log::warning('GNewsService: API call failed. Status=' . $response->status() . ' Body=' . $response->body());
            return [];
        });

        $savedArticles = [];
        foreach ($articles as $article) {
            $saved = NewsCache::updateOrCreate(
                ['url' => $article['url']],
                [
                    'country_id'  => $country->id,
                    'title'       => $article['title'],
                    'description' => $article['description'] ?? '',
                    'source_name' => $article['source']['name'] ?? 'Unknown',
                    'published_at'=> Carbon::parse($article['publishedAt']),
                    'category'    => 'logistics',
                ]
            );
            $savedArticles[] = $saved;
        }

        return $savedArticles;
    }

    /**
     * Fetch news by a free-form query (used by NewsApiController).
     * Results are cached for 1 hour.
     */
    public function fetchByQuery(string $query): array
    {
        $apiKey = config('services.gnews.key');

        if (empty($apiKey)) {
            return [];
        }

        $cacheKey = 'gnews_query_' . md5($query);

        return Cache::remember($cacheKey, 3600, function () use ($query, $apiKey) {
            $response = Http::timeout(15)->retry(3, 1000)->get("{$this->baseUrl}/search", [
                'q'      => $query,
                'lang'   => 'en',
                'max'    => 10,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                return $response->json()['articles'] ?? [];
            }

            \Log::warning('GNewsService::fetchByQuery failed. Status=' . $response->status());
            return [];
        });
    }
}
