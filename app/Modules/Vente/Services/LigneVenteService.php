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

            // =================================================
            // 1. Récupération ligne de vente
            // =================================================
            $item = LigneVente::find($id);

            if (! $item) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Ligne de vente introuvable.',
                ]);
            }

            // =================================================
            // 2. Index existant (déjà en base)
            // =================================================
            $indexDebut = (float) $item->index_debut;

            // Index fin envoyé
            $indexFin = isset($data['index_fin'])
                ? (float) $data['index_fin']
                : null;

            if ($indexFin === null) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Index fin requis pour la mise à jour.',
                ]);
            }

            // =================================================
            // 3. Sécurité métier : cohérence des index
            // =================================================
            if ($indexFin < $indexDebut) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Index incohérent : index_fin doit être supérieur ou égal à index_debut.',
                ]);
            }

            // =================================================
            // 4. Calcul automatique de la quantité vendue
            // =================================================
            $qteVendu = $indexFin - $indexDebut;

            // =================================================
            // 5. Mise à jour sécurisée
            // =================================================
            $item->update([
                'index_fin' => $indexFin,
                'qte_vendu' => $qteVendu,
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Ligne de vente modifiée avec succès.',
                'data'    => new LigneVenteResource($item->fresh()),
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de la modification de la ligne de vente.',
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
