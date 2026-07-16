<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $guarded = [];
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function sentiment()
    {
        return $this->hasOne(NewsSentiment::class);
    }
}
