<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Produit;
use App\Modules\Vente\Resources\ProduitResource;
use Exception;

class ProduitService
{
    /**
     * =========================
     * LISTE DES PRODUITS
     * =========================
     */
    public function getAll()
    {
        try {

            $produits = Produit::orderBy('libelle')->get();

            return response()->json([
                'status' => 200,
                'data'   => ProduitResource::collection($produits),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des produits.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UN PRODUIT
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $produit = Produit::findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new ProduitResource($produit),
            ]);

        } catch (Exception) {

            return response()->json([
                'status'  => 404,
                'message' => 'Produit introuvable.',
            ]);
        }
    }

    /**
     * =========================
     * CRÉATION
     * =========================
     */
    public function store(array $data)
    {
        try {

            $produit = Produit::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Produit créé avec succès.',
                'data'    => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du produit.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * MODIFICATION
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $produit = Produit::findOrFail($id);
            $produit->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Produit modifié avec succès.',
                'data'    => new ProduitResource($produit),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification du produit.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    public function delete(int $id)
    {
        try {

            Produit::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Produit supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du produit.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
