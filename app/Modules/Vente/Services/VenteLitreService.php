<?php
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\VenteLitre;
use App\Modules\Vente\Resources\VenteLitreResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class VenteLitreService
{
    /**
     * =========================
     * LISTE DES VENTES
     * =========================
     */
    public function getAll()
    {
        try {
            $items = VenteLitre::visible()
                ->with('cuve')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => VenteLitreResource::collection($items),
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des ventes.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL
     * =========================
     */
    public function getOne(int $id)
    {
        try {
            $item = VenteLitre::visible()
                ->with('cuve')
                ->findOrFail($id);

            return response()->json([
                'status' => 200,
                'data'   => new VenteLitreResource($item),
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Vente introuvable.',
            ], 404);
        }
    }

    /**
     * =========================
     * CRÉATION (VENTE EN COURS)
     * =========================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            // =================================================
            // 🔹 Cuve visible + verrouillage
            // =================================================
            $cuve = Cuve::visible()
                ->lockForUpdate()
                ->find($data['id_cuve']);

            if (! $cuve) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable ou non autorisée.',
                ], 404);
            }

            // =================================================
            // 🔹 Vérification stock
            // =================================================
            if ($data['qte_vendu'] > $cuve->qt_actuelle) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Stock insuffisant dans la cuve.',
                ], 409);
            }

            // =================================================
            // 🔹 Déduction immédiate du stock cuve
            // =================================================
            $cuve->update([
                'qt_actuelle' => $cuve->qt_actuelle - (float) $data['qte_vendu'],
            ]);

            // =================================================
            // 🔹 Création vente
            // =================================================
            $vente = VenteLitre::create([
                'id_cuve'     => $cuve->id,
                'qte_vendu'   => (float) $data['qte_vendu'],
                'commentaire' => $data['commentaire'] ?? null,
                'status'      => true, // ✅ vente directement effective
            ]);

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Vente enregistrée et stock mis à jour avec succès.',
                'data'    => new VenteLitreResource($vente->load('cuve')),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * VALIDATION (CLÔTURE)
     * =========================
     */
    public function validateVente(int $id)
    {
        DB::beginTransaction();

        try {
            $vente = VenteLitre::visible()
                ->lockForUpdate()
                ->find($id);

            if (! $vente) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable.',
                ], 404);
            }

            if ($vente->status === true) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette vente est déjà validée.',
                ], 409);
            }

            $cuve = Cuve::lockForUpdate()->find($vente->id_cuve);

            if (! $cuve) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ], 404);
            }

            // 🔹 Décrément réel du stock
            if ($vente->qte_vendu > $cuve->qt_actuelle) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Stock insuffisant pour valider la vente.',
                ], 409);
            }

            $cuve->update([
                'qt_actuelle' => $cuve->qt_actuelle - $vente->qte_vendu,
            ]);

            $vente->update([
                'status' => true,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente validée et stock mis à jour.',
                'data'    => new VenteLitreResource($vente->fresh()->load('cuve')),
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la validation de la vente.',
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
            $item = VenteLitre::visible()->findOrFail($id);
            $item->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente supprimée avec succès.',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la vente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
