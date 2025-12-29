<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Services\PompeService;
use App\Modules\Settings\Requests\StorePompeRequest;
use App\Modules\Settings\Requests\UpdatePompeRequest;

class PompeController extends Controller
{
    public function __construct(private PompeService $service) {}

   
    public function index()
    {
        return $this->service->getAll();
    }
     public function  pompesDisponibles()
    {
        return $this->service->pompesDisponibles();
    }

    public function store(StorePompeRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function update(UpdatePompeRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->delete($id);
    }
}
