<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\ParametrageStation;
use App\Modules\Settings\Resources\ParametrageStationResource;
use Exception;

class ParametrageStationService
{
    public function getAll()
    {
        try {

            $params = ParametrageStation::with(['station','createdBy','modifiedBy'])->get();

            return response()->json([
                'status' => 200,
                'data'   => ParametrageStationResource::collection($params),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la récupération du paramétrage des stations.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function store(array $data)
    {
        try {

            $param = ParametrageStation::create($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Paramétrage station créé avec succès.',
                'data'    => new ParametrageStationResource($param),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la création du paramétrage station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function update(int $id, array $data)
    {
        try {

            $param = ParametrageStation::findOrFail($id);
            $param->update($data);

            return response()->json([
                'status'  => 200,
                'message' => 'Paramétrage station modifié avec succès.',
                'data'    => new ParametrageStationResource($param),
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la modification du paramétrage station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {

            ParametrageStation::findOrFail($id)->delete();

            return response()->json([
                'status'  => 200,
                'message' => 'Paramétrage station supprimé avec succès.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 500,
                'message' => 'Erreur lors de la suppression du paramétrage station.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
