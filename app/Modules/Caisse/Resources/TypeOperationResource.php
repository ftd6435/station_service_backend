<?php

namespace App\Modules\Caisse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'libelle'     => $this->libelle,
            'commentaire' => $this->commentaire,

            // =============================================
            // 🔹 NATURE (AU PREMIER NIVEAU)
            // =============================================
            'nature'       => (int) $this->nature,
            'nature_label' => match ((int) $this->nature) {
                1 => 'entrée',
                0 => 'sortie',
                2 => 'transfert inter-station',
                default => 'inconnu',
            },

            // =============================================
            // 🔹 DATES
            // =============================================
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
