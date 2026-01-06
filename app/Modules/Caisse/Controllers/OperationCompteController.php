<?php

namespace App\Modules\Caisse\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Caisse\Services\OperationCompteService;
use App\Modules\Caisse\Requests\StoreOperationCompteRequest;
use App\Modules\Caisse\Requests\StoreTransfertCompteRequest;
use App\Modules\Caisse\Requests\ConfirmTransfertCompteRequest;

class OperationCompteController extends Controller
{
    public function __construct(private OperationCompteService $service)
    {
    }

    /**
     * LISTE DES OPÉRATIONS
     */
    public function index()
    {
        return $this->service->getAll();
    }
     public function listeTransfet()
    {
        return $this->service->getAll1();
    }

    /**
     * DÉTAIL
     */
    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    /**
     * OPÉRATION SIMPLE (ENTRÉE / SORTIE)
     */
    public function store(StoreOperationCompteRequest $request)
    {
        return $this->service->store($request->validated());
    }

    /**
     * TRANSFERT INTER-STATION (ENVOI)
     */
    public function transfer(StoreTransfertCompteRequest $request)
    {
        return $this->service->transfer($request->validated());
    }

    /**
     * CONFIRMATION TRANSFERT
     */
    public function confirm(ConfirmTransfertCompteRequest $request)
    {
        return $this->service->confirm($request->validated()['reference']);
    }

    /**
     * ANNULATION TRANSFERT
     */
    public function cancel(ConfirmTransfertCompteRequest $request)
    {
        return $this->service->cancel($request->validated()['reference']);
    }
}
