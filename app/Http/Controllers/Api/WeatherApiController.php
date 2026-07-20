<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\WeatherData;
use App\Services\OpenMeteoService;

class WeatherApiController extends Controller
{
    protected $weatherService;

    public function __construct(OpenMeteoService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get current weather for a country (fetches fresh data if older than 1 hour).
     */
    public function current($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $weather = WeatherData::where('country_id', $country->id)->first();

        // Refresh if missing or stale (older than 1 hour)
        if (!$weather || $weather->last_updated_at < now()->subHour()) {
            $weather = $this->weatherService->getWeather($country);
        }

        return response()->json($weather);
    }

    /**
     * Get 7-day real forecast from Open-Meteo for a country.
     */
    public function forecast($code)
    {
        $country = Country::where('code', $code)->firstOrFail();

        // 7-day daily forecast from Open-Meteo API (no API key needed)
        $forecast = $this->weatherService->getForecast($country);

        // Also include current cached weather for reference
        $current = WeatherData::where('country_id', $country->id)->first();

        return response()->json([
            'country'  => $country->only(['name', 'code', 'lat', 'lng']),
            'current'  => $current,
            'forecast' => $forecast,
        ]);
    }
}
