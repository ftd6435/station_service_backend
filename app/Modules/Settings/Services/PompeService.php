<?php
namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Pompe;
use App\Modules\Settings\Resources\PompeResource;
use App\Modules\Vente\Models\LigneVente;
use Exception;

class PompeService
{

    public function getAll()
    {
        try {

            // 🔹 Requête avec filtrage métier explicite
            $pompes = Pompe::visible()
                ->with([
                    'station.ville',
                    'createdBy',
                    'modifiedBy',
                ])
                ->orderBy('reference')
                ->get();

            return response()->json([
                'status' => 200,
                'data'   => PompeResource::collection($pompes),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération des pompes.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
    public function pompesDisponibles()
{
    try {

        $pompes = Pompe::visible()
            ->available()
            ->orderBy('libelle')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => PompeResource::collection($pompes),
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status'  => 500,
            'message' => 'Erreur lors de la récupération des pompes disponibles.',
            'error'   => $e->getMessage(),
        ]);
    }
}


public function getDernierIndexPourAffectation(int $id_pompe): array
{
    try {

        // =================================================
        // 1. POMPE
        // =================================================
        $pompe = Pompe::visible()->findOrFail($id_pompe);

        // =================================================
        // 2. DERNIÈRE VENTE (SANS SCOPE)
        // =================================================
        $lastVente = LigneVente::withoutGlobalScopes()
            ->whereHas('affectation', function ($q) use ($id_pompe) {
                $q->where('id_pompe', $id_pompe);
            })
            ->where('status', true) // vente fermée
            ->orderByDesc('created_at')
            ->first();

        // =================================================
        // 3. INDEX
        // =================================================
        return [
            'status'      => 200,
            'index_debut' => (float) (
                $lastVente && ! is_null($lastVente->index_fin)
                    ? $lastVente->index_fin
                    : $pompe->index_initial
            ),
        ];

    } catch (\Throwable $e) {

        return [
            'status' => 500,
            'error'  => $e->getMessage(),
        ];
    }
}


    public function store(array $data)
    {
        try {

            $pompe = Pompe::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Pompe créée avec succès.',
                'data'    => new PompeResource($pompe),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création de la pompe.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $pompe = Pompe::findOrFail($id);
            $pompe->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Pompe modifiée avec succès.',
                'data'    => new PompeResource($pompe),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification de la pompe.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {

            Pompe::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Pompe supprimée avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression de la pompe.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
