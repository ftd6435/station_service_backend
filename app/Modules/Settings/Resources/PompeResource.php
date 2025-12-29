<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Services\PompeService;

class PompeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PompeService $pompeService */
        $pompeService = app(PompeService::class);

        $indexData = $pompeService->getDernierIndexPourAffectation($this->id);

        return [
            'id'            => $this->id,
            'libelle'       => $this->libelle,
            'reference'     => $this->reference,
            'type_pompe'    => $this->type_pompe,
            'index_initial' => $this->index_initial,

            // =========================
            // INDEX MÉTIER
            // =========================
            'index_fin'   => $indexData['index_debut'] ?? null,

            'status'        => $this->status,

            // =========================
            // Station (si chargée)
            // =========================
            'station' => $this->whenLoaded(
                'station',
                fn () => new StationResource($this->station)
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
