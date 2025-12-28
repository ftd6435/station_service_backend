<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Resources\LigneVenteResource;
use Exception;

class LigneVenteService
{
    /**
     * =========================
     * LISTE DES LIGNES DE VENTE
     * =========================
     */
    public function getAll()
    {
        try {

            $items = LigneVente::visible()
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => LigneVenteResource::collection($items),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des lignes de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UNE LIGNE DE VENTE
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $item = LigneVente::visible()->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new LigneVenteResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 404,
                'message' => 'Ligne de vente introuvable.',
                'error'   => $e->getMessage(),
            ], 404);
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

            $item = LigneVente::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Ligne de vente créée avec succès.',
                'data'    => new LigneVenteResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * MISE À JOUR
     * =========================
     */
    public function update(int $id, array $data)
    {
        try {

            $item = LigneVente::visible()->findOrFail($id);
            $item->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Ligne de vente modifiée avec succès.',
                'data'    => new LigneVenteResource($item),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
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

            $item = LigneVente::visible()->findOrFail($id);
            $item->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Ligne de vente supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
