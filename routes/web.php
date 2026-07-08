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

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/toggle-dark-mode', [AuthController::class, 'toggleDarkMode'])->name('toggle-dark-mode');

    // Inventory Module
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show']);
    Route::resource('brands', BrandController::class)->except(['create', 'show']);
    Route::resource('providers', ProviderController::class)->except(['create', 'show']);
    Route::get('departments/{department}/categories', [CategoryController::class, 'getByDepartment'])->name('departments.categories');
    Route::resource('products', ProductController::class)->except(['show']);
    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Finances (Currencies and Payment Methods)
    Route::get('currencies', [App\Http\Controllers\CurrencyController::class, 'index'])->name('currencies.index');
    Route::get('api/currencies', [App\Http\Controllers\CurrencyController::class, 'fetchAll']);
    Route::post('api/currencies', [App\Http\Controllers\CurrencyController::class, 'store']);
    Route::put('api/currencies/{currency}', [App\Http\Controllers\CurrencyController::class, 'update']);
    Route::delete('api/currencies/{currency}', [App\Http\Controllers\CurrencyController::class, 'destroy']);
    
    Route::post('api/payment-methods', [App\Http\Controllers\PaymentMethodController::class, 'store']);
    Route::put('api/payment-methods/{paymentMethod}', [App\Http\Controllers\PaymentMethodController::class, 'update']);
    Route::delete('api/payment-methods/{paymentMethod}', [App\Http\Controllers\PaymentMethodController::class, 'destroy']);

    // POS Control Module
    Route::get('/pos-control', [CashRegisterController::class, 'index'])->name('pos-control.index');
    Route::post('/pos-control/registers', [CashRegisterController::class, 'store'])->name('pos-control.registers.store');
    Route::put('/pos-control/registers/{cashRegister}', [CashRegisterController::class, 'update'])->name('pos-control.registers.update');
    Route::delete('/pos-control/registers/{cashRegister}', [CashRegisterController::class, 'destroy'])->name('pos-control.registers.destroy');
    Route::get('/pos-control/registers/{cashRegister}/sessions', [CashRegisterController::class, 'sessions'])->name('pos-control.registers.sessions');

    // Cash Sessions
    Route::get('/pos-control/sessions/{cashSession}', [CashSessionController::class, 'show'])->name('pos-control.sessions.show');
    Route::post('/pos-control/sessions/open', [CashSessionController::class, 'open'])->name('pos-control.sessions.open');
    Route::post('/pos-control/sessions/{cashSession}/close', [CashSessionController::class, 'close'])->name('pos-control.sessions.close');
    Route::post('/pos-control/sessions/{cashSession}/withdraw', [CashSessionController::class, 'withdraw'])->name('pos-control.sessions.withdraw');
    Route::post('/pos-control/sessions/{cashSession}/deposit', [CashSessionController::class, 'deposit'])->name('pos-control.sessions.deposit');
});
