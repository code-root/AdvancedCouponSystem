<?php

namespace App\Http\Controllers\admin;

use App\Models\Admin;
use App\Models\Status;
use App\Models\Target;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Network;
use App\Models\SyncLog;
use App\Models\UserSession;
use App\Models\AdminSession;
use App\Models\AdminAuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\admin\AdminUpdateRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;


class AdminController extends Controller
{
//    Admin Auth
    public function __construct()
    {
        //
    }

    public function login()
    {
        return view('admin.auth.login');
    }


    public function postLogin(Request $request)
    {
        // Validate the login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->remember ? 1 : 0;
        
        // Try to authenticate as admin (Admin model only)
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();
            $request->session()->regenerate();
            $request->session()->forget('url.intended');
            
            // Check if the account is active
            if ($admin->active == 0) {
                Auth::guard('admin')->logout();
                return redirect()->route('admin.login')->with('error', 'Your account is inactive. Please contact support.')->withInput($request->only('email'));
            }
            // If active, redirect to the admin dashboard
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, Admin!');
        }

        // If credentials are invalid, redirect back with an error message
        return redirect()->route('admin.login')->with('error', 'Login details are not valid')->withInput($request->only('email'));
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return to_route('admin.login');
    }

    public function dashboard(Request $request)
    {
        // For AJAX requests, return JSON data
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getDashboardData($request);
        }

        // Get initial statistics for page load
        $stats = $this->getInitialStats();
        $recentActivities = $this->getRecentActivities();
        $pendingActivations = $this->getPendingManualActivations();
        $systemAlerts = $this->getSystemAlerts();

        return view('admin.dashboard', compact('stats', 'recentActivities', 'pendingActivations', 'systemAlerts'));
    }

    /**
     * Get dashboard data (AJAX)
     */
    private function getDashboardData(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        // Main statistics
        $stats = [
            'total_users' => User::count(),
            'active_subscriptions' => User::whereHas('subscription', function ($q) {
                $q->where('status', 'active');
            })->count(),
            'trial_users' => User::whereHas('subscription', function ($q) {
                $q->where('status', 'trial');
            })->count(),
            'expired_subscriptions' => User::whereHas('subscription', function ($q) {
                $q->where('status', 'expired');
            })->count(),
            'active_networks' => Network::where('is_active', true)->count(),
            'syncs_today' => SyncLog::whereDate('started_at', $date)->count(),
            'active_sessions' => UserSession::where('is_active', true)->count(),
            'total_revenue' => DB::table('purchases')->sum('revenue'),
            'users_growth' => $this->getGrowthPercentage('users', $date),
            'revenue_growth' => $this->getGrowthPercentage('revenue', $date),
        ];

        // Charts data
        $charts = [
            'revenue_trend' => $this->getRevenueTrend(),
            'plan_distribution' => $this->getPlanDistribution(),
        ];

        // Tables data
        $tables = [
            'recent_users' => $this->getRecentUsers(),
            'recent_subscriptions' => $this->getRecentSubscriptions(),
            'failed_syncs' => $this->getFailedSyncs($date),
            'active_sessions' => $this->getActiveSessions(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'charts' => $charts,
            'tables' => $tables
        ]);
    }

    /**
     * Get initial stats for page load
     */
    private function getInitialStats()
    {
        return Cache::remember('admin_dashboard_stats', 300, function () {
            $stats = [
                'total_users' => User::count(),
                'active_subscriptions' => Subscription::where('status', 'active')->count(),
                'trial_subscriptions' => Subscription::where('status', 'trialing')->count(),
                'canceled_subscriptions' => Subscription::where('status', 'canceled')->count(),
                'active_networks' => Network::where('is_active', true)->count(),
                'syncs_today' => SyncLog::whereDate('started_at', today())->count(),
                'active_user_sessions' => UserSession::where('is_active', true)->count(),
                'active_admin_sessions' => AdminSession::where('is_active', true)->count(),
                'total_revenue' => $this->getTotalRevenue(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'new_subscriptions_today' => Subscription::whereDate('created_at', today())->count(),
                'new_users_today' => User::whereDate('created_at', today())->count(),
                'failed_syncs_today' => SyncLog::where('status', 'failed')->whereDate('started_at', today())->count(),
                'audit_logs_today' => AdminAuditLog::whereDate('created_at', today())->count(),
            ];

            // Add system health stats with error handling
            try {
                $stats['queue_status'] = $this->getQueueStatus();
            } catch (\Exception $e) {
                $stats['queue_status'] = 'unknown';
            }

            try {
                $stats['storage_usage'] = $this->getStorageUsage();
            } catch (\Exception $e) {
                $stats['storage_usage'] = 0;
            }

            return $stats;
        });
    }

    /**
     * Get growth percentage compared to previous period
     */
    private function getGrowthPercentage($metric, $date)
    {
        $currentDate = Carbon::parse($date);
        $previousDate = $currentDate->copy()->subDay();

        if ($metric === 'users') {
            $current = User::whereDate('created_at', $currentDate)->count();
            $previous = User::whereDate('created_at', $previousDate)->count();
        } else {
            $current = DB::table('purchases')->whereDate('order_date', $currentDate)->sum('revenue');
            $previous = DB::table('purchases')->whereDate('order_date', $previousDate)->sum('revenue');
        }

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Get revenue trend for last 30 days
     */
    private function getRevenueTrend()
    {
        return DB::table('purchases')
            ->select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->where('order_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get plan distribution
     */
    private function getPlanDistribution()
    {
        return DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select(
                'plans.name as plan_name',
                DB::raw('COUNT(*) as subscriber_count')
            )
            ->where('subscriptions.status', 'active')
            ->groupBy('plans.id', 'plans.name')
            ->get();
    }

    /**
     * Get recent users
     */
    private function getRecentUsers()
    {
        return User::with('subscription')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'subscription_status' => $user->subscription ? $user->subscription->status : 'no_subscription'
                ];
            });
    }

    /**
     * Get recent subscriptions
     */
    private function getRecentSubscriptions()
    {
        return Subscription::with(['user:id,name', 'plan:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($subscription) {
                return [
                    'user_name' => $subscription->user->name,
                    'plan_name' => $subscription->plan->name,
                    'status' => $subscription->status,
                    'created_at' => $subscription->created_at
                ];
            });
    }

    /**
     * Get failed syncs for a specific date
     */
    private function getFailedSyncs($date)
    {
        return SyncLog::with(['user:id,name', 'network:id,display_name'])
            ->where('status', 'failed')
            ->whereDate('started_at', $date)
            ->latest('started_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'user_name' => $log->user->name,
                    'network_name' => $log->network->display_name,
                    'error_message' => $log->error_message,
                    'started_at' => $log->started_at
                ];
            });
    }

    /**
     * Get active sessions
     */
    private function getActiveSessions()
    {
        return UserSession::with('user:id,name')
            ->where('is_active', true)
            ->latest('last_activity')
            ->limit(10)
            ->get()
            ->map(function ($session) {
                return [
                    'user_name' => $session->user->name,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => $session->last_activity
                ];
            });
    }


