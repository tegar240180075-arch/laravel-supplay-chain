<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    /**
     * Get the ports that belong to this country.
     */
    public function ports()
    {
        return $this->hasMany(Port::class);
    }
}
