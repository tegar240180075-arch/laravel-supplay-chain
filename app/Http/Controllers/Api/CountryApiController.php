<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\CountryEconomicData;
use App\Services\RestCountriesService;
use App\Services\WorldBankService;

class CountryApiController extends Controller
{
    public function index(RestCountriesService $restCountries)
    {
        // Auto-sync if the database is empty (e.g., fresh deployment on Railway)
        if (Country::count() === 0) {
            $countries = $restCountries->getAllCountries();
            foreach ($countries as $c) {
                Country::firstOrCreate(['code' => $c['code']], [
                    'name' => $c['name'],
                    'region' => $c['region'],
                    'currency_code' => $c['currency_code'],
                    'lat' => $c['lat'],
                    'lng' => $c['lng'],
                ]);
            }
        }

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

    public function show($code, RestCountriesService $restCountries)
    {
        $country = Country::where('code', $code)->firstOrFail();

        // Fetch real-time data from RestCountries API (cache for 24 hours to prevent rate limits)
        $apiData = \Illuminate\Support\Facades\Cache::remember("country_api_{$code}", 86400, function () use ($restCountries, $code) {
            return $restCountries->getCountryInfo($code);
        });

        if ($apiData) {
            $country->update([
                'name' => $apiData['name'],
                'region' => $apiData['region'],
                'currency_code' => $apiData['currency_code'],
                'lat' => $apiData['lat'],
                'lng' => $apiData['lng'],
            ]);
        }

        return response()->json($country);
    }

    public function economicData($code, WorldBankService $worldBank)
    {
        $country = Country::where('code', $code)->firstOrFail();

        // Fetch real-time economic data from World Bank API and update DB (cache for 24 hours)
        \Illuminate\Support\Facades\Cache::remember("economic_data_{$code}", 86400, function () use ($worldBank, $country) {
            $worldBank->getEconomicData($country);
            return true; // Return a truthy value so it gets cached
        });

        $data = CountryEconomicData::where('country_id', $country->id)->orderBy('year', 'desc')->get();
        return response()->json($data);
    }

    public function economicRanking()
    {
        // Get the latest year's economic data for each country
        $latestRecords = CountryEconomicData::select('country_economic_data.*')
            ->join(\DB::raw('(select country_id, max(year) as max_year from country_economic_data group by country_id) latest'), function($join) {
                $join->on('country_economic_data.country_id', '=', 'latest.country_id')
                     ->on('country_economic_data.year', '=', 'latest.max_year');
            })
            ->get()
            ->keyBy('country_id');

        $countries = Country::all();
        $results = [];

        foreach ($countries as $country) {
            $econ = $latestRecords->get($country->id);
            $results[] = [
                'country' => $country,
                'year' => $econ ? $econ->year : null,
                'gdp_billions' => $econ ? (float)$econ->gdp_billions : null,
                'inflation_rate' => $econ ? (float)$econ->inflation_rate : null,
                'population' => $econ ? (int)$econ->population : null,
                'exports_billions' => $econ ? (float)$econ->exports_billions : null,
                'imports_billions' => $econ ? (float)$econ->imports_billions : null,
            ];
        }

        return response()->json($results);
    }
}
