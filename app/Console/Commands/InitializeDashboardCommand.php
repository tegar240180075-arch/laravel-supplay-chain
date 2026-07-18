<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Services\GNewsService;
use App\Services\SentimentAnalysisService;
use App\Services\RiskScoringService;
use App\Services\ExchangeRateService;

class InitializeDashboardCommand extends Command
{
    protected $signature = 'dashboard:init {--limit=20 : Number of countries to process for risk calculation}';
    protected $description = 'Initialize dashboard with full data pipeline: seed countries & ports, fetch news, analyze sentiment, and calculate risk scores';

    public function handle(
        GNewsService $gnews,
        SentimentAnalysisService $sentiment,
        RiskScoringService $riskEngine,
        ExchangeRateService $exchange
    ) {
        $this->info('🚀 Initializing Dashboard Data Pipeline...');
        $this->newLine();

        // Step 1: Ensure countries are seeded
        $countryCount = Country::count();
        if ($countryCount === 0) {
            $this->warn('No countries found. Running seeders first...');
            $this->call('db:seed', ['--class' => 'CountrySeeder']);
            $this->call('db:seed', ['--class' => 'PortSeeder']);
            $this->call('db:seed', ['--class' => 'SentimentWordSeeder']);
        } else {
            $this->info("✅ {$countryCount} countries already exist in database.");
        }

        // Step 2: Update exchange rates
        $this->info('💱 Fetching latest exchange rates...');
        try {
            $rates = $exchange->getRates('USD');
            $this->info('  ✅ Exchange rates updated: ' . count($rates) . ' currencies');
        } catch (\Exception $e) {
            $this->warn('  ⚠ Exchange rate fetch failed: ' . $e->getMessage());
        }

        // Step 3: Process countries for risk calculation
        $limit = (int) $this->option('limit');
        $countries = Country::take($limit)->get();

        $this->info("📊 Processing risk data for {$countries->count()} countries...");
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($countries as $country) {
            try {
                // Fetch news (uses mock data if no GNews API key)
                $articles = $gnews->fetchNewsForCountry($country);

                // Analyze sentiment for each article
                foreach ($articles as $article) {
                    $sentiment->analyzeAndSave($article);
                }

                // Calculate risk scores
                $riskEngine->calculateRiskForCountry($country);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error("Dashboard init error for {$country->code}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('═══════════════════════════════════════');
        $this->info('  Dashboard Initialization Complete!');
        $this->info('═══════════════════════════════════════');
        $this->info("  ✅ Countries processed: {$successCount}");
        if ($errorCount > 0) {
            $this->warn("  ⚠  Errors: {$errorCount}");
        }
        $this->info("  📍 Total ports: " . \App\Models\Port::count());
        $this->info("  📰 Total news articles: " . \App\Models\NewsCache::count());
        $this->info("  📊 Total risk scores: " . \App\Models\RiskScore::count());
        $this->newLine();
        $this->info('Dashboard is now ready! Visit your application in the browser.');

        return Command::SUCCESS;
    }
}
