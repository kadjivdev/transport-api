<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Tva extends Model
{
    use SoftDeletes;

    protected $fillable = ["reference", "location_id", "montant", "preuve", "commentaire", "created_by", "validated_by", "validated_at"];

    // les relations
    function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, "location_id");
    }

    function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    // handle  preuve file
    function getPreuveUrl()
    {
        Log::debug("getPreuveUrl is called ...");

        $fileUrl = null;
        if (request()->hasFile("preuve")) {
            $file = request()->file("preuve");
            $name = time() . "_" . $file->getClientOriginalName();
            $file->move("preuves", $name);
            $fileUrl = asset("/preuves/" . $name);
        }

        return $fileUrl;
    }

    // handle reference
    function generateReference()
    {
        Log::debug("generateReference is called...");

        $date = Carbon::now()->format("Y-m-d");

        $prevRef = "REF{$this->id}-TVA-{$date}";
        $prevRefExist = self::firstWhere("reference", $prevRef);

        if ($prevRefExist) {
            $idPlusOne = $this->id + 1;
            $prevRef = "REFAC{$idPlusOne}-{$date}";
        }
        return $prevRef;
    }

    protected static function boot()
    {
        parent::boot();

        // creating
        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        // created
        static::created(function ($model) {
            $model->reference = $model->generateReference();
            $model->preuve = $model->getPreuveUrl();
            $model->saveQuietly(); // VERY IMPORTANT
        });

        // updating
        static::updating(function ($model) {
            if (request()->hasFile("preuve")) {
                $model->preuve = $model->getPreuveUrl();
                $model->saveQuietly(); // VERY IMPORTANT
            } else {
                // retrait de la clé *preuve* des données
                unset($model->preuve);
            }
        });
    }
}
