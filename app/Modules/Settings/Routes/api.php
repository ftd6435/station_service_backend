<?php

use App\Modules\Settings\Controllers\PaysController;
use App\Modules\Settings\Controllers\VilleController;
use Illuminate\Support\Facades\Route;
Route::middleware(['station.db','auth:sanctum'])->prefix('v1/settings')->group(function () {
    Route::apiResource('pays',PaysController::class);
    Route::apiResource('villes', VilleController::class);
    Route::apiResource('stations', VilleController::class);
    
   
});
