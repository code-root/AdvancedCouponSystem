<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [\App\Http\Controllers\AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::get('user', [\App\Http\Controllers\AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    
    // User Routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Broker Routes
    Route::apiResource('brokers', \App\Http\Controllers\BrokerController::class);
    Route::post('brokers/{broker}/connections', [\App\Http\Controllers\BrokerController::class, 'createConnection']);
    Route::get('brokers/{broker}/data', [\App\Http\Controllers\BrokerController::class, 'getData']);
    
    // Campaign Routes
    Route::apiResource('campaigns', \App\Http\Controllers\CampaignController::class);
    Route::post('campaigns/{campaign}/activate', [\App\Http\Controllers\CampaignController::class, 'activate']);
    Route::post('campaigns/{campaign}/deactivate', [\App\Http\Controllers\CampaignController::class, 'deactivate']);
    Route::get('campaigns/{campaign}/statistics', [\App\Http\Controllers\CampaignController::class, 'statistics']);
    
    // Coupon Routes
    Route::apiResource('coupons', \App\Http\Controllers\CouponController::class);
    Route::post('coupons/validate', [\App\Http\Controllers\CouponController::class, 'validate']);
    Route::post('coupons/{coupon}/redeem', [\App\Http\Controllers\CouponController::class, 'redeem']);
    Route::post('coupons/{coupon}/activate', [\App\Http\Controllers\CouponController::class, 'activate']);
    Route::post('coupons/{coupon}/deactivate', [\App\Http\Controllers\CouponController::class, 'deactivate']);
    Route::get('coupons/{coupon}/history', [\App\Http\Controllers\CouponController::class, 'history']);
    
    // Purchase Routes
    Route::apiResource('purchases', \App\Http\Controllers\PurchaseController::class);
    Route::post('purchases/{purchase}/confirm', [\App\Http\Controllers\PurchaseController::class, 'confirm']);
    Route::post('purchases/{purchase}/cancel', [\App\Http\Controllers\PurchaseController::class, 'cancel']);
    Route::get('purchases/statistics', [\App\Http\Controllers\PurchaseController::class, 'statistics']);
    
    // Country Routes
    Route::apiResource('countries', \App\Http\Controllers\CountryController::class);
    Route::get('countries/{country}/brokers', [\App\Http\Controllers\CountryController::class, 'brokers']);
    
    // Dashboard & Statistics
    Route::prefix('dashboard')->group(function () {
        Route::get('overview', [\App\Http\Controllers\DashboardController::class, 'overview']);
        Route::get('analytics', [\App\Http\Controllers\DashboardController::class, 'analytics']);
        Route::get('recent-activities', [\App\Http\Controllers\DashboardController::class, 'recentActivities']);
    });
    
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('coupons', [\App\Http\Controllers\ReportController::class, 'coupons']);
        Route::get('purchases', [\App\Http\Controllers\ReportController::class, 'purchases']);
        Route::get('campaigns', [\App\Http\Controllers\ReportController::class, 'campaigns']);
        Route::get('export/{type}', [\App\Http\Controllers\ReportController::class, 'export']);
    });
});

// Public Routes
Route::prefix('public')->group(function () {
    Route::get('countries', [\App\Http\Controllers\CountryController::class, 'index']);
    Route::get('campaigns/active', [\App\Http\Controllers\CampaignController::class, 'active']);
});

// Health Check
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'AdvancedCouponSystem API'
    ]);
});

