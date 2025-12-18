<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\PasswordResetController;

// Business Registration and Management Routes
Route::post('/business-register', [AuthController::class, 'registerBusinessOwner']);

// login routes for system_admin
Route::prefix('/admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/send-otp', [PasswordResetController::class, 'sendOtp']);
    Route::post('/password/verify-otp', [PasswordResetController::class, 'verifyOtp']);
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
});

Route::prefix('/admin')->middleware(['auth:api', 'role:system_admin'])->group(function () {

    // landlord-level routes
    Route::post('/approve-business-owner/{id}', [AdminController::class, 'approveBusinessOwner']);
    Route::apiResource('business', BusinessController::class);

    Route::get('/get-all-tenants', [AdminController::class, 'getAllTenants']);
    Route::get('/get-tenants-count', [AdminController::class, 'getTenantsCount']);
    Route::get('/get-active-tenants-count', [AdminController::class, 'getActiveTenantsCount']);
    Route::get('/get-active-tenants', [AdminController::class, 'getActiveTenants']);
    Route::get('/get-inactive-tenants', [AdminController::class, 'getInactiveTenants']);
    Route::get('/get-pending-tenants', [AdminController::class, 'getPendingTenants']);

    // After auth routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'adminLogout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

});


