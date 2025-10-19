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
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\admin\AdminUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return view('admin.dashboard', compact('stats'));
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
        return [
            'total_users' => User::count(),
            'active_subscriptions' => User::whereHas('subscription', function ($q) {
                $q->where('status', 'active');
            })->count(),
            'trial_users' => User::whereHas('subscription', function ($q) {
                $q->where('status', 'trial');
            })->count(),
            'active_networks' => Network::where('is_active', true)->count(),
            'syncs_today' => SyncLog::whereDate('started_at', today())->count(),
            'active_sessions' => UserSession::where('is_active', true)->count(),
            'total_revenue' => DB::table('purchases')->sum('revenue'),
        ];
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

    public function postProfile(AdminUpdateRequest $request)
    {
        $user = Auth::guard('admin')->user();
        $data = $request->except('password');
        if ($request->password != null) {
            $user->password = Hash::make($request->password);
        }
        $user->update($data);
        return back()->with('success', 'Profile updated successfully');
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
}
