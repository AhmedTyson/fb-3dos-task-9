<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');
Route::post('/forgot-password', [PasswordController::class, 'forgotPassword'])->middleware('throttle:3,60');
Route::post('/reset-password',  [PasswordController::class, 'resetPassword'])->middleware('throttle:5,30');

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me',       [AuthController::class, 'me'])->middleware('cache.json:5');
    Route::post('/logout',  [AuthController::class, 'logout']);

    Route::get('/cart',                  [CartController::class, 'index'])->middleware('cache.json:5');
    Route::post('/cart/items',           [CartController::class, 'store']);
    Route::put('/cart/items/{item}',     [CartController::class, 'update']);
    Route::delete('/cart/items/{item}',  [CartController::class, 'destroy']);

    Route::post('/products', [ProductController::class, 'store']);

    Route::get('/orders',                [OrderController::class, 'index']);
    Route::post('/orders',               [OrderController::class, 'store']);
    Route::get('/orders/{order}',        [OrderController::class, 'show']);
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});

Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('users',                     [AdminUserController::class, 'index'])->middleware('cache.json:5');
        Route::get('orders',                    [AdminOrderController::class, 'index'])->middleware('cache.json:10');
        Route::put('orders/{order}/status',     [AdminOrderController::class, 'updateStatus']);
        Route::get('orders/{order}/print-file', [AdminOrderController::class, 'printFile']);
        Route::get('reports/sales',             [AdminOrderController::class, 'salesReport'])->middleware('cache.json:15');
    });

    Route::put('/products/{product}',    [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

Route::get('/products',           [ProductController::class, 'index'])->middleware('cache.json:5');
Route::get('/products/{product}', [ProductController::class, 'show'])->middleware('cache.json:5');
