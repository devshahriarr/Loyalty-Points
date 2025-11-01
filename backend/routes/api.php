<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;

// Business Registration and Management Routes
Route::post('/business-register', [AuthController::class, 'registerBusinessOwner']);
// Route::post('/admin/approve-business-owner/{id}', [AdminController::class, 'approveBusinessOwner']);
Route::middleware(['auth:api', 'role:system_admin'])->group(function () {
    Route::post('/admin/approve-business-owner/{id}', [AdminController::class, 'approveBusinessOwner']);
});


    Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Route::apiResource('businesses', \App\Http\Controllers\BusinessController::class);
    Route::apiResource('business', BusinessController::class);
});
