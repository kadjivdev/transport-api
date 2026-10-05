<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ReglementVenteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "reference" => $this->code,
            "location" => $this->whenLoaded("location"),
            "montant" => $this->montant,
            "commentaire" => $this->commentaire,
            "preuve" => $this->preuve,
            "createdBy" => $this->createdBy,
            "validatedAt" => $this->validated_at
                ? Carbon::parse($this->validated_at)->locale("fr")->isoFormat("D MMMM YYYY")
                : null,
            "createdAt" => $this->created_at
                ? Carbon::parse($this->created_at)->locale("fr")->isoFormat("D MMMM YYYY")
                : null,
            "validatedBy" => $this->validatedBy,
        ];
    }
}
