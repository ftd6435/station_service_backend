<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class VenteLitre extends Model
{
    protected $table = 'vente_litres';

    protected $fillable = [
        'id_cuve',
        'qte_vendu',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'qte_vendu' => 'float',
        'status'    => 'boolean',
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
     * SCOPE : VISIBILITÉ DES VENTES
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
         * → toutes les ventes
         */
        if ($user->role === 'super_admin') {
            return $query;
        }

        /**
         * 🔴 POMPISTE
         * → ventes de TOUTES les stations
         *   liées à SES affectations (actives ou non)
         */
        if ($user->role === 'pompiste') {

            $stationIds = $user->affectations()
                ->pluck('id_station')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($stationIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('cuve', function ($q) use ($stationIds) {
                $q->whereIn('id_station', $stationIds);
            });
        }

        /**
         * 🔵 ADMIN / 🟣 SUPERVISEUR / 🟡 GÉRANT
         * → ventes de la station issue
         *   de la DERNIÈRE affectation active
         */
        $stationId = $user->affectations()
            ->where('status', true)
            ->latest('created_at')
            ->value('id_station');

        if (! $stationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('cuve', function ($q) use ($stationId) {
            $q->where('id_station', $stationId);
        });
    }

    /**
     * =================================================
     * RELATIONS MÉTIER
     * =================================================
     */

    /**
     * Cuve concernée par la vente
     */
    public function cuve(): BelongsTo
    {
        return $this->belongsTo(Cuve::class, 'id_cuve');
    }

    /**
     * =================================================
     * RELATIONS AUDIT
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
