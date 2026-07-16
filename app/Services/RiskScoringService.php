<?php

namespace App\Services;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\RiskScoreHistory;

class RiskScoringService
{
    protected $weatherService;
    protected $worldBankService;
    protected $exchangeRateService;
    protected $sentimentService;

    public function __construct(
        OpenMeteoService $weather,
        WorldBankService $worldBank,
        ExchangeRateService $exchange,
        SentimentAnalysisService $sentiment
    ) {
        $this->weatherService = $weather;
        $this->worldBankService = $worldBank;
        $this->exchangeRateService = $exchange;
        $this->sentimentService = $sentiment;
    }

    public function calculateRiskForCountry(Country $country)
    {
        // 1. Weather Risk (30%)
        $weatherData = $this->weatherService->getWeather($country);
        $weatherRisk = 0;
        if ($weatherData) {
            if ($weatherData->condition == 'Storm') $weatherRisk = 100;
            elseif ($weatherData->condition == 'Rain Showers') $weatherRisk = 60;
            elseif ($weatherData->condition == 'Rain') $weatherRisk = 40;
            elseif ($weatherData->condition == 'Snow') $weatherRisk = 50;
            elseif ($weatherData->condition == 'Fog') $weatherRisk = 30;
            
            // Add wind speed factor
            if ($weatherData->wind_speed > 50) $weatherRisk += 30;
            $weatherRisk = min(100, $weatherRisk);
        }

        // 2. Inflation Risk (20%)
        $economicData = $this->worldBankService->getEconomicData($country);
        $inflationRisk = 0;
        if ($economicData && $economicData->inflation_rate) {
            $inflation = $economicData->inflation_rate;
            if ($inflation > 10) $inflationRisk = 100;
            elseif ($inflation > 5) $inflationRisk = 75;
            elseif ($inflation > 2) $inflationRisk = 30;
            elseif ($inflation < 0) $inflationRisk = 60; // Deflation is also risky
        }

        // 3. Currency Risk (10%)
        $currencyRisk = 0;
        if ($country->currency_code) {
            // Simplified risk: if currency is not USD/EUR/GBP, assume slightly higher baseline risk
            $stable = ['USD', 'EUR', 'GBP', 'CHF', 'JPY'];
            if (!in_array($country->currency_code, $stable)) {
                $currencyRisk = 40;
            }
        }

        // 4. News Sentiment Risk (40%)
        // Assuming GNewsService is run periodically to fetch news, 
        // here we just use the sentiment service on existing news.
        $newsRisk = $this->sentimentService->getCountrySentimentScore($country->id);

        // Weighted Total
        $totalScore = ($weatherRisk * 0.30) + ($inflationRisk * 0.20) + ($newsRisk * 0.40) + ($currencyRisk * 0.10);

        $level = $this->getRiskLevel($totalScore);

        $riskScore = RiskScore::updateOrCreate(
            ['country_id' => $country->id],
            [
                'weather_risk' => $weatherRisk,
                'inflation_risk' => $inflationRisk,
                'news_risk' => $newsRisk,
                'currency_risk' => $currencyRisk,
                'total_score' => $totalScore,
                'risk_level' => $level,
                'last_calculated_at' => now(),
            ]
        );

        // Record history for charts
        RiskScoreHistory::updateOrCreate(
            ['country_id' => $country->id, 'record_date' => today()],
            [
                'total_score' => $totalScore,
                'risk_level' => $level,
            ]
        );

        return $riskScore;
    }

    protected function getRiskLevel($score)
    {
        if ($score >= 75) return 'Critical';
        if ($score >= 50) return 'High';
        if ($score >= 25) return 'Medium';
        return 'Low';
    }
}
