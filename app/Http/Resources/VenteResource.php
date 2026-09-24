<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class VenteResource extends JsonResource
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
            "code" => $this->code,
            "achat" => $this->whenLoaded("achat"),
            "client" => $this->whenLoaded("client"),
            "camion" => $this->whenLoaded("camion"),
            "qte" => $this->qte,
            "unitePrice" => $this->unite_price,
            "_amount" => $this->amount,
            "amount" => number_format($this->amount, 2, ",", " "),
            "reste" => number_format($this->reste, 2, ",", " "),
            "lieuDechargement" => $this->lieu_dechargement,
            "observation" => $this->observation,
            "document" => $this->document,
            "createdBy" => $this->whenLoaded("createdBy"),
            "validatedAt" => $this->validated_at
                ? Carbon::parse($this->validated_at)->locale("fr")->isoFormat("D MMMM YYYY")
                : null,
            "createdAt" => $this->created_at
                ? Carbon::parse($this->created_at)->locale("fr")->isoFormat("D MMMM YYYY")
                : null,
            "validatedBy" => $this->whenLoaded("validatedBy"),
        ];
    }
}
