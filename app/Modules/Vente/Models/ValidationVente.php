<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ValidationVente extends Model
{
    protected $table = 'validation_ventes';

    protected $fillable = [
        'id_vente',
        'commentaire',
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
     * SCOPE : VISIBILITÉ
     * (ALIGNÉ À LigneVente)
     * =========================
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereHas('vente', function ($q) {
            $q->visible();
        });
    }

    /**
     * =========================
     * RELATIONS
     * =========================
     */
    public function vente()
    {
        return $this->belongsTo(
            LigneVente::class,
            'id_vente'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
}
