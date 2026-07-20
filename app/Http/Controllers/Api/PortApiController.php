<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Port;
use App\Models\Country;

class PortApiController extends Controller
{
    public function index()
    {
        return response()->json(Port::with(['country', 'country.riskScore'])->get());
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $ports = Port::with(['country', 'country.riskScore'])
                    ->where('name', 'like', "%{$q}%")
                    ->orWhereHas('country', function($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                              ->orWhere('code', 'like', "%{$q}%");
                    })
                    ->get();
        return response()->json($ports);
    }

    public function show($id)
    {
        $port = Port::with('country')->findOrFail($id);
        return response()->json($port);
    }

    public function byCountry($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $ports = Port::where('country_id', $country->id)->get();
        return response()->json($ports);
    }
}
