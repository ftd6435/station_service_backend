<?php

namespace App\Modules\Caisse\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OperationCompte extends Model
{
    protected $table = 'operations_comptes';

    protected $fillable = [
        'id_compte',
        'id_source',
        'id_destination',
        'id_type_operation',
        'montant',
        'reference',
        'commentaire',
        'status',
        'created_by',
        'modify_by',
    ];

    protected $casts = [
        'montant' => 'float',
    ];

    /**
     * =================================================
     * BOOT : AUDIT + RÉFÉRENCE AUTO
     * =================================================
     */
    protected static function booted(): void
    {
        static::creating(function ($m) {

            // 🔹 Audit
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // 🔹 Génération automatique de la référence
            if (empty($m->reference)) {
                /*
                 * Format :
                 * OPC-YYYYMMDD-XXXXXX
                 * ex : OPC-20251229-A9F3KQ
                 */
                $m->reference = 'OPC-'
                    . now()->format('Ymd')
                    . '-'
                    . strtoupper(Str::random(6));
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
    public function scopeEffectif(Builder $query): Builder
    {
        return $query->where('status', 'effectif');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereHas('compte', fn ($q) => $q->visible());
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */
    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_compte');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_source');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Compte::class, 'id_destination');
    }

    public function typeOperation(): BelongsTo
    {
        return $this->belongsTo(TypeOperation::class, 'id_type_operation');
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
