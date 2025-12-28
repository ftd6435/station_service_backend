<?php
namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Station extends Model
{
    protected $table = 'stations';

    protected $fillable = [
        'libelle',
        'code',
        'adresse',
        'latitude',
        'longitude',
        'id_ville',
        'status',
        'created_by',
        'modify_by',
    ];

    /**
     * =================================================
     * BOOT : audit + code station + filtrage par rôle
     * =================================================
     */
    protected static function booted(): void
    {
        /*
        |--------------------------------------------------------------------------
        | GLOBAL SCOPE : VISIBILITÉ PAR RÔLE
        |--------------------------------------------------------------------------
        */
       

        /*
        |--------------------------------------------------------------------------
        | CRÉATION : audit + code automatique
        |--------------------------------------------------------------------------
        */
        static::creating(function ($m) {

            // Audit
            if (Auth::check()) {
                $m->created_by = Auth::id();
            }

            // Génération automatique du code station
            if (empty($m->code)) {
                $lastId = self::withoutGlobalScopes()->max('id') + 1;

                $m->code = 'STAT-' . str_pad($lastId, 3, '0', STR_PAD_LEFT);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | MISE À JOUR : audit
        |--------------------------------------------------------------------------
        */
        static::updating(function ($m) {
            if (Auth::check()) {
                $m->modify_by = Auth::id();
            }
        });
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

    public function pompes(): HasMany
    {
        return $this->hasMany(Pompe::class, 'id_station');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modify_by');
    }
    public function parametrage()
    {
        return $this->hasOne(ParametrageStation::class, 'id_station');
    }
}
