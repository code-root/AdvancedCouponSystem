<?php

use Illuminate\Support\Facades\Route;

// Emergency stop impersonation route - works without middleware conflicts
Route::post('/admin/emergency-stop-impersonating', function() {
    try {
        $adminId = \App\Helpers\ImpersonationHelper::getAdminId();
        $impersonatedUserName = \App\Helpers\ImpersonationHelper::getImpersonatedUserName();
        
        if (!$adminId) {
            return redirect()->route('admin.login')->with('error', 'No active impersonation session.');
        }
        
        // Log impersonation stop
        \Illuminate\Support\Facades\Log::info('Admin stopped impersonation (emergency admin route)', [
            'admin_id' => $adminId,
            'impersonated_user_name' => $impersonatedUserName,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        // Logout from user session
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        
        // Clear impersonation session data
        \App\Helpers\ImpersonationHelper::stopImpersonation();
        session()->forget(['original_admin_session_id']);
        
        // Restore admin session
        $admin = \App\Models\Admin::find($adminId);
        if ($admin && $admin->active) {
            \Illuminate\Support\Facades\Auth::guard('admin')->login($admin);
            session()->regenerate();
            return redirect()->route('admin.dashboard')->with('success', "Stopped impersonating {$impersonatedUserName}.");
        } else {
            return redirect()->route('admin.login')->with('error', 'Admin account not found or inactive.');
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Emergency stop impersonation error: ' . $e->getMessage());
        return redirect()->route('admin.login')->with('error', 'Failed to stop impersonation: ' . $e->getMessage());
    }
})->name('admin.emergency.stop-impersonating');
use App\Http\Controllers\admin\AdminController as AdminPanelController;
use App\Http\Controllers\admin\AdminProfileController;
use App\Http\Controllers\admin\Settings\GeneralSettingsController;
use App\Http\Controllers\admin\Settings\BrandingSettingsController;
use App\Http\Controllers\admin\Settings\SmtpSettingsController;
use App\Http\Controllers\admin\Settings\SeoSettingsController;
use App\Http\Controllers\admin\Settings\PaymentSettingsController;
use App\Http\Controllers\admin\RoleManagementController;
use App\Http\Controllers\admin\PermissionManagementController;
use App\Http\Controllers\admin\AdminUserController;
use App\Http\Controllers\admin\Reports\UserSessionReportController;
use App\Http\Controllers\admin\Reports\NetworkSessionReportController;
use App\Http\Controllers\admin\Reports\SyncLogReportController;
use App\Http\Controllers\admin\Reports\SyncStatisticsController;
use App\Http\Controllers\admin\System\CountryController;
use App\Http\Controllers\admin\System\CampaignController;
use App\Http\Controllers\admin\roles\RoleController as AdminRoleController;
use App\Http\Controllers\admin\NotificationController;
use App\Http\Controllers\admin\roles\PermissionController as AdminPermissionController;
use App\Http\Controllers\admin\PlanController as AdminPlanController;
use App\Http\Controllers\admin\PlanCouponController as AdminPlanCouponController;
use App\Http\Controllers\admin\AdminSubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\admin\AdminSettingsController as AdminSettingsController;
use App\Http\Controllers\admin\NetworkManagementController;
use App\Http\Controllers\admin\ReportsController;
use App\Http\Controllers\admin\SystemController;
use App\Http\Controllers\admin\AdminUserManagementController;
use App\Http\Controllers\admin\AuditLogController;
use App\Http\Controllers\admin\AdminSessionController;

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth routes (no middleware)
    Route::get('login', [AdminPanelController::class, 'login'])->name('login');
    Route::post('login', [AdminPanelController::class, 'postLogin'])->name('post-login');
    Route::post('logout', [AdminPanelController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware(['auth:admin', 'ensure.user.type:admin'])->group(function () {
        
        // Dashboard
        Route::get('/', [AdminPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/real-time-data', [AdminPanelController::class, 'getRealTimeData'])->name('real-time-data');

        // Profile Management
        Route::controller(AdminProfileController::class)->prefix('profile')->name('profile.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'update')->name('update');
            Route::put('/password', 'updatePassword')->name('password');
            Route::post('/avatar', 'uploadAvatar')->name('avatar');
            
            // AJAX variants
            Route::post('/ajax', 'updateAjax')->name('update.ajax');
            Route::put('/password/ajax', 'updatePasswordAjax')->name('password.ajax');
        });

        // Settings Management
        Route::prefix('settings')->name('settings.')->group(function () {
            
            // General Settings
            Route::controller(GeneralSettingsController::class)->prefix('general')->name('general.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'update')->name('save');
                Route::post('/ajax', 'updateAjax')->name('save.ajax');
            });

            // Branding Settings
            Route::controller(BrandingSettingsController::class)->prefix('branding')->name('branding.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'update')->name('save');
                Route::post('/ajax', 'updateAjax')->name('save.ajax');
                Route::post('/logo', 'uploadLogo')->name('logo');
            });

            // SMTP Settings
            Route::controller(SmtpSettingsController::class)->prefix('smtp')->name('smtp.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'update')->name('save');
                Route::post('/ajax', 'updateAjax')->name('save.ajax');
                Route::post('/test-email', 'testEmail')->name('test-email');
            });

            // SEO Settings
            Route::controller(SeoSettingsController::class)->prefix('seo')->name('seo.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'update')->name('save');
                Route::post('/ajax', 'updateAjax')->name('save.ajax');
            });

            // Payment Settings
            Route::controller(PaymentSettingsController::class)->prefix('payment')->name('payment.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'update')->name('save');
                Route::post('/ajax', 'updateAjax')->name('save.ajax');
                Route::post('/test', 'testPayment')->name('test');
            });

            // Settings Index
            Route::get('/', [AdminSettingsController::class, 'general'])->name('index');
        });

        // Role & Permission Management
        Route::middleware(['admin.permission:manage-roles'])->group(function () {
            
            // Role Management
            Route::controller(RoleManagementController::class)->prefix('roles')->name('roles.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::post('/{id}/clone', 'cloneRole')->name('clone');
                Route::get('/export', 'exportRoles')->name('export');
                
                // AJAX variants
                Route::post('/ajax', 'storeAjax')->name('store.ajax');
                Route::put('/{id}/ajax', 'updateAjax')->name('update.ajax');
                Route::delete('/{id}/ajax', 'destroyAjax')->name('destroy.ajax');
                Route::put('/{id}/permissions/ajax', 'assignPermissionsAjax')->name('permissions.ajax');
            });

            // Permission Management
            Route::controller(PermissionManagementController::class)->prefix('permissions')->name('permissions.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::get('/groups', 'getGroups')->name('groups');
                Route::post('/bulk', 'bulkCreate')->name('bulk');
                
                // AJAX variants
                Route::get('/ajax', 'listAjax')->name('list.ajax');
                Route::get('/search/ajax', 'searchAjax')->name('search.ajax');
            });
        });

        // Admin User Management
        Route::middleware(['admin.permission:manage-admins'])->group(function () {
            Route::controller(AdminUserController::class)->prefix('admin-users')->name('admin-users.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::post('/{id}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::put('/{id}/roles', 'assignRoles')->name('assign-roles');
                Route::get('/{id}/permissions', 'permissions')->name('permissions');
                
                // AJAX variants
                Route::post('/ajax', 'storeAjax')->name('store.ajax');
                Route::put('/{id}/ajax', 'updateAjax')->name('update.ajax');
                Route::delete('/{id}/ajax', 'destroyAjax')->name('destroy.ajax');
            });
        });

        // User Management
        Route::middleware(['admin.permission:manage-users'])->group(function () {
            Route::controller(AdminUserManagementController::class)->prefix('user-management')->name('user-management.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::post('/{id}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::post('/link-subuser', 'linkSubUser')->name('link-subuser');
                Route::delete('/{id}/unlink', 'unlinkSubUser')->name('unlink-subuser');
                Route::post('/{id}/impersonate', 'impersonate')->name('impersonate');
                Route::post('/stop-impersonating', 'stopImpersonating')->name('stop-impersonating');
                Route::get('/{id}/stats', 'getUserStats')->name('stats');
                Route::put('/{id}/password', 'updatePassword')->name('password');
                Route::post('/{id}/send-email', 'sendEmail')->name('send-email');
            });
        });

        // Reports
        Route::middleware(['admin.permission:view-reports'])->group(function () {
            Route::prefix('reports')->name('reports.')->group(function () {
                
                // User Sessions Report
                Route::controller(ReportsController::class)->prefix('user-sessions')->name('user-sessions.')->group(function () {
                    Route::get('/', 'userSessions')->name('index');
                    Route::get('/ajax', 'getDataAjax')->name('data.ajax');
                    Route::get('/stats/ajax', 'getStatsAjax')->name('stats.ajax');
                    Route::get('/export', 'export')->name('export');
                    Route::get('/{id}/details', 'getSessionDetails')->name('details');
                    Route::post('/{id}/terminate', 'terminateSession')->name('terminate');
                });

                // Network Sessions Report
                Route::controller(NetworkSessionReportController::class)->prefix('network-sessions')->name('network-sessions.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/ajax', 'getDataAjax')->name('data.ajax');
                    Route::get('/stats/ajax', 'getStatsAjax')->name('stats.ajax');
                    Route::get('/export', 'export')->name('export');
                });

                // Sync Logs Report
                Route::controller(SyncLogReportController::class)->prefix('sync-logs')->name('sync-logs.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/ajax', 'getDataAjax')->name('data.ajax');
                    Route::get('/stats/ajax', 'getStatsAjax')->name('stats.ajax');
                    Route::get('/chart-data/ajax', 'getChartDataAjax')->name('chart-data.ajax');
                    Route::get('/export', 'export')->name('export');
                });

                // Sync Statistics Report
                Route::controller(SyncStatisticsController::class)->prefix('sync-statistics')->name('sync-statistics.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/overall-stats/ajax', 'getOverallStatsAjax')->name('overall-stats.ajax');
                    Route::get('/top-users/ajax', 'getTopUsersAjax')->name('top-users.ajax');
                    Route::get('/trends/ajax', 'getSyncTrendsAjax')->name('trends.ajax');
                    Route::get('/status-distribution/ajax', 'getStatusDistributionAjax')->name('status-distribution.ajax');
                    Route::get('/export', 'export')->name('export');
                });

                // Advanced Reports
                Route::get('/advanced', [ReportsController::class, 'advanced'])->name('advanced');

                // Reports Index
                Route::get('/', [ReportsController::class, 'userSessions'])->name('index');
            });
        });

        // System Management
        Route::middleware(['admin.permission:manage-system'])->group(function () {
            
            // Notifications Management
            Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'show')->name('show');
                Route::post('/{id}/mark-read', 'markAsRead')->name('mark-read');
                Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::post('/clear-all', 'clearAll')->name('clear-all');
            });
            
            // Countries Management
            Route::controller(CountryController::class)->prefix('countries')->name('countries.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{id}', 'show')->name('show');
                Route::get('/{id}/edit', 'edit')->name('edit');
                Route::put('/{id}', 'update')->name('update');
                Route::delete('/{id}', 'destroy')->name('destroy');
                Route::post('/{id}/toggle-status', 'toggleStatus')->name('toggle-status');
                Route::post('/import', 'import')->name('import');
                Route::get('/export', 'export')->name('export');
                
                // AJAX variants
                Route::post('/ajax', 'storeAjax')->name('store.ajax');
                Route::put('/{id}/ajax', 'updateAjax')->name('update.ajax');
                Route::delete('/{id}/ajax', 'destroyAjax')->name('destroy.ajax');
            });

            // Campaigns Management
            Route::controller(CampaignController::class)->prefix('campaigns')->name('campaigns.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'show')->name('show');
                Route::post('/{id}/update-status', 'updateStatus')->name('update-status');
                Route::get('/{id}/stats/ajax', 'getStatsAjax')->name('stats.ajax');
                Route::get('/{id}/chart-data/ajax', 'getChartDataAjax')->name('chart-data.ajax');
                Route::get('/export', 'export')->name('export');
            });
        });

        // Legacy routes for backward compatibility
        Route::prefix('legacy')->group(function () {
            
            // Legacy Admin Users (from roles folder)
            Route::resource('users', AdminUserController::class);
            Route::post('users/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

            // Legacy Plans CRUD
            Route::resource('plans', AdminPlanController::class);

            // Legacy Plan Coupons CRUD
            Route::resource('plan-coupons', AdminPlanCouponController::class);

            // Legacy Site Settings dashboard
            Route::get('site-settings', [AdminPanelController::class, 'siteSettingsDashboard'])->name('site-settings.dashboard');

            // Audit Logs Management
            Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
                Route::get('/', [AuditLogController::class, 'index'])->name('index');
                Route::get('/{id}', [AuditLogController::class, 'show'])->name('show');
                Route::get('/export', [AuditLogController::class, 'export'])->name('export');
            });

            // Admin Sessions Management
            Route::prefix('sessions')->name('sessions.')->group(function () {
                Route::get('/', [AdminSessionController::class, 'index'])->name('index');
                Route::get('/my-sessions', [AdminSessionController::class, 'mySessions'])->name('my-sessions');
                Route::get('/statistics', [AdminSessionController::class, 'statistics'])->name('statistics');
                Route::get('/{id}', [AdminSessionController::class, 'show'])->name('show');
                Route::post('/{id}/terminate', [AdminSessionController::class, 'terminate'])->name('terminate');
                Route::post('/{adminId}/terminate-all', [AdminSessionController::class, 'terminateAllForAdmin'])->name('terminate-all');
                Route::post('/my-sessions/{id}/terminate', [AdminSessionController::class, 'terminateMy'])->name('terminate-my');
            });

            // Legacy Subscriptions Management
            Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
                Route::get('/', [AdminSubscriptionController::class, 'index'])->name('index');
                Route::get('/statistics', [AdminSubscriptionController::class, 'statistics'])->name('statistics');
                Route::get('/{id}', [AdminSubscriptionController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [AdminSubscriptionController::class, 'edit'])->name('edit');
                Route::put('/{id}', [AdminSubscriptionController::class, 'update'])->name('update');
                Route::post('/{id}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('cancel');
                Route::post('/{id}/upgrade', [AdminSubscriptionController::class, 'upgrade'])->name('upgrade');
                Route::post('/{id}/manual-activate', [AdminSubscriptionController::class, 'manualActivate'])->name('manual-activate');
                Route::post('/{id}/extend', [AdminSubscriptionController::class, 'extend'])->name('extend');
                Route::post('/export', [AdminSubscriptionController::class, 'export'])->name('export');
                Route::post('/{userId}/{planId}/trial', [AdminSubscriptionController::class, 'activateTrial'])->name('trial');
                Route::post('/{userId}/{planId}/activate', [AdminSubscriptionController::class, 'activatePlan'])->name('activate');
            });

            // Legacy Network Management
            Route::resource('networks', NetworkManagementController::class)->only(['index','show']);
            Route::post('networks/{id}/toggle-status', [NetworkManagementController::class, 'updateStatus'])->name('networks.toggle-status');
            Route::get('networks/proxies', [NetworkManagementController::class, 'proxies'])->name('networks.proxies');
            Route::post('networks/proxies', [NetworkManagementController::class, 'storeProxy'])->name('networks.proxies.store');
            Route::get('networks/proxies/{id}/edit', [AdminUserManagementController::class, 'editProxy'])->name('networks.proxies.edit');
            Route::put('networks/proxies/{id}', [NetworkManagementController::class, 'updateProxy'])->name('networks.proxies.update');
            Route::delete('networks/proxies/{id}', [NetworkManagementController::class, 'destroyProxy'])->name('networks.proxies.destroy');
            Route::get('networks/{id}/stats', [NetworkManagementController::class, 'getNetworkStats'])->name('networks.stats');
            
            // Emergency Network Proxies route - works without middleware conflicts
            Route::get('emergency-networks-proxies', function() {
                try {
                    // Check if admin is authenticated
                    if (!\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
                        return redirect()->route('admin.login')->with('error', 'Please login as admin first.');
                    }
                    
                    $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                    if (!$admin->active) {
                        return redirect()->route('admin.login')->with('error', 'Your admin account is inactive.');
                    }
                    
                    // Get proxies data
                    $proxies = \App\Models\NetworkProxy::with('network:id,display_name')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

                    $networks = \App\Models\Network::where('is_active', true)
                        ->orderBy('display_name')
                        ->get();

                    return view('admin.networks.proxies', compact('proxies', 'networks'));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Emergency networks proxies error: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to load network proxies: ' . $e->getMessage());
                }
            })->name('admin.emergency.networks.proxies');

            // Legacy System Management
            Route::get('system/global-settings', [SystemController::class, 'globalSettings'])->name('system.global-settings');
            Route::post('system/global-settings', [SystemController::class, 'updateGlobalSettings'])->name('system.global-settings.update');
            Route::get('system/stats', [SystemController::class, 'getSystemStats'])->name('system.stats');
        });

        // Notifications Management
        Route::middleware(['admin.permission:view-notifications'])->group(function () {
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', [NotificationController::class, 'index'])->name('index');
                Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
                Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
                Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
                Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
                Route::post('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
            });
        });
    });
});

// AJAX API Routes for Admin
Route::prefix('admin/ajax')->name('admin.ajax.')->middleware(['auth:admin', 'ensure.user.type:admin', 'admin.rate.limit:100,1'])->group(function () {
    
    // Bulk operations
    Route::post('/bulk/delete', function() {
        return response()->json(['message' => 'Bulk delete endpoint']);
    })->name('bulk.delete');
    
    Route::post('/bulk/export', function() {
        return response()->json(['message' => 'Bulk export endpoint']);
    })->name('bulk.export');
    
    Route::post('/bulk/status-update', function() {
        return response()->json(['message' => 'Bulk status update endpoint']);
    })->name('bulk.status-update');
    
    // File uploads
    Route::post('/upload', function() {
        return response()->json(['message' => 'File upload endpoint']);
    })->name('upload');
    
    // Search
    Route::get('/search', function() {
        return response()->json(['message' => 'Search endpoint']);
    })->name('search');
});
