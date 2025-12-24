<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\StoreVilleRequest;
use App\Modules\Settings\Http\Requests\UpdateVilleRequest;
use App\Modules\Settings\Services\VilleService;

class VilleController extends Controller
{
    public function __construct(
        protected VilleService $service
    ) {}

    /**
     * ============================
     * Liste des villes
     * ============================
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * ============================
     * Créer une ville
     * ============================
     */
    public function store(StoreVilleRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * ============================
     * Mettre à jour une ville
     * ============================
     */
    public function update(UpdateVilleRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * ============================
     * Supprimer une ville
     * ============================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
