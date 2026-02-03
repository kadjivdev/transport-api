<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Camion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "libelle",
        "immatriculation"
    ];
}
