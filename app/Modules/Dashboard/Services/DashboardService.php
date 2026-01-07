<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Vente\Models\LigneVente;
use App\Modules\Settings\Models\Pompe;
use App\Modules\Caisse\Models\OperationCompte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * =================================================
     * 🔹 Dashboard principal
     * =================================================
     */
    public function getDashboard(): array
    {
        return [
            'kpis'                     => $this->getKpis(),
            'progression_7_jours'      => $this->getProgression7Jours(),
            'repartition_carburant'    => $this->getRepartitionCarburant(),
            'volume_par_pompe'         => $this->getVolumeParPompe(),
            'approvisionnements_30j'   => $this->getApprovisionnements30Jours(),
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

    // ✅ RECETTES DU JOUR
    // ⚠️ À définir selon ta vraie source (caisse / opérations / encaissements)
    // ex: OperationCompte (nature entrée/sortie) ou autre table
    $recettesDuJour = 0;

    $volumeVendu = (clone $ventes)->sum('qte_vendu');

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
            'montant' => 0, // ⚠️ à implémenter plus tard
            'volume'  => (float) $row->volume,
        ])
        ->toArray();
}


    /**
     * =================================================
     * 🔹 RÉPARTITION PAR CARBURANT
     * =================================================
     */
    private function getRepartitionCarburant(): array
    {
        return LigneVente::visible()
            ->where('status', true)
            ->join('pompes', 'ligne_ventes.id_pompe', '=', 'pompes.id')
            ->join('cuves', 'pompes.id_cuve', '=', 'cuves.id')
            ->join('carburants', 'cuves.id_carburant', '=', 'carburants.id')
            ->selectRaw('
                carburants.libelle as carburant,
                SUM(ligne_ventes.qte_vendu) as volume
            ')
            ->groupBy('carburants.libelle')
            ->get()
            ->map(fn ($row) => [
                'carburant' => $row->carburant,
                'volume'    => (float) $row->volume,
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
            ->where('status', true)
            ->join('pompes', 'ligne_ventes.id_pompe', '=', 'pompes.id')
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
        return DB::table('approvisionnements')
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
