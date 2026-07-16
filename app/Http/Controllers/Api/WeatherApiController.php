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

    public function current($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $weather = WeatherData::where('country_id', $country->id)->first();
        
        if (!$weather || $weather->last_updated_at < now()->subHours(1)) {
            $weather = $this->weatherService->getWeather($country);
        }
        
        return response()->json($weather);
    }

    public function forecast($code)
    {
        // Simple implementation - in real life, would return 7 day forecast from OpenMeteo
        $country = Country::where('code', $code)->firstOrFail();
        $weather = WeatherData::where('country_id', $country->id)->first();
        
        return response()->json([
            'current' => $weather,
            'forecast_note' => 'Forecast data would be implemented here from Open-Meteo'
        ]);
    }
}
