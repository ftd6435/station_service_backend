<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneVenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'index_debut' => $this->index_debut,
            'index_fin'   => $this->index_fin,
            'qte_vendu'   => $this->qte_vendu,
            'status'      => $this->status,

            'produit' => $this->whenLoaded('produit', fn () => [
                'id'      => $this->produit->id,
                'libelle' => $this->produit->libelle,
            ]),

            'station' => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
