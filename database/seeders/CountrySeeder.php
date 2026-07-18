<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Fetching all countries from API...');

        try {
            $response = Http::timeout(30)->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');

            if ($response->successful()) {
                $data = $response->json();
                
                // The API might return 200 OK but with an error payload like ['success' => false, 'errors' => ...]
                if (isset($data['success']) && $data['success'] === false) {
                    throw new \Exception($data['errors'][0]['message'] ?? 'API returned failure payload');
                }

                $count = 0;

                foreach ($data as $item) {
                    if (!is_array($item)) continue;
                    
                    $code = $item['cca2'] ?? null;
                    if (!$code) continue;

                    $currencyCode = isset($item['currencies']) ? array_key_first($item['currencies']) : null;

                    DB::table('countries')->updateOrInsert(
                        ['code' => $code],
                        [
                            'name' => $item['name']['common'] ?? '',
                            'region' => $item['region'] ?? '',
                            'currency_code' => $currencyCode,
                            'lat' => $item['latlng'][0] ?? null,
                            'lng' => $item['latlng'][1] ?? null,
                            'updated_at' => now(),
                        ]
                    );
                    $count++;
                }

                if ($count > 0) {
                    $this->command->info("Successfully synced {$count} countries from API.");
                    return;
                } else {
                    throw new \Exception('API returned empty list or unexpected format');
                }
            } else {
                throw new \Exception('HTTP request failed with status ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::warning('CountrySeeder API fetch failed: ' . $e->getMessage());
            $this->command->warn('API fetch failed, falling back to static data...');
        }

        // Fallback: static data in case API is unavailable
        $this->seedFallbackCountries();
    }

    /**
     * Fallback static country list when API is unavailable.
     * Covers the most important trading nations.
     */
    protected function seedFallbackCountries(): void
    {
        $countries = [
            // Americas
            ['name' => 'United States', 'code' => 'US', 'region' => 'Americas', 'currency_code' => 'USD', 'lat' => 37.0902, 'lng' => -95.7129],
            ['name' => 'Canada', 'code' => 'CA', 'region' => 'Americas', 'currency_code' => 'CAD', 'lat' => 56.1304, 'lng' => -106.3468],
            ['name' => 'Mexico', 'code' => 'MX', 'region' => 'Americas', 'currency_code' => 'MXN', 'lat' => 23.6345, 'lng' => -102.5528],
            ['name' => 'Brazil', 'code' => 'BR', 'region' => 'Americas', 'currency_code' => 'BRL', 'lat' => -14.2350, 'lng' => -51.9253],
            ['name' => 'Argentina', 'code' => 'AR', 'region' => 'Americas', 'currency_code' => 'ARS', 'lat' => -38.4161, 'lng' => -63.6167],
            ['name' => 'Chile', 'code' => 'CL', 'region' => 'Americas', 'currency_code' => 'CLP', 'lat' => -35.6751, 'lng' => -71.5430],
            ['name' => 'Colombia', 'code' => 'CO', 'region' => 'Americas', 'currency_code' => 'COP', 'lat' => 4.5709, 'lng' => -74.2973],
            ['name' => 'Panama', 'code' => 'PA', 'region' => 'Americas', 'currency_code' => 'PAB', 'lat' => 8.5380, 'lng' => -80.7821],

            // Europe
            ['name' => 'Germany', 'code' => 'DE', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 51.1657, 'lng' => 10.4515],
            ['name' => 'United Kingdom', 'code' => 'GB', 'region' => 'Europe', 'currency_code' => 'GBP', 'lat' => 55.3781, 'lng' => -3.4360],
            ['name' => 'France', 'code' => 'FR', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 46.2276, 'lng' => 2.2137],
            ['name' => 'Italy', 'code' => 'IT', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 41.8719, 'lng' => 12.5674],
            ['name' => 'Spain', 'code' => 'ES', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 40.4637, 'lng' => -3.7492],
            ['name' => 'Netherlands', 'code' => 'NL', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 52.1326, 'lng' => 5.2913],
            ['name' => 'Belgium', 'code' => 'BE', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 50.5039, 'lng' => 4.4699],
            ['name' => 'Switzerland', 'code' => 'CH', 'region' => 'Europe', 'currency_code' => 'CHF', 'lat' => 46.8182, 'lng' => 8.2275],
            ['name' => 'Poland', 'code' => 'PL', 'region' => 'Europe', 'currency_code' => 'PLN', 'lat' => 51.9194, 'lng' => 19.1451],
            ['name' => 'Sweden', 'code' => 'SE', 'region' => 'Europe', 'currency_code' => 'SEK', 'lat' => 60.1282, 'lng' => 18.6435],
            ['name' => 'Norway', 'code' => 'NO', 'region' => 'Europe', 'currency_code' => 'NOK', 'lat' => 60.4720, 'lng' => 8.4689],
            ['name' => 'Denmark', 'code' => 'DK', 'region' => 'Europe', 'currency_code' => 'DKK', 'lat' => 56.2639, 'lng' => 9.5018],
            ['name' => 'Finland', 'code' => 'FI', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 61.9241, 'lng' => 25.7482],
            ['name' => 'Greece', 'code' => 'GR', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 39.0742, 'lng' => 21.8243],
            ['name' => 'Portugal', 'code' => 'PT', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 39.3999, 'lng' => -8.2245],
            ['name' => 'Turkey', 'code' => 'TR', 'region' => 'Europe', 'currency_code' => 'TRY', 'lat' => 38.9637, 'lng' => 35.2433],
            ['name' => 'Russia', 'code' => 'RU', 'region' => 'Europe', 'currency_code' => 'RUB', 'lat' => 61.5240, 'lng' => 105.3188],

            // Asia
            ['name' => 'China', 'code' => 'CN', 'region' => 'Asia', 'currency_code' => 'CNY', 'lat' => 35.8617, 'lng' => 104.1954],
            ['name' => 'Japan', 'code' => 'JP', 'region' => 'Asia', 'currency_code' => 'JPY', 'lat' => 36.2048, 'lng' => 138.2529],
            ['name' => 'South Korea', 'code' => 'KR', 'region' => 'Asia', 'currency_code' => 'KRW', 'lat' => 35.9078, 'lng' => 127.7669],
            ['name' => 'India', 'code' => 'IN', 'region' => 'Asia', 'currency_code' => 'INR', 'lat' => 20.5937, 'lng' => 78.9629],
            ['name' => 'Indonesia', 'code' => 'ID', 'region' => 'Asia', 'currency_code' => 'IDR', 'lat' => -0.7893, 'lng' => 113.9213],
            ['name' => 'Singapore', 'code' => 'SG', 'region' => 'Asia', 'currency_code' => 'SGD', 'lat' => 1.3521, 'lng' => 103.8198],
            ['name' => 'Malaysia', 'code' => 'MY', 'region' => 'Asia', 'currency_code' => 'MYR', 'lat' => 4.2105, 'lng' => 101.9758],
            ['name' => 'Thailand', 'code' => 'TH', 'region' => 'Asia', 'currency_code' => 'THB', 'lat' => 15.8700, 'lng' => 100.9925],
            ['name' => 'Vietnam', 'code' => 'VN', 'region' => 'Asia', 'currency_code' => 'VND', 'lat' => 14.0583, 'lng' => 108.2772],
            ['name' => 'Philippines', 'code' => 'PH', 'region' => 'Asia', 'currency_code' => 'PHP', 'lat' => 12.8797, 'lng' => 121.7740],
            ['name' => 'Taiwan', 'code' => 'TW', 'region' => 'Asia', 'currency_code' => 'TWD', 'lat' => 23.6978, 'lng' => 120.9605],
            ['name' => 'Bangladesh', 'code' => 'BD', 'region' => 'Asia', 'currency_code' => 'BDT', 'lat' => 23.6850, 'lng' => 90.3563],
            ['name' => 'Pakistan', 'code' => 'PK', 'region' => 'Asia', 'currency_code' => 'PKR', 'lat' => 30.3753, 'lng' => 69.3451],
            ['name' => 'Sri Lanka', 'code' => 'LK', 'region' => 'Asia', 'currency_code' => 'LKR', 'lat' => 7.8731, 'lng' => 80.7718],
            ['name' => 'Myanmar', 'code' => 'MM', 'region' => 'Asia', 'currency_code' => 'MMK', 'lat' => 21.9162, 'lng' => 95.9560],

            // Middle East
            ['name' => 'Saudi Arabia', 'code' => 'SA', 'region' => 'Asia', 'currency_code' => 'SAR', 'lat' => 23.8859, 'lng' => 45.0792],
            ['name' => 'United Arab Emirates', 'code' => 'AE', 'region' => 'Asia', 'currency_code' => 'AED', 'lat' => 23.4241, 'lng' => 53.8478],
            ['name' => 'Qatar', 'code' => 'QA', 'region' => 'Asia', 'currency_code' => 'QAR', 'lat' => 25.3548, 'lng' => 51.1839],
            ['name' => 'Kuwait', 'code' => 'KW', 'region' => 'Asia', 'currency_code' => 'KWD', 'lat' => 29.3117, 'lng' => 47.4818],
            ['name' => 'Oman', 'code' => 'OM', 'region' => 'Asia', 'currency_code' => 'OMR', 'lat' => 21.4735, 'lng' => 55.9754],
            ['name' => 'Bahrain', 'code' => 'BH', 'region' => 'Asia', 'currency_code' => 'BHD', 'lat' => 25.9304, 'lng' => 50.6378],
            ['name' => 'Iraq', 'code' => 'IQ', 'region' => 'Asia', 'currency_code' => 'IQD', 'lat' => 33.2232, 'lng' => 43.6793],
            ['name' => 'Iran', 'code' => 'IR', 'region' => 'Asia', 'currency_code' => 'IRR', 'lat' => 32.4279, 'lng' => 53.6880],
            ['name' => 'Israel', 'code' => 'IL', 'region' => 'Asia', 'currency_code' => 'ILS', 'lat' => 31.0461, 'lng' => 34.8516],

            // Africa
            ['name' => 'South Africa', 'code' => 'ZA', 'region' => 'Africa', 'currency_code' => 'ZAR', 'lat' => -30.5595, 'lng' => 24.6727],
            ['name' => 'Egypt', 'code' => 'EG', 'region' => 'Africa', 'currency_code' => 'EGP', 'lat' => 26.8206, 'lng' => 30.8025],
            ['name' => 'Nigeria', 'code' => 'NG', 'region' => 'Africa', 'currency_code' => 'NGN', 'lat' => 9.0820, 'lng' => 8.6753],
            ['name' => 'Kenya', 'code' => 'KE', 'region' => 'Africa', 'currency_code' => 'KES', 'lat' => -0.0236, 'lng' => 37.9062],
            ['name' => 'Morocco', 'code' => 'MA', 'region' => 'Africa', 'currency_code' => 'MAD', 'lat' => 31.7917, 'lng' => -7.0926],
            ['name' => 'Tanzania', 'code' => 'TZ', 'region' => 'Africa', 'currency_code' => 'TZS', 'lat' => -6.3690, 'lng' => 34.8888],
            ['name' => 'Ghana', 'code' => 'GH', 'region' => 'Africa', 'currency_code' => 'GHS', 'lat' => 7.9465, 'lng' => -1.0232],
            ['name' => 'Mozambique', 'code' => 'MZ', 'region' => 'Africa', 'currency_code' => 'MZN', 'lat' => -18.6657, 'lng' => 35.5296],
            ['name' => 'Djibouti', 'code' => 'DJ', 'region' => 'Africa', 'currency_code' => 'DJF', 'lat' => 11.8251, 'lng' => 42.5903],

            // Oceania
            ['name' => 'Australia', 'code' => 'AU', 'region' => 'Oceania', 'currency_code' => 'AUD', 'lat' => -25.2744, 'lng' => 133.7751],
            ['name' => 'New Zealand', 'code' => 'NZ', 'region' => 'Oceania', 'currency_code' => 'NZD', 'lat' => -40.9006, 'lng' => 174.8860],
            ['name' => 'Papua New Guinea', 'code' => 'PG', 'region' => 'Oceania', 'currency_code' => 'PGK', 'lat' => -6.3150, 'lng' => 143.9555],
            ['name' => 'Fiji', 'code' => 'FJ', 'region' => 'Oceania', 'currency_code' => 'FJD', 'lat' => -17.7134, 'lng' => 178.0650],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(
                ['code' => $country['code']],
                array_merge($country, ['updated_at' => now()])
            );
        }

        $this->command->info('Seeded ' . count($countries) . ' fallback countries.');
    }
}
