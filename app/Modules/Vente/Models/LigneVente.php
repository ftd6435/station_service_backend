<?php
namespace App\Modules\Vente\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Station;
// ✅ import manquant
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;


class LigneVente extends Model
{
    protected $table = 'ligne_ventes';

    protected $fillable = [
        'id_station',
        'id_cuve', // ✅ clé métier
        'id_affectation',
        'index_debut',
        'index_fin',
        'qte_vendu',
        'status',
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
     * SCOPE : VISIBILITÉ PAR RÔLE
     * (100 % basé sur AFFECTATION)
     * =========================
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
                 * → accès total
                 */
            case 'super_admin':
                return $query;

            /**
                 * 🔵 ADMIN / 🟣 SUPERVISEUR / 🟡 GÉRANT
                 * → ventes de la station issue
                 *   de la DERNIÈRE affectation active
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
                 * → toutes ses ventes, via TOUTES ses affectations
                 *   (actives ou non)
                 */
            case 'pompiste':

                $affectationIds = $user->affectations()
                    ->pluck('id')
                    ->filter()
                    ->values()
                    ->all();

                if (empty($affectationIds)) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereIn('id_affectation', $affectationIds);
            /**
                 * ❌ AUTRES CAS
                 */
            default:
                return $query->whereRaw('1 = 0');
        }
    }

    // =========================
    // RELATIONS
    // =========================

    public function station()
    {
        return $this->belongsTo(
            Station::class,
            'id_station'
        );
    }

    /**
     * 🔹 CUVE
     * Table = produits
     * Clé métier = id_cuve
     */
    public function cuve()
    {
        return $this->belongsTo(
            Cuve::class,
            'id_cuve'
        );
    }

    public function affectation()
    {
        return $this->belongsTo(
            Affectation::class,
            'id_affectation'
        );
    }

    /**
     * =========================
     * AUDIT
     * =========================
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    /**
 * =========================
 * VALIDATION DE VENTE
 * =========================
 */
public function validation()
{
    return $this->hasOne(
        ValidationVente::class,
        'id_vente'
    );
}
/**
 * =========================
 * COMMENTAIRE DE VALIDATION
 * =========================
 */
protected function validationCommentaire(): Attribute
{
    return Attribute::get(
        fn () => $this->validation?->commentaire
    );
}

}
