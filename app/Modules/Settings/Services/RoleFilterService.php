<?php

namespace App\Modules\Settings\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RoleFilterService
{
    public static function apply(Builder $query, array $options = []): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $stationColumn  = $options['station']  ?? 'id_station';
        $pompisteColumn = $options['pompiste'] ?? 'id_pompiste';
        $villeColumn    = $options['ville']    ?? null;

        switch ($user->role) {

            // 🔥 VOIT TOUT
            case 'super_admin':
                return $query;

            // 🔵 SUPERVISEUR → SA VILLE
            case 'superviseur':

                // Si la table contient directement id_ville
                if ($villeColumn) {
                    return $query->where($villeColumn, $user->station->id_ville);
                }

                // Sinon via relation station
                return $query->whereHas('station', function ($q) use ($user) {
                    $q->where('id_ville', $user->station->id_ville);
                });

            // 🟡 ADMIN / GERANT → SA STATION
            case 'admin':
            case 'gerant':
                return $query->where($stationColumn, $user->id_station);

            // 🔴 POMPISTE → LUI-MÊME UNIQUEMENT
            case 'pompiste':
                return $query->where($pompisteColumn, $user->id);

            // ❌ AUTRES → RIEN
            default:
                return $query->whereRaw('1 = 0');
        }
    }
}
