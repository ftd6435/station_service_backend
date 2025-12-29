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
    // =================================================
    // 🔒 CONTRAT : TOUJOURS retourner index_debut
    // =================================================
    $indexDebut = 0.0;

    try {

        // 1. Pompe (existence uniquement)
        $pompe = Pompe::withoutGlobalScopes()->find($id_pompe);

        if (! $pompe) {
            return [
                'status'      => 404,
                'index_debut' => $indexDebut,
            ];
        }

        // Valeur par défaut = index initial
        $indexDebut = (float) $pompe->index_initial;

        // 2. Dernière vente réelle de la pompe
        $lastVente = LigneVente::withoutGlobalScopes()
            ->whereHas('affectation', function ($q) use ($id_pompe) {
                $q->where('id_pompe', $id_pompe);
            })
            ->where('status', true) // vente fermée
            ->orderByDesc('created_at')
            ->first();

        if ($lastVente && $lastVente->index_fin !== null) {
            $indexDebut = (float) $lastVente->index_fin;
        }

        return [
            'status'      => 200,
            'index_debut' => $indexDebut,
        ];

    } catch (\Throwable $e) {

        // 🔥 Même en erreur → index_debut existe
        return [
            'status'      => 500,
            'index_debut' => $indexDebut,
            'error'       => $e->getMessage(), // pour debug / logs
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
