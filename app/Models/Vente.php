<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vente extends Model
{
    use SoftDeletes;
    protected $appends = ["amount", "reste"];

    protected $fillable = [
        "achat_id",
        "client_id",
        "camion_id",
        "qte",
        "unite_price",
        "lieu_dechargement",
        "observation",
        "document",
        "validated_at",
        "validated_by",
    ];

    /**get amount */
    function getAmountAttribute()
    {
        return $this->qte * $this->unite_price;
    }

    /**get reste to reglement */
    function getResteAttribute()
    {
        return $this->amount - $this->reglements()->sum("montant");
    }

    /**achat */
    function achat(): BelongsTo
    {
        return $this->belongsTo(Achat::class);
    }

    /**client */
    function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**camion */
    function camion(): BelongsTo
    {
        return $this->belongsTo(Camion::class);
    }

    /**
     * Reglements
     */
    function reglements(): HasMany
    {
        return $this->hasMany(Reglement::class, "vente_id");
    }

    /**created by */
    function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**validated by */
    function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    /**HandleDocument */
    function handleDocumentUploading()
    {
        $documentPath = $this->document;
        $request = request();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('preuves'), $name);
            $documentPath = asset('preuves/' . $name);
        }

        return $documentPath;
    }

    /**Boot */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }

            // Handle document uploading
            $model->document = $model->handleDocumentUploading();
        });

        static::created(function ($model) {
            $model->code = "VEN" . date("ymd") . $model->id . "TE";
            $model->saveQuietly();
        });

        static::updating(function ($model) {
            // Handle document uploading
            $model->document = $model->handleDocumentUploading();
            $model->saveQuietly();
        });
    }
}
