<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me',     [AuthController::class, 'me'])->middleware('cache.json:5');
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/cart',                [CartController::class, 'index'])->middleware('cache.json:5');
    Route::post('/cart/items',         [CartController::class, 'store']);
    Route::put('/cart/items/{item}',   [CartController::class, 'update']);
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy']);
});

Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('orders',                    [AdminOrderController::class, 'index'])->middleware('cache.json:10');
        Route::put('orders/{order}/status',     [AdminOrderController::class, 'updateStatus']);
        Route::get('orders/{order}/print-file', [AdminOrderController::class, 'printFile']);
        Route::get('reports/sales',             [AdminOrderController::class, 'salesReport'])->middleware('cache.json:15');
    });

    Route::put('/products/{product}',    [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
