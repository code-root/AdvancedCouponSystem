<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Network;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard main page
     */
    public function index(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getDashboardData($request);
        }
        
        /** @var User $user */
        $user = Auth::user();
        
        // If sub-user, get parent's data
        $targetUserId = $user->isSubUser() ? $user->parent_user_id : $user->id;
        
        // Get connected networks for filters
        $networks = User::find($targetUserId)->connectedNetworks;
        
        // Get initial stats
        $stats = $this->getInitialStats($targetUserId);
        
        return view('dashboard.index', compact('stats', 'networks'));
    }
    
    /**
     * Get dashboard data (AJAX)
     */
    private function getDashboardData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // If sub-user, get parent's data
        $userId = $user->isSubUser() ? $user->parent_user_id : $user->id;
        
        $dateRange = $this->getDateRange($request);
        
        // Get network IDs from request (support multiple)
        $networkIds = $request->input('network_ids', []);
        
        // Ensure it's an array
        if (!is_array($networkIds)) {
            $networkIds = $networkIds ? [$networkIds] : [];
        }
        
        // Clean up
        $networkIds = array_filter($networkIds, function($id) {
            return !empty($id) && $id !== 'null' && $id !== null && $id !== '';
        });
        
        $networkIds = array_values($networkIds);
        $networkIds = !empty($networkIds) ? $networkIds : null;
        
        // Main statistics
        $stats = [
            // Overview stats
            'total_revenue' => $this->getTotalRevenue($userId, $dateRange, $networkIds),
            'total_commission' => $this->getTotalCommission($userId, $dateRange, $networkIds),
            'total_purchases' => $this->getTotalPurchases($userId, $dateRange, $networkIds),
            'total_campaigns' => $this->getTotalCampaigns($userId, $networkIds),
            'total_coupons' => $this->getTotalCoupons($userId, $networkIds),
            'active_networks' => $user->networkConnections()->where('is_connected', true)->count(),
            
            // Comparison with previous period
            'revenue_growth' => $this->getGrowthPercentage($userId, 'revenue', $dateRange, $networkIds),
            'purchases_growth' => $this->getGrowthPercentage($userId, 'purchases', $dateRange, $networkIds),
            
            // Network comparison
            'network_comparison' => $this->getNetworkComparison($userId, $dateRange),
            
            // Daily revenue trend
            'daily_revenue' => $this->getDailyRevenue($userId, $dateRange, $networkIds),
            
            // Top performers
            'top_campaigns' => $this->getTopCampaigns($userId, $dateRange, $networkIds),
            'top_networks' => $this->getTopNetworks($userId, $dateRange),
            
            // Recent activities
            'recent_purchases' => $this->getRecentPurchases($userId, $networkIds),
            
            // Status breakdown
            'purchase_status' => $this->getPurchaseStatusBreakdown($userId, $dateRange, $networkIds),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get initial stats for page load
     */
    private function getInitialStats($userId)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_revenue' => Purchase::where('user_id', $userId)->sum('revenue'),
            'total_commission' => Purchase::where('user_id', $userId)->sum('commission'),
            'total_purchases' => Purchase::where('user_id', $userId)->count(),
            'total_campaigns' => Campaign::where('user_id', $userId)->count(),
            'total_coupons' => Coupon::whereHas('campaign', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'active_networks' => User::find($userId)->networkConnections()->where('is_connected', true)->count(),
        ];
    }
    
    /**
     * Get date range from request
     */
    private function getDateRange(Request $request)
    {
        $from = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to = $request->date_to ?? Carbon::now()->format('Y-m-d');
        
        return ['from' => $from, 'to' => $to];
    }
    
    /**
     * Get total revenue
     */
    private function getTotalRevenue($userId, $dateRange, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']]);
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->sum('revenue');
    }
    
    /**
     * Get total commission
     */
    private function getTotalCommission($userId, $dateRange, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']]);
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->sum('commission');
    }
    
    /**
     * Get total purchases
     */
    private function getTotalPurchases($userId, $dateRange, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']]);
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->count();
    }
    
    /**
     * Get total campaigns
     */
    private function getTotalCampaigns($userId, $networkIds = null)
    {
        $query = Campaign::where('user_id', $userId);
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->count();
    }
    
    /**
     * Get total coupons
     */
    private function getTotalCoupons($userId, $networkIds = null)
    {
        $query = Coupon::whereHas('campaign', function($q) use ($userId, $networkIds) {
            $q->where('user_id', $userId);
            if ($networkIds) {
                $q->whereIn('network_id', $networkIds);
            }
        });
        
        return $query->count();
    }
    
    /**
     * Get growth percentage compared to previous period
     */
    private function getGrowthPercentage($userId, $metric, $dateRange, $networkIds = null)
    {
        $from = Carbon::parse($dateRange['from']);
        $to = Carbon::parse($dateRange['to']);
        $days = $from->diffInDays($to) + 1;
        
        $previousFrom = $from->copy()->subDays($days);
        $previousTo = $from->copy()->subDay();
        
        $query = Purchase::where('user_id', $userId);
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        if ($metric === 'revenue') {
            $current = (clone $query)->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])->sum('revenue');
            $previous = (clone $query)->whereBetween('order_date', [$previousFrom->format('Y-m-d'), $previousTo->format('Y-m-d')])->sum('revenue');
        } else {
            $current = (clone $query)->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])->count();
            $previous = (clone $query)->whereBetween('order_date', [$previousFrom->format('Y-m-d'), $previousTo->format('Y-m-d')])->count();
        }
        
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
    
    /**
     * Get network comparison
     */
    private function getNetworkComparison($userId, $dateRange)
    {
        return Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])
            ->select('network_id',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('SUM(commission) as total_commission'),
                DB::raw('COUNT(*) as total_purchases'),
                DB::raw('AVG(revenue) as avg_revenue'))
            ->with('network:id,display_name')
            ->groupBy('network_id')
            ->orderByDesc('total_revenue')
            ->get();
    }
    
    /**
     * Get daily revenue
     */
    private function getDailyRevenue($userId, $dateRange, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])
            ->select(DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(revenue) as revenue'),
                DB::raw('COUNT(*) as purchases'))
            ->groupBy('date')
            ->orderBy('date', 'asc');
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->get();
    }
    
    /**
     * Get top campaigns
     */
    private function getTopCampaigns($userId, $dateRange, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])
            ->select('campaign_id',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('COUNT(*) as total_purchases'))
            ->with('campaign:id,name,network_id')
            ->groupBy('campaign_id');
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->orderByDesc('total_revenue')->limit(10)->get();
    }
    
    /**
     * Get top networks
     */
    private function getTopNetworks($userId, $dateRange)
    {
        return Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])
            ->select('network_id',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('SUM(commission) as total_commission'),
                DB::raw('COUNT(*) as total_purchases'))
            ->with('network:id,display_name')
            ->groupBy('network_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get recent purchases
     */
    private function getRecentPurchases($userId, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->with(['coupon', 'campaign', 'network'])
            ->latest('order_date');
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->limit(10)->get();
    }
    
    /**
     * Get purchase status breakdown
     */
    private function getPurchaseStatusBreakdown($userId, $dateRange, $networkIds = null)
    {
        $query = Purchase::where('user_id', $userId)
            ->whereBetween('order_date', [$dateRange['from'], $dateRange['to']])
            ->select('status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(revenue) as revenue'))
            ->groupBy('status');
        
        if ($networkIds) {
            $query->whereIn('network_id', $networkIds);
        }
        
        return $query->get();
    }

    /**
     * Get dashboard overview data
     */
    public function overview()
    {
        $data = [
            'coupons_usage' => Coupon::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->latest('date')
                ->limit(30)
                ->get(),
            
            'revenue_trend' => Purchase::selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->where('status', 'completed')
                ->groupBy('date')
                ->latest('date')
                ->limit(30)
                ->get(),
        ];

        return response()->json($data);
    }

    /**
     * Get analytics data
     */
    public function analytics()
    {
        $analytics = [
            'campaigns_performance' => Campaign::withCount(['coupons', 'purchases'])
                ->with(['purchases' => function($query) {
                    $query->selectRaw('campaign_id, SUM(amount) as revenue')
                        ->where('status', 'completed')
                        ->groupBy('campaign_id');
                }])
                ->get(),
            
            'top_coupons' => Coupon::withCount('purchases')
                ->orderBy('purchases_count', 'desc')
                ->limit(10)
                ->get(),
                
            'network_stats' => Network::withCount(['campaigns', 'purchases'])
                ->get(),
        ];

        return response()->json($analytics);
    }

    /**
     * Get recent activities
     */
    public function recentActivities()
    {
        $activities = Purchase::with(['user', 'coupon', 'campaign'])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($activities);
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('dashboard.profile.index', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    /**
     * Show settings page (Admin only)
     */
    public function settings()
    {
        return view('dashboard.settings');
    }

    /**
     * Update general settings
     */
    public function updateGeneralSettings(Request $request)
    {
        // Implementation depends on settings structure
        return back()->with('success', 'Settings updated successfully');
    }

    /**
     * Update email settings
     */
    public function updateEmailSettings(Request $request)
    {
        // Implementation depends on settings structure
        return back()->with('success', 'Email settings updated successfully');
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        // Implementation depends on settings structure
        return back()->with('success', 'Notification settings updated successfully');
    }

    /**
     * Show users list (only users created by current user)
     */
    public function users()
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        
        // Get users created by current user
        $users = User::where('created_by', $currentUser->id)
            ->withCount(['networkConnections', 'campaigns', 'purchases' => function($q) {
                $q->where('status', 'approved');
            }])
            ->latest()
            ->paginate(20);
        
        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        return view('dashboard.users.create');
    }

    /**
     * Store new user (sub-user inherits parent's parent_user_id)
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();
        
        // Get the main parent user ID
        $parentUserId = $currentUser->isSubUser() ? $currentUser->parent_user_id : $currentUser->id;
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'created_by' => $currentUser->id, // Who created this user
            'parent_user_id' => $parentUserId, // Main parent account
        ]);

        return redirect()->route('users.index')->with('success', 'Sub-user created successfully');
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        
        // Check if current user can edit this user
        if ($user->created_by !== $currentUser->id && $user->id !== $currentUser->id) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('dashboard.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        
        // Check if current user can update this user
        if ($user->created_by !== $currentUser->id && $user->id !== $currentUser->id) {
            return back()->with('error', 'You can only edit users you created or your own account');
        }
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Update basic info
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        
        // Update password if provided
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function destroyUser(User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        
        // Prevent deleting yourself
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account');
        }
        
        // Prevent deleting users not created by you
        if ($user->created_by !== $currentUser->id) {
            return back()->with('error', 'You can only delete users you created');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
