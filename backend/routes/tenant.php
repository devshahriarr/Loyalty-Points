<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware(['api', 'auth:api'])->prefix('api')->group(function () {
    // Tenant-specific routes
    Route::get('/tenant-info', function () {
        return response()->json([
            'tenant' => tenant(),
            'message' => 'You are accessing tenant-specific data'
        ]);
    });

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
