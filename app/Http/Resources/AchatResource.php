<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class AchatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ventes = $this->ventes->whereNotNull("validated_at");
        $vendue = $ventes->sum(fn ($vente) => $vente->amount);
        $reste = $this->montant - $vendue;

        return [
            "id" => $this->id,
            "code" => $this->code,
            "product" => $this->product,
            "fournisseur" => $this->fournisseur,
            "camion" => $this->camion,
            "qte" => $this->qte,
            "_unite_price" => (int) $this->unite_price,
            "unite_price" => number_format($this->unite_price, 2, ',', ' '),
            "_montant" => $this->montant,
            "montant" => number_format($this->montant, 2, ',', ' '),
            "_vendue" => $vendue,
            "_reste" => $reste,
            "reste" => number_format($reste, 2, ',', ' '),
            "vendue" => number_format($vendue, 2, ',', ' '),
            "paiement_preuve" => $this->paiement_preuve,
            "bordereau" => $this->bordereau,
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
