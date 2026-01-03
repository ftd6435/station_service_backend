<?php

use App\Modules\Caisse\Controllers\CompteController;
use App\Modules\Caisse\Controllers\OperationCompteController;
use App\Modules\Caisse\Controllers\TypeOperationController;
use Illuminate\Support\Facades\Route;

// Define API routes for Caisse module here
Route::middleware(['station.db', 'auth:sanctum'])
    ->prefix('v1/caisse')
    ->group(function () {

        Route::apiResource('comptes', CompteController::class);

        Route::apiResource('type-operations', TypeOperationController::class);

        Route::apiResource('operations', OperationCompteController::class);

        // =============================================
        // TRANSFERT INTER-STATION
        // =============================================
        Route::post(
            'operations/transfert',
            [OperationCompteController::class, 'tracnsfer']
        );

        // =============================================
        // CONFIRMATION TRANSFERT
        // =============================================
        Route::post(
            'operations/transfert/confirm',
            [OperationCompteController::class, 'confirm']
        );

        // =============================================
        // ANNULATION TRANSFERT
        // =============================================
        Route::post(
            'operations/transfert/cancel',
            [OperationCompteController::class, 'cancel']
        );

    });
