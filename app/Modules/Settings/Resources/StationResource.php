<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Settings\Resources\PompeResource;
use App\Modules\Settings\Resources\VilleResource;
use App\Modules\Settings\Resources\AffectationResource;

class StationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'libelle'   => $this->libelle,
            'code'      => $this->code,
            'adresse'   => $this->adresse,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,

            // 🔹 Paramétrage
            'parametrage' => $this->whenLoaded(
                'parametrage',
                fn () => new ParametrageStationResource($this->parametrage)
            ),

            // 🔹 Ville (via Resource)
            'ville' => $this->whenLoaded(
                'ville',
                fn () => new VilleResource($this->ville)
            ),

            // 🔹 Pompes
            'pompes' => PompeResource::collection(
                $this->whenLoaded('pompes')
            ),

            // 🔹 Affectations de la station (HISTORIQUE COMPLET)
            'affectations' => AffectationResource::collection(
                $this->whenLoaded('affectations')
            ),

            'status' => $this->status,

            // 🔹 Audit
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
