<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Models\Tenant;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant-specific API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group and "tenant" middleware.
|
*/

Route::middleware(['api', 'tenant'])->prefix('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    // Public tenant endpoints (no auth required)
    Route::get('/tenant-info', [\App\Http\Controllers\TenantTestController::class, 'info']);
    Route::get('/tenant-test-db', [\App\Http\Controllers\TenantTestController::class, 'testDatabase']);
    
    // Protected tenant endpoints
    Route::middleware(['auth:api'])->group(function () {

    // Branch routes
    // Route::apiResource('branches', \App\Http\Controllers\BranchController::class);

    // Customer routes
    // Route::apiResource('customers', \App\Http\Controllers\CustomerController::class);

    // Loyalty card routes
    // Route::apiResource('loyalty-cards', \App\Http\Controllers\LoyaltyCardController::class);

    // Customer points routes
    // Route::apiResource('customer-points', \App\Http\Controllers\CustomerPointController::class);

    // Visit logs routes
    // Route::apiResource('visit-logs', \App\Http\Controllers\VisitLogController::class);

    // Offers routes
    // Route::apiResource('offers', \App\Http\Controllers\OfferController::class);
    });
});
