<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\WeatherData;
use App\Models\Country;

class OpenMeteoService
{
    protected $baseUrl;

    public function __construct()
    {
        // Tetap gunakan v1 sebagai base URL, parameter forecast ditambahkan di fungsi
        $this->baseUrl = config('services.open_meteo.base_url', 'https://api.open-meteo.com/v1');
    }

    /**
     * Fetch current weather for a country and store it.
     */
    public function getWeather(Country $country): ?WeatherData
    {
        if (!$country->lat || !$country->lng) {
            return null;
        }

        // Tambahkan retry(3, 500) dan naikkan timeout ke 30s agar tidak cURL 28 error
        $response = Http::timeout(30)->retry(3, 500)->get("{$this->baseUrl}/forecast", [
            'latitude'        => $country->lat,
            'longitude'       => $country->lng,
            'current_weather' => true,
            'hourly'          => 'precipitation,windspeed_10m',
            'forecast_days'   => 1,
            'timezone'        => 'auto',
        ]);

        if ($response->successful()) {
            $data    = $response->json();
            $current = $data['current_weather'];

            // Try to get precipitation from the first hourly data point
            $precipitation = isset($data['hourly']['precipitation'][0])
                ? $data['hourly']['precipitation'][0]
                : 0;

            $weatherData = WeatherData::updateOrCreate(
                ['country_id' => $country->id],
                [
                    'temperature'   => $current['temperature'],
                    'wind_speed'    => $current['windspeed'],
                    'precipitation' => $precipitation,
                    'condition'     => $this->getConditionFromCode($current['weathercode'] ?? 0),
                    'last_updated_at' => now(),
                ]
            );

            return $weatherData;
        }

        return null;
    }

    /**
     * Fetch 7-day hourly forecast for a country.
     * Returns an array of daily summaries.
     */
    public function getForecast(Country $country): array
    {
        if (!$country->lat || !$country->lng) {
            return [];
        }

        $response = Http::timeout(30)->retry(3, 500)->get("{$this->baseUrl}/forecast", [
            'latitude'        => $country->lat,
            'longitude'       => $country->lng,
            'daily'           => 'temperature_2m_max,temperature_2m_min,precipitation_sum,windspeed_10m_max,weathercode',
            'forecast_days'   => 7,
            'timezone'        => 'auto',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $data  = $response->json();
        $daily = $data['daily'] ?? [];

        if (empty($daily['time'])) {
            return [];
        }

        $forecast = [];
        foreach ($daily['time'] as $index => $date) {
            $code = $daily['weathercode'][$index] ?? 0;
            $forecast[] = [
                'date'          => $date,
                'temp_max'      => $daily['temperature_2m_max'][$index] ?? null,
                'temp_min'      => $daily['temperature_2m_min'][$index] ?? null,
                'precipitation' => $daily['precipitation_sum'][$index] ?? 0,
                'wind_speed'    => $daily['windspeed_10m_max'][$index] ?? 0,
                'condition'     => $this->getConditionFromCode($code),
                'weather_code'  => $code,
            ];
        }

        return $forecast;
    }

    /**
     * Map WMO weathercode to a readable condition label.
     */
    public function getConditionFromCode(int $code): string
    {
        if ($code <= 3)                     return 'Clear';
        if ($code >= 45 && $code <= 48)    return 'Fog';
        if ($code >= 51 && $code <= 67)    return 'Rain';
        if ($code >= 71 && $code <= 77)    return 'Snow';
        if ($code >= 80 && $code <= 82)    return 'Rain Showers';
        if ($code >= 95)                   return 'Storm';
        return 'Cloudy';
    }
}
