<?php

namespace App\Modules\Administration\Models;

use App\Modules\Settings\Models\Station;
use App\Modules\Settings\Models\Ville;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'telephone',
        'adresse',
        'image',
        'role',
        'id_station',
        'id_ville',
        'status',
        'password',
        'created_by',
        'modify_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status' => 'boolean',
        ];
    }

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
     * SCOPE LOCAL : VISIBILITÉ DES UTILISATEURS
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
             * → utilisateurs de leur station
             */
            case 'admin':
            case 'superviseur':
            case 'gerant':

                if (! $user->id_station) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where('id_station', $user->id_station);

            /**
             * 🔴 POMPISTE
             * → lui-même uniquement
             */
            case 'pompiste':
                return $query->where('id', $user->id);

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

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'modify_by');
    }
}
