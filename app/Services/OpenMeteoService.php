<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\WeatherData;
use App\Models\Country;

class OpenMeteoService
{
    protected $baseUrl = 'https://api.open-meteo.com/v1';

    public function getWeather(Country $country)
    {
        if (!$country->lat || !$country->lng) return null;

        $response = Http::get("{$this->baseUrl}/forecast", [
            'latitude' => $country->lat,
            'longitude' => $country->lng,
            'current_weather' => true,
            'timezone' => 'auto'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $current = $data['current_weather'];
            
            // Save to cache table
            $weatherData = WeatherData::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'temperature' => $current['temperature'],
                    'wind_speed' => $current['windspeed'],
                    'condition' => $this->getConditionFromCode($current['weathercode'] ?? 0),
                    'last_updated_at' => now(),
                ]
            );

            return $weatherData;
        }

        return null;
    }

    protected function getConditionFromCode($code)
    {
        // Simple mapping based on WMO Weather interpretation codes
        if ($code <= 3) return 'Clear';
        if ($code >= 45 && $code <= 48) return 'Fog';
        if ($code >= 51 && $code <= 67) return 'Rain';
        if ($code >= 71 && $code <= 77) return 'Snow';
        if ($code >= 80 && $code <= 82) return 'Rain Showers';
        if ($code >= 95) return 'Storm';
        return 'Unknown';
    }
}
