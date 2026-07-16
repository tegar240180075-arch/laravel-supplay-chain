<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CurrencyRate;

class ExchangeRateService
{
    protected $baseUrl = 'https://open.er-api.com/v6/latest'; // Free ExchangeRate-API endpoint

    public function getRates($base = 'USD')
    {
        $response = Http::get("{$this->baseUrl}/{$base}");

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['rates'])) {
                foreach ($data['rates'] as $target => $rate) {
                    CurrencyRate::updateOrCreate(
                        ['base_currency' => $base, 'target_currency' => $target],
                        [
                            'rate' => $rate,
                            'last_updated_at' => now()
                        ]
                    );
                }
                return $data['rates'];
            }
        }
        return [];
    }

    public function getRateForCurrency($target, $base = 'USD')
    {
        $rateRecord = CurrencyRate::where('base_currency', $base)
                                  ->where('target_currency', $target)
                                  ->where('last_updated_at', '>=', now()->subHours(12))
                                  ->first();
                                  
        if ($rateRecord) {
            return $rateRecord->rate;
        }
        
        $rates = $this->getRates($base);
        return $rates[$target] ?? null;
    }
}
