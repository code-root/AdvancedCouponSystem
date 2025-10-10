<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ReportController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    
    // Register Routes
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    
    // Password Reset Routes
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('overview', [DashboardController::class, 'overview'])->name('overview');
        Route::get('analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('recent-activities', [DashboardController::class, 'recentActivities'])->name('activities');
        Route::get('profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('password', [DashboardController::class, 'updatePassword'])->name('password.update');
    });
    
    // Broker Management Routes
    Route::prefix('brokers')->name('brokers.')->group(function () {
        Route::get('/', [BrokerController::class, 'index'])->name('index');
        Route::get('create', [BrokerController::class, 'create'])->name('create');
        Route::post('/', [BrokerController::class, 'store'])->name('store');
        Route::get('{broker}', [BrokerController::class, 'show'])->name('show');
        Route::get('{broker}/edit', [BrokerController::class, 'edit'])->name('edit');
        Route::put('{broker}', [BrokerController::class, 'update'])->name('update');
        Route::delete('{broker}', [BrokerController::class, 'destroy'])->name('destroy');
        Route::post('{broker}/connections', [BrokerController::class, 'createConnection'])->name('connections.create');
        Route::get('{broker}/data', [BrokerController::class, 'getData'])->name('data');
    });
    
    // Campaign Management Routes
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->name('index');
        Route::get('create', [CampaignController::class, 'create'])->name('create');
        Route::post('/', [CampaignController::class, 'store'])->name('store');
        Route::get('{campaign}', [CampaignController::class, 'show'])->name('show');
        Route::get('{campaign}/edit', [CampaignController::class, 'edit'])->name('edit');
        Route::put('{campaign}', [CampaignController::class, 'update'])->name('update');
        Route::delete('{campaign}', [CampaignController::class, 'destroy'])->name('destroy');
        Route::post('{campaign}/activate', [CampaignController::class, 'activate'])->name('activate');
        Route::post('{campaign}/deactivate', [CampaignController::class, 'deactivate'])->name('deactivate');
        Route::get('{campaign}/statistics', [CampaignController::class, 'statistics'])->name('statistics');
        Route::get('{campaign}/coupons', [CampaignController::class, 'coupons'])->name('coupons');
    });
    
    // Coupon Management Routes
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index');
        Route::get('create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
        Route::get('{coupon}', [CouponController::class, 'show'])->name('show');
        Route::get('{coupon}/edit', [CouponController::class, 'edit'])->name('edit');
        Route::put('{coupon}', [CouponController::class, 'update'])->name('update');
        Route::delete('{coupon}', [CouponController::class, 'destroy'])->name('destroy');
        Route::post('validate', [CouponController::class, 'validate'])->name('validate');
        Route::post('{coupon}/redeem', [CouponController::class, 'redeem'])->name('redeem');
        Route::post('{coupon}/activate', [CouponController::class, 'activate'])->name('activate');
        Route::post('{coupon}/deactivate', [CouponController::class, 'deactivate'])->name('deactivate');
        Route::get('{coupon}/history', [CouponController::class, 'history'])->name('history');
        Route::post('bulk-generate', [CouponController::class, 'bulkGenerate'])->name('bulk-generate');
        Route::post('export', [CouponController::class, 'export'])->name('export');
    });
    
    // Purchase Management Routes
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('{purchase}', [PurchaseController::class, 'update'])->name('update');
        Route::delete('{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
        Route::post('{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('confirm');
        Route::post('{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
        Route::get('statistics', [PurchaseController::class, 'statistics'])->name('statistics');
        Route::post('export', [PurchaseController::class, 'export'])->name('export');
    });
    
    // Country Management Routes
    Route::prefix('countries')->name('countries.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
        Route::get('create', [CountryController::class, 'create'])->name('create');
        Route::post('/', [CountryController::class, 'store'])->name('store');
        Route::get('{country}', [CountryController::class, 'show'])->name('show');
        Route::get('{country}/edit', [CountryController::class, 'edit'])->name('edit');
        Route::put('{country}', [CountryController::class, 'update'])->name('update');
        Route::delete('{country}', [CountryController::class, 'destroy'])->name('destroy');
        Route::get('{country}/brokers', [CountryController::class, 'brokers'])->name('brokers');
    });
    
    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('coupons', [ReportController::class, 'coupons'])->name('coupons');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('campaigns', [ReportController::class, 'campaigns'])->name('campaigns');
        Route::get('brokers', [ReportController::class, 'brokers'])->name('brokers');
        Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::post('export/{type}', [ReportController::class, 'export'])->name('export');
        Route::get('download/{file}', [ReportController::class, 'download'])->name('download');
    });
    
    // Settings Routes (Admin Only)
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'settings'])->name('index');
        Route::put('general', [DashboardController::class, 'updateGeneralSettings'])->name('general.update');
        Route::put('email', [DashboardController::class, 'updateEmailSettings'])->name('email.update');
        Route::put('notification', [DashboardController::class, 'updateNotificationSettings'])->name('notification.update');
    });
    
    // User Management Routes (Admin Only)
    Route::prefix('users')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'users'])->name('index');
        Route::get('create', [DashboardController::class, 'createUser'])->name('create');
        Route::post('/', [DashboardController::class, 'storeUser'])->name('store');
        Route::get('{user}/edit', [DashboardController::class, 'editUser'])->name('edit');
        Route::put('{user}', [DashboardController::class, 'updateUser'])->name('update');
        Route::delete('{user}', [DashboardController::class, 'destroyUser'])->name('destroy');
        Route::post('{user}/roles', [DashboardController::class, 'assignRole'])->name('roles.assign');
    });
});
