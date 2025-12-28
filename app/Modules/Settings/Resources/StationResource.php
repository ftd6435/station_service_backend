<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // =========================
            // IDENTITÉ
            // =========================
            'id'        => $this->id,
            'libelle'   => $this->libelle,
            'code'      => $this->code,
            'adresse'   => $this->adresse,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,

            // =========================
            // PARAMÉTRAGE
            // =========================
            'parametrage' => $this->whenLoaded(
                'parametrage',
                fn () => new ParametrageStationResource($this->parametrage)
            ),

            // =========================
            // VILLE
            // =========================
            'ville' => $this->whenLoaded(
                'ville',
                fn () => new VilleResource($this->ville)
            ),

            // =========================
            // POMPES
            // =========================
            'pompes' => PompeResource::collection(
                $this->whenLoaded('pompes')
            ),

            // // =========================
            // // AFFECTATIONS (HISTORIQUE)
            // // =========================
            // 'affectations' => AffectationResource::collection(
            //     $this->whenLoaded('affectations')
            // ),

            // =========================
            // DERNIER GÉRANT (SIMPLIFIÉ)
            // =========================
            'dernier_gerant' => $this->whenLoaded('affectations', function () {

                $gerant = $this->affectations
                    ->filter(fn ($a) => $a->user && $a->user->role === 'gerant')
                    ->sortByDesc('created_at')
                    ->first()?->user;

                return $gerant ? [
                    'name'      => $gerant->name,
                    'email'     => $gerant->email,
                    'telephone' => $gerant->telephone,
                    'adresse'   => $gerant->adresse,
                ] : null;
            }),

            // =========================
            // ÉTAT
            // =========================
            'status' => $this->status,

            // =========================
            // AUDIT
            // =========================
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            // =========================
            // DATES
            // =========================
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
