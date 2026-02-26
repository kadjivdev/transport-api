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
            "montant" => number_format($this->total_amount, 2, ",", ''),
            "regle" => number_format($this->reglements()->whereNotNull("validated_at")->sum("montant"), 2, ",", ''),
            "reste" => number_format($this->reste_a_regler, 2, ",", ''),
            "_reste" => $this->reste_a_regler,
            "contrat" => $this->contrat,
            "commentaire" => $this->commentaire,

            // relations
            "client" => $this->client,
            "date" => $this->date_location,
            "date_location" => $this->date_location ? Carbon::parse($this->date_location)->locale("fr")->isoFormat("D MMMM YYYY") : null,
            "type" => $this->type,
            "details" => $this->details->load("camion"),
            "createdAt" => Carbon::parse($this->created_at)->locale("fr")->isoFormat("D MMMM YYYY"),
            "createdBy" => $this->createdBy,
            "validatedBy" => $this->validatedBy,
            "validatedAt" => $this->validated_at ? Carbon::parse($this->validated_at)->locale("fr")->isoFormat("D MMMM YYYY") : null,
        ];
    }
}
