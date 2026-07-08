<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;

Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
});



// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

});

    // Orders
    Route::get('/orders/{id}', [OrderController::class, 'show']);
   Route::middleware('auth:api')->group(function () {

    Route::get('/orders/{id}', [OrderController::class, 'show']);

    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

});

