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
                ->with('cuve.station')
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
                ->with('cuve.station')
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
     * CRÉATION (DÉDUCTION IMMÉDIATE)
     * =========================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {
            $qte = (float) ($data['qte_vendu'] ?? 0);

            if ($qte <= 0) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Quantité vendue invalide.',
                ], 409);
            }

            // 🔒 CUVE SANS SCOPE
            $cuve = Cuve::lockForUpdate()->find($data['id_cuve']);

            if (! $cuve) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ], 404);
            }

            if ($qte > $cuve->qt_actuelle) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Stock insuffisant dans la cuve.',
                ], 409);
            }

            // 🔻 Déduction stock
            $cuve->update([
                'qt_actuelle' => $cuve->qt_actuelle - $qte,
            ]);

            // 🔹 Création vente
            $vente = VenteLitre::create([
                'id_cuve'     => $cuve->id,
                'qte_vendu'   => $qte,
                'commentaire' => $data['commentaire'] ?? null,
                'status'      => true,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Vente enregistrée et stock déduit.',
                'data'    => new VenteLitreResource($vente->load('cuve.station')),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la vente.',
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION (ROLLBACK STOCK)
     * =========================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {
            $vente = VenteLitre::visible()
                ->with('cuve')
                ->lockForUpdate()
                ->find($id);

            if (! $vente) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable.',
                ], 404);
            }

            if ($vente->cuve) {
                $vente->cuve->update([
                    'qt_actuelle' => $vente->cuve->qt_actuelle + $vente->qte_vendu,
                ]);
            }

            $vente->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Vente supprimée et stock restauré.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la vente.',
            ], 500);
        }
    }
}
