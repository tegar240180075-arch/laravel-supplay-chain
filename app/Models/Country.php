<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function riskScore()
    {
        return $this->hasOne(RiskScore::class);
    }

    public function economicData()
    {
        return $this->hasMany(CountryEconomicData::class)->orderBy('year', 'desc');
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function weatherData()
    {
        return $this->hasOne(WeatherData::class);
    }

    public function newsCaches()
    {
        return $this->hasMany(NewsCache::class);
    }
}

