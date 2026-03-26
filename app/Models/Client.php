<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $appends = ["solde"];

    protected $fillable = [
        "nom",
        "prenom",
        "phone",
        "ifu"
    ];

    // acomptes
    function acomptes(): HasMany
    {
        return $this->hasMany(ClientAcompte::class, "client_id");
    }

    // locations
    function locations(): HasMany
    {
        return $this->hasMany(Location::class, "client_id");
    }

    // le solde du client
    function getSoldeAttribute() 
    {
        return
            // approvisionnement
            $this->acomptes()
            ->whereNotNull("validated_by")
            ->sum("montant")
            -
            // reglements
            $this->locations()
            ->whereNotNull("validated_by") // locations validées
            ->with(['reglements' => function ($query) {
                $query->whereNotNull('validated_by'); // on filtre en meme temps les reglements validés
            }])
            ->get()
            ->flatMap->reglements // on recupère les reglements
            ->sum("montant");
    }
}
