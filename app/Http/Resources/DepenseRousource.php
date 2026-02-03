<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepenseRousource extends JsonResource
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
            "montant" => $this->montant,
            "preuve" => $this->preuve,
            "commentaire" => $this->commentaire,

            // relations
            "location" => $this->location,
            "createdAt" => $this->created_at,
            "createdBy" => $this->createdBy,
            "validatedBy" => $this->validatedBy,
            "validatedAt" => $this->validated_at,
            "createdAt" => $this->created_at
        ];
    }
}
