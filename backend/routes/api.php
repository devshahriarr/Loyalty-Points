<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\GeolocationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PasswordResetController;

// Business Registration and Management Routes
Route::post('/business-register', [AuthController::class, 'registerBusinessOwner']);

Route::post('/login', [AuthController::class, 'login']); // login routes for all roles(system_admin, business_owner, manager, staff, customer)
Route::post('/password/send-otp', [PasswordResetController::class, 'sendOtp']);
Route::post('/password/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

Route::middleware(['auth:api', 'role:system_admin'])->group(function () {

    Route::prefix('/admin')->group(function () {

        // landlord-level routes
        Route::post('/approve-business-owner/{id}', [AdminController::class, 'approveBusinessOwner']);
        Route::apiResource('business', BusinessController::class);

        Route::get('/get-all-tenants', [AdminController::class, 'getAllTenants']);
        Route::get('/get-tenants-count', [AdminController::class, 'getTenantsCount']);
        Route::get('/get-active-tenants-count', [AdminController::class, 'getActiveTenantsCount']);
        Route::get('/get-active-tenants', [AdminController::class, 'getActiveTenants']);
        Route::get('/get-inactive-tenants', [AdminController::class, 'getInactiveTenants']);
        Route::get('/get-pending-tenants', [AdminController::class, 'getPendingTenants']);
    });

});

// Everything below MUST run inside tenant DB
Route::prefix('/tenant/geolocation')->middleware(['auth:api','tenant'])->group(function () {

    Route::get('/branches', [GeolocationController::class, 'allBranches']);
    Route::post('/reverse', [GeolocationController::class, 'reverseGeocode']);
    Route::post('/geocode', [GeolocationController::class, 'geocodeAddress']);
    Route::post('/search', [GeolocationController::class, 'searchPlace']);
    Route::post('/nearest', [GeolocationController::class, 'nearestBranch']);
    Route::post('/check-geofence', [GeolocationController::class, 'checkGeofence']);
    // Route::post('/branches/create-auto', [GeolocationController::class, 'createBranchAuto']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

});


