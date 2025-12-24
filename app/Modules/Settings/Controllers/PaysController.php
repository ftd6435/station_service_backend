<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\StorePaysRequest;
use App\Modules\Settings\Requests\UpdatePaysRequest;
use App\Modules\Settings\Services\PaysService;
use Illuminate\Http\Request;

class PaysController extends Controller
{
    public function __construct(
        protected PaysService $service
    ) {}

    /**
     * ============================
     * Liste des pays
     * ============================
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * ============================
     * Créer un pays
     * ============================
     */
    public function store(StorePaysRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * ============================
     * Mettre à jour un pays
     * ============================
     */
    public function update(UpdatePaysRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * ============================
     * Supprimer un pays
     * ============================
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
