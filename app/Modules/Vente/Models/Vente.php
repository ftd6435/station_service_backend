<?php

namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'libelle',
        'type_produit',
        'qt_initial',
        'qt_actuelle',
        'pu_vente',
        'pu_unitaire',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =========================
     * BOOT : audit automatique
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
     * RELATIONS AUDIT
     * =========================
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
