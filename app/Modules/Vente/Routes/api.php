<?php

use App\Modules\Vente\Controllers\ApprovisionnementCuveController;
use App\Modules\Vente\Controllers\LigneVenteController;
use App\Modules\Vente\Controllers\ProduitController;
use Illuminate\Support\Facades\Route;
Route::middleware(['station.db','auth:sanctum'])->prefix('v1/vente')->group(function () {
    Route::apiResource('cuves',ProduitController::class);
    //  Route::apiResource('ligne-ventes',LigneVenteController::class);
    Route::post(
    'ligne-ventes/index-fin/{id}',
    [LigneVenteController::class, 'update']
);

      Route::apiResource('appro',ApprovisionnementCuveController::class);
   

    
   
});
