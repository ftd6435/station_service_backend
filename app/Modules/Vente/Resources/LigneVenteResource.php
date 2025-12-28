<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneVenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // =========================
            // IDENTITÉ
            // =========================
            'id'          => $this->id,
            'status'      => $this->status,

            // =========================
            // DONNÉES DE VENTE
            // =========================
            'index_debut' => $this->index_debut,
            'index_fin'   => $this->index_fin,
            'qte_vendu'   => $this->qte_vendu,

            // =========================
            // CUVE (ex-produit)
            // =========================
            'cuve' => $this->whenLoaded('cuve', fn () => [
                'id'      => $this->cuve->id,
                'libelle' => $this->cuve->libelle,
            ]),

            // =========================
            // STATION
            // =========================
            'station' => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            // =========================
            // MÉTADONNÉES
            // =========================
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
