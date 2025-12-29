<?php
namespace App\Modules\Settings\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Affectation;
use App\Modules\Settings\Models\Pompe;
use App\Modules\Settings\Resources\AffectationResource;
use App\Modules\Vente\Models\Cuve;
use App\Modules\Vente\Models\LigneVente;
use Exception;
use Illuminate\Support\Facades\DB;

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
                'user',        // ✅ UserResource
                'station',     // ✅ StationResource
                'pompe.station.ville.pays',       // ✅ PompeResource
                'createdBy',
                'modifiedBy',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => AffectationResource::collection($affectations),
        ]);

    } catch (\Throwable $e) {

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

        /**
         * =================================================
         * 1. RÉCUPÉRATION UTILISATEUR
         * =================================================
         */
        $user = User::findOrFail($data['id_user']);

        /**
         * =================================================
         * 2. UN UTILISATEUR = UNE SEULE AFFECTATION ACTIVE
         * =================================================
         */
        $hasActiveUser = Affectation::where('id_user', $user->id)
            ->where('status', true)
            ->exists();

        if ($hasActiveUser) {
            DB::rollBack();

            return response()->json([
                'status'  => 409,
                'message' => 'Cet utilisateur possède déjà une affectation active.',
            ]);
        }

        /**
         * =================================================
         * 3. CONTRÔLE id_pompe SELON LE RÔLE
         * =================================================
         */
        if ($user->role !== 'pompiste') {
            // 🔒 Sécurité : un non-pompiste ne doit PAS avoir de pompe
            $data['id_pompe'] = null;
        }

        /**
         * =================================================
         * 4. POMPISTE → CONTRÔLES SPÉCIFIQUES
         * =================================================
         */
        if ($user->role === 'pompiste') {

            // 🔴 id_pompe obligatoire
            if (empty($data['id_pompe'])) {
                DB::rollBack();

                return response()->json([
                    'status'  => 422,
                    'message' => 'id_pompe est obligatoire pour un pompiste.',
                ]);
            }

            // 🔴 Une pompe = un seul pompiste actif
            $hasActivePompe = Affectation::where('id_pompe', $data['id_pompe'])
                ->where('status', true)
                ->exists();

            if ($hasActivePompe) {
                DB::rollBack();

                return response()->json([
                    'status'  => 409,
                    'message' => 'Cette pompe est déjà affectée à un autre pompiste.',
                ]);
            }
        }

        /**
         * =================================================
         * 5. CRÉATION DE L’AFFECTATION
         * =================================================
         */
        $affectation = Affectation::create([
            'id_user'    => $user->id,
            'id_station' => $data['id_station'],
            'id_pompe'   => $data['id_pompe'] ?? null,
            'status'     => true,
        ]);

        /**
         * =================================================
         * 6. OUVERTURE DE VENTE (UNIQUEMENT POMPISTE)
         * =================================================
         */
        if ($user->role === 'pompiste') {

            if (! isset($data['index_debut'])) {
                DB::rollBack();

                return response()->json([
                    'status'  => 422,
                    'message' => 'index_debut est obligatoire pour un pompiste.',
                ]);
            }

            $pompe = Pompe::findOrFail($data['id_pompe']);

            $cuve = Cuve::where('id_station', $data['id_station'])
                ->where('type_cuve', $pompe->type_pompe)
                ->where('status', true)
                ->first();

            if (! $cuve) {
                DB::rollBack();

                return response()->json([
                    'status'  => 404,
                    'message' => "Aucune cuve '{$pompe->type_pompe}' trouvée pour cette station.",
                ]);
            }

            LigneVente::create([
                'id_station'     => $data['id_station'],
                'id_cuve'        => $cuve->id,
                'id_affectation' => $affectation->id,
                'index_debut'    => $data['index_debut'],
                'status'         => false,
            ]);
        }

        DB::commit();

        return response()->json([
            'status'  => 200,
            'message' => 'Affectation créée avec succès.',
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