//    A Profile
    public function profile()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.profile', [
            'user' => $user
        ]);
    }

    public function postProfile(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
        ]);
        
        $user->update($validated);
        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);
        
        return back()->with('success', 'Password updated successfully');
    }

    public function getLeads($categoryId)
    {
        $value = $this->adminRepository->getLeads($categoryId);

        if (!$value) {
            return back()->with('error', 'Not Enough Patients');

        }
        return back()->with('success', 'Patients retrieved successfully');
    }

    public function markAllNotificationsRead()
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            $admin->unreadNotifications()->update(['read_at' => now()]);
        }
        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Display site settings dashboard
     */
    public function siteSettingsDashboard()
    {
        // Get statistics
        $totalSettings = SiteSetting::count();
        $activeSettings = SiteSetting::where('is_active', true)->count();
        $recentChangesCount = SiteSetting::where('last_modified_at', '>=', now()->subDays(7))->count();
        $languagesCount = SiteSetting::distinct('locale')->count('locale');

        // Get recent changes
        $recentChanges = SiteSetting::with(['creator', 'updater'])
            ->orderBy('last_modified_at', 'desc')
            ->limit(10)
            ->get();

        // Get settings by group
        $settingsByGroup = SiteSetting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.site-settings.dashboard', compact(
            'totalSettings',
            'activeSettings', 
            'recentChangesCount',
            'languagesCount',
            'recentChanges',
            'settingsByGroup'
        ));
    }

    /**
     * Get total revenue
     */
    private function getTotalRevenue()
    {
        return DB::table('purchases')->sum('revenue') ?? 0;
    }

    /**
     * Get monthly revenue
     */
    private function getMonthlyRevenue()
    {
        return DB::table('purchases')
            ->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->sum('revenue') ?? 0;
    }

    /**
     * Get recent activities from audit logs
     */
    private function getRecentActivities()
    {
        return Cache::remember('admin_recent_activities', 60, function () {
            return AdminAuditLog::with('admin:id,name')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'admin_name' => $log->admin->name ?? 'System',
                        'action' => $log->action,
                        'description' => $log->description,
                        'model_type' => $log->model_type,
                        'created_at' => $log->created_at,
                        'time_ago' => $log->created_at->diffForHumans(),
                    ];
                });
        });
    }

    /**
     * Get pending manual activations
     */
    private function getPendingManualActivations()
    {
        return Cache::remember('pending_manual_activations', 300, function () {
            return Subscription::with(['user:id,name,email', 'plan:id,name'])
                ->where('status', 'pending')
                ->where('gateway', 'manual')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($subscription) {
                    return [
                        'id' => $subscription->id,
                        'user_name' => $subscription->user->name,
                        'user_email' => $subscription->user->email,
                        'plan_name' => $subscription->plan->name,
                        'created_at' => $subscription->created_at,
                        'time_ago' => $subscription->created_at->diffForHumans(),
                    ];
                });
        });
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        // Check for failed syncs
        $failedSyncsCount = SyncLog::where('status', 'failed')
            ->where('started_at', '>=', now()->subHours(24))
            ->count();

        if ($failedSyncsCount > 10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'High Failed Syncs',
                'message' => "{$failedSyncsCount} syncs failed in the last 24 hours",
                'action_url' => route('admin.reports.sync-logs'),
            ];
        }

        // Check for expiring subscriptions
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('ends_at', '>', now())
            ->count();

        if ($expiringSubscriptions > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Expiring Subscriptions',
                'message' => "{$expiringSubscriptions} subscriptions expiring in the next 7 days",
                'action_url' => route('admin.subscriptions.index'),
            ];
        }

        // Check for inactive admins
        $inactiveAdmins = Admin::where('active', false)->count();
        if ($inactiveAdmins > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Inactive Admins',
                'message' => "{$inactiveAdmins} admin accounts are inactive",
                'action_url' => route('admin.legacy.users.index'),
            ];
        }

        return $alerts;
    }

    /**
     * Get real-time dashboard data
     */
    public function getRealTimeData()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'active_sessions' => [
                    'users' => UserSession::where('is_active', true)->count(),
                    'admins' => AdminSession::where('is_active', true)->count(),
                ],
                'new_subscriptions_today' => Subscription::whereDate('created_at', today())->count(),
                'new_users_today' => User::whereDate('created_at', today())->count(),
                'failed_syncs_today' => SyncLog::where('status', 'failed')->whereDate('started_at', today())->count(),
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Get queue status
     */
    private function getQueueStatus(): string
    {
        try {
            // Check if queue worker is running by checking for recent jobs
            $recentJobs = DB::table('jobs')->where('created_at', '>=', now()->subMinutes(5))->count();
            $failedJobs = DB::table('failed_jobs')->where('failed_at', '>=', now()->subMinutes(5))->count();
            
            if ($recentJobs > 0 && $failedJobs === 0) {
                return 'running';
            } elseif ($failedJobs > 0) {
                return 'failed';
            } else {
                return 'idle';
            }
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Get storage usage percentage
     */
    private function getStorageUsage(): int
    {
        try {
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $usedSpace = $totalSpace - $freeSpace;
            
            return round(($usedSpace / $totalSpace) * 100);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
