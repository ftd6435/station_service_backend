<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Pompe extends Model
{
    protected $table = 'pompes';

    protected $fillable = [
        'libelle',
        'reference',
        'type_pompe',
        'index_initial',
        'id_station',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =================================================
     * BOOT : AUDIT + RÉFÉRENCE
     * =================================================
     */
    protected static function booted(): void
    {
        /*
        |--------------------------------------------------
        | CRÉATION : audit + référence automatique
        |--------------------------------------------------
        */
        static::creating(function ($m) {

            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // Génération automatique de la référence
            if (empty($m->reference)) {
                $nextId = self::withoutGlobalScopes()->max('id') + 1;
                $m->reference = 'PMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });

        /*
        |--------------------------------------------------
        | MISE À JOUR : audit
        |--------------------------------------------------
        */
        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * SCOPE LOCAL : VISIBILITÉ DES POMPES
     * =================================================
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
             */
            case 'super_admin':
                return $query;

            /**
             * 🔵 ADMIN
             * 🟣 SUPERVISEUR
             * 🟡 GÉRANT
             * → pompes de la station issue
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
             * → pompes via son affectation active
             */
            case 'pompiste':

                return $query->whereHas('affectations', function (Builder $q) use ($user) {
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

    /**
     * ============================
     * RELATIONS
     * ============================
     */

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
