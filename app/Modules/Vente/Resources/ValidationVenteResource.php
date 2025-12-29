<?php

namespace App\Modules\Vente\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ValidationVenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'commentaire'=> $this->commentaire,

            'vente' => $this->whenLoaded(
                'vente',
                fn () => [
                    'id'          => $this->vente->id,
                    'index_debut' => $this->vente->index_debut,
                    'index_fin'   => $this->vente->index_fin,
                    'qte_vendu'   => $this->vente->qte_vendu,
                ]
            ),

            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
