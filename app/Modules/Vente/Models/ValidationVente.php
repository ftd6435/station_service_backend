<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ValidationVente extends Model
{
    protected $table = 'validation_ventes';

    protected $fillable = [
        'id_vente',
        'commentaire',
        'created_by',
        'modify_by',
    ];

    /**
     * =================================================
     * BOOT : AUDIT
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (Auth::check()) {
                $m->created_by = Auth::id();
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
     * SCOPE : VISIBILITÉ (RÈGLE MÉTIER FINALE)
     * =================================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        /**
         * 🔥 SUPER ADMIN
         * → voit TOUTES les validations (toutes stations)
         */
        if ($user->role === 'super_admin') {
            return $query;
        }

        /**
         * 🔹 ADMIN / GÉRANT / SUPERVISEUR
         * → visibilité limitée à leur station
         */
        if (in_array($user->role, ['admin', 'gerant', 'superviseur'], true)) {

            $stationId = $user->affectations()
                ->where('status', true)
                ->latest('created_at')
                ->value('id_station');

            if (! $stationId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('vente.affectation.pompe', function ($q) use ($stationId) {
                $q->where('id_station', $stationId);
            });
        }

        /**
         * 🔹 POMPISTE
         * → uniquement ses ventes
         */
        if ($user->role === 'pompiste') {
            return $query->whereHas('vente.affectation', function ($q) use ($user) {
                $q->where('id_user', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function vente()
    {
        return $this->belongsTo(
            LigneVente::class,
            'id_vente'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
