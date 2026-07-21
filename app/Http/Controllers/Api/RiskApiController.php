<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskScore;
use App\Models\Country;
use App\Models\RiskScoreHistory;
use App\Services\RiskScoringService;

class RiskApiController extends Controller
{
    protected $riskService;

    public function __construct(RiskScoringService $riskService)
    {
        $this->riskService = $riskService;
    }

    public function index()
    {
        // Get all countries and their risk scores
        $countries = Country::all();
        $riskScores = RiskScore::all()->keyBy('country_id');
        
        $results = [];
        foreach ($countries as $country) {
            $score = $riskScores->get($country->id);
            if ($score) {
                // Attach country object for the frontend
                $score->country = $country;
                $results[] = $score;
            } else {
                // Return default 0 structure if risk not calculated yet (due to API limits etc)
                $results[] = [
                    'country' => $country,
                    'total_score' => 0.0,
                    'weather_risk' => 0.0,
                    'inflation_risk' => 0.0,
                    'news_risk' => 0.0,
                    'currency_risk' => 0.0,
                    'risk_level' => 'Low'
                ];
            }
        }
        
        return response()->json($results);
    }

    public function show($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $score = RiskScore::where('country_id', $country->id)->first();
        return response()->json($score);
    }

    public function history($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $history = RiskScoreHistory::where('country_id', $country->id)->orderBy('record_date', 'asc')->get();
        return response()->json($history);
    }

    public function calculate($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $score = $this->riskService->calculateRiskForCountry($country);
        return response()->json(['success' => true, 'data' => $score]);
    }

    public function compare(Request $request)
    {
        $codes = explode(',', $request->query('countries', ''));
        $countries = Country::whereIn('code', $codes)->get();
        
        $results = [];
        foreach ($countries as $country) {
            $score = RiskScore::where('country_id', $country->id)->first();
            $results[] = [
                'country' => $country,
                'risk' => $score
            ];
        }
        
        return response()->json($results);
    }
}
