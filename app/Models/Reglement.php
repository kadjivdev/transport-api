<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reglement extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "vente_id",
        "montant",
        "observation",
        "document",
        "created_by",
        "validated_by"
    ];

    protected $casts = [
        "vente_id" => "integer",
        "montant" => "decimal:2",
    ];

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
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

    protected function handleDocumentUploading(): ?string
    {
        if (!request()->hasFile("document")) {
            return $this->document;
        }

        $file = request()->file("document");
        $name = time() . "_" . $file->getClientOriginalName();
        $file->move(public_path("preuves"), $name);

        return asset("preuves/" . $name);
    }

    protected static function booted(): void
    {
        static::creating(function ($model): void {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            $model->document = $model->handleDocumentUploading();
        });

        static::created(function ($model) {
            $model->code = "RGL" . date("ymd") . $model->id . "T";
            $model->saveQuietly();
        });

        static::updating(function ($model): void {
            if (request()->hasFile("document")) {
                $model->document = $model->handleDocumentUploading();
            } else {
                unset($model->document);
            }
        });
    }
}
