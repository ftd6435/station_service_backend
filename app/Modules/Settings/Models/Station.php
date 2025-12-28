<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Station extends Model
{
    protected $table = 'stations';

    protected $fillable = [
        'libelle',
        'code',
        'adresse',
        'latitude',
        'longitude',
        'id_ville',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =================================================
     * BOOT : AUDIT + CODE STATION
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {

            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            if (empty($m->code)) {
                $lastId  = self::withoutGlobalScopes()->max('id') + 1;
                $m->code = 'STAT-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
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
     * SCOPE LOCAL : VISIBILITÉ DES STATIONS
     * (STRICTEMENT BASÉ SUR AFFECTATION)
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
             * → toutes les stations
             */
            case 'super_admin':
                return $query;

            /**
             * 🔵 ADMIN
             * 🟣 SUPERVISEUR
             * 🟡 GÉRANT
             * → station issue de la DERNIÈRE affectation active
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

                return $query->where('id', $stationId);

            /**
             * 🔴 POMPISTE
             * → station via sa DERNIÈRE affectation (pompe → station)
             * → JAMAIS regroupé avec admin / gérant
             */
            case 'pompiste':

                $stationId = $user->affectations()
                    ->where('status', true)
                    ->latest('created_at')
                    ->value('id_station');

                if (! $stationId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id', $stationId);

            /**
             * ❌ AUTRES CAS
             */
            default:
                return $query->whereRaw('1 = 0');
        }
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

    public function pompes(): HasMany
    {
        return $this->hasMany(Pompe::class, 'id_station');
    }

    public function parametrage(): HasOne
    {
        return $this->hasOne(ParametrageStation::class, 'id_station');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    /**
     * Affectations liées à cette station
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class, 'id_station')
            ->orderBy('created_at', 'desc');
    }
}
