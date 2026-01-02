<?php

namespace App\Modules\Caisse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Caisse\Services\CompteService;
use App\Modules\Caisse\Requests\StoreCompteRequest;
use App\Modules\Caisse\Requests\UpdateCompteRequest;

class CompteController extends Controller
{
    public function __construct(private CompteService $service)
    {
    }

    /**
     * LISTE DES COMPTES
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * DÉTAIL D’UN COMPTE
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * CRÉATION
     */
    public function store(StoreCompteRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * MISE À JOUR
     */
    public function update(UpdateCompteRequest $request, int $id)
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
