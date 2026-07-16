<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\NewsCache;
use App\Models\Country;
use Carbon\Carbon;

class GNewsService
{
    protected $baseUrl = 'https://gnews.io/api/v4';

    public function fetchNewsForCountry(Country $country)
    {
        $apiKey = env('GNEWS_API_KEY');
        if (!$apiKey || $apiKey == 'your_gnews_api_key_here') {
            // Mock data for prototype demonstration since no API key is provided
            $articles = [
                [
                    'title' => "Supply chain disruptions in {$country->name} cause major delays",
                    'description' => "Severe weather and inflation have caused a massive delay in logistics operations across {$country->name}.",
                    'url' => "https://example.com/news/{$country->code}/1",
                    'source' => ['name' => 'Logistics Daily'],
                    'publishedAt' => now()->subDays(rand(1, 5))->toIso8601String()
                ],
                [
                    'title' => "New port expansion project approved in {$country->name}",
                    'description' => "A new development project aims to increase the efficiency of trade and shipping.",
                    'url' => "https://example.com/news/{$country->code}/2",
                    'source' => ['name' => 'Global Trade News'],
                    'publishedAt' => now()->subDays(rand(1, 10))->toIso8601String()
                ]
            ];
            
            $savedArticles = [];
            foreach ($articles as $article) {
                $saved = NewsCache::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'country_id' => $country->id,
                        'title' => $article['title'],
                        'description' => $article['description'],
                        'source_name' => $article['source']['name'],
                        'published_at' => Carbon::parse($article['publishedAt']),
                        'category' => 'logistics'
                    ]
                );
                $savedArticles[] = $saved;
            }
            return $savedArticles;
        }

        $query = urlencode($country->name . ' AND (logistics OR economy OR supply chain OR trade OR port)');
        
        $response = Http::get("{$this->baseUrl}/search", [
            'q' => $query,
            'lang' => 'en',
            'max' => 10,
            'apikey' => $apiKey
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $articles = $data['articles'] ?? [];
            
            $savedArticles = [];
            foreach ($articles as $article) {
                $saved = NewsCache::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'country_id' => $country->id,
                        'title' => $article['title'],
                        'description' => $article['description'],
                        'source_name' => $article['source']['name'] ?? 'Unknown',
                        'published_at' => Carbon::parse($article['publishedAt']),
                        'category' => 'logistics'
                    ]
                );
                $savedArticles[] = $saved;
            }
            return $savedArticles;
        }
        
        return [];
    }
}
