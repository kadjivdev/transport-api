<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            "reference" => $this->reference,
            "montant_total" => $this->montant_total,
            "contrat" => $this->contrat,
            "commentaire" => $this->commentaire,

            // relations
            "client" => $this->client,
            "type" => $this->type,
            "details" => $this->details->load("camion"),
            "createdAt" => $this->created_at,
            "createdBy" => $this->createdBy,
            "validatedBy" => $this->validatedBy,
            "validatedAt" => $this->validated_at,
            "createdAt" => $this->created_at
        ];
    }
}
