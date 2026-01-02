<?php

namespace App\Modules\Caisse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Caisse\Services\TypeOperationService;
use App\Modules\Caisse\Requests\StoreTypeOperationRequest;
use App\Modules\Caisse\Requests\UpdateTypeOperationRequest;

class TypeOperationController extends Controller
{
    public function __construct(private TypeOperationService $service)
    {
    }

    /**
     * LISTE DES TYPES (AVEC AUTO-INITIALISATION)
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * DÉTAIL
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * CRÉATION
     */
    public function store(StoreTypeOperationRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * MISE À JOUR
     */
    public function update(UpdateTypeOperationRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * SUPPRESSION
     */
    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
