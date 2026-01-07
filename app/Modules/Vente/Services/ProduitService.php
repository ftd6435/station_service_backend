<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Resources\ProduitResource;
use Exception;

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
}
