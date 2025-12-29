<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\ValidationVente;
use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Resources\ValidationVenteResource;
use Illuminate\Support\Facades\DB;

class ValidationVenteService
{
    /**
     * =========================
     * LISTE DES VALIDATIONS
     * =========================
     */
    public function getAll()
    {
        try {

            $validations = ValidationVente::visible()
                ->with('vente')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => ValidationVenteResource::collection($validations),
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des validations.',
            ], 500);
        }
    }

    /**
     * =========================
     * DÉTAIL D’UNE VALIDATION
     * =========================
     */
    public function getOne(int $id)
    {
        try {

            $validation = ValidationVente::visible()
                ->with('vente')
                ->find($id);

            if (! $validation) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Validation introuvable.',
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'data'   => new ValidationVenteResource($validation),
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération de la validation.',
            ], 500);
        }
    }

    /**
     * =========================
     * VALIDER UNE VENTE
     * =========================
     */
    public function store(array $data)
    {
        DB::beginTransaction();

        try {

            $vente = LigneVente::visible()
                ->with('cuve')
                ->find($data['id_vente']);

            if (! $vente) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Vente introuvable ou non autorisée.',
                ], 404);
            }

            if ($vente->status === true) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette vente est déjà validée.',
                ], 409);
            }

            $qteVendu = (float) $vente->qte_vendu;

            if ($qteVendu <= 0) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Quantité vendue invalide.',
                ], 409);
            }

            $cuve = $vente->cuve;

            if (! $cuve || $cuve->qte_actuelle < $qteVendu) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Stock cuve insuffisant.',
                ], 409);
            }

            $validation = ValidationVente::create([
                'id_vente'    => $vente->id,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            $cuve->update([
                'qte_actuelle' => $cuve->qte_actuelle - $qteVendu,
            ]);

            $vente->update([
                'status' => true,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 201,
                'message' => 'Vente validée avec succès.',
                'data'    => new ValidationVenteResource(
                    $validation->load('vente')
                ),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur interne lors de la validation.',
            ], 500);
        }
    }

    /**
     * =========================
     * SUPPRESSION / ROLLBACK
     * =========================
     */
    public function delete(int $id)
    {
        DB::beginTransaction();

        try {

            $validation = ValidationVente::visible()
                ->with('vente.cuve')
                ->find($id);

            if (! $validation) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Validation introuvable.',
                ], 404);
            }

            $vente = $validation->vente;
            $cuve  = $vente?->cuve;

            if ($vente && $cuve) {
                $cuve->update([
                    'qte_actuelle' => $cuve->qte_actuelle + $vente->qte_vendu,
                ]);

                $vente->update([
                    'status' => false,
                ]);
            }

            $validation->delete();

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Validation supprimée et vente restaurée.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression.',
            ], 500);
        }
    }
}
