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
        $scores = RiskScore::with('country')->get();
        return response()->json($scores);
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
