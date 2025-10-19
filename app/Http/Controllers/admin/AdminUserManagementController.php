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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        // Optimize user query with specific field selection
        $user = User::with([
            'subscription:id,user_id,plan_id,status,starts_at,ends_at',
            'subscription.plan:id,name',
            'networkConnections:id,user_id,network_id,connection_name,is_connected,connected_at',
            'networkConnections.network:id,display_name,name',
            'campaigns:id,user_id,name,status',
            'purchases:id,user_id,campaign_id,network_id,revenue,order_date'
        ])->findOrFail($id);

        // Load additional relationships separately to avoid conflicts
        $user->load([
            'campaigns' => function ($query) {
                $query->withCount(['coupons', 'purchases']);
            },
            'purchases' => function ($query) {
                $query->with(['campaign:id,name', 'network:id,display_name'])->latest()->limit(10);
            }
        ]);

        // Get recent sync logs with optimized query
        $recentSyncLogs = SyncLog::where('user_id', $id)
            ->select('id', 'user_id', 'network_id', 'sync_type', 'status', 'duration_seconds', 'records_synced', 'started_at')
            ->with('network:id,display_name')
            ->latest('started_at')
            ->limit(10)
            ->get();

        // Get usage statistics with optimized queries
        $usageStats = $this->getUserUsageStats($id);

        // Get monthly revenue trend with optimized query
        $monthlyRevenue = Purchase::where('user_id', $id)
            ->selectRaw('DATE_FORMAT(order_date, "%Y-%m") as month, SUM(revenue) as revenue, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.users.show', compact('user', 'recentSyncLogs', 'usageStats', 'monthlyRevenue'));
    }

    /**
     * Get optimized user usage statistics.
     */
    private function getUserUsageStats($userId): array
    {
        try {
            // Use cached statistics for better performance
            return cache()->remember("user_usage_stats_{$userId}", 300, function () use ($userId) {
                $today = today();
                $currentMonth = now()->month;
                $currentYear = now()->year;
                
                // Get last sync date efficiently
                $lastSync = SyncLog::where('user_id', $userId)
                    ->latest('started_at')
                    ->value('started_at');
                
                return [
                    'daily_syncs' => SyncLog::where('user_id', $userId)
                        ->whereDate('started_at', $today)
                        ->count(),
                    'monthly_syncs' => SyncLog::where('user_id', $userId)
                        ->whereMonth('started_at', $currentMonth)
                        ->whereYear('started_at', $currentYear)
                        ->count(),
                    'total_revenue' => Purchase::where('user_id', $userId)->sum('revenue') ?? 0,
                    'total_orders' => Purchase::where('user_id', $userId)->count(),
                    'connected_networks' => NetworkConnection::where('user_id', $userId)
                        ->where('is_connected', true)
                        ->count(),
                    'last_sync' => $lastSync ? \Carbon\Carbon::parse($lastSync) : null,
                ];
            });
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'daily_syncs' => 0,
                'monthly_syncs' => 0,
                'total_revenue' => 0,
                'total_orders' => 0,
                'connected_networks' => 0,
                'last_sync' => null,
            ];
        }
    }

    /**
     * Toggle user status (active/inactive/suspended).
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);
        
        $user->update(['status' => $validated['status']]);
        
        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'new_status' => $validated['status']
        ]);
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
        try {
            $user = User::findOrFail($id);
            
            // Check if admin is authenticated
            if (!Auth::guard('admin')->check()) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Admin authentication required'
                    ], 401);
                }
                return redirect()->route('admin.login')->with('error', 'Admin authentication required');
            }
            
            $adminId = Auth::guard('admin')->id();
            
            // Store original admin session ID for restoration
            $originalAdminSessionId = session()->getId();
            session(['original_admin_session_id' => $originalAdminSessionId]);
            
            // Login as user first
            Auth::guard('web')->login($user);
            
            // Store impersonation data AFTER logging in as user
            \App\Helpers\ImpersonationHelper::startImpersonation($adminId, $user->id, $user->name);
            
            // Log impersonation start
            Log::info('Admin started impersonation', [
                'admin_id' => $adminId,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Create audit log entry if available
            if (class_exists('\App\Models\AdminAuditLog')) {
                \App\Models\AdminAuditLog::create([
                    'admin_id' => $adminId,
                    'action' => 'impersonate_user',
                    'description' => "Started impersonating user: {$user->name} (ID: {$user->id})",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Now impersonating {$user->name}",
                    'redirect_url' => url('/dashboard')
                ]);
            }
            
            return redirect()->route('dashboard')->with('success', "Now impersonating {$user->name}");
        } catch (\Exception $e) {
            Log::error('Impersonation error: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to impersonate user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to impersonate user: ' . $e->getMessage());
        }
    }

    /**
     * Stop impersonating and return to admin.
     */
    public function stopImpersonating()
    {
        try {
            $adminId = \App\Helpers\ImpersonationHelper::getAdminId();
            $impersonatedUserName = \App\Helpers\ImpersonationHelper::getImpersonatedUserName();
            
            if (!$adminId) {
                // If no impersonation session, check if admin is already logged in
                if (Auth::guard('admin')->check()) {
                    return redirect()->route('admin.dashboard')->with('info', 'No active impersonation session.');
                }
                return redirect()->route('admin.login')->with('error', 'No active impersonation session.');
            }
            
            // Log impersonation stop
            Log::info('Admin stopped impersonation', [
                'admin_id' => $adminId,
                'impersonated_user_name' => $impersonatedUserName,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Create audit log entry if available
            if (class_exists('\App\Models\AdminAuditLog')) {
                \App\Models\AdminAuditLog::create([
                    'admin_id' => $adminId,
                    'action' => 'stop_impersonate_user',
                    'description' => "Stopped impersonating user: {$impersonatedUserName}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
            
            // Logout from user session
            Auth::guard('web')->logout();
            
            // Clear impersonation session data
            \App\Helpers\ImpersonationHelper::stopImpersonation();
            session()->forget(['original_admin_session_id']);
            
            // Restore admin session
            $admin = \App\Models\Admin::find($adminId);
            if ($admin && $admin->active) {
                Auth::guard('admin')->login($admin);
                
                // Regenerate session for security
                session()->regenerate();
                
                return redirect()->route('admin.dashboard')->with('success', "Stopped impersonating {$impersonatedUserName}.");
            } else {
                return redirect()->route('admin.login')->with('error', 'Admin account not found or inactive.');
            }
        } catch (\Exception $e) {
            Log::error('Stop impersonation error: ' . $e->getMessage());
            
            // Clear any remaining impersonation data
            \App\Helpers\ImpersonationHelper::stopImpersonation();
            session()->forget(['original_admin_session_id']);
            
            return redirect()->route('admin.login')->with('error', 'Error stopping impersonation. Please log in again.');
        }
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
    public function edit($id) {
        $user = User::findOrFail($id);
        $plans = \App\Models\Plan::where('is_active', true)->get();
        return view('admin.user-management.edit', compact('user', 'plans'));
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
        try {
            // Use the same optimized method as show()
            $usageStats = $this->getUserUsageStats($id);
            
            // Add additional stats for AJAX requests
            $additionalStats = [
                'this_month_revenue' => Purchase::where('user_id', $id)
                    ->whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->sum('revenue'),
            'this_month_orders' => Purchase::where('user_id', $id)
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->count(),
        ];

            $stats = array_merge($usageStats, $additionalStats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Get user stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user statistics: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Update user password.
     */
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Send email to user.
     */
    public function sendEmail(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
            ]);
            
            Mail::raw($validated['message'], function ($message) use ($user, $validated) {
                $message->to($user->email)
                        ->subject($validated['subject']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete users.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:users,id',
        ]);

        $deleted = 0;
        $errors = [];

        foreach ($validated['ids'] as $id) {
            try {
                $user = User::findOrFail($id);
                
                // Check if user has important data
                $hasData = $user->campaigns()->count() > 0 || 
                           $user->purchases()->count() > 0 || 
                           $user->networkConnections()->count() > 0;

                if ($hasData) {
                    $errors[] = "User {$user->name} has associated data and cannot be deleted";
                    continue;
                }

                $user->delete();
                $deleted++;
            } catch (\Exception $e) {
                $errors[] = "Failed to delete user ID {$id}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} users successfully",
            'deleted' => $deleted,
            'errors' => $errors
        ]);
    }

    /**
     * Bulk export users.
     */
    public function bulkExport(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:users,id',
            'format' => 'in:csv,excel,json',
        ]);

        $users = User::whereIn('id', $validated['ids'])->get();
        
        $data = $users->map(function ($user) {
            return [
                'ID' => $user->id,
                'Name' => $user->name,
                'Email' => $user->email,
                'Status' => $user->status ?? 'active',
                'Created At' => $user->created_at->format('Y-m-d H:i:s'),
                'Last Login' => $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                'Total Campaigns' => $user->campaigns()->count(),
                'Total Purchases' => $user->purchases()->count(),
                'Total Revenue' => $user->purchases()->sum('revenue'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => 'users_export_' . now()->format('Y-m-d_H-i-s') . '.' . ($validated['format'] ?? 'csv')
        ]);
    }

    /**
     * Bulk status update.
     */
    public function bulkStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:users,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $updated = User::whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} users to {$validated['status']} status",
            'updated' => $updated
        ]);
    }

    /**
     * Get user activity log.
     */
    public function activityLog($id)
    {
        $user = User::findOrFail($id);
        
        // This would need an activity log model/table
        $activities = collect([
            [
                'action' => 'Login',
                'description' => 'User logged in',
                'timestamp' => $user->last_login_at,
                'ip_address' => '192.168.1.1',
            ],
            [
                'action' => 'Campaign Created',
                'description' => 'Created new campaign',
                'timestamp' => $user->campaigns()->latest()->first()?->created_at,
                'ip_address' => '192.168.1.1',
            ],
        ])->filter(fn($activity) => $activity['timestamp']);

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

}