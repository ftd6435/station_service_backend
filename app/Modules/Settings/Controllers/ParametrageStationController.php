<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Services\ParametrageStationService;
use App\Modules\Settings\Requests\StoreParametrageStationRequest;
use App\Modules\Settings\Requests\UpdateParametrageStationRequest;

class ParametrageStationController extends Controller
{
    public function __construct(private ParametrageStationService $service) {}

    public function index()
    {
        return $this->service->getAll();
    }

    public function store(StoreParametrageStationRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdateParametrageStationRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
