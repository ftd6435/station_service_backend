<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // =========================
            // Données métier
            // =========================
            'libelle'        => $this->libelle,
            'type_produit'   => $this->type_produit,
            'qt_initial'     => (float) $this->qt_initial,
            'qt_actuelle'    => (float) $this->qt_actuelle,
            'pu_vente'       => (float) $this->pu_vente,
            'pu_unitaire'    => (float) $this->pu_unitaire,
            'status'         => (bool) $this->status,

            // =========================
            // Audit
            // =========================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
