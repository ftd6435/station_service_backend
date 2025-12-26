<?php
namespace App\Modules\Settings\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RoleFilterService
{
    /**
     * Filtrage centralisé basé sur le rôle et les relations métier
     */
    public static function apply(Builder $query, array $options = []): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $table = $model->getTable();

        $stationRelation = $options['station_relation'] ?? null;
        $pompisteColumn  = $options['pompiste_column'] ?? null;

        switch ($user->role) {

            /**
                 * 🔥 SUPER ADMIN
                 */
            case 'super_admin':
                return $query;

            /**
                 * 🔵 ADMIN & SUPERVISEUR
                 * - visibilité sur TOUTE la ville
                 */
            case 'admin':
            case 'superviseur':

                if (
                    ! $stationRelation ||
                    ! method_exists($model, $stationRelation) ||
                    ! $user->station
                ) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas($stationRelation, function ($q) use ($user) {
                    $q->where('id_ville', $user->station->id_ville);
                });

            /**
                 * 🟠 GERANT
                 * - visibilité sur SA station uniquement
                 */
            case 'gerant':

                if (
                    ! $stationRelation ||
                    ! method_exists($model, $stationRelation) ||
                    ! $user->id_station
                ) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->whereHas($stationRelation, function ($q) use ($user) {
                    $q->where('id', $user->id_station);
                });

            /**
                 * 🔴 POMPISTE
                 * - UNIQUEMENT ses données personnelles
                 */
            case 'pompiste':

                if (
                    ! $pompisteColumn ||
                    ! Schema::hasColumn($table, $pompisteColumn)
                ) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->where($pompisteColumn, $user->id);

            /**
                 * ❌ AUTRES
                 */
            default:
                return $query->whereRaw('1 = 0');
        }
    }
}
