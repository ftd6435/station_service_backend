<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Station;
 // ✅ import manquant
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LigneVente extends Model
{
    protected $table = 'ligne_ventes';

    protected $fillable = [
        'id_station',
        'id_cuve',          // ✅ clé métier
        'id_affectation',
        'index_debut',
        'index_fin',
        'qte_vendu',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =========================
     * BOOT : AUDIT
     * =========================
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
     * =========================
     * SCOPE : VISIBILITÉ PAR RÔLE
     * (100 % basé sur AFFECTATION)
     * =========================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        switch ($user->role) {

            /**
             * 🔥 SUPER ADMIN
             * → accès total
             */
            case 'super_admin':
                return $query;

            /**
             * 🔵 ADMIN / 🟣 SUPERVISEUR / 🟡 GÉRANT
             * → ventes de la station issue
             *   de la DERNIÈRE affectation active
             */
            case 'admin':
            case 'superviseur':
            case 'gerant':

                $stationId = $user->affectations()
                    ->where('status', true)
                    ->latest('created_at')
                    ->value('id_station');

                if (! $stationId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id_station', $stationId);

            /**
             * 🔴 POMPISTE
             * → uniquement ses ventes
             *   via son AFFECTATION ACTIVE
             */
            case 'pompiste':

                $affectationId = $user->affectations()
                    ->where('status', true)
                    ->latest('created_at')
                    ->value('id');

                if (! $affectationId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id_affectation', $affectationId);

            /**
             * ❌ AUTRES CAS
             */
            default:
                return $query->whereRaw('1 = 0');
        }
    }

    // =========================
    // RELATIONS
    // =========================

    public function station()
    {
        return $this->belongsTo(
            Station::class,
            'id_station'
        );
    }

    /**
     * 🔹 CUVE
     * Table = produits
     * Clé métier = id_cuve
     */
    public function cuve()
    {
        return $this->belongsTo(
            Cuve::class,
            'id_cuve'
        );
    }

    public function affectation()
    {
        return $this->belongsTo(
            Affectation::class,
            'id_affectation'
        );
    }

    /**
     * =========================
     * AUDIT
     * =========================
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
