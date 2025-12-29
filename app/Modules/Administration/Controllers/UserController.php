<?php

namespace App\Modules\Administration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Administration\Services\UserService;
use App\Modules\Administration\Requests\StoreUserRequest;
use App\Modules\Administration\Requests\UpdateUserRequest;
use App\Modules\Administration\Requests\LoginRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * ============================
     * Liste des utilisateurs
     * ============================
     */
    public function index()
    {
        return $this->userService->getAll();
    }

     public function pompisteDisp()
    {
        return $this->userService->pompisteDisp();
    }

    /**
     * ============================
     * Création utilisateur
     * ============================
     */
    public function store(StoreUserRequest $request)
    {
        return $this->userService->store($request->validated());
    }

    /**
     * ============================
     * Détail utilisateur
     * ============================
     */
    public function show(int $id)
    {
        return $this->userService->getOne($id);
    }

    /**
     * ============================
     * Mise à jour utilisateur
     * ============================
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        return $this->userService->update($id, $request->validated());
    }

    /**
     * ============================
     * Suppression utilisateur
     * ============================
     */
    public function destroy(int $id)
    {
        return $this->userService->delete($id);
    }

    /**
     * ============================
     * Connexion utilisateur
     * ============================
     */
    public function login(LoginRequest $request)
    {
        return $this->userService->login($request->validated());
    }

    
}
