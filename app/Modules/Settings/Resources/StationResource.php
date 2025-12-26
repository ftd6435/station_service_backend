<?php

namespace App\Modules\Settings\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

            'ville' => [
                'id'      => $this->ville?->id,
                'libelle' => $this->ville?->libelle,
            ],

            'status' => $this->status,

            'created_by' => $this->createdBy?->name,
            'modify_by'  => $this->modifiedBy?->name,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
