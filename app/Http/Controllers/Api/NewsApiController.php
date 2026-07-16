<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsCache;
use App\Models\Country;
use App\Models\NewsSentiment;

class NewsApiController extends Controller
{
    public function index()
    {
        $news = NewsCache::with(['country', 'sentiment'])->orderBy('published_at', 'desc')->take(20)->get();
        return response()->json($news);
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $news = NewsCache::with(['country', 'sentiment'])
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orderBy('published_at', 'desc')
                    ->get();
        return response()->json($news);
    }

    public function byCountry($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $news = NewsCache::with('sentiment')
                    ->where('country_id', $country->id)
                    ->orderBy('published_at', 'desc')
                    ->get();
        return response()->json($news);
    }

    public function sentiment()
    {
        $sentiments = NewsSentiment::with('newsCache')->take(50)->get();
        return response()->json($sentiments);
    }
}
