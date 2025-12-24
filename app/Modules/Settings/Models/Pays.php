<?php

namespace App\Modules\Settings\Models;

use App\Modules\Backoffice\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Pays extends Model
{
    protected $table = 'pays';

    protected $fillable = [
        'libelle',
        'created_by',
        'modify_by',
    ];

    /**
     * ======================================
     * Boot : audit automatique
     * ======================================
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->modify_by = Auth::id();
            }
        });
    }

    /**
     * ======================================
     * Relations
     * ======================================
     */
    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class, 'id_pays');
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
