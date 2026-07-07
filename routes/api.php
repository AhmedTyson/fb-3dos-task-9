<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CartController;
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
// Remas' Scope: Cart Module
// ==========================================
Route::middleware('auth:api')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'store']);
    Route::put('/cart/items/{id}', [CartController::class, 'update']);
    Route::delete('/cart/items/{id}', [CartController::class, 'destroy']);
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
