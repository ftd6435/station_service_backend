<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * BOOT : AUDIT + GÉNÉRATION RÉFÉRENCE
     * =================================================
     */
    protected static function booted(): void
    {
        // 🔹 Création
        static::creating(function ($m) {

            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 Référence automatique
            if (empty($m->reference)) {
                $nextId = self::withoutGlobalScopes()->max('id') + 1;
                $m->reference = 'PMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });

        // 🔹 Mise à jour
        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * SCOPE : VISIBILITÉ DES POMPES
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
             * 🔵 ADMIN / 🟣 SUPERVISEUR / 🟡 GÉRANT
             * → pompes de leur station (via affectation active)
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
             * → uniquement la pompe à laquelle il est affecté
             */
            case 'pompiste':

                return $query->whereHas('affectations', function (Builder $q) use ($user) {
                    $q->where('id_user', $user->id)
                      ->where('status', true);
                });

            default:
                return $query->whereRaw('1 = 0');
        }
    }

    /**
     * =================================================
     * SCOPE : POMPES DISPONIBLES
     * → aucune affectation active
     * =================================================
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereDoesntHave('affectations', function (Builder $q) {
            $q->where('status', false);
        });
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    /**
     * Station propriétaire
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    /**
     * Affectations liées à la pompe
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(Affectation::class, 'id_pompe');
    }

    /**
     * Audit
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
