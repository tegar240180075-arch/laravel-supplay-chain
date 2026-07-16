<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'region' => 'Americas', 'currency_code' => 'USD', 'lat' => 37.0902, 'lng' => -95.7129],
            ['name' => 'China', 'code' => 'CN', 'region' => 'Asia', 'currency_code' => 'CNY', 'lat' => 35.8617, 'lng' => 104.1954],
            ['name' => 'Japan', 'code' => 'JP', 'region' => 'Asia', 'currency_code' => 'JPY', 'lat' => 36.2048, 'lng' => 138.2529],
            ['name' => 'Germany', 'code' => 'DE', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 51.1657, 'lng' => 10.4515],
            ['name' => 'United Kingdom', 'code' => 'GB', 'region' => 'Europe', 'currency_code' => 'GBP', 'lat' => 55.3781, 'lng' => -3.4360],
            ['name' => 'India', 'code' => 'IN', 'region' => 'Asia', 'currency_code' => 'INR', 'lat' => 20.5937, 'lng' => 78.9629],
            ['name' => 'France', 'code' => 'FR', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 46.2276, 'lng' => 2.2137],
            ['name' => 'Italy', 'code' => 'IT', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 41.8719, 'lng' => 12.5674],
            ['name' => 'Canada', 'code' => 'CA', 'region' => 'Americas', 'currency_code' => 'CAD', 'lat' => 56.1304, 'lng' => -106.3468],
            ['name' => 'South Korea', 'code' => 'KR', 'region' => 'Asia', 'currency_code' => 'KRW', 'lat' => 35.9078, 'lng' => 127.7669],
            ['name' => 'Russia', 'code' => 'RU', 'region' => 'Europe/Asia', 'currency_code' => 'RUB', 'lat' => 61.5240, 'lng' => 105.3188],
            ['name' => 'Brazil', 'code' => 'BR', 'region' => 'Americas', 'currency_code' => 'BRL', 'lat' => -14.2350, 'lng' => -51.9253],
            ['name' => 'Australia', 'code' => 'AU', 'region' => 'Oceania', 'currency_code' => 'AUD', 'lat' => -25.2744, 'lng' => 133.7751],
            ['name' => 'Spain', 'code' => 'ES', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 40.4637, 'lng' => -3.7492],
            ['name' => 'Indonesia', 'code' => 'ID', 'region' => 'Asia', 'currency_code' => 'IDR', 'lat' => -0.7893, 'lng' => 113.9213],
            ['name' => 'Netherlands', 'code' => 'NL', 'region' => 'Europe', 'currency_code' => 'EUR', 'lat' => 52.1326, 'lng' => 5.2913],
            ['name' => 'Saudi Arabia', 'code' => 'SA', 'region' => 'Middle East', 'currency_code' => 'SAR', 'lat' => 23.8859, 'lng' => 45.0792],
            ['name' => 'Turkey', 'code' => 'TR', 'region' => 'Europe/Asia', 'currency_code' => 'TRY', 'lat' => 38.9637, 'lng' => 35.2433],
            ['name' => 'Switzerland', 'code' => 'CH', 'region' => 'Europe', 'currency_code' => 'CHF', 'lat' => 46.8182, 'lng' => 8.2275],
            ['name' => 'Taiwan', 'code' => 'TW', 'region' => 'Asia', 'currency_code' => 'TWD', 'lat' => 23.6978, 'lng' => 120.9605]
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(['code' => $country['code']], $country);
        }
    }
}
