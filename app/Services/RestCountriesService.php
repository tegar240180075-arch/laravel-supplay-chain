<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RestCountriesService
{
    protected $baseUrl = 'https://restcountries.com/v3.1';

    /**
     * Get info for a single country by code.
     */
    public function getCountryInfo($code)
    {
        $response = Http::get("{$this->baseUrl}/alpha/{$code}");

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data[0])) {
                return $this->formatCountry($data[0]);
            }
        }

        return null;
    }

    /**
     * Fetch ALL countries from RestCountries API.
     * Returns an array of formatted country data.
     */
    public function getAllCountries()
    {
        try {
            $response = Http::timeout(30)->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success'] === false) {
                    \Log::error('RestCountriesService::getAllCountries API deprecated payload: ' . ($data['errors'][0]['message'] ?? ''));
                    return [];
                }
                
                $countries = [];

                foreach ($data as $item) {
                    if (!is_array($item)) continue;
                    
                    $country = $this->formatCountry($item);
                    if ($country && !empty($country['code'])) {
                        $countries[] = $country;
                    }
                }

                // Sort by name
                usort($countries, fn($a, $b) => strcmp($a['name'], $b['name']));

                return $countries;
            }
        } catch (\Exception $e) {
            \Log::error('RestCountriesService::getAllCountries failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Format raw API response into a standard country array.
     */
    protected function formatCountry(array $data): ?array
    {
        $code = $data['cca2'] ?? null;
        if (!$code) {
            return null;
        }

        return [
            'name' => $data['name']['common'] ?? '',
            'code' => $code,
            'region' => $data['region'] ?? '',
            'currency_code' => isset($data['currencies']) ? array_key_first($data['currencies']) : null,
            'lat' => $data['latlng'][0] ?? null,
            'lng' => $data['latlng'][1] ?? null,
        ];
    }
}
