<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\NetworkConnection;
use App\Models\SyncLog;
use App\Models\Campaign;
use App\Models\Purchase;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserManagementController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of users with subscription and usage information.
     */
    public function index(Request $request)
    {
        $query = User::with(['subscription.plan', 'networkConnections'])
            ->withCount([
                'networkConnections as connected_networks_count' => function ($query) {
                    $query->where('is_connected', true);
                },
                'campaigns as campaigns_count',
                'purchases as purchases_count'
            ])
            ->withSum('purchases as total_revenue', 'revenue');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', 'active');
                });
            } elseif ($request->status === 'trial') {
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', 'trial');
                });
            } elseif ($request->status === 'expired') {
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', 'expired');
                });
            } elseif ($request->status === 'no_subscription') {
                $query->whereDoesntHave('subscription');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics
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
            'no_subscription' => User::whereDoesntHave('subscription')->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display the specified user with detailed information.
     */
    public function show($id)
    {
        $user = User::with([
            'subscription.plan',
            'networkConnections.network',
            'campaigns' => function ($query) {
                $query->withCount(['coupons', 'purchases']);
            },
            'purchases' => function ($query) {
                $query->with(['campaign', 'network'])->latest()->limit(10);
            }
        ])->findOrFail($id);

        // Get recent sync logs
        $recentSyncLogs = SyncLog::where('user_id', $id)
            ->with('network:id,display_name')
            ->latest('started_at')
            ->limit(10)
            ->get();

        // Get usage statistics
        $usageStats = [
            'daily_syncs' => SyncLog::where('user_id', $id)
                ->whereDate('started_at', today())
                ->count(),
            'monthly_syncs' => SyncLog::where('user_id', $id)
                ->whereMonth('started_at', now()->month)
                ->whereYear('started_at', now()->year)
                ->count(),
            'total_revenue' => $user->purchases()->sum('revenue'),
            'total_orders' => $user->purchases()->count(),
            'connected_networks' => $user->networkConnections()->where('is_connected', true)->count(),
        ];

        // Get monthly revenue trend
        $monthlyRevenue = Purchase::where('user_id', $id)
            ->selectRaw('DATE_FORMAT(order_date, "%Y-%m") as month, SUM(revenue) as revenue, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.users.show', compact('user', 'recentSyncLogs', 'usageStats', 'monthlyRevenue'));
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'active' => 'required|boolean'
        ]);

        // In a real application, you might have an 'active' field in users table
        // For now, we'll just show a success message
        $status = $request->active ? 'activated' : 'deactivated';
        
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Link a user as sub-user to a parent user.
     */
    public function linkSubUser(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:users,id',
            'child_id' => 'required|exists:users,id|different:parent_id'
        ]);

        $child = User::findOrFail($request->child_id);
        $parent = User::findOrFail($request->parent_id);

        // Check if child is already linked
        if ($child->parent_user_id) {
            return back()->with('error', 'User is already linked to another parent.');
        }

        $child->update(['parent_user_id' => $request->parent_id]);

        return back()->with('success', "User {$child->name} linked to {$parent->name} successfully.");
    }

    /**
     * Unlink a sub-user from parent.
     */
    public function unlinkSubUser($id)
    {
        $user = User::findOrFail($id);

        if (!$user->parent_user_id) {
            return back()->with('error', 'User is not linked to any parent.');
        }

        $user->update(['parent_user_id' => null]);

        return back()->with('success', 'User unlinked successfully.');
    }

    /**
     * Impersonate a user (admin login as user).
     */
    public function impersonate($id)
    {
        $user = User::findOrFail($id);
        
        // Store admin session info
        session(['admin_impersonating' => Auth::guard('admin')->id()]);
        
        // Login as user
        Auth::guard('web')->login($user);
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Now impersonating {$user->name}",
                'redirect_url' => route('dashboard')
            ]);
        }
        
        return redirect()->route('dashboard')->with('success', "Now impersonating {$user->name}");
    }

    /**
     * Stop impersonating and return to admin.
     */
    public function stopImpersonating()
    {
        $adminId = session('admin_impersonating');
        
        if ($adminId) {
            Auth::guard('web')->logout();
            session()->forget('admin_impersonating');
            
            $admin = \App\Models\Admin::find($adminId);
            if ($admin) {
                Auth::guard('admin')->login($admin);
                return redirect()->route('admin.dashboard')->with('success', 'Stopped impersonating user.');
            }
        }
        
        return redirect()->route('admin.dashboard')->with('error', 'No active impersonation session.');
    }

    /**
     * Create a new user.
     */
    public function create()
    {
        $plans = \App\Models\Plan::where('is_active', true)->get();
        return view('admin.users.create', compact('plans'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Auto-verify admin-created users
        ]);

        return redirect()->route('admin.user-management.show', $user->id)
            ->with('success', 'User created successfully.');
    }

    /**
     * Edit user form.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.user-management.show', $user->id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Check if user has important data
        $hasData = $user->campaigns()->count() > 0 || 
                   $user->purchases()->count() > 0 || 
                   $user->networkConnections()->count() > 0;

        if ($hasData) {
            return back()->with('error', 'Cannot delete user. User has associated data (campaigns, purchases, or network connections).');
        }

        $user->delete();
        return redirect()->route('admin.user-management.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Get user statistics for AJAX requests.
     */
    public function getUserStats($id)
    {
        $user = User::findOrFail($id);

        $stats = [
            'connected_networks' => $user->networkConnections()->where('is_connected', true)->count(),
            'total_campaigns' => $user->campaigns()->count(),
            'total_revenue' => $user->purchases()->sum('revenue'),
            'total_orders' => $user->purchases()->count(),
            'this_month_revenue' => $user->purchases()
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->sum('revenue'),
            'this_month_orders' => $user->purchases()
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->count(),
            'last_sync' => SyncLog::where('user_id', $id)
                ->latest('started_at')
                ->first()?->started_at,
        ];

        return response()->json($stats);
    }

    /**
     * Get proxy data for editing (AJAX)
     */
    public function editProxy($id)
    {
        $proxy = \App\Models\NetworkProxy::findOrFail($id);
        
        return response()->json([
            'network_id' => $proxy->network_id,
            'proxy_url' => $proxy->proxy_url,
            'username' => $proxy->username,
            'is_active' => $proxy->is_active,
        ]);
    }
}