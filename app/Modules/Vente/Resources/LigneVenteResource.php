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

            'index_debut' => (float) $this->index_debut,
            'index_fin'   => (float) $this->index_fin,
            'qte_vendu'   => (float) $this->qte_vendu,
            'status'      => (bool) $this->status,
            'commentaire' => $this->commentaire,

            // =================================================
            // 🔹 CONTEXTE VENTE / AFFECTATION
            // =================================================
            'contexte' => $this->whenLoaded('affectation', function () {

                return [
                    // =============================================
                    // 🔹 STATION
                    // =============================================
                    'station' => $this->affectation?->pompe?->station
                        ? [
                            'id'      => $this->affectation->pompe->station->id,
                            'libelle' => $this->affectation->pompe->station->libelle,
                          ]
                        : null,

                    // =============================================
                    // 🔹 POMPE
                    // =============================================
                    'pompe' => $this->affectation?->pompe
                        ? [
                            'id'        => $this->affectation->pompe->id,
                            'libelle'   => $this->affectation->pompe->libelle,
                            'reference' => $this->affectation->pompe->reference,
                          ]
                        : null,

                    // =============================================
                    // 🔹 POMPISTE
                    // =============================================
                    'pompiste' => $this->affectation?->user
                        ? [
                            'id'        => $this->affectation->user->id,
                            'name'      => $this->affectation->user->name,
                            'email'     => $this->affectation->user->email,
                            'telephone' => $this->affectation->user->telephone,
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
