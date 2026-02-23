<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LocationType extends Model
{
    protected $fillable = [
        "libelle",
        "description",
        "price"
    ];

    protected $casts = [
        "price" => "decimal:2"
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->locale("fr")->isoFormat("D MMMM YYYY");
    }
}
