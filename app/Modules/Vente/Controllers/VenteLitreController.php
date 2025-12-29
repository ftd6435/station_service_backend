<?php

namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\StoreVenteLitreRequest;
use App\Modules\Vente\Services\VenteLitreService;
use Illuminate\Http\JsonResponse;

class VenteLitreController extends Controller
{
    public function __construct(
        private readonly VenteLitreService $service
    ) {}

    /**
     * =========================
     * LISTE DES VENTES
     * =========================
     */
    public function index(): JsonResponse
    {
        return $this->service->getAll();
    }

    /**
     * =========================
     * DÉTAIL D’UNE VENTE
     * =========================
     */
    public function show(int $id): JsonResponse
    {
        return $this->service->getOne($id);
    }

    /**
     * =========================
     * CRÉATION D’UNE VENTE
     * =========================
     */
    public function store(StoreVenteLitreRequest $request): JsonResponse
    {
        return $this->service->store($request->validated());
    }

    /**
     * =========================
     * SUPPRESSION
     * =========================
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
