<?php
namespace App\Modules\Administration\Models;

use App\Modules\Settings\Models\Affectation;
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
            'status'            => 'boolean',
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
     * (basé sur la DERNIÈRE affectation)
     * =================================================
     */
    public function scopeVisible(Builder $query): Builder
    {
        $auth = Auth::user();

        if (! $auth) {
            return $query->whereRaw('1 = 0');
        }

        switch ($auth->role) {

            /**
                 * 🔥 SUPER ADMIN
                 */
            case 'super_admin':
                return $query;

            /**
                 * 🔵 ADMIN
                 * 🟣 SUPERVISEUR
                 * 🟡 GÉRANT
                 * → utilisateurs de la même station
                 * (via dernière affectation)
                 */
            case 'admin':
            case 'superviseur':
            case 'gerant':

                $stationId = $auth->station?->id;

                if (! $stationId) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas('station', function (Builder $q) use ($stationId) {
                    $q->where('stations.id', $stationId);
                });

            /**
                 * 🔴 POMPISTE
                 * → lui-même uniquement
                 */
            case 'pompiste':
                return $query->where('id', $auth->id);

            /**
                 * ❌ AUTRES CAS
                 */
            default:
                return $query->whereRaw('1 = 0');
        }
    }

    /**
     * =================================================
     * RELATIONS
     * =================================================
     */

    /**
     * Station courante de l'utilisateur
     * → dernière affectation vers une station
     */
    public function station()
    {
        return $this->hasOneThrough(
            Station::class,
            Affectation::class,
            'id_user',   // FK sur affectations
            'id',        // PK sur stations
            'id',        // PK sur users
            'id_station' // FK vers stations
        )->latest('affectations.created_at');
    }

    /**
     * Ville (si besoin direct)
     */
    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'id_ville');
    }

    /**
     * Audit
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'modify_by');
    }

    /**
     * Historique des affectations de l'utilisateur
     */
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'id_user')
            ->orderBy('created_at', 'desc');
    }

}
