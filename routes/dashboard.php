<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\BillingController;

// Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Email Verification Routes
Route::middleware(['auth'])->group(function () {
    // Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('plans', [SubscriptionController::class, 'plans'])->name('plans');
        Route::get('compare', [SubscriptionController::class, 'compare'])->name('compare');
        Route::get('manage', [SubscriptionController::class, 'manage'])->name('manage');
        Route::post('plans/{plan}/trial', [SubscriptionController::class, 'startTrial'])->name('trial');
        Route::post('plans/{plan}/activate', [SubscriptionController::class, 'activate'])->name('activate');
        Route::post('plans/{plan}/checkout', [BillingController::class, 'checkout'])->name('checkout');
        Route::post('cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    });
    Route::get('email/verify', [VerifyEmailController::class, 'notice'])->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/resend', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

// Authenticated Routes
Route::middleware(['auth', 'ensure.user.type:user'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Broadcast::routes();

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->middleware('verified')->group(function () {
        Route::get('overview', [DashboardController::class, 'overview'])->name('overview');
        Route::get('analytics', [DashboardController::class, 'analytics'])->name('analytics');
        Route::get('recent-activities', [DashboardController::class, 'recentActivities'])->name('activities');
        Route::get('profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('change-password', function() { return view('dashboard.profile.change-password'); })->name('password.change');
        Route::put('password', [DashboardController::class, 'updatePassword'])->name('password.update');
    });

    Route::prefix('networks')->name('networks.')->middleware(['verified', 'enforce.plan:add-network'])->group(function () {
        Route::get('/', [NetworkController::class, 'index'])->name('index');
        Route::get('create', [NetworkController::class, 'create'])->name('create');
        Route::post('/', [NetworkController::class, 'store'])->name('store');
        Route::get('connections/{connection}/edit', [NetworkController::class, 'edit'])->name('edit');
        Route::put('connections/{connection}', [NetworkController::class, 'update'])->name('update');
        Route::delete('connections/{connection}', [NetworkController::class, 'destroy'])->name('destroy');
        Route::get('connections/{connection}', [NetworkController::class, 'show'])->name('show');
        Route::post('connections/{connection}/sync', [NetworkController::class, 'syncConnection'])->name('sync');
        Route::get('{network}/data', [NetworkController::class, 'getData'])->name('data');
        Route::get('{network}/config', [NetworkController::class, 'getNetworkConfig'])->name('config');
        Route::post('{network}/connections', [NetworkController::class, 'createConnection'])->name('connections.create');
        Route::post('test-connection', [NetworkController::class, 'testConnection'])->name('test-connection');
        Route::post('verify-password', [NetworkController::class, 'verifyPassword'])->name('verify-password');
        Route::post('reconnect', [NetworkController::class, 'reconnect'])->name('reconnect');
    });

    Route::get('/admitad/callback', [NetworkController::class, 'admitadCallback'])->name('admitad.callback');

    Route::prefix('campaigns')->name('campaigns.')->middleware('verified')->group(function () {
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
        Route::get('{campaign}/coupon-stats', [CampaignController::class, 'getCouponStats'])->name('coupon-stats');
    });

    Route::prefix('coupons')->name('coupons.')->middleware('verified')->group(function () {
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
        Route::get('{coupon}/daily-stats', [CouponController::class, 'getDailyStats'])->name('daily-stats');
        Route::post('bulk-generate', [CouponController::class, 'bulkGenerate'])->name('bulk-generate');
        Route::post('export', [CouponController::class, 'export'])->name('export');
    });

    Route::prefix('dashboard/sessions')->name('sessions.')->group(function () {
        Route::get('/', [SessionController::class, 'index'])->name('index');
        Route::get('data', [SessionController::class, 'getData'])->name('data');
        Route::get('statistics', [SessionController::class, 'statistics'])->name('statistics');
        Route::post('heartbeat', [SessionController::class, 'heartbeat'])->name('heartbeat');
        Route::get('{session}', [SessionController::class, 'show'])->name('show');
        Route::delete('{session}', [SessionController::class, 'destroy'])->name('destroy');
        Route::post('logout-others', [SessionController::class, 'destroyOthers'])->name('logout-others');
        Route::post('cleanup', [SessionController::class, 'cleanup'])->name('cleanup');
    });

    Route::prefix('dashboard/notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('statistics', [PurchaseController::class, 'statisticsPage'])->name('statistics');
        Route::get('statistics-data', [PurchaseController::class, 'statistics'])->name('statistics-data');
        Route::get('network-comparison', [PurchaseController::class, 'networkComparison'])->name('network-comparison');
        Route::get('campaigns-by-network/{networkId}', [PurchaseController::class, 'getCampaignsByNetwork'])->name('campaigns-by-network');
        Route::get('create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::post('export', [PurchaseController::class, 'export'])->name('export');
        Route::get('{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::get('{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('{purchase}', [PurchaseController::class, 'update'])->name('update');
        Route::delete('{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
        Route::post('{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('confirm');
        Route::post('{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
    });

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

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('coupons', [ReportController::class, 'coupons'])->name('coupons');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('campaigns', [ReportController::class, 'campaigns'])->name('campaigns');
        Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('export/{type}', [ReportController::class, 'export'])->name('export');
    });

    Route::prefix('sync')->name('dashboard.sync.')->middleware('enforce.plan:sync')->group(function () {
        Route::get('schedules', [\App\Http\Controllers\SyncController::class, 'schedulesIndex'])->name('schedules.index');
        Route::get('schedules/create', [\App\Http\Controllers\SyncController::class, 'schedulesCreate'])->name('schedules.create');
        Route::post('schedules', [\App\Http\Controllers\SyncController::class, 'schedulesStore'])->name('schedules.store');
        Route::get('schedules/{id}/edit', [\App\Http\Controllers\SyncController::class, 'schedulesEdit'])->name('schedules.edit');
        Route::put('schedules/{id}', [\App\Http\Controllers\SyncController::class, 'schedulesUpdate'])->name('schedules.update');
        Route::delete('schedules/{id}', [\App\Http\Controllers\SyncController::class, 'schedulesDestroy'])->name('schedules.destroy');
        Route::post('schedules/{id}/toggle', [\App\Http\Controllers\SyncController::class, 'schedulesToggle'])->name('schedules.toggle');
        Route::post('schedules/{id}/run', [\App\Http\Controllers\SyncController::class, 'schedulesRunNow'])->name('schedules.run');
        Route::get('quick-sync', [\App\Http\Controllers\SyncController::class, 'quickSyncPage'])->name('quick-sync');
        Route::post('manual', [\App\Http\Controllers\SyncController::class, 'manualSync'])->name('manual');
        Route::get('logs', [\App\Http\Controllers\SyncController::class, 'logsIndex'])->name('logs.index');
        Route::get('logs/{id}', [\App\Http\Controllers\SyncController::class, 'logsShow'])->name('logs.show');
        Route::get('settings', [\App\Http\Controllers\SyncController::class, 'settingsIndex'])->name('settings.index');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [DashboardController::class, 'settings'])->name('index');
        Route::put('general', [DashboardController::class, 'updateGeneralSettings'])->name('general.update');
        Route::put('email', [DashboardController::class, 'updateEmailSettings'])->name('email.update');
        Route::put('notification', [DashboardController::class, 'updateNotificationSettings'])->name('notification.update');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [DashboardController::class, 'users'])->name('index');
        Route::get('create', [DashboardController::class, 'createUser'])->name('create');
        Route::post('/', [DashboardController::class, 'storeUser'])->name('store');
        Route::get('{user}/edit', [DashboardController::class, 'editUser'])->name('edit');
        Route::put('{user}', [DashboardController::class, 'updateUser'])->name('update');
        Route::delete('{user}', [DashboardController::class, 'destroyUser'])->name('destroy');
    });
});


