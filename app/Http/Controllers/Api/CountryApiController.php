<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\CountryEconomicData;

class CountryApiController extends Controller
{
    public function index()
    {
        return response()->json(Country::all());
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $countries = Country::where('name', 'like', "%{$q}%")
                            ->orWhere('code', 'like', "%{$q}%")
                            ->get();
        return response()->json($countries);
    }

    public function show($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        return response()->json($country);
    }

    public function economicData($code)
    {
        $country = Country::where('code', $code)->firstOrFail();
        $data = CountryEconomicData::where('country_id', $country->id)->orderBy('year', 'desc')->get();
        return response()->json($data);
    }
}
