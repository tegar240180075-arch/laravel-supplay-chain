<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Country;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            ['name' => 'Port of Los Angeles', 'country_code' => 'US', 'lat' => 33.7288, 'lng' => -118.2620, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Long Beach', 'country_code' => 'US', 'lat' => 33.7542, 'lng' => -118.2165, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Shanghai', 'country_code' => 'CN', 'lat' => 30.6225, 'lng' => 122.0625, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Shenzhen', 'country_code' => 'CN', 'lat' => 22.5036, 'lng' => 113.8821, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Tokyo', 'country_code' => 'JP', 'lat' => 35.6175, 'lng' => 139.7828, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Hamburg', 'country_code' => 'DE', 'lat' => 53.5350, 'lng' => 9.9723, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Felixstowe', 'country_code' => 'GB', 'lat' => 51.9567, 'lng' => 1.3122, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Mumbai', 'country_code' => 'IN', 'lat' => 18.9438, 'lng' => 72.8430, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Le Havre', 'country_code' => 'FR', 'lat' => 49.4862, 'lng' => 0.1197, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Genoa', 'country_code' => 'IT', 'lat' => 44.4071, 'lng' => 8.9189, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Vancouver', 'country_code' => 'CA', 'lat' => 49.2882, 'lng' => -123.1118, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Busan', 'country_code' => 'KR', 'lat' => 35.1017, 'lng' => 129.0345, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Novorossiysk', 'country_code' => 'RU', 'lat' => 44.7239, 'lng' => 37.7818, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Santos', 'country_code' => 'BR', 'lat' => -23.9575, 'lng' => -46.3039, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Melbourne', 'country_code' => 'AU', 'lat' => -37.8202, 'lng' => 144.9142, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Valencia', 'country_code' => 'ES', 'lat' => 39.4444, 'lng' => -0.3168, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Tanjung Priok', 'country_code' => 'ID', 'lat' => -6.1042, 'lng' => 106.8796, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Rotterdam', 'country_code' => 'NL', 'lat' => 51.9486, 'lng' => 4.1439, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Jeddah Islamic Port', 'country_code' => 'SA', 'lat' => 21.4697, 'lng' => 39.1672, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Ambarli Port', 'country_code' => 'TR', 'lat' => 40.9632, 'lng' => 28.6795, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Kaohsiung', 'country_code' => 'TW', 'lat' => 22.5694, 'lng' => 120.3013, 'type' => 'Seaport', 'size' => 'Large'],
        ];

        foreach ($ports as $portData) {
            $country = Country::where('code', $portData['country_code'])->first();
            if ($country) {
                DB::table('ports')->insert([
                    'name' => $portData['name'],
                    'country_id' => $country->id,
                    'lat' => $portData['lat'],
                    'lng' => $portData['lng'],
                    'type' => $portData['type'],
                    'size' => $portData['size'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
