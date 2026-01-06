<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationTransfertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /**
         * =================================================
         * 🔹 CONTEXTE TRANSFERT
         * =================================================
         * - EMIS     : id_source = compte courant
         * - REÇUS    : id_destination = compte courant
         *
         * ⚠️ Le "compte courant" est déduit de la visibilité
         */
        $isEmis   = ! is_null($this->id_source);
        $isRecus  = ! is_null($this->id_destination);

        return [
            // =========================
            // IDENTITÉ OPÉRATION
            // =========================
            'id'        => $this->id,
            'reference' => $this->reference,
            'montant'   => (float) $this->montant,
            'status'    => $this->status,
            'commentaire' => $this->commentaire,

            // =========================
            // TYPE TRANSFERT
            // =========================
            'type_transfert' => $isEmis
                ? 'TRANSFERT_EMIS'
                : 'TRANSFERT_RECUS',

            'libelle_transfert' => $isEmis
                ? 'ENVOIE DE FOND'
                : 'RÉCEPTION DE FOND',

            // =========================
            // TYPE OPÉRATION
            // =========================
            'type_operation' => [
                'id'      => $this->typeOperation->id,
                'libelle' => $this->typeOperation->libelle,
                'nature'  => $this->typeOperation->nature,
            ],

            // =========================
            // COMPTE CONCERNÉ (UNIQUE)
            // =========================
            'compte' => $isEmis
                ? $this->formatCompte($this->source)
                : $this->formatCompte($this->destination),

            // =========================
            // SOURCE & DESTINATION
            // =========================
            'source' => $this->formatCompte($this->source),
            'destination' => $this->formatCompte($this->destination),

            // =========================
            // AUDIT
            // =========================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * =================================================
     * 🔹 FORMAT COMPTE STANDARD
     * =================================================
     */
    private function formatCompte($compte): ?array
    {
        if (! $compte) {
            return null;
        }

        return [
            'id'      => $compte->id,
            'libelle' => $compte->libelle,
            'station' => $compte->station
                ? [
                    'id'      => $compte->station->id,
                    'libelle' => $compte->station->libelle,
                ]
                : null,
        ];
    }
}
