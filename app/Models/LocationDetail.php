<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "location_id",
        "camion_id",
        "price"
    ];

    // casts
    protected $casts = [
        "location_id" => "integer",
        "camion_id" => "integer",
        "price" => "decimal:2"
    ];

    // les relations
    function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, "location_id");
    }

    function camion(): BelongsTo
    {
        return $this->belongsTo(Camion::class, "camion_id");
    }

    // rules & messages
    public static function rules(): array
    {
        return [
            'location_id' => 'required|integer|exists:locations,id',
            'camion_id'   => 'required|integer|exists:camions,id',
            'price'       => 'required|decimal:15,2',
        ];
    }

    public static function messages(): array
    {
        return [
            // location_id
            'location_id.required' => 'La location est obligatoire.',
            'location_id.integer'  => 'La location doit être un identifiant valide.',
            'location_id.exists'   => 'La location sélectionnée est introuvable.',

            // camion_id
            'camion_id.required' => 'Le camion est obligatoire.',
            'camion_id.integer'  => 'Le camion doit être un identifiant valide.',
            'camion_id.exists'   => 'Le camion sélectionné est introuvable.',

            // price
            'price.required' => 'Le prix est obligatoire.',
            'price.decimal'  => 'Le prix doit être un nombre décimal valide.',
        ];
    }
}
