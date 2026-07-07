<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routes (Tymon JWT)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    //
});
// Cart routes
// Order routes
// Admin routes
