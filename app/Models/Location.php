<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "reference",
        "client_id",
        "location_type_id",
        "type_location_price",
        "montant_total",
        "date_location",
        "contrat",
        "commentaire",
        "created_by",
        "validated_by",
        "validated_at"
    ];

    // casts
    protected $casts = [
        "client_id" => "integer",
        "location_type_id" => "integer",
        "type_location_price" => "decimal:2",
        "montant_total" => "decimal:2",
        "date_location" => "datetime",

        "created_by" => "integer",
        "validated_by" => "integer",
        "validated_at" => "datetime",
    ];

    // les relations
    function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    function type(): BelongsTo
    {
        return $this->belongsTo(LocationType::class, "client_id");
    }

    function details(): HasMany
    {
        return $this->hasMany(LocationDetail::class, "location_id");
    }

    function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    // handle  contrat file
    function getContratUrl()
    {
        Log::info("getContratUrl is called ...");
        $fileUrl = null;
        if (request()->hasFile("contrat")) {
            $file = request()->file("contrat");
            $name = time() . "_" . $file->getClientOriginalName();
            $file->move("contrats", $name);
            $fileUrl = asset("/contrats/" . $name);
        }

        return $fileUrl;
    }

    // handle total amount
    function getTotalAmount()
    {
        Log::info("getTotalAmount is Called.....");
        return $this->details()->sum("price");
    }

    // handle reference
    function generateReference()
    {
        Log::info("generateReference is Called.....");

        $date = Carbon::now()->format("Y-m-d");

        $prevRef = "REFL{$this->id}-{$date}";
        $prevRefExist = self::firstWhere("reference", $prevRef);

        if ($prevRefExist) {
            $idPlusOne = $this->id + 1;
            $prevRef = "REFL{$idPlusOne}-{$date}";
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
            $model->montant_total = $model->getTotalAmount();
            $model->contrat = $model->getContratUrl();
            $model->saveQuietly(); // VERY IMPORTANT
        });

        // updating
        static::updated(function ($model) {
            if (request()->hasFile("contrat")) {
                $model->contrat = $model->getContratUrl();
                $model->saveQuietly(); // VERY IMPORTANT
            }
        });
    }
}
