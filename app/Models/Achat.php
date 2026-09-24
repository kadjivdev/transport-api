<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Achat extends Model
{
    use softDeletes;
    protected $fillable = [
        "code",
        "product_id",
        "fournisseur_id",
        "camion_id",
        "qte",
        "paiement_preuve",
        "bordereau",
        "validated_at",

        "validated_by",
        "created_by"
    ];

    /**Product */
    function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**camion */
    function camion(): BelongsTo
    {
        return $this->belongsTo(Camion::class);
    }

    /**founrisseur */
    function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
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

    /**HandlePreuve */
    function handlePreuveUploading()
    {
        $preuvePath = $this->paiement_preuve;
        $request = request();

        if ($request->hasFile('paiement_preuve')) {
            $file = $request->file('paiement_preuve');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('preuves'), $name);
            $preuvePath = asset('preuves/' . $name);
        }

        return $preuvePath;
    }

    /**HandleBordereau */
    function handleBordereauUploading()
    {
        $bordereauPath = $this->bordereau;
        $request = request();

        if ($request->hasFile('bordereau')) {
            $file = $request->file('bordereau');
            $name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('bordereaux'), $name);
            $bordereauPath = asset('bordereaux/' . $name);
        }

        return $bordereauPath;
    }

    /**Boot */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }

            // Handle document uploading
            $model->paiement_preuve = $model->handlePreuveUploading();
            $model->bordereau = $model->handleBordereauUploading();
        });

        static::created(function ($model) {
            $model->code = "ACH" . date("ymd") . $model->id . "AT";
            $model->saveQuietly();
        });

        static::updating(function ($model) {
            // Handle document uploading
            $model->paiement_preuve = $model->handlePreuveUploading();
            $model->bordereau = $model->handleBordereauUploading();
        });

    }
}
