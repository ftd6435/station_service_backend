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
    $dateCarbon = Carbon::today();

    /**
     * 1️⃣ MESURE MATIN (première saisie du jour)
     */
    $mesureMatin = DB::table('vente_litres')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $dateCarbon)
        ->orderBy('created_at', 'asc')
        ->value('qte_vendu');

    /**
     * 2️⃣ MESURE SOIR (dernière saisie du jour)
     */
    $mesureSoir = DB::table('vente_litres')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $dateCarbon)
        ->orderBy('created_at', 'desc')
        ->value('qte_vendu');

    /**
     * 3️⃣ STOCK INITIAL
     * → dernière mesure AVANT aujourd’hui
     */
    $stockInitial = DB::table('vente_litres')
        ->where('id_cuve', $idCuve)
        ->where('created_at', '<', $dateCarbon->copy()->startOfDay())
        ->orderByDesc('created_at')
        ->value('qte_vendu') ?? 0;

    /**
     * 4️⃣ ENTRÉES (approvisionnement du jour)
     */
    $entrees = DB::table('approvisionnement_cuves')
        ->where('id_cuve', $idCuve)
        ->whereDate('created_at', $dateCarbon)
        ->sum('qte_appro');

    /**
     * 5️⃣ STOCK PHYSIQUE (lecture du soir)
     */
    $stockPhysique = $mesureSoir ?? $stockInitial;

    /**
     * 6️⃣ SORTIES (LOGIQUE EXCEL)
     * sorties = (stock initial + entrées) - stock physique
     */
    $sorties = ($stockInitial + $entrees) - $stockPhysique;

    /**
     * 7️⃣ RETOUR CUVE (manuel pour l’instant)
     */
    $retourCuve = 0;

    /**
     * 8️⃣ STOCK THÉORIQUE
     */
    $stockTheorique = $stockInitial + $entrees - $sorties + $retourCuve;

    /**
     * 9️⃣ ÉCART
     */
    $ecart = $stockPhysique - $stockTheorique;

    return [
        'date'            => $dateCarbon->toDateString(),
        'id_cuve'         => $idCuve,
        'stock_initial'   => (float) $stockInitial,
        'entrees'         => (float) $entrees,
        'sorties'         => (float) $sorties,
        'retour_cuve'     => (float) $retourCuve,
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
