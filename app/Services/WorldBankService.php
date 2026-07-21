<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Country;
use App\Models\CountryEconomicData;

class WorldBankService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.world_bank.base_url', 'https://api.worldbank.org/v2/country');
    }

    /**
     * Fetch the LATEST economic data for a country and return the record.
     * Also seeds the multi-year dataset so analytics charts have real data.
     */
    public function getEconomicData(Country $country)
    {
        $endYear   = (int) date('Y');
        $startYear = $endYear - 5;
        $dateRange = "{$startYear}:{$endYear}";

        // Indicators to fetch
        $indicators = [
            'gdp'        => 'NY.GDP.MKTP.CD',   // GDP (current US$)
            'inflation'  => 'FP.CPI.TOTL.ZG',  // Inflation, consumer prices (annual %)
            'population' => 'SP.POP.TOTL',      // Population, total
        ];

        // Fetch all years for each indicator and save every row
        $allYears = $this->fetchMultiYearData($country->code, $indicators, $dateRange);

        if (empty($allYears)) {
            return null;
        }

        // Upsert every year into the DB so the analytics trend charts have real data
        $latestRecord = null;
        foreach ($allYears as $year => $values) {
            $record = CountryEconomicData::updateOrCreate(
                ['country_id' => $country->id, 'year' => $year],
                [
                    'gdp_billions'   => isset($values['gdp']) ? ($values['gdp'] / 1_000_000_000) : null,
                    'inflation_rate' => $values['inflation'] ?? null,
                    'population'     => $values['population'] ?? null,
                ]
            );

            // Keep track of the most recent valid record
            if ($latestRecord === null || (int) $year > (int) $latestRecord->year) {
                $latestRecord = $record;
            }
        }

        return $latestRecord;
    }

    /**
     * Fetch multi-year data for all requested indicators at once.
     * Returns an associative array keyed by year, e.g.:
     * [ '2023' => ['gdp' => 4.2e12, 'inflation' => 3.5, 'population' => 83_900_000], ... ]
     */
    protected function fetchMultiYearData(string $countryCode, array $indicators, string $dateRange): array
    {
        $allYears = [];

        foreach ($indicators as $key => $indicator) {
            usleep(500_000); // 0.5 s delay — be polite to the World Bank API

            try {
                $response = Http::timeout(15)->retry(3, 2000)->get(
                    "{$this->baseUrl}/{$countryCode}/indicator/{$indicator}",
                    [
                        'date'     => $dateRange,
                        'format'   => 'json',
                        'per_page' => 10,
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data[1]) && is_array($data[1])) {
                        foreach ($data[1] as $entry) {
                            if (isset($entry['value']) && $entry['value'] !== null && isset($entry['date'])) {
                                $allYears[$entry['date']][$key] = $entry['value'];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("WorldBankService: indicator={$indicator} country={$countryCode} error: " . $e->getMessage());
            }
        }

        return $allYears;
    }
}
