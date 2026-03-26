<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ClientAccompteResource extends JsonResource
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
            "montant" => number_format($this->montant, 2, ",", " "),
            "_montant" => $this->montant,
            "preuve" => $this->preuve,
            "commentaire" => $this->commentaire,

            // relations
            "client" => $this->client,
            "createdBy" => $this->createdBy,
            "validatedBy" => $this->validatedBy,

            "createdAt" => Carbon::parse($this->created_at)->locale("fr")->isoFormat("D MMMM YYYY"),
            "validatedAt" => $this->validated_at ? Carbon::parse($this->validated_at)->locale("fr")->isoFormat("D MMMM YYYY") : null,
        ];
    }
}
