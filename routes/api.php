<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PosIntegrationController;

// CapyPOS Integration Routes
Route::prefix('pos')->group(function () {
    Route::get('/session-status', [PosIntegrationController::class, 'checkSession']);
    Route::post('/sales', [PosIntegrationController::class, 'storeSale']);
});
