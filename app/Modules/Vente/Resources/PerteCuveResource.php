<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerteCuveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // =========================
            // Identité
            // =========================
            'id'              => $this->id,
            'quantite_perdue' => (float) $this->quantite_perdue,
            'commentaire'     => $this->commentaire,

            // =========================
            // Cuve (champs réels UNIQUEMENT)
            // =========================
            'cuve' => $this->whenLoaded(
                'cuve',
                fn () => [
                    'id'         => $this->cuve->id,
                    'libelle'    => $this->cuve->libelle,
                    'reference'  => $this->cuve->reference,
                    'type_cuve'  => $this->cuve->type_cuve,
                    'id_station' => $this->cuve->id_station,
                ]
            ),

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
