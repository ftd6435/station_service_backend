<?php

use App\Modules\Vente\Controllers\ProduitController;
use Illuminate\Support\Facades\Route;
Route::middleware(['station.db','auth:sanctum'])->prefix('v1/vente')->group(function () {
    Route::apiResource('produits',ProduitController::class);
     Route::apiResource('ligne-ventes',ProduitController::class);
   

    
   
});
