<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Resources\AffectationResource;
use Illuminate\Support\Facades\DB;
use Exception;

class AffectationService
{
    /**
     * =================================================
     * LISTE DES AFFECTATIONS (VISIBILITÉ PAR RÔLE)
     * =================================================
     */
    public function getAll()
    {
        try {

            $affectations = Affectation::visible()
                ->with([
                    'user.affectations',
                    'station',
                    'pompe',
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => AffectationResource::collection($affectations),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des affectations.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =================================================
     * CRÉATION D’UNE AFFECTATION
     * =================================================
     */
   

public function store(array $data)
{
    try {

        DB::beginTransaction();

        // 🔒 RÈGLE MÉTIER :
        // un utilisateur ne peut avoir qu'une seule affectation active
        if (! empty($data['id_user'])) {

            $hasActive = Affectation::where('id_user', $data['id_user'])
                ->where('status', true)
                ->exists();

            if ($hasActive) {

                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Cet utilisateur possède déjà une affectation active. Veuillez d’abord la désactiver.',
                ]);
            }
        }

        // ✅ Création toujours ACTIVE (gérée côté backend)
        $data['status'] = true;

        $affectation = Affectation::create($data);

        $affectation->load([
            'user',
            'station',
            'pompe',
            'createdBy',
        ]);

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Affectation créée avec succès.',
            'data'    => new AffectationResource($affectation),
        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la création de l’affectation.',
            'error'   => $e->getMessage(),
        ]);
    }
}


    /**
     * =================================================
     * MODIFICATION D’UNE AFFECTATION
     * =================================================
     */
    public function update(int $id, array $data)
    {
        try {

            $affectation = Affectation::visible()->findOrFail($id);
            $affectation->update($data);

            $affectation->load([
                'user',
                'station',
                'pompe',
                'modifiedBy',
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Affectation modifiée avec succès.',
                'data'    => new AffectationResource($affectation),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de l’affectation.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * =================================================
     * SUPPRESSION D’UNE AFFECTATION
     * =================================================
     */
    public function delete(int $id)
    {
        try {

            $affectation = Affectation::visible()->findOrFail($id);
            $affectation->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Affectation supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de l’affectation.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
