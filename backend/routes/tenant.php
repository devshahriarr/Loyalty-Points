<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BusinessOwnerPasswordOtpController;
use App\Http\Controllers\CustomerAnalyticsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPasswordOtpController;
use App\Http\Controllers\CustomerPointController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\LoyaltyCardController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\PlanActivationController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffPasswordOtpController;
use App\Http\Controllers\TenantAuthController;
// use App\Http\Controllers\TenantTestController;
use App\Http\Controllers\UserActivityController;
use App\Models\Customer;
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

// Route::post('/customer/register', [CustomerController::class, 'store']);

    // Public tenant endpoints (no auth required)
    // Route::get('/tenant-info', [TenantTestController::class, 'info']);
    // Route::get('/tenant-test-db', [TenantTestController::class, 'testDatabase']);

    // Protected tenant endpoints
    // Route::middleware(['auth:api', 'role:business_owner'])->group(function () {

Route::middleware(['tenant'])->group(function () {

    // Route::post('/register', [AuthController::class, 'register']);
    Route::post('/owner/login', [TenantAuthController::class, 'login']);
    Route::post('/owner/password/send-otp', [BusinessOwnerPasswordOtpController::class, 'sendOtp']);
    Route::post('/owner/password/verify-otp', [BusinessOwnerPasswordOtpController::class, 'verifyOtp']);
    Route::post('/owner/password/reset', [BusinessOwnerPasswordOtpController::class, 'resetPassword']);

    Route::prefix('/owner')->middleware(['auth:tenant', 'tenant.role:business_owner'])->group(function () {
        // After auth routes
        Route::get('/me', [TenantAuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'ownerLogout']);
        Route::post('/refresh', [TenantAuthController::class, 'refresh']);

        // Branch routes
        Route::apiResource('branches', BranchController::class);

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

        // Loyalty card Routes
        Route::get('loyalty-cards', [LoyaltyCardController::class, 'index']);
        Route::get('loyalty-cards/types', [LoyaltyCardController::class,'availableTypes']);
        Route::post('loyalty-cards', [LoyaltyCardController::class,'store']); //->middleware(['subscription.limit:cards']);
        Route::put('loyalty-cards/{id}/design', [LoyaltyCardController::class,'updateDesign']);
        Route::post('loyalty-cards/{id}/activate', [LoyaltyCardController::class,'activate']);
        Route::delete('loyalty-cards/{id}', [LoyaltyCardController::class,'destroy']);

        // Customer routes
        // Route::apiResource('customers', CustomerController::class);

        // Loyalty card routes
        // Route::apiResource('loyalty-cards', LoyaltyCardController::class);

        // Customer points routes
        // Route::apiResource('customer-points', CustomerPointController::class);

        // Visit logs routes
        // Route::apiResource('user-activities', UserActivityController::class);

        // Offers routes
        // Route::apiResource('offers', OfferController::class);
    });


    // Customer routes
    // Route::post('/customer/register', [CustomerController::class, 'store']);
    Route::prefix('/customer')->group(function () {
        Route::post('/register', [CustomerController::class, 'register']);
        Route::post('/login', [CustomerController::class, 'login']);
        Route::post('/password/send-otp', [CustomerPasswordOtpController::class, 'sendOtp']);
        Route::post('/password/verify-otp', [CustomerPasswordOtpController::class, 'verifyOtp']);
        Route::post('/password/reset', [CustomerPasswordOtpController::class, 'resetPassword']);

        Route::middleware(['auth:tenant', 'tenant.role:customer'])->group(function () {
            Route::get('/all', [CustomerController::class,'index']);
            Route::put('/{id}/update', [CustomerController::class, 'update']);
            Route::delete('/{id}/delete', [CustomerController::class, 'destroy']);
            Route::get('/{id}/show', [CustomerController::class, 'show']);
            Route::get('/me', [CustomerController::class,'me']);
            Route::post('/refresh', [CustomerController::class, 'refresh']);
            Route::post('/logout', [CustomerController::class, 'logout']);
        });
    });

    Route::prefix('/staff')->group(function () {
        Route::post('/register', [StaffController::class, 'register']);
        Route::post('/login', [StaffController::class, 'login']);
        Route::post('/password/send-otp', [StaffPasswordOtpController::class, 'sendOtp']);
        Route::post('/password/verify-otp', [StaffPasswordOtpController::class, 'verifyOtp']);
        Route::post('/password/reset', [StaffPasswordOtpController::class, 'resetPassword']);

        Route::middleware(['auth:tenant', 'tenant.role:staff'])->group(function () {
            Route::get('/all', [StaffController::class,'index']);
            Route::put('/{id}/update', [StaffController::class, 'update']);
            Route::delete('/{id}/delete', [StaffController::class, 'destroy']);
            Route::get('/{id}/show', [StaffController::class, 'show']);
            Route::get('/me', [StaffController::class,'me']);
            Route::post('/refresh', [StaffController::class, 'refresh']);
            Route::post('/logout', [StaffController::class, 'logout']);
        });
    });

});



