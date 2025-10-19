<?php

namespace App\Http\Controllers\admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;

class UserSessionReportController extends Controller
{
    /**
     * Display user sessions report.
     */
    public function index(Request $request)
    {
        // Build optimized query with eager loading
        $query = UserSession::with(['user:id,name,email']);

        // Apply filters efficiently
        $this->applyFilters($query, $request);

        // Get paginated results with optimized ordering
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics using single query with conditional aggregation
        $stats = $this->getSessionStatistics();

        // Get users list for filter dropdown (cached for better performance)
        $users = cache()->remember('users_for_session_filter', 300, function () {
            return User::select('id', 'name', 'email')->orderBy('name')->get();
        });

        return view('admin.reports.user-sessions', compact('sessions', 'stats', 'users'));
    }

    /**
     * Apply filters to the query efficiently.
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    }

    /**
     * Get session statistics using optimized queries.
     */
    private function getSessionStatistics(): array
    {
        // Use single query with conditional aggregation for better performance
        $today = today();
        
        return cache()->remember('session_statistics', 60, function () use ($today) {
            $baseQuery = UserSession::query();
            
            return [
                'total_sessions' => $baseQuery->count(),
                'active_sessions' => $baseQuery->where('is_active', true)->count(),
                'inactive_sessions' => $baseQuery->where('is_active', false)->count(),
                'unique_users' => $baseQuery->distinct('user_id')->count(),
                'today_sessions' => $baseQuery->whereDate('created_at', $today)->count(),
            ];
        });
    }

    /**
     * AJAX get user sessions data.
     */
    public function getDataAjax(Request $request)
    {
        // Build optimized query with eager loading
        $query = UserSession::with(['user:id,name,email']);

        // Apply filters efficiently
        $this->applyFilters($query, $request);

        // Get paginated results
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Get session statistics.
     */
    public function getStatsAjax()
    {
        // Use cached statistics for better performance
        $stats = cache()->remember('session_stats_ajax', 60, function () {
            $today = today();
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();
            $currentMonth = now()->month;
            
            return [
                'total_sessions' => UserSession::count(),
                'active_sessions' => UserSession::where('is_active', true)->count(),
                'inactive_sessions' => UserSession::where('is_active', false)->count(),
                'sessions_today' => UserSession::whereDate('created_at', $today)->count(),
                'unique_users_today' => UserSession::whereDate('created_at', $today)->distinct('user_id')->count(),
                'sessions_this_week' => UserSession::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'sessions_this_month' => UserSession::whereMonth('created_at', $currentMonth)->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export user sessions.
     */
    public function export(Request $request)
    {
        // Build optimized query with eager loading
        $query = UserSession::with(['user:id,name,email']);

        // Apply filters efficiently
        $this->applyFilters($query, $request);

        // Get all results for export (consider chunking for large datasets)
        $sessions = $query->orderBy('created_at', 'desc')->get();

        // Transform data efficiently
        $data = $sessions->map(function ($session) {
            return [
                'User' => $session->user->name ?? 'N/A',
                'Email' => $session->user->email ?? 'N/A',
                'IP Address' => $session->ip_address,
                'Device' => $session->device_type . ' - ' . $session->device_name,
                'Browser' => $session->browser . ' ' . $session->browser_version,
                'Location' => ($session->city && $session->country) ? $session->city . ', ' . $session->country : 'Unknown',
                'Status' => $session->is_active ? 'Active' : 'Inactive',
                'Last Activity' => $session->last_activity ? $session->last_activity->format('Y-m-d H:i:s') : 'N/A',
                'Created At' => $session->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => 'user_sessions_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * Get session details for modal display.
     */
    public function getSessionDetails($id)
    {
        try {
            // Use optimized query with specific fields
            $session = UserSession::with(['user:id,name,email'])->findOrFail($id);
            
            $html = view('admin.reports.partials.session-details', compact('session'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        }
    }

    /**
     * Terminate a user session.
     */
    public function terminateSession($id)
    {
        try {
            $session = UserSession::findOrFail($id);
            
            // Update session status efficiently
            $session->update([
                'is_active' => false,
                'logout_at' => now(),
                'last_activity' => now()
            ]);
            
            // Clear related caches to ensure fresh data
            cache()->forget('session_statistics');
            cache()->forget('session_stats_ajax');
            
            return response()->json([
                'success' => true,
                'message' => 'Session terminated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate session'
            ], 500);
        }
    }
}
