<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ValidationVenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'commentaire' => $this->commentaire,

            // =================================================
            // 🔹 VENTE
            // =================================================
            'vente' => $this->whenLoaded('vente', function () {
                return [
                    'id'          => $this->vente->id,
                    'index_debut' => (float) $this->vente->index_debut,
                    'index_fin'   => (float) $this->vente->index_fin,
                    'qte_vendu'   => (float) $this->vente->qte_vendu,

                    // =============================================
                    // 🔹 AFFECTATION / CONTEXTE
                    // =============================================
                    'station' => $this->vente->affectation?->pompe?->station
                        ? [
                            'id'      => $this->vente->affectation->pompe->station->id,
                            'libelle' => $this->vente->affectation->pompe->station->libelle,
                          ]
                        : null,

                    'pompe' => $this->vente->affectation?->pompe
                        ? [
                            'id'       => $this->vente->affectation->pompe->id,
                            'libelle'  => $this->vente->affectation->pompe->libelle,
                            'reference'=> $this->vente->affectation->pompe->reference,
                          ]
                        : null,

                    'pompiste' => $this->vente->affectation?->user
                        ? [
                            'id'   => $this->vente->affectation->user->id,
                            'name' => $this->vente->affectation->user->name,
                            'email' => $this->vente->affectation->user->email,
                            'telephone' => $this->vente->affectation->user->telephone,
                          ]
                        : null,
                ];
            }),

            // =================================================
            // 🔹 AUDIT
            // =================================================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
