<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\PerteCuve;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Resources\PerteCuveResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class PerteCuveService
{
    /**
     * =========================
     * LISTE DES PERTES
     * =========================
     */
    public function getAll()
    {
        try {

            $pertes = PerteCuve::visible()
                ->with('cuve.station')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => PerteCuveResource::collection($pertes),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des pertes de cuves.',
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UNE PERTE
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $perte = PerteCuve::visible()
                ->with('cuve.station')
                ->find($id);

            if (! $perte) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Perte de cuve introuvable.',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data'   => new PerteCuveResource($perte),
            ], 200);

        } catch (Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération de la perte.',
            ], 500);
        }
    }

    /**
     * =========================
     * ENREGISTRER UNE PERTE
     * =========================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            // =================================================
            // 🔹 Données obligatoires
            // =================================================
            $idCuve = $data['id_cuve'] ?? null;
            $qte    = $data['quantite_perdue'] ?? null;

            if (! $idCuve || ! $qte) {
                return response()->json([
                    'status'  => 400,
                    'message' => 'La cuve et la quantité perdue sont obligatoires.',
                ], 400);
            }

            $qte = (float) $qte;

            if ($qte <= 0) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'La quantité perdue doit être strictement positive.',
                ], 409);
            }

            // =================================================
            // 🔒 RÉCUPÉRATION CUVE (SANS scope visible)
            // =================================================
            $cuve = Cuve::lockForUpdate()->find($idCuve);

            if (! $cuve) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Cuve introuvable.',
                ], 404);
            }

            // =================================================
            // 🔹 Vérification stock
            // =================================================
            if ($qte > $cuve->qt_actuelle) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Stock insuffisant dans la cuve.',
                ], 409);
            }

            // =================================================
            // 🔻 DÉDUCTION IMMÉDIATE DU STOCK
            // =================================================
            $cuve->update([
                'qt_actuelle' => $cuve->qt_actuelle - $qte,
            ]);

            // =================================================
            // 🔹 ENREGISTREMENT DE LA PERTE
            // =================================================
            $perte = PerteCuve::create([
                'id_cuve'         => $cuve->id,
                'quantite_perdue' => $qte,
                'commentaire'     => $data['commentaire'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Perte de cuve enregistrée et stock mis à jour.',
                'data'    => new PerteCuveResource(
                    $perte->load('cuve.station')
                ),
            ], 201);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de l’enregistrement de la perte.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION (ROLLBACK)
     * =========================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {

            $perte = PerteCuve::visible()
                ->with('cuve')
                ->lockForUpdate()
                ->find($id);

            if (! $perte) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Perte de cuve introuvable.',
                ], 404);
            }

            // =================================================
            // 🔄 RESTAURATION DU STOCK
            // =================================================
            if ($perte->cuve) {
                $perte->cuve->update([
                    'qt_actuelle' => $perte->cuve->qt_actuelle + $perte->quantite_perdue,
                ]);
            }

            $perte->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Perte supprimée et stock restauré.',
            ], 200);

        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la perte.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
