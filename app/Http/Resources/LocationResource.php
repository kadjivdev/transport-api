<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            "date_location" => $this->date_location?Carbon::parse($this->date_location)->locale("fr")->isoFormat("D MMMM YYYY"):null,
            "type" => $this->type,
            "details" => $this->details->load("camion"),
            "createdAt" => Carbon::parse($this->created_at)->locale("fr")->isoFormat("D MMMM YYYY"),
            "createdBy" => $this->createdBy,
            "validatedBy" => $this->validatedBy,
            "validatedAt" => $this->validated_at?Carbon::parse($this->validated_at)->locale("fr")->isoFormat("D MMMM YYYY"):null,
        ];
    }
}
