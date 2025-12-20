<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerAnalyticsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPointController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\GeolocationController;
use App\Http\Controllers\LoyaltyCardController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PlanActivationController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\TenantAuthController;
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

Route::post('/customer/register', [CustomerController::class, 'store']);

Route::middleware(['tenant'])->group(function () {

    // Public tenant endpoints (no auth required)
    // Route::get('/tenant-info', [TenantTestController::class, 'info']);
    // Route::get('/tenant-test-db', [TenantTestController::class, 'testDatabase']);

    // Protected tenant endpoints
    // Route::middleware(['auth:api', 'role:business_owner'])->group(function () {

    Route::prefix('/owner')->group(function () {

        // Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [TenantAuthController::class, 'login']);
        Route::post('/password/send-otp', [PasswordResetController::class, 'sendOtp']);
        Route::post('/password/verify-otp', [PasswordResetController::class, 'verifyOtp']);
        Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

        Route::middleware(['auth:tenant', 'tenant.role:business_owner'])->group(function () {
            // After auth routes
            Route::get('/me', [TenantAuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'ownerLogout']);
            Route::post('/refresh', [TenantAuthController::class, 'refresh']);

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

            Route::get('/analytics/customers', [CustomerAnalyticsController::class, 'index']); // list
            Route::post('/analytics/recalc-all', [CustomerAnalyticsController::class, 'recalcAll']);

            Route::post('/analytics/recalc/{customerId}', [CustomerAnalyticsController::class, 'recalcCustomer']);

            //Customer Reviews routes
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

            // Geolocation endpoints
            Route::get('/branches', [GeolocationController::class, 'allBranches']);
            Route::post('/reverse', [GeolocationController::class, 'reverseGeocode']);
            Route::post('/geocode', [GeolocationController::class, 'geocodeAddress']);
            Route::post('/search', [GeolocationController::class, 'searchPlace']);
            Route::post('/nearest', [GeolocationController::class, 'nearestBranch']);
            Route::post('/check-geofence', [GeolocationController::class, 'checkGeofence']);
            // Route::post('/branches/create-auto', [GeolocationController::class, 'createBranchAuto']);

            Route::get('/plans', [PlanActivationController::class, 'index']);
            Route::post('/plans/activate', [PlanActivationController::class, 'activate']);
        });
    });
});
