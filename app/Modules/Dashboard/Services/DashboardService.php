<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Vente\Models\LigneVente;
use App\Modules\Settings\Models\Pompe;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * =================================================
     * 🔹 DASHBOARD PRINCIPAL
     * =================================================
     */
    public function getDashboard(): array
    {
        return [
            'kpis'                   => $this->getKpis(),
            'progression_7_jours'    => $this->getProgression7Jours(),
            'repartition_carburant'  => $this->getRepartitionCarburant(),
            'volume_par_pompe'       => $this->getVolumeParPompe(),
            'approvisionnements_30j' => $this->getApprovisionnements30Jours(),
        ];
    }

    /**
     * =================================================
     * 🔹 KPIs DU JOUR
     * =================================================
     */
    private function getKpis(): array
    {
        $today = Carbon::today();

        $ventes = LigneVente::visible()
            ->where('ligne_ventes.status', true)
            ->whereDate('ligne_ventes.created_at', $today);

        $ventesDuJour = (clone $ventes)->count();
        $volumeVendu  = (clone $ventes)->sum('ligne_ventes.qte_vendu');

        // ❗ Recettes volontairement désactivées pour l’instant
        $recettesDuJour = 0;

        $totalPompes   = Pompe::visible()->count();
        $pompesActives = Pompe::visible()->where('status', true)->count();

        return [
            'ventes_du_jour'   => $ventesDuJour,
            'recettes_du_jour' => (float) $recettesDuJour,
            'volume_vendu'     => (float) $volumeVendu,
            'pompes_actives'   => [
                'actives' => $pompesActives,
                'total'   => $totalPompes,
            ],
        ];
    }

    /**
     * =================================================
     * 🔹 PROGRESSION DES VENTES (7 JOURS)
     * =================================================
     */
    private function getProgression7Jours(): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();

        return LigneVente::visible()
            ->where('ligne_ventes.status', true)
            ->where('ligne_ventes.created_at', '>=', $start)
            ->selectRaw('
                DATE(ligne_ventes.created_at) as date,
                SUM(ligne_ventes.qte_vendu) as volume
            ')
            ->groupBy(DB::raw('DATE(ligne_ventes.created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'    => $row->date,
                'montant' => 0, // caisse non branchée
                'volume'  => (float) $row->volume,
            ])
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 RÉPARTITION PAR CARBURANT
     * (basée sur pompes.type_pompe)
     * =================================================
     */
    private function getRepartitionCarburant(): array
    {
        return LigneVente::visible()
            ->where('ligne_ventes.status', true)

            // ligne_ventes → affectations
            ->join('affectations', 'ligne_ventes.id_affectation', '=', 'affectations.id')

            // affectations → pompes
            ->join('pompes', 'affectations.id_pompe', '=', 'pompes.id')

            ->selectRaw('
                pompes.type_pompe as type_pompe,
                SUM(ligne_ventes.qte_vendu) as volume
            ')
            ->groupBy('pompes.type_pompe')
            ->get()
            ->map(fn ($row) => [
                'type_pompe' => $row->type_pompe, // essence | gasoil
                'volume'     => (float) $row->volume,
            ])
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 VOLUME PAR POMPE
     * =================================================
     */
    private function getVolumeParPompe(): array
    {
        return LigneVente::visible()
            ->where('ligne_ventes.status', true)

            ->join('affectations', 'ligne_ventes.id_affectation', '=', 'affectations.id')
            ->join('pompes', 'affectations.id_pompe', '=', 'pompes.id')

            ->selectRaw('
                pompes.libelle as pompe,
                SUM(ligne_ventes.qte_vendu) as volume
            ')
            ->groupBy('pompes.libelle')
            ->orderByDesc('volume')
            ->get()
            ->map(fn ($row) => [
                'pompe'  => $row->pompe,
                'volume' => (float) $row->volume,
            ])
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 APPROVISIONNEMENTS (30 JOURS)
     * =================================================
     */
    private function getApprovisionnements30Jours(): array
    {
        return DB::table('approvisionnement_cuves')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('
                DATE(created_at) as date,
                SUM(volume) as volume
            ')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'   => $row->date,
                'volume' => (float) $row->volume,
            ])
            ->toArray();
    }
}
