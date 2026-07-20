<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Services\GNewsService;
use App\Services\SentimentAnalysisService;
use App\Services\RiskScoringService;
use App\Services\ExchangeRateService;
use App\Services\WorldBankService;

class InitializeDashboardCommand extends Command
{
    protected $signature = 'dashboard:init
                            {--limit=20 : Jumlah negara yang diproses untuk risk scoring}
                            {--skip-economic : Lewati pengambilan data ekonomi (lebih cepat)}';

    protected $description = 'Inisialisasi dashboard: seed negara & pelabuhan, ambil kurs mata uang, isi data ekonomi multi-tahun, fetch berita, analisis sentimen, hitung risk score.';

    public function handle(
        GNewsService $gnews,
        SentimentAnalysisService $sentiment,
        RiskScoringService $riskEngine,
        ExchangeRateService $exchange,
        WorldBankService $worldBank
    ) {
        $this->info('🚀 Memulai Pipeline Data Dashboard...');
        $this->newLine();

        // ── Step 1: Pastikan data negara & pelabuhan sudah ada ─────────────────
        $countryCount = Country::count();
        if ($countryCount === 0) {
            $this->warn('Tidak ada negara. Menjalankan seeder terlebih dahulu...');
            $this->call('db:seed', ['--class' => 'CountrySeeder']);
            $this->call('db:seed', ['--class' => 'PortSeeder']);
            $this->call('db:seed', ['--class' => 'SentimentWordSeeder']);
        } else {
            $this->info("✅ {$countryCount} negara sudah ada di database.");
        }

        // ── Step 2: Ambil kurs mata uang terbaru ───────────────────────────────
        $this->info('💱 Mengambil kurs mata uang terbaru dari ExchangeRate API...');
        try {
            $rates = $exchange->getRates('USD');
            $this->info('  ✅ Kurs diperbarui: ' . count($rates) . ' mata uang (riwayat hari ini dicatat)');
        } catch (\Exception $e) {
            $this->warn('  ⚠ Gagal ambil kurs: ' . $e->getMessage());
        }

        // ── Step 3: Isi data ekonomi multi-tahun dari World Bank ───────────────
        if (!$this->option('skip-economic')) {
            $this->info('🏦 Mengambil data ekonomi multi-tahun dari World Bank API...');
            $countries     = Country::all();
            $econBar       = $this->output->createProgressBar($countries->count());
            $econBar->start();
            $econSuccess   = 0;
            $econFailed    = 0;

            foreach ($countries as $country) {
                try {
                    $result = $worldBank->getEconomicData($country);
                    if ($result) $econSuccess++;
                    else $econFailed++;
                } catch (\Exception $e) {
                    $econFailed++;
                    \Log::warning("Economic data failed for {$country->code}: " . $e->getMessage());
                }
                $econBar->advance();
            }

            $econBar->finish();
            $this->newLine();
            $this->info("  ✅ Berhasil: {$econSuccess} | Gagal: {$econFailed}");
        } else {
            $this->warn('  ⏭ Step data ekonomi dilewati (--skip-economic).');
        }

        // ── Step 4: Proses risk scoring untuk negara-negara pilihan ───────────
        $limit     = (int) $this->option('limit');
        $countries = Country::take($limit)->get();

        $this->info("📊 Menghitung risk score untuk {$countries->count()} negara...");
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $successCount = 0;
        $errorCount   = 0;

        foreach ($countries as $country) {
            try {
                // Fetch & save news (dengan cache 2 jam via GNewsService)
                $articles = $gnews->fetchNewsForCountry($country);

                // Analisis sentimen tiap artikel
                foreach ($articles as $article) {
                    $sentiment->analyzeAndSave($article);
                }

                // Hitung risk score berdasarkan data nyata
                $riskEngine->calculateRiskForCountry($country);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                \Log::error("Dashboard init error [{$country->code}]: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ── Ringkasan ──────────────────────────────────────────────────────────
        $this->info('═══════════════════════════════════════════════════');
        $this->info('  ✅ Inisialisasi Dashboard Selesai!');
        $this->info('═══════════════════════════════════════════════════');
        $this->info("  Negara diproses : {$successCount}");
        if ($errorCount > 0) {
            $this->warn("  Gagal           : {$errorCount}  (lihat storage/logs/laravel.log)");
        }
        $this->info("  Total pelabuhan : " . \App\Models\Port::count());
        $this->info("  Total berita    : " . \App\Models\NewsCache::count());
        $this->info("  Total risk score: " . \App\Models\RiskScore::count());
        $this->info("  Data ekonomi    : " . \App\Models\CountryEconomicData::count() . " baris (multi-tahun)");
        $this->newLine();
        $this->info('Dashboard siap! Buka aplikasi di browser Anda.');

        return Command::SUCCESS;
    }
}
