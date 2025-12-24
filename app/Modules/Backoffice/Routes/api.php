<?php

use App\Modules\Backoffice\Controllers\AuthUserController;
use App\Modules\Backoffice\Controllers\ClientController;
use App\Modules\Backoffice\Controllers\UserController;
use App\Modules\Backoffice\Controllers\UserGenerateDbContoller;
use Illuminate\Support\Facades\Route;

/*
    |--------------------------------------------------------------------------
    | ADMINISTRATION SPA TECHNOLOGY
    |--------------------------------------------------------------------------
    |
    | Routes d'authentification des administrateur
    | Seul la route LOGIN n'est pas protéger par le middleware auth:sanctum
    | Pour ajouter un autre utilisateur ou performer des tâches comme générer la base de données, il faut s'authentifié d'abord
    |
*/

Route::prefix('v1/users')->group(function () {
    Route::post('/login', [AuthUserController::class, 'login']);
    Route::post('/signup', [AuthUserController::class, 'signup']);
});

Route::middleware(['auth:sanctum'])->prefix('v1/users')->group(function () {
    Route::post('/logout', [AuthUserController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->prefix('v1/users')->group(function () {
    Route::post('/generate-db', [UserGenerateDbContoller::class, 'generate']);
});

Route::middleware(['auth:sanctum'])->prefix('v1/users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/clients', [UserController::class, 'clients']);
    Route::put('/role/{id}', [UserController::class, 'role']);
});





/*
    |--------------------------------------------------------------------------
    | GESTION DES CLIENTS
    |--------------------------------------------------------------------------
    |
    | Routes d'authentification des clients
    | Chaque client peut s'inscrire et s'authentifié
    | Pour acheter une licence ou ajouter une nouvelle école, il faut s'authentifié d'abord
    |
*/
Route::prefix('v1/clients')->group(function () {
    Route::post('/signup', [ClientController::class, 'signup']);
});
