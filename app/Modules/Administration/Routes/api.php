<?php

use App\Modules\Administration\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->middleware('station.db')->group(function () {
    Route::post('/login', [UserController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});
