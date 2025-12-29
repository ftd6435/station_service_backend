<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Cuve extends Model
{
    protected $table = 'cuves';

    protected $fillable = [
        'libelle',
        'reference',
        'id_station',
        'type_cuve',
        'qt_initial',
        'qt_actuelle',
        'pu_vente',
        'pu_unitaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'qt_initial'  => 'float',
        'qt_actuelle' => 'float',
        'pu_vente'    => 'float',
        'pu_unitaire' => 'float',
        'status'      => 'boolean',
    ];

    /**
     * =================================================
     * BOOT : AUDIT + GÉNÉRATION RÉFÉRENCE CUVE
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {

            // 🔹 Audit
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 Génération automatique de la référence CUVE
            if (empty($m->reference)) {

                $nextId = self::withoutGlobalScopes()->max('id') + 1;

                $m->reference = 'CUV-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * SCOPE LOCAL : VISIBILITÉ DES CUVES
     * (100 % basé sur la DERNIÈRE AFFECTATION ACTIVE)
     * =================================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // 🔹 Super admin : tout voir
        if ($user->role === 'super_admin') {
            return $query;
        }

        // 🔹 Récupération de la station via DERNIÈRE affectation active
        $stationId = $user->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        // 🔹 Admin / Superviseur / Gérant / Pompiste
        // → tous filtrés par leur station d’affectation active
        return $query->where('id_station', $stationId);
    }

    /**
     * =================================================
     * RELATIONS MÉTIER
     * =================================================
     */

    /**
     * Station propriétaire de la cuve
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    /**
     * =================================================
     * RELATIONS AUDIT
     * =================================================
     */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }


    // App\Modules\Vente\Models\Cuve.php

public function ligneVentes()
{
    return $this->hasMany(
        LigneVente::class,
        'id_cuve'
    );
}

}
