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
        $jsonPath = database_path('seeders/ports.json');
        if (!file_exists($jsonPath)) {
            if (app()->runningInConsole()) {
                echo "  ⚠ File database/seeders/ports.json tidak ditemukan!\n";
            }
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        if (!isset($data['ports']) || !is_array($data['ports'])) {
            if (app()->runningInConsole()) {
                echo "  ⚠ Format file JSON pelabuhan tidak valid!\n";
            }
            return;
        }

        $rawPorts = $data['ports'];

        // Cache all countries for fast lookup
        $countries = Country::all()->keyBy('name');
        // Also map standard alternative country names if any
        $countryNameMapping = [
            "Côte d'Ivoire" => "Ivory Coast",
            "United States" => "United States",
            "United Kingdom" => "United Kingdom",
            "Russia" => "Russian Federation",
            "South Korea" => "Korea, South",
            "North Korea" => "Korea, North",
            "Iran" => "Iran (Islamic Republic of)",
            "Syria" => "Syrian Arab Republic",
            "Venezuela" => "Venezuela (Bolivarian Republic of)",
            "Vietnam" => "Viet Nam",
            "Democratic Republic of the Congo" => "Congo (Democratic Republic of the)",
            "Congo" => "Congo (Republic of the)",
            "Tanzania" => "Tanzania, United Republic of",
            "Moldova" => "Moldova (Republic of)",
        ];

        // Fetch codes to map by code as well
        $countriesByCode = Country::all()->keyBy('code');

        $inserted = 0;
        $limit = 300; // Target limit requested by user

        // Sort raw ports so that Major ports come first, then others
        usort($rawPorts, function($a, $b) {
            $sizeOrder = ['Major' => 1, 'Medium' => 2, 'Large' => 2, 'Minor' => 3, 'Small' => 4, 'Very Small' => 5];
            $orderA = $sizeOrder[$a['port_size'] ?? ''] ?? 99;
            $orderB = $sizeOrder[$b['port_size'] ?? ''] ?? 99;
            return $orderA - $orderB;
        });

        $portsToInsert = [];

        foreach ($rawPorts as $portData) {
            if ($inserted >= $limit) {
                break;
            }

            $countryName = $portData['country'] ?? '';
            $country = $countries->get($countryName);

            if (!$country && isset($countryNameMapping[$countryName])) {
                $mappedName = $countryNameMapping[$countryName];
                $country = $countries->get($mappedName);
            }

            if (!$country) {
                $country = $countriesByCode->get($countryName);
            }

            if (!$country) {
                foreach ($countries as $cName => $cObj) {
                    if (stripos($cName, $countryName) !== false || stripos($countryName, $cName) !== false) {
                        $country = $cObj;
                        break;
                    }
                }
            }

            if (!$country) {
                continue;
            }

            // Map Size to match application's expected sizes (Small, Medium, Large, Very Large)
            $size = 'Medium';
            $rawSize = $portData['port_size'] ?? '';
            if ($rawSize === 'Major') $size = 'Very Large';
            elseif ($rawSize === 'Minor') $size = 'Small';
            elseif ($rawSize === 'Small') $size = 'Small';
            elseif ($rawSize === 'Very Small') $size = 'Small';

            $portName = empty($portData['point_of_interest']) ? $portData['wpi_port_name'] : $portData['point_of_interest'];
            $portName = ucwords(strtolower($portName));
            if (stripos($portName, 'Port Of') === false && stripos($portName, 'Port') === false) {
                $portName = 'Port of ' . $portName;
            }

            // Skip if coordinates are null
            if (is_null($portData['latitude']) || is_null($portData['longitude'])) {
                continue;
            }

            $portsToInsert[] = [
                'name' => $portName,
                'country_id' => $country->id,
                'lat' => $portData['latitude'],
                'lng' => $portData['longitude'],
                'type' => 'Seaport',
                'size' => $size,
            ];

            $inserted++;
        }

        // Insert/Update database in chunks for efficiency
        $chunkSize = 50;
        foreach (array_chunk($portsToInsert, $chunkSize) as $chunk) {
            foreach ($chunk as $port) {
                DB::table('ports')->updateOrInsert(
                    [
                        'name' => $port['name'],
                        'country_id' => $port['country_id']
                    ],
                    [
                        'lat' => $port['lat'],
                        'lng' => $port['lng'],
                        'type' => $port['type'],
                        'size' => $port['size'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        if (app()->runningInConsole()) {
            echo "  ✅ Ports seeded: " . count($portsToInsert) . " inserted/updated\n";
        }
    }
}
