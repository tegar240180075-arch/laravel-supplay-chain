<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CurrencyRate;
use App\Models\CurrencyRateHistory;
use App\Services\ExchangeRateService;

class CurrencyApiController extends Controller
{
    protected $exchangeService;

    public function __construct(ExchangeRateService $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    public function rates(Request $request)
    {
        $base = $request->query('base', 'USD');
        $rates = CurrencyRate::where('base_currency', $base)->get();
        
        if ($rates->isEmpty()) {
            $apiRates = $this->exchangeService->getRates($base);
            return response()->json($apiRates);
        }
        
        return response()->json($rates);
    }

    public function convert(Request $request)
    {
        $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'amount' => 'required|numeric'
        ]);

        $from = strtoupper($request->from);
        $to = strtoupper($request->to);
        $amount = $request->amount;

        // Simple conversion via USD if direct rate not found
        if ($from === 'USD') {
            $rate = $this->exchangeService->getRateForCurrency($to, 'USD');
            $converted = $amount * $rate;
        } elseif ($to === 'USD') {
            $rate = $this->exchangeService->getRateForCurrency($from, 'USD');
            $converted = $amount / $rate;
        } else {
            $rateFrom = $this->exchangeService->getRateForCurrency($from, 'USD');
            $rateTo = $this->exchangeService->getRateForCurrency($to, 'USD');
            
            $usdAmount = $amount / $rateFrom;
            $converted = $usdAmount * $rateTo;
        }

        return response()->json([
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted_amount' => round($converted, 2)
        ]);
    }

    public function history(Request $request)
    {
        $base = $request->query('base', 'USD');
        $target = $request->query('target', 'EUR');
        
        $history = CurrencyRateHistory::where('base_currency', $base)
                                      ->where('target_currency', $target)
                                      ->orderBy('record_date', 'asc')
                                      ->get();
                                      
        return response()->json($history);
    }
}
