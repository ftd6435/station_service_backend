<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Ville;
use App\Modules\Settings\Resources\VilleResource;
use Exception;

class VilleService
{
    /**
     * ============================
     * Créer une ville
     * ============================
     */
    public function store(array $data)
    {
        try {

            $ville = Ville::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Ville créée avec succès.',
                'data'    => new VilleResource($ville),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la ville.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Mettre à jour une ville
     * ============================
     */
    public function update(int $id, array $data)
    {
        try {

            $ville = Ville::findOrFail($id);
            $ville->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Ville mise à jour avec succès.',
                'data'    => new VilleResource($ville),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la mise à jour de la ville.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Supprimer une ville
     * ============================
     */
    public function delete(int $id)
    {
        try {

            $ville = Ville::findOrFail($id);
            $ville->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Ville supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la ville.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * ============================
     * Liste des villes
     * ============================
     */
    public function getAll()
    {
        try {

            $villes = Ville::with('pays')
                ->orderBy('libelle')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => VilleResource::collection($villes),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des villes.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
