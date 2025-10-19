<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminController as AdminPanelController;
use App\Http\Controllers\admin\roles\RoleController as AdminRoleController;
use App\Http\Controllers\admin\roles\PermissionController as AdminPermissionController;
use App\Http\Controllers\admin\roles\UserController as AdminUserController;
use App\Http\Controllers\admin\PlanController as AdminPlanController;
use App\Http\Controllers\admin\PlanCouponController as AdminPlanCouponController;
use App\Http\Controllers\admin\AdminSubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\admin\AdminSettingsController as AdminSettingsController;
use App\Http\Controllers\admin\NetworkManagementController;
use App\Http\Controllers\admin\ReportsController;
use App\Http\Controllers\admin\SystemController;
use App\Http\Controllers\admin\AdminUserManagementController;

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth
    Route::get('login', [AdminPanelController::class, 'login'])->name('login');
    Route::post('login', [AdminPanelController::class, 'postLogin'])->name('post-login');
    Route::post('logout', [AdminPanelController::class, 'logout'])->name('logout');

    Route::middleware(['auth:admin', 'ensure.user.type:admin'])->group(function () {
        Route::get('/', [AdminPanelController::class, 'dashboard'])->name('dashboard');

        // Profile
        Route::get('profile', [AdminPanelController::class, 'profile'])->name('profile');
        Route::post('profile', [AdminPanelController::class, 'postProfile'])->name('profile.update');

        // Roles & Permissions
        Route::get('permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
        Route::resource('roles', AdminRoleController::class)->except(['destroy']);
        Route::delete('roles/{id}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

        // Admin Users
        Route::resource('users', AdminUserController::class);
        Route::post('users/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Plans CRUD
        Route::resource('plans', AdminPlanController::class);

        // Plan Coupons CRUD
        Route::resource('plan-coupons', AdminPlanCouponController::class);

        // Site Settings dashboard
        Route::get('site-settings', [AdminPanelController::class, 'siteSettingsDashboard'])->name('site-settings.dashboard');

        // Subscriptions Management
        Route::resource('subscriptions', AdminSubscriptionController::class)->only(['index','edit','update']);
        Route::post('subscriptions/{userId}/{planId}/trial', [AdminSubscriptionController::class, 'activateTrial'])->name('subscriptions.trial');
        Route::post('subscriptions/{userId}/{planId}/activate', [AdminSubscriptionController::class, 'activatePlan'])->name('subscriptions.activate');

        // Branding & SEO settings
        Route::get('settings/branding', [AdminSettingsController::class, 'branding'])->name('settings.branding');
        Route::post('settings/branding', [AdminSettingsController::class, 'saveBranding'])->name('settings.branding.save');

        // Network Management
        Route::resource('networks', NetworkManagementController::class)->only(['index','show']);
        Route::post('networks/{id}/toggle-status', [NetworkManagementController::class, 'updateStatus'])->name('networks.toggle-status');
        Route::get('networks/proxies', [NetworkManagementController::class, 'proxies'])->name('networks.proxies');
        Route::post('networks/proxies', [NetworkManagementController::class, 'storeProxy'])->name('networks.proxies.store');
        Route::get('networks/proxies/{id}/edit', [AdminUserManagementController::class, 'editProxy'])->name('networks.proxies.edit');
        Route::put('networks/proxies/{id}', [NetworkManagementController::class, 'updateProxy'])->name('networks.proxies.update');
        Route::delete('networks/proxies/{id}', [NetworkManagementController::class, 'destroyProxy'])->name('networks.proxies.destroy');
        Route::get('networks/{id}/stats', [NetworkManagementController::class, 'getNetworkStats'])->name('networks.stats');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function() {
            Route::get('user-sessions', [ReportsController::class, 'userSessions'])->name('user-sessions');
            Route::get('network-sessions', [ReportsController::class, 'networkSessions'])->name('network-sessions');
            Route::get('sync-logs', [ReportsController::class, 'syncLogs'])->name('sync-logs');
            Route::get('sync-statistics', [ReportsController::class, 'syncStatistics'])->name('sync-statistics');
            Route::get('sync-chart-data', [ReportsController::class, 'getSyncChartData'])->name('sync-chart-data');
        });

        // User Management
        Route::prefix('user-management')->name('user-management.')->group(function() {
            Route::get('/', [AdminUserManagementController::class, 'index'])->name('index');
            Route::get('create', [AdminUserManagementController::class, 'create'])->name('create');
            Route::post('/', [AdminUserManagementController::class, 'store'])->name('store');
            Route::get('{id}', [AdminUserManagementController::class, 'show'])->name('show');
            Route::get('{id}/edit', [AdminUserManagementController::class, 'edit'])->name('edit');
            Route::put('{id}', [AdminUserManagementController::class, 'update'])->name('update');
            Route::delete('{id}', [AdminUserManagementController::class, 'destroy'])->name('destroy');
            Route::post('{id}/toggle-status', [AdminUserManagementController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('link-subuser', [AdminUserManagementController::class, 'linkSubUser'])->name('link-subuser');
            Route::delete('{id}/unlink', [AdminUserManagementController::class, 'unlinkSubUser'])->name('unlink-subuser');
            Route::post('{id}/impersonate', [AdminUserManagementController::class, 'impersonate'])->name('impersonate');
            Route::post('stop-impersonating', [AdminUserManagementController::class, 'stopImpersonating'])->name('stop-impersonating');
            Route::get('{id}/stats', [AdminUserManagementController::class, 'getUserStats'])->name('stats');
        });

        // System Management
        Route::resource('countries', SystemController::class);
        Route::get('campaigns', [SystemController::class, 'campaigns'])->name('campaigns.index');
        Route::get('system/global-settings', [SystemController::class, 'globalSettings'])->name('system.global-settings');
        Route::post('system/global-settings', [SystemController::class, 'updateGlobalSettings'])->name('system.global-settings.update');
        Route::get('system/stats', [SystemController::class, 'getSystemStats'])->name('system.stats');
        
        // Additional routes for missing functionality
        Route::get('settings', [AdminSettingsController::class, 'general'])->name('settings.index');
        Route::get('reports', [ReportsController::class, 'userSessions'])->name('reports.index');

        // Settings
        Route::prefix('settings')->name('settings.')->group(function() {
            Route::get('smtp', [AdminSettingsController::class, 'smtp'])->name('smtp');
            Route::post('smtp', [AdminSettingsController::class, 'saveSmtp'])->name('smtp.save');
            Route::get('seo', [AdminSettingsController::class, 'seo'])->name('seo');
            Route::post('seo', [AdminSettingsController::class, 'saveSeo'])->name('seo.save');
            Route::get('general', [AdminSettingsController::class, 'general'])->name('general');
            Route::post('general', [AdminSettingsController::class, 'saveGeneral'])->name('general.save');
            Route::get('payment', [AdminSettingsController::class, 'payment'])->name('payment');
            Route::post('payment', [AdminSettingsController::class, 'savePayment'])->name('payment.save');
            Route::post('test-email', [AdminSettingsController::class, 'testEmail'])->name('test-email');
        });
    });
});




