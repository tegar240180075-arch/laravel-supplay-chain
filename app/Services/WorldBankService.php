<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Country;
use App\Models\CountryEconomicData;

class WorldBankService
{
    protected $baseUrl = 'https://api.worldbank.org/v2/country';

    public function getEconomicData(Country $country)
    {
        // NY.GDP.MKTP.CD = GDP (current US$)
        // FP.CPI.TOTL.ZG = Inflation, consumer prices (annual %)
        // SP.POP.TOTL = Population, total
        
        $year = date('Y') - 2; // World Bank data is usually delayed by 1-2 years
        
        $gdp = $this->fetchIndicator($country->code, 'NY.GDP.MKTP.CD', $year);
        $inflation = $this->fetchIndicator($country->code, 'FP.CPI.TOTL.ZG', $year);
        $population = $this->fetchIndicator($country->code, 'SP.POP.TOTL', $year);

        if ($gdp || $inflation || $population) {
            return CountryEconomicData::updateOrCreate(
                ['country_id' => $country->id, 'year' => $year],
                [
                    'gdp_billions' => $gdp ? ($gdp / 1000000000) : null,
                    'inflation_rate' => $inflation,
                    'population' => $population,
                ]
            );
        }
        
        return null;
    }

    protected function fetchIndicator($countryCode, $indicator, $year)
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$countryCode}/indicator/{$indicator}", [
                'date' => $year,
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[1]) && isset($data[1][0]['value'])) {
                    return $data[1][0]['value'];
                }
            }
        } catch (\Exception $e) {
            \Log::warning("WorldBankService failed for $countryCode indicator $indicator: " . $e->getMessage());
        }
        return null;
    }
}
