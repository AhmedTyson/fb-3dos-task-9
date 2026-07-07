<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminOrderController;
use Illuminate\Support\Facades\Route;

// ==========================================
// Sara's Scope: Auth Module
// ==========================================
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// ==========================================
// Ahmed's Scope: Admin Orders & Reports
// ==========================================
Route::middleware(['auth:api', 'isAdmin'])->prefix('admin')->group(function () {
    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    Route::get('orders/{order}/print-file', [AdminOrderController::class, 'printFile']);
    Route::get('reports/sales', [AdminOrderController::class, 'salesReport']);
});
