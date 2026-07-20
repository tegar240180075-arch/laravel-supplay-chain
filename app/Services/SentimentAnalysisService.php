<?php

namespace App\Services;

use App\Models\NewsCache;
use App\Models\NewsSentiment;
use App\Models\PositiveWord;
use App\Models\NegativeWord;

class SentimentAnalysisService
{
    protected $positiveWords = [];
    protected $negativeWords = [];

    public function __construct()
    {
        $this->positiveWords = PositiveWord::pluck('word')->toArray();
        if (empty($this->positiveWords)) {
            $this->positiveWords = ['growth', 'profit', 'increase', 'stable', 'success', 'boom', 'positive', 'good', 'excellent', 'great', 'improve', 'up', 'recovery'];
        }
        $this->negativeWords = NegativeWord::pluck('word')->toArray();
        if (empty($this->negativeWords)) {
            $this->negativeWords = ['delay', 'disruption', 'inflation', 'crisis', 'risk', 'bad', 'drop', 'fall', 'decrease', 'loss', 'poor', 'problem', 'shortage', 'fail', 'strike'];
        }
    }

    public function analyzeAndSave(NewsCache $news)
    {
        $sentiment = $this->analyzeText($news->title, $news->description);
        
        return NewsSentiment::updateOrCreate(
            ['news_cache_id' => $news->id],
            $sentiment
        );
    }
    
    public function analyzeText($title, $description)
    {
        $text = strtolower($title . ' ' . $description);
        
        // Simple tokenization (remove punctuation and split by space)
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $words = explode(' ', $text);
        
        $positiveScore = 0;
        $negativeScore = 0;
        
        foreach ($this->positiveWords as $pWord) {
            if (str_contains($text, strtolower($pWord))) $positiveScore++;
        }
        foreach ($this->negativeWords as $nWord) {
            if (str_contains($text, strtolower($nWord))) $negativeScore++;
        }
        
        $sentimentLabel = 'Neutral';
        if ($positiveScore > $negativeScore) {
            $sentimentLabel = 'Positive';
        } elseif ($negativeScore > $positiveScore) {
            $sentimentLabel = 'Negative';
        }
        
        return [
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'sentiment_label' => $sentimentLabel
        ];
    }
    
    public function getCountrySentimentScore($countryId)
    {
        $recentNews = NewsCache::where('country_id', $countryId)
            ->where('published_at', '>=', now()->subDays(30))
            ->with('sentiment')
            ->get();
            
        if ($recentNews->isEmpty()) return 50; // 50 is Neutral baseline, 0 would mean zero risk (false positive)
        
        $totalSentiments = 0;
        $score = 0;
        
        foreach ($recentNews as $news) {
            if ($news->sentiment) {
                $totalSentiments++;
                if ($news->sentiment->sentiment_label == 'Negative') $score += 2; // Higher risk
                elseif ($news->sentiment->sentiment_label == 'Positive') $score -= 1; // Lower risk
            }
        }
        
        if ($totalSentiments == 0) return 50;
        
        // Normalize to a 0-100 scale where higher is worse (more negative news)
        // Let's map it roughly: baseline is 50. Negative pulls it to 100, Positive pulls to 0.
        $avgScore = $score / $totalSentiments;
        // if all negative, avgScore = 2.
        // if all positive, avgScore = -1.
        
        $normalizedRisk = 50 + ($avgScore * 25);
        return max(0, min(100, $normalizedRisk)); // Clamp between 0 and 100
    }
}
