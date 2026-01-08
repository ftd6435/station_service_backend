<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Resources\ProduitResource;
use Exception;

    use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProduitService
{
    /**
     * =========================
     * LISTE DES CUVES
     * =========================
     */
    public function getAll()
    {
        try {

            $produits = Cuve::visible()
                ->orderBy('libelle')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => ProduitResource::collection($produits),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des cuves.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UNE CUVE
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $produit = Cuve::visible()->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Cuve introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION (CUVE)
     * =========================
     */
    // public function store(array $data)
    // {
    //     try {

    //         $produit = Cuve::create($data);

    //         return response()->json([
    //             'status'  => 200,
    //             'message' => 'Cuve créée avec succès.',
    //             'data'    => new ProduitResource($produit),
    //         ]);

    //     } catch (Exception $e) {

    //         return response()->json([
    //             'status'  => 500,
    //             'message' => 'Erreur lors de la création de la cuve.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function store(array $data)
{
    try {

        // =================================================
        // 🔹 INITIALISATION STOCK
        // qt_actuelle = qt_initial à la création
        // =================================================
        if (
            array_key_exists('qt_initial', $data)
            && ! array_key_exists('qt_actuelle', $data)
        ) {
            $data['qt_actuelle'] = $data['qt_initial'];
        }

        // =================================================
        // 🔹 CRÉATION CUVE
        // =================================================
        $produit = Cuve::create($data);

        return response()->json([
            'status'  => 200,
            'message' => 'Cuve créée avec succès.',
            'data'    => new ProduitResource($produit),
        ]);

    } catch (Exception $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la création de la cuve.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    /**
     * =========================
     * MODIFICATION (CUVE)
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $produit = Cuve::visible()->findOrFail($id);
            $produit->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve modifiée avec succès.',
                'data'    => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la cuve.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION (CUVE)
     * =========================
     */
    public function delete(int $id)
    {
        try {

            $produit = Cuve::visible()->findOrFail($id);
            $produit->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Cuve supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la cuve.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

private function calculerStockJournalierParCuve(int $idCuve): array
{
    $date = Carbon::today();

    /**
     * 1️⃣ STOCK MATIN
     * → première lecture cuve de la journée
     */
    $stockMatin = DB::table('vente_litres')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $date)
        ->orderBy('created_at', 'asc')   // 👈 IMPORTANT
        ->value('qte_vendu') ?? 0;

    /**
     * 2️⃣ ENTRÉES DU JOUR
     * → bons de livraison
     */
    $entrees = DB::table('approvisionnement_cuves')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $date)
        ->sum('qte_appro');

    /**
     * 3️⃣ SORTIES (VENTES RÉELLES)
     * → issues UNIQUEMENT des ventes par index
     */
    $sorties = DB::table('lignes_vente')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $date)
        ->sum('qte_vendu');

    /**
     * 4️⃣ STOCK THÉORIQUE (LOGIQUE EXCEL / STATION)
     */
    $stockTheorique = $stockMatin + $entrees - $sorties;

    /**
     * 5️⃣ STOCK PHYSIQUE SOIR
     * → dernière lecture cuve de la journée
     */
    $stockPhysique = DB::table('vente_litres')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $date)
        ->orderBy('created_at', 'desc')  // 👈 IMPORTANT
        ->value('qte_vendu') ?? 0;

    /**
     * 6️⃣ ÉCART (CONTRÔLE)
     * positif = surplus
     * négatif = manque
     */
    $ecart = $stockPhysique - $stockTheorique;

    return [
        'date'            => $date->toDateString(),
        'id_cuve'         => $idCuve,
        'stock_matin'     => (float) $stockMatin,
        'entrees'         => (float) $entrees,
        'sorties'         => (float) $sorties,
        'stock_theorique' => (float) $stockTheorique,
        'stock_physique'  => (float) $stockPhysique,
        'ecart'           => (float) $ecart,
    ];
}

public function calculerStockJournalierToutesCuves()
{
    try {

        $resultats = [];

        $cuves = DB::table('cuves')
            ->where('status', true) // cuves visibles / actives
            ->pluck('id');

        foreach ($cuves as $idCuve) {
            $resultats[] = $this->calculerStockJournalierParCuve((int) $idCuve);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Statistiques journalières des cuves.',
            'data'    => $resultats,
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des statistiques.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}





}
