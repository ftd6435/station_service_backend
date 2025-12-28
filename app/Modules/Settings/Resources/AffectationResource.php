<?php

namespace App\Modules\Settings\Resources;

use App\Modules\Administration\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffectationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'status' => $this->status,

            // 🔹 Pompe (optionnelle)
            'pompe' => $this->whenLoaded(
                'pompe',
                fn () => new PompeResource($this->pompe)
            ),

            // 🔹 Agent affecté (user générique)
            'agent' => $this->whenLoaded(
                'user',
                fn () => new UserResource($this->user)
            ),

            // 🔹 Station (optionnelle)
            'station' => $this->whenLoaded(
                'station',
                fn () => new StationResource($this->station)
            ),

            // 🔹 Audit
            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
