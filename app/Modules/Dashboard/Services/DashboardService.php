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
            ->whereDate('created_at', $today)
            ->where('status', true);

        $ventesDuJour = (clone $ventes)->count();
        $volumeVendu  = (clone $ventes)->sum('qte_vendu');

        $totalPompes   = Pompe::visible()->count();
        $pompesActives = Pompe::visible()->where('status', true)->count();

        return [
            'ventes_du_jour'   => $ventesDuJour,

            // ⚠️ volontairement à 0 (branché plus tard à la caisse)
            'recettes_du_jour' => 0,

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
            ->where('status', true)
            ->where('created_at', '>=', $start)
            ->selectRaw('
                DATE(created_at) as date,
                SUM(qte_vendu) as volume
            ')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'    => $row->date,
                'montant' => 0, // prévu plus tard
                'volume'  => (float) $row->volume,
            ])
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 RÉPARTITION PAR TYPE DE POMPE
     * (ESSENCE / GASOIL)
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
                'type_pompe' => $row->type_pompe, // essence / gasoil
                'volume'     => (float) $row->volume,
            ])
            ->toArray();
    }

    /**
     * =================================================
     * 🔹 VOLUME VENDU PAR POMPE
     * =================================================
     */
    private function getVolumeParPompe(): array
    {
        return LigneVente::visible()
            ->where('ligne_ventes.status', true)

            // ligne_ventes → affectations
            ->join('affectations', 'ligne_ventes.id_affectation', '=', 'affectations.id')

            // affectations → pompes
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
                SUM(qte_appro) as volume
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
