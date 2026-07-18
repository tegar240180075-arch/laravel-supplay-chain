<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Services\GNewsService;
use App\Services\SentimentAnalysisService;
use App\Services\RiskScoringService;
use App\Services\ExchangeRateService;

class UpdateRiskData extends Command
{
    protected $signature = 'risk:update';
    protected $description = 'Fetch latest news, run sentiment analysis, and update risk scores for all countries';

    public function handle(
        GNewsService $gnews, 
        SentimentAnalysisService $sentiment, 
        RiskScoringService $riskEngine,
        ExchangeRateService $exchange
    ) {
        $this->info('Starting Risk Data Update...');

        // 1. Update Global Currency Rates
        $this->info('Updating global currency rates...');
        $exchange->getRates('USD');

        // Only get countries that don't have a risk score yet
        $countries = Country::whereNotIn('id', function($query) {
            $query->select('country_id')->from('risk_scores');
        })->get();
        
        $this->info('Processing ' . $countries->count() . ' remaining countries...');
        
        $bar = $this->output->createProgressBar($countries->count());

        foreach ($countries as $country) {
            // Add a small delay to prevent API rate limits
            usleep(500000); // 0.5 seconds
            try {
                // 2. Fetch News
                $articles = $gnews->fetchNewsForCountry($country);
                
                // 3. Analyze Sentiment
                foreach ($articles as $article) {
                    $sentiment->analyzeAndSave($article);
                }

                // 4. Calculate Final Risk Score
                $riskEngine->calculateRiskForCountry($country);
            } catch (\Exception $e) {
                $this->error("Failed to update risk data for {$country->name}: " . $e->getMessage());
                \Log::error("UpdateRiskData error for {$country->code}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Risk Data Update Completed Successfully!');
    }
}
