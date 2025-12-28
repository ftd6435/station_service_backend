<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ApprovisionnementCuve extends Model
{
    protected $table = 'approvisionnement_cuves';

    protected $fillable = [
        'id_cuve',
        'qte_appro',
        'pu_unitaire',
        'commentaire',
        'created_by',
    ];

    protected $casts = [
        'qte_appro'   => 'float',
        'pu_unitaire' => 'float',
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
    }

    /**
     * =========================
     * SCOPE : VISIBILITÉ
     * (basé sur la DERNIÈRE affectation active)
     * =========================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // Super admin : tout voir
        if ($user->role === 'super_admin') {
            return $query;
        }

        // Station issue de la dernière affectation
        $stationId = $user->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        // Filtrage via la cuve
        return $query->whereHas('cuve', function (Builder $q) use ($stationId) {
            $q->where('id_station', $stationId);
        });
    }

    /**
     * =========================
     * RELATIONS
     * =========================
     */
    public function cuve(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'id_cuve');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
