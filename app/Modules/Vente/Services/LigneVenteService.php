<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\ValidationVente;
use App\Modules\Vente\Resources\LigneVenteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LigneVenteService
{
    /**
     * =========================
     * LISTE DES LIGNES DE VENTE
     * =========================
     */
    public function getAll(): JsonResponse
    {
        try {
            $items = LigneVente::visible()
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => LigneVenteResource::collection($items),
            ], 200);

        } catch (Throwable $e) {
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
    public function getOne(int $id): JsonResponse
    {
        try {
            $item = LigneVente::visible()->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new LigneVenteResource($item),
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Ligne de vente introuvable.',
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION
     * =========================
     */
    public function store(array $data): JsonResponse
    {
        try {
            $item = LigneVente::create($data);

            return response()->json([
                'status'  => 201,
                'message' => 'Ligne de vente créée avec succès.',
                'data'    => new LigneVenteResource($item),
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * MISE À JOUR / CLÔTURE
     * =========================
     */
    public function update(int $id, array $data): JsonResponse
    {
        DB::beginTransaction();

        try {
            // 1. Ligne visible
            $item = LigneVente::visible()->find($id);

            if (! $item) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Ligne de vente introuvable.',
                ], 404);
            }

            // 2. Déjà validée
            if ((bool) $item->status === true) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette vente est déjà validée.',
                ], 409);
            }

            // 3. Index
            $indexDebut = (float) $item->index_debut;
            $indexFin   = $data['index_fin'] ?? null;

            if ($indexFin === null) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'Index fin requis pour la validation.',
                ], 400);
            }

            $indexFin = (float) $indexFin;

            if ($indexFin < $indexDebut) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Index incohérent : index_fin < index_debut.',
                ], 409);
            }

            // 4. Quantité vendue
            $qteVendu = $indexFin - $indexDebut;

            if ($qteVendu <= 0) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Quantité vendue invalide.',
                ], 409);
            }

            // 5. Mise à jour
            $item->update([
                'index_fin' => $indexFin,
                'qte_vendu' => $qteVendu,
                'status'    => true,
            ]);

            // 6. Validation vente
            ValidationVente::create([
                'id_vente'    => $item->id,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente clôturée et validée avec succès.',
                'data'    => new LigneVenteResource($item->fresh()),
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de la clôture de la vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    public function delete(int $id): JsonResponse
    {
        try {
            $item = LigneVente::visible()->findOrFail($id);
            $item->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Ligne de vente supprimée avec succès.',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la ligne de vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
