<?php

use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageCacheController;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login',    [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [PasswordController::class, 'forgetPassword'])->middleware('throttle:3,60');
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
    Route::get('/orders/{order}/print-file', [OrderController::class, 'printFile']);
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
});

Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('users',                     [AdminUserController::class, 'index']);
        Route::get('orders',                    [AdminOrderController::class, 'index']);
        Route::put('orders/{order}/status',     [AdminOrderController::class, 'updateStatus']);
        Route::get('orders/{order}/print-file', [AdminOrderController::class, 'printFile']);
        Route::get('reports/sales',             [AdminOrderController::class, 'salesReport']);
    });

    Route::put('/products/{product}',    [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::post('/categories', function (Illuminate\Http\Request $request) {
        $data = $request->validate(['name' => 'required|string|max:255|unique:categories,name']);
        $category = App\Models\Category::create($data);
        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    });
});

Route::get('/products',           [ProductController::class, 'index'])->middleware('cache.json:60');
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index'])->middleware('cache.json:60');

Route::get('/storage/{path}', [ImageCacheController::class, 'show'])->where('path', '.*');
