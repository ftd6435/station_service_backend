<?php

use App\Modules\Vente\Controllers\ApprovisionnementCuveController;
use App\Modules\Vente\Controllers\LigneVenteController;
use App\Modules\Vente\Controllers\PerteCuveController;
use App\Modules\Vente\Controllers\ProduitController;
use App\Modules\Vente\Controllers\ValidationVenteController;
use App\Modules\Vente\Controllers\VenteLitreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['station.db', 'auth:sanctum'])->prefix('v1/vente')->group(function () {
    Route::apiResource('cuves', ProduitController::class);
    //  Route::apiResource('ligne-ventes',LigneVenteController::class);
    Route::post(
        'ligne-ventes/index-fin/{id}',
        [LigneVenteController::class, 'update']
    );

    Route::get(
        'liste',
        [ValidationVenteController::class, 'index']
    );

    Route::post(
        'validation/ligne-ventes',
        [ValidationVenteController::class, 'store']
    );

    Route::apiResource('appro', ApprovisionnementCuveController::class);
    Route::apiResource('validation', ValidationVenteController::class);
    Route::apiResource('ventre-par-litre', VenteLitreController::class);
    Route::apiResource('perte-cuves', PerteCuveController::class);

});
