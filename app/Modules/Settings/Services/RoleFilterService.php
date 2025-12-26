<?php

// Namespace du service, placé dans le module Settings
namespace App\Modules\Settings\Services;

// Import du Builder Eloquent pour typer la requête
use Illuminate\Database\Eloquent\Builder;

// Import de la façade Auth pour récupérer l'utilisateur connecté
use Illuminate\Support\Facades\Auth;

class RoleFilterService
{
    /**
     * Applique le filtrage des données en fonction du rôle de l'utilisateur connecté
     *
     * @param Builder $query   Requête Eloquent à filtrer
     * @param array   $options Options permettant de préciser les colonnes
     *
     * @return Builder         Requête filtrée
     */
    public static function apply(Builder $query, array $options = []): Builder
    {
        // Récupération de l'utilisateur actuellement authentifié
        $user = Auth::user();

        // Sécurité : si aucun utilisateur n'est connecté,
        // on retourne une requête vide (aucune donnée)
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // Nom de la colonne représentant la station
        // Par défaut : id_station
        $stationColumn = $options['station'] ?? 'id_station';

        // Nom de la colonne représentant l'utilisateur (pompiste)
        // Par défaut : id_pompiste
        $pompisteColumn = $options['pompiste'] ?? 'id_pompiste';

        // Filtrage selon le rôle de l'utilisateur
        switch ($user->role) {

            // Le super administrateur voit toutes les données
            case 'super_admin':
                return $query;

            // Les rôles admin, gérant et superviseur
            // voient uniquement les données de leur station
            case 'admin':
            case 'gerant':
            case 'superviseur':
                return $query->where($stationColumn, $user->id_station);

            // Le pompiste ne voit que ses propres données
            case 'pompiste':
                return $query->where($pompisteColumn, $user->id);

            // Sécurité : tout autre rôle non prévu ne voit rien
            default:
                return $query->whereRaw('1 = 0');
        }
    }
}
