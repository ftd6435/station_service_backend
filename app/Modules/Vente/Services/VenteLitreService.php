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

            $niveauCuve = (float) ($data['qte_vendu'] ?? 0);

            if ($niveauCuve < 0) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Lecture de cuve invalide.',
                ], 422);
            }

            /**
             * =================================================
             * 🔒 CUVE VISIBLE (PAS DE DÉDUCTION)
             * =================================================
             */
            $cuve = Cuve::visible()->find($data['id_cuve']);

            if (! $cuve) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable ou non autorisée.',
                ], 404);
            }

            /**
             * =================================================
             * 🔹 ENREGISTREMENT LECTURE CUVE
             * (MATIN OU SOIR)
             * =================================================
             */
            $lecture = VenteLitre::create([
                'id_cuve'     => $cuve->id,
                'qte_vendu'   => $niveauCuve, // 🔥 niveau réel, pas une vente
                'commentaire' => $data['commentaire'] ?? null,
                'status'      => true,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Lecture de cuve enregistrée avec succès.',
                'data'    => new VenteLitreResource(
                    $lecture->load('cuve.station')
                ),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de l’enregistrement de la lecture cuve.',
                'error'   => $e->getMessage(),
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
