<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CashSessionController;
use App\Http\Controllers\InventoryAdjustmentController;

Route::get('/run-migrations', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate');
    return \Illuminate\Support\Facades\Artisan::output();
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('permission:dashboard.view');
    Route::post('/toggle-dark-mode', [AuthController::class, 'toggleDarkMode'])->name('toggle-dark-mode');

    // --- Inventory Module ---
    Route::middleware('permission:inventory.view')->group(function () {
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('categories', CategoryController::class)->only(['index']);
        Route::resource('brands', BrandController::class)->only(['index']);
        Route::resource('providers', ProviderController::class)->only(['index']);
        Route::get('departments/{department}/categories', [CategoryController::class, 'getByDepartment'])->name('departments.categories');
        Route::resource('products', ProductController::class)->only(['index']);
        
        Route::get('/inventory-adjustments', [InventoryAdjustmentController::class, 'index'])->name('inventory-adjustments.index');
        Route::get('/inventory-adjustments/search-products', [InventoryAdjustmentController::class, 'searchProducts'])->name('inventory-adjustments.search-products');
        Route::get('/inventory-adjustments/{id}/lifecycle', [InventoryAdjustmentController::class, 'getBatchLifecycle'])->name('inventory-adjustments.lifecycle');
        Route::get('/inventory-adjustments/{id}/batches/edit', [InventoryAdjustmentController::class, 'editBatches'])->name('inventory-adjustments.edit-batches');
        
        Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    });

    Route::middleware('permission:inventory.edit')->group(function () {
        Route::resource('departments', DepartmentController::class)->except(['index', 'show']);
        Route::resource('categories', CategoryController::class)->except(['index', 'create', 'show']);
        Route::resource('brands', BrandController::class)->except(['index', 'create', 'show']);
        Route::resource('providers', ProviderController::class)->except(['index', 'create', 'show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        
        Route::post('/inventory-adjustments', [InventoryAdjustmentController::class, 'store'])->name('inventory-adjustments.store');
        Route::put('/inventory-adjustments/{id}/batches', [InventoryAdjustmentController::class, 'updateBatches'])->name('inventory-adjustments.update-batches');
        Route::post('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    });

    // --- Finances Module ---
    Route::middleware('permission:finances.view')->group(function () {
        Route::get('currencies', [App\Http\Controllers\CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('api/currencies', [App\Http\Controllers\CurrencyController::class, 'fetchAll']);
        
        // Declarations
        Route::get('finances/declarations', [App\Http\Controllers\DeclarationReportController::class, 'index'])->name('declarations.index');
        Route::get('finances/declarations/{session}', [App\Http\Controllers\DeclarationReportController::class, 'show'])->name('declarations.show');
    });
    Route::middleware('permission:finances.edit')->group(function () {
        Route::post('api/currencies', [App\Http\Controllers\CurrencyController::class, 'store']);
        Route::put('api/currencies/{currency}', [App\Http\Controllers\CurrencyController::class, 'update']);
        Route::delete('api/currencies/{currency}', [App\Http\Controllers\CurrencyController::class, 'destroy']);
        Route::post('api/payment-methods', [App\Http\Controllers\PaymentMethodController::class, 'store']);
        Route::put('api/payment-methods/{paymentMethod}', [App\Http\Controllers\PaymentMethodController::class, 'update']);
        Route::delete('api/payment-methods/{paymentMethod}', [App\Http\Controllers\PaymentMethodController::class, 'destroy']);
    });

    // --- POS Control Module ---
    Route::middleware('permission:pos_control.view')->group(function () {
        Route::get('/pos-control', [CashRegisterController::class, 'index'])->name('pos-control.index');
        Route::get('/pos-control/registers', [CashRegisterController::class, 'registers'])->name('pos-control.registers');
        Route::get('/pos-control/registers/{cashRegister}/sessions', [CashRegisterController::class, 'sessions'])->name('pos-control.registers.sessions');
        Route::get('/pos-control/sessions/{cashSession}', [CashSessionController::class, 'show'])->name('pos-control.sessions.show');
    });

    Route::middleware('permission:pos_control.manage')->group(function () {
        Route::post('/pos-control/registers', [CashRegisterController::class, 'store'])->name('pos-control.registers.store');
        Route::put('/pos-control/registers/{cashRegister}', [CashRegisterController::class, 'update'])->name('pos-control.registers.update');
        Route::delete('/pos-control/registers/{cashRegister}', [CashRegisterController::class, 'destroy'])->name('pos-control.registers.destroy');
    });

    Route::middleware('permission:pos_control.sessions')->group(function () {
        Route::post('/pos-control/sessions/open', [CashSessionController::class, 'open'])->name('pos-control.sessions.open');
        Route::post('/pos-control/sessions/{cashSession}/close', [CashSessionController::class, 'close'])->name('pos-control.sessions.close');
        Route::post('/pos-control/sessions/{cashSession}/withdraw', [CashSessionController::class, 'withdraw'])->name('pos-control.sessions.withdraw');
        Route::post('/pos-control/sessions/{cashSession}/deposit', [CashSessionController::class, 'deposit'])->name('pos-control.sessions.deposit');
    });

    // Configuraciones Module
    Route::middleware('permission:configuraciones.view')->group(function () {
        Route::get('/configuraciones/parametros', [App\Http\Controllers\ParameterController::class, 'index'])->name('config.parametros');
        Route::post('/configuraciones/parametros', [App\Http\Controllers\ParameterController::class, 'update'])->name('config.parametros.update');
        Route::get('/configuraciones/usuarios', [App\Http\Controllers\UserController::class, 'index'])->name('config.usuarios');
        Route::get('/api/roles', [App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('permission:configuraciones.edit')->group(function () {
        Route::post('/configuraciones/usuarios', [App\Http\Controllers\UserController::class, 'store'])->name('config.usuarios.store');
        Route::put('/configuraciones/usuarios/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('config.usuarios.update');
        Route::delete('/configuraciones/usuarios/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('config.usuarios.destroy');
        Route::post('/api/roles', [App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
        Route::put('/api/roles/{role}', [App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/api/roles/{role}', [App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
