<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Affectation extends Model
{
    protected $table = 'affectations';

    protected $fillable = [
        'id_pompe',
        'id_pompiste',
        'id_station',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =================================================
     * BOOT : AUDIT UNIQUEMENT
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
     * SCOPE LOCAL : VISIBILITÉ PAR RÔLE
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
             * → affectations des stations de la ville de sa station
             */
            case 'admin':

                if (! $user->station || ! $user->station->id_ville) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas('station', function (Builder $q) use ($user) {
                    $q->where('id_ville', $user->station->id_ville);
                });

            /**
             * 🟣 SUPERVISEUR
             * → affectations des stations de sa ville
             */
            case 'superviseur':

                if (! $user->id_ville) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas('station', function (Builder $q) use ($user) {
                    $q->where('id_ville', $user->id_ville);
                });

            /**
             * 🟡 GÉRANT
             * → affectations de sa station
             */
            case 'gerant':

                if (! $user->id_station) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id_station', $user->id_station);

            /**
             * 🔴 POMPISTE
             * → uniquement ses affectations
             */
            case 'pompiste':
                return $query->where('id_pompiste', $user->id);

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

    public function pompe(): BelongsTo
    {
        return $this->belongsTo(Pompe::class, 'id_pompe');
    }

    public function pompiste(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pompiste');
    }

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
