<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RestCountriesService
{
    protected $baseUrl = 'https://restcountries.com/v3.1';

    public function getCountryInfo($code)
    {
        $response = Http::get("{$this->baseUrl}/alpha/{$code}");

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data[0])) {
                return [
                    'name' => $data[0]['name']['common'] ?? '',
                    'region' => $data[0]['region'] ?? '',
                    'currency_code' => isset($data[0]['currencies']) ? array_key_first($data[0]['currencies']) : null,
                    'lat' => $data[0]['latlng'][0] ?? null,
                    'lng' => $data[0]['latlng'][1] ?? null,
                ];
            }
        }

        return null;
    }
}
