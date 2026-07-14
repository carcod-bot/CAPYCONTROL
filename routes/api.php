<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PosIntegrationController;

// CapyPOS Integration Routes
Route::prefix('pos')->group(function () {
    // Sesiones
    Route::get('/session-status', [PosIntegrationController::class, 'checkSession']);
    Route::post('/session/open', [PosIntegrationController::class, 'openSession']);
    Route::get('/session/declaration-totals', [PosIntegrationController::class, 'getDeclarationTotals']);
    Route::post('/session/close', [PosIntegrationController::class, 'closeSession']);
    Route::post('/session/withdraw', [PosIntegrationController::class, 'withdrawCash']);
    Route::post('/session/log-event', [PosIntegrationController::class, 'logEvent']);
    
    // Ventas
    Route::post('/sales', [PosIntegrationController::class, 'storeSale']);
    
    // Clientes
    Route::get('/customers', [PosIntegrationController::class, 'searchCustomers']);
    Route::post('/customers', [PosIntegrationController::class, 'storeCustomer']);
});
