<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StorePerteCuveRequest;
use App\Modules\Vente\Services\PerteCuveService;

class PerteCuveController extends Controller
{
    public function __construct(
        private PerteCuveService $service
    ) {}

    /**
     * =========================
     * LISTE DES PERTES
     * =========================
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * =========================
     * DÉTAIL D’UNE PERTE
     * =========================
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * =========================
     * ENREGISTRER UNE PERTE
     * =========================
     */
    public function store(StorePerteCuveRequest $request)
    {
        return $this->service->store(
            $request->validated()
        );
    }

    /**
     * =========================
     * SUPPRIMER UNE PERTE
     * =========================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
