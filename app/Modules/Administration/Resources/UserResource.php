<?php
namespace App\Modules\Administration\Resources;

use App\Modules\Settings\Resources\AffectationResource;
use App\Modules\Settings\Resources\StationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // =========================
            // Identité
            // =========================
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'telephone'    => $this->telephone,
            'adresse'      => $this->adresse,

            // =========================
            // Image (URL publique)
            // =========================
            'image'        => $this->image
                ? asset('storage/images/users/' . $this->image)
                : null,

            // =========================
            // Métier
            // =========================
            'role'         => $this->role,
            'status'       => $this->status,

            // =========================
            // Station courante
            // (dernière affectation)
            // =========================
// Station courante
// (dernière affectation)
// =========================
            'station'      => $this->whenLoaded(
                'station',
                fn() => new StationResource($this->station)
            ),

            // =========================
            // Historique des affectations
            // =========================
            'affectations' => AffectationResource::collection(
                $this->whenLoaded('affectations')
            ),

            // =========================
            // Audit
            // =========================
            'created_by'   => $this->createdBy?->name,
            'modify_by'    => $this->modifiedBy?->name,

            // =========================
            // Dates
            // =========================
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
