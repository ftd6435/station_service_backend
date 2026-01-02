<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationCompteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'montant'   => (float) $this->montant,
            'status'    => $this->status,
            'commentaire'=> $this->commentaire,

            // =============================================
            // 🔹 TYPE OPÉRATION
            // =============================================
            'type_operation' => $this->whenLoaded('typeOperation', function () {
                return [
                    'id'      => $this->typeOperation->id,
                    'libelle' => $this->typeOperation->libelle,
                    'nature'  => (int) $this->typeOperation->nature,
                ];
            }),

            // =============================================
            // 🔹 COMPTE PRINCIPAL
            // =============================================
            'compte' => $this->whenLoaded('compte', function () {
                return [
                    'id'      => $this->compte->id,
                    'libelle' => $this->compte->libelle,
                    'station' => $this->compte->station
                        ? [
                            'id'      => $this->compte->station->id,
                            'libelle' => $this->compte->station->libelle,
                          ]
                        : null,
                ];
            }),

            // =============================================
            // 🔹 TRANSFERT : SOURCE
            // =============================================
            'source' => $this->whenLoaded('source', function () {
                return [
                    'id'      => $this->source->id,
                    'libelle' => $this->source->libelle,
                    'station' => $this->source->station
                        ? [
                            'id'      => $this->source->station->id,
                            'libelle' => $this->source->station->libelle,
                          ]
                        : null,
                ];
            }),

            // =============================================
            // 🔹 TRANSFERT : DESTINATION
            // =============================================
            'destination' => $this->whenLoaded('destination', function () {
                return [
                    'id'      => $this->destination->id,
                    'libelle' => $this->destination->libelle,
                    'station' => $this->destination->station
                        ? [
                            'id'      => $this->destination->station->id,
                            'libelle' => $this->destination->station->libelle,
                          ]
                        : null,
                ];
            }),

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
