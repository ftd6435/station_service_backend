<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\LigneVenteRequest;
use App\Modules\Vente\Services\LigneVenteService;
use Illuminate\Http\JsonResponse;

class LigneVenteController extends Controller
{
    public function __construct(
        private readonly LigneVenteService $service
    ) {}

    /**
     * Liste des ventes
     */
    public function index(): JsonResponse
    {
        return $this->service->getAll();
    }

    /**
     * Détail d'une vente
     */
    public function show(int $id): JsonResponse
    {
        return $this->service->getOne($id);
    }

    /**
     * Création d'une vente
     */
    public function store(LigneVenteRequest $request): JsonResponse
    {
        return $this->service->store($request->validated());
    }

    /**
     * Mise à jour d'une vente
     */
    public function update(LigneVenteRequest $request, int $id): JsonResponse
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * Suppression d'une vente
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
