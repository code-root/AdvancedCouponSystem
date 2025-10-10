<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Broker;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    /**
     * Display dashboard main page
     */
    public function index()
    {
        $stats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::where('is_active', true)->count(),
            'total_purchases' => Purchase::count(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('amount'),
            'total_brokers' => Broker::count(),
            'active_brokers' => Broker::where('is_active', true)->count(),
        ];

        $recent_purchases = Purchase::with(['user', 'coupon'])
            ->latest()
            ->limit(10)
            ->get();

        $recent_coupons = Coupon::with('campaign')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('stats', 'recent_purchases', 'recent_coupons'));
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
                
            'broker_stats' => Broker::withCount(['campaigns', 'purchases'])
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
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
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

        Auth::user()->update([
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
     * Show users list (Admin only)
     */
    public function users()
    {
        $users = User::with('roles')->paginate(20);
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
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        return view('dashboard.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function destroyUser(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Role assigned successfully');
    }
}
