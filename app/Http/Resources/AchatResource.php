<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AchatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            "id" => $this->id,
            "code" => $this->code,
            "product" => $this->product,
            "fournisseur" => $this->fournisseur,
            "camion" => $this->camion,
            "qte" => $this->qte,
            "paiement_preuve" => $this->paiement_preuve,
            "bordereau" => $this->bordereau,
            "createdBy" => $this->whenLoaded("createdBy"),
            "validatedAt" => $this->validated_at
                ? Carbon::parse($this->validated_at)->locale("fr")->isoFormat("D MMMM YYYY")
                : null,
            "validatedBy" => $this->whenLoaded("validatedBy"),
        ];
    }
}
