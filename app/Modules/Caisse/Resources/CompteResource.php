<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'libelle'        => $this->libelle,
             'numero'        => $this->numero,
            'commentaire'    => $this->commentaire,

            // =============================================
            // 🔹 STATION
            // =============================================
            'station' => $this->whenLoaded('station', function () {
                return [
                    'id'      => $this->station->id,
                    'libelle' => $this->station->libelle,
                ];
            }),

            // =============================================
            // 🔹 SOLDES
            // =============================================
            'solde_initial' => (float) $this->solde_initial,
            'solde_actuel'  => (float) $this->solde_actuel,

            // =============================================
            // 🔹 AUDIT
            // =============================================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
