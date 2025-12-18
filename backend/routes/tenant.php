<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerAnalyticsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPointController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\LoyaltyCardController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\RewardController;
// use App\Http\Controllers\TenantTestController;
use App\Http\Controllers\UserActivityController;
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

// Route::middleware(['api', 'tenant'])->group(function () {
Route::middleware(['tenant', 'api'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    // Public tenant endpoints (no auth required)
    // Route::get('/tenant-info', [TenantTestController::class, 'info']);
    // Route::get('/tenant-test-db', [TenantTestController::class, 'testDatabase']);

    Route::get('/analytics/customers', [CustomerAnalyticsController::class, 'index']); // list
    Route::post('/analytics/recalc-all', [CustomerAnalyticsController::class, 'recalcAll']);

    Route::get('/reviews', [CustomerReviewController::class, 'index']);
    Route::post('/reviews', [CustomerReviewController::class, 'store']);
    Route::put('/reviews/{id}', [CustomerReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [CustomerReviewController::class, 'destroy']);

    // SHOW/HIDE toggle
    Route::post('/reviews/{id}/toggle', [CustomerReviewController::class, 'toggleVisibility']);

    // Reward Module Endpoints
    Route::get('/rewards', [RewardController::class, 'index']);
    Route::post('/rewards', [RewardController::class, 'store']);
    Route::put('/rewards/{id}', [RewardController::class, 'update']);
    Route::delete('/rewards/{id}', [RewardController::class, 'destroy']);

    // Active/Inactive toggle
    Route::post('/rewards/{id}/toggle', [RewardController::class, 'toggle']);


    // Protected tenant endpoints
    // Route::middleware(['auth:api', 'role:business_owner'])->group(function () {
        Route::middleware(['auth:api'])->group(function () {

            // Branch routes
            Route::apiResource('branches', BranchController::class);

            // Customer routes
            // Route::apiResource('customers', CustomerController::class);

            // Loyalty card routes
            Route::apiResource('loyalty-cards', LoyaltyCardController::class);

            // Customer points routes
            Route::apiResource('customer-points', CustomerPointController::class);

            // Visit logs routes
            Route::apiResource('user-activities', UserActivityController::class);

            // Offers routes
            Route::apiResource('offers', OfferController::class);

            Route::post('/analytics/recalc/{customerId}', [CustomerAnalyticsController::class, 'recalcCustomer']);

            // Customer Reviews routes
            // Route::get('/reviews', [CustomerReviewController::class, 'index']);
            // Route::post('/reviews', [CustomerReviewController::class, 'store']);
            // Route::put('/reviews/{id}', [CustomerReviewController::class, 'update']);
            // Route::delete('/reviews/{id}', [CustomerReviewController::class, 'destroy']);
        });
});
