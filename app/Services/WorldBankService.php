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
        
        $endYear = date('Y');
        $startYear = $endYear - 5;
        $dateRange = "{$startYear}:{$endYear}";
        
        $gdpResult = $this->fetchIndicatorRange($country->code, 'NY.GDP.MKTP.CD', $dateRange);
        $inflationResult = $this->fetchIndicatorRange($country->code, 'FP.CPI.TOTL.ZG', $dateRange);
        $populationResult = $this->fetchIndicatorRange($country->code, 'SP.POP.TOTL', $dateRange);

        // Use the most recent available year from any indicator
        $year = $gdpResult['year'] ?? $inflationResult['year'] ?? $populationResult['year'] ?? ($endYear - 2);

        $gdp = $gdpResult['value'] ?? null;
        $inflation = $inflationResult['value'] ?? null;
        $population = $populationResult['value'] ?? null;

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

    /**
     * Fetch indicator data across a range of years and return the most recent non-null value.
     */
    protected function fetchIndicatorRange($countryCode, $indicator, $dateRange)
    {
        try {
            $response = Http::timeout(15)->get("{$this->baseUrl}/{$countryCode}/indicator/{$indicator}", [
                'date' => $dateRange,
                'format' => 'json',
                'per_page' => 10,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data[1]) && is_array($data[1])) {
                    // Data comes sorted newest first - find first non-null value
                    foreach ($data[1] as $entry) {
                        if (isset($entry['value']) && $entry['value'] !== null) {
                            return [
                                'value' => $entry['value'],
                                'year' => $entry['date'] ?? null,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("WorldBankService failed for $countryCode indicator $indicator: " . $e->getMessage());
        }
        return ['value' => null, 'year' => null];
    }
}
