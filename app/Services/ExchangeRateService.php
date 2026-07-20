<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;

class ExchangeRateService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.exchange_rate.base_url', 'https://open.er-api.com/v6/latest');
    }

    /**
     * Fetch latest rates for a base currency, store them, and record history.
     */
    public function getRates(string $base = 'USD'): array
    {
        $response = Http::timeout(10)->get("{$this->baseUrl}/{$base}");

        if (!$response->successful()) {
            \Log::warning("ExchangeRateService: Failed to fetch rates for base={$base}. Status=" . $response->status());
            return [];
        }

        $data = $response->json();
        if (!isset($data['rates'])) {
            return [];
        }

        $today = today()->toDateString();

        foreach ($data['rates'] as $target => $rate) {
            // Upsert the current rate (live)
            CurrencyRate::updateOrCreate(
                ['base_currency' => $base, 'target_currency' => $target],
                ['rate' => $rate, 'last_updated_at' => now()]
            );

            // Record one history row per day (idempotent via updateOrCreate)
            CurrencyRateHistory::updateOrCreate(
                [
                    'base_currency'   => $base,
                    'target_currency' => $target,
                    'record_date'     => $today,
                ],
                ['rate' => $rate]
            );
        }

        return $data['rates'];
    }

    /**
     * Get the rate for a specific target currency, using the DB cache.
     * Falls back to a live API call if the cached rate is stale (>12 h).
     */
    public function getRateForCurrency(string $target, string $base = 'USD'): ?float
    {
        $rateRecord = CurrencyRate::where('base_currency', $base)
            ->where('target_currency', $target)
            ->where('last_updated_at', '>=', now()->subHours(12))
            ->first();

        if ($rateRecord) {
            return (float) $rateRecord->rate;
        }

        $rates = $this->getRates($base);
        return isset($rates[$target]) ? (float) $rates[$target] : null;
    }
}
