<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenteLitreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'id_cuve'    => $this->id_cuve,

            'qte_vendu'  => (float) $this->qte_vendu,
            'commentaire'=> $this->commentaire,
            'status'     => (bool) $this->status,

            // =========================
            // 🔹 CUVE (chargée si nécessaire)
            // =========================
            'cuve' => $this->whenLoaded('cuve', function () {
                return [
                    'id'           => $this->cuve->id,
                    'libelle'      => $this->cuve->libelle,
                    'reference'    => $this->cuve->reference,
                    'qt_actuelle'  => (float) $this->cuve->qt_actuelle,
                   
                ];
            }),

            // =========================
            // 🔹 AUDIT
            // =========================
            'created_by' => $this->created_by,
            'modify_by'  => $this->modify_by,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
