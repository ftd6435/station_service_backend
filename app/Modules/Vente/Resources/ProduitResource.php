<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // =========================
            // IDENTITÉ CUVE
            // =========================
            'id'        => $this->id,
            'reference' => $this->reference,
            'libelle'   => $this->libelle,
            'status'    => (bool) $this->status,

            // =========================
            // DONNÉES DE STOCK (CUVE)
            // =========================
            'type'        => $this->type_cuve, // ex : gasoil, essence
            'qt_initial'  => (float) $this->qt_initial,
            'qt_actuelle' => (float) $this->qt_actuelle,

            // =========================
            // PRIX (VENTE)
            // =========================
            'pu_vente'    => (float) $this->pu_vente,
            'pu_unitaire' => (float) $this->pu_unitaire,

            // =========================
            // STATION (si chargée)
            // =========================
            'station' => $this->whenLoaded('station', fn () => [
                'id'      => $this->station->id,
                'libelle' => $this->station->libelle,
            ]),

            // =========================
            // AUDIT
            // =========================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
