<?php

namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Compte extends Model
{
    protected $table = 'comptes';

    protected $fillable = [
        'id_station',
        'libelle',
        'numero',
        'commentaire',
        'solde_initial',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'solde_initial' => 'float',
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
     * SCOPES
     * =================================================
     */

    /**
     * 🔹 Visibilité par station (comme tout ton projet)
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->role === 'super_admin') {
            return $query;
        }

        $stationId = $user->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('id_station', $stationId);
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'id_station');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(OperationCompte::class, 'id_compte');
    }

    /**
     * =================================================
     * MÉTIER : CALCUL DU SOLDE ACTUEL
     * =================================================
     */
    public function getSoldeActuelAttribute(): float
    {
        $entrees = $this->operations()
            ->where('status', 'effectif')
            ->whereHas('typeOperation', fn ($q) => $q->where('nature', 1))
            ->sum('montant');

        $sorties = $this->operations()
            ->where('status', 'effectif')
            ->whereHas('typeOperation', fn ($q) => $q->where('nature', 0))
            ->sum('montant');

        $transfertsSortants = $this->operations()
            ->where('status', 'effectif')
            ->whereHas('typeOperation', fn ($q) => $q->where('nature', 2))
            ->sum('montant');

        return (float) ($this->solde_initial + $entrees - $sorties - $transfertsSortants);
    }

    /**
     * =================================================
     * AUDIT
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
}
