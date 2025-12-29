<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreValidationVenteRequest;
use App\Modules\Vente\Services\ValidationVenteService;

class ValidationVenteController extends Controller
{
    public function __construct(
        private ValidationVenteService $service
    ) {}

    /**
     * =========================
     * LISTE DES VALIDATIONS
     * =========================
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * =========================
     * DÉTAIL D’UNE VALIDATION
     * =========================
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * =========================
     * VALIDER UNE VENTE
     * =========================
     */
    public function store(StoreValidationVenteRequest $request)
    {
        return $this->service->store(
            $request->validated()
        );
    }

    /**
     * =========================
     * SUPPRIMER UNE VALIDATION
     * (cas exceptionnel / rollback)
     * =========================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
