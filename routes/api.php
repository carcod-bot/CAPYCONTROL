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
    
    // Ventas y Devoluciones
    Route::post('/sales', [PosIntegrationController::class, 'storeSale']);
    Route::get('/sales/{ticket}', [PosIntegrationController::class, 'getSale']);
    Route::post('/refund', [PosIntegrationController::class, 'storeRefund']);
    
    // Clientes y Créditos
    Route::get('/customers', [PosIntegrationController::class, 'searchCustomers']);
    Route::get('/customers/{id}/credit-details', [PosIntegrationController::class, 'getCustomerCreditDetails']);
    Route::post('/customers', [PosIntegrationController::class, 'storeCustomer']);
    Route::post('/credit/pay', [PosIntegrationController::class, 'payCredit']);
    
    // Promociones
    Route::get('/promotions', [PosIntegrationController::class, 'getPromotions']);
});
