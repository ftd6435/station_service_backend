<?php

namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\LigneVente;
use App\Modules\Vente\Resources\LigneVenteResource;

class LigneVenteService
{
    public function getAll()
    {
        $items = LigneVente::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data'   => LigneVenteResource::collection($items),
        ]);
    }

    public function getOne(int $id)
    {
        $item = LigneVente::findOrFail($id);

        return response()->json([
            'status' => 200,
            'data'   => new LigneVenteResource($item),
        ]);
    }

    public function store(array $data)
    {
        $item = LigneVente::create($data);

        return response()->json([
            'status'  => 200,
            'message' => 'Ligne de vente créée avec succès.',
            'data'    => new LigneVenteResource($item),
        ]);
    }

    public function update(int $id, array $data)
    {
        $item = LigneVente::findOrFail($id);
        $item->update($data);

        return response()->json([
            'status'  => 200,
            'message' => 'Ligne de vente modifiée avec succès.',
            'data'    => new LigneVenteResource($item),
        ]);
    }

    public function delete(int $id)
    {
        LigneVente::findOrFail($id)->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Ligne de vente supprimée.',
        ]);
    }
}
