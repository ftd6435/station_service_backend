<?php

namespace App\Modules\Backoffice\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'is_created' => $this->is_created,
            'is_active' => $this->is_active,
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'licences' => $this->whenLoaded('licences'),
            'current_licence' => $this->whenLoaded('licences', function () {
                return $this->licences()->latest()->first();
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
