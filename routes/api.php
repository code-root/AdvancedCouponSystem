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
    
    // Network Routes
    Route::apiResource('networks', \App\Http\Controllers\NetworkController::class)->names([
        'index' => 'api.networks.index',
        'store' => 'api.networks.store',
        'show' => 'api.networks.show',
        'update' => 'api.networks.update',
        'destroy' => 'api.networks.destroy',
    ]);
    Route::post('networks/{network}/connections', [\App\Http\Controllers\NetworkController::class, 'createConnection'])->name('api.networks.connections.create');
    Route::get('networks/{network}/data', [\App\Http\Controllers\NetworkController::class, 'getData'])->name('api.networks.data');
    
    // Campaign Routes
    Route::apiResource('campaigns', \App\Http\Controllers\CampaignController::class)->names([
        'index' => 'api.campaigns.index',
        'store' => 'api.campaigns.store',
        'show' => 'api.campaigns.show',
        'update' => 'api.campaigns.update',
        'destroy' => 'api.campaigns.destroy',
    ]);
    Route::post('campaigns/{campaign}/activate', [\App\Http\Controllers\CampaignController::class, 'activate'])->name('api.campaigns.activate');
    Route::post('campaigns/{campaign}/deactivate', [\App\Http\Controllers\CampaignController::class, 'deactivate'])->name('api.campaigns.deactivate');
    Route::get('campaigns/{campaign}/statistics', [\App\Http\Controllers\CampaignController::class, 'statistics'])->name('api.campaigns.statistics');
    Route::get('campaigns/{campaign}/coupon-stats', [\App\Http\Controllers\CampaignController::class, 'getCouponStats'])->name('api.campaigns.coupon-stats');
    
    // Coupon Routes
    Route::apiResource('coupons', \App\Http\Controllers\CouponController::class)->names([
        'index' => 'api.coupons.index',
        'store' => 'api.coupons.store',
        'show' => 'api.coupons.show',
        'update' => 'api.coupons.update',
        'destroy' => 'api.coupons.destroy',
    ]);
    Route::post('coupons/validate', [\App\Http\Controllers\CouponController::class, 'validate'])->name('api.coupons.validate');
    Route::post('coupons/{coupon}/redeem', [\App\Http\Controllers\CouponController::class, 'redeem'])->name('api.coupons.redeem');
    Route::post('coupons/{coupon}/activate', [\App\Http\Controllers\CouponController::class, 'activate'])->name('api.coupons.activate');
    Route::post('coupons/{coupon}/deactivate', [\App\Http\Controllers\CouponController::class, 'deactivate'])->name('api.coupons.deactivate');
    Route::get('coupons/{coupon}/history', [\App\Http\Controllers\CouponController::class, 'history'])->name('api.coupons.history');
    
    // Subscription Coupon Routes
    Route::post('validate-coupon', [\App\Http\Controllers\Api\SubscriptionCouponController::class, 'validate'])->name('api.subscription.coupon.validate');
    
    // Purchase Routes
    Route::apiResource('purchases', \App\Http\Controllers\PurchaseController::class)->names([
        'index' => 'api.orders.index',
        'store' => 'api.orders.store',
        'show' => 'api.orders.show',
        'update' => 'api.orders.update',
        'destroy' => 'api.orders.destroy',
    ]);
    Route::post('purchases/{purchase}/confirm', [\App\Http\Controllers\PurchaseController::class, 'confirm'])->name('api.orders.confirm');
    Route::post('purchases/{purchase}/cancel', [\App\Http\Controllers\PurchaseController::class, 'cancel'])->name('api.orders.cancel');
    Route::get('purchases/statistics', [\App\Http\Controllers\PurchaseController::class, 'statistics'])->name('api.orders.statistics');
    
    // Country Routes
    Route::apiResource('countries', \App\Http\Controllers\CountryController::class)->names([
        'index' => 'api.countries.index',
        'store' => 'api.countries.store',
        'show' => 'api.countries.show',
        'update' => 'api.countries.update',
        'destroy' => 'api.countries.destroy',
    ]);
    Route::get('countries/{country}/brokers', [\App\Http\Controllers\CountryController::class, 'brokers'])->name('api.countries.brokers');
    
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

