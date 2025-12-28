<?php
namespace App\Modules\Vente\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vente\Requests\LigneVenteRequest;
use App\Modules\Vente\Services\LigneVenteService;

class LigneVenteController extends Controller
{
    public function __construct(private LigneVenteService $service)
    {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function show(int $id)
    {
        return $this->service->getOne($id);
    }

    public function store(LigneVenteRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(LigneVenteRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
