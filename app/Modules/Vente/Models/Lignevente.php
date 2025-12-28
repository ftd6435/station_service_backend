<?php

namespace App\Modules\Vente\Models;

use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LigneVente extends Model
{
    protected $table = 'ligne_ventes';

    protected $fillable = [
        'id_station',
        'id_produit',
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
     * SCOPE : VISIBILITÉ PAR RÔLE / ENTITÉ
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
             * → ventes de leur station
             */
            case 'admin':
            case 'superviseur':
            case 'gerant':

                if (! $user->station) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id_station', $user->station->id);

            /**
             * 🔴 POMPISTE
             * → uniquement ses ventes (via affectation active)
             */
            case 'pompiste':

                return $query->whereHas('affectation', function (Builder $q) use ($user) {
                    $q->where('id_user', $user->id)
                      ->where('status', true);
                });

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

    public function produit()
    {
        return $this->belongsTo(
            Produit::class,
            'id_produit'
        );
    }

    public function affectation()
    {
        return $this->belongsTo(
            Affectation::class,
            'id_affectation'
        );
    }
}
