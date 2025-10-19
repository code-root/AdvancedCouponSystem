<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\NetworkSession;
use App\Models\SyncLog;
use App\Models\Network;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display user sessions report.
     */
    public function userSessions(Request $request)
    {
        // Build optimized query with specific field selection
        $query = UserSession::with(['user:id,name,email']);

        // Apply filters efficiently
        $this->applyUserSessionFilters($query, $request);

        // Get paginated results
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics with all required keys
        $stats = $this->getUserSessionStatistics();

        // Get users list for filter dropdown (cached for better performance)
        $users = cache()->remember('users_for_reports_filter', 300, function () {
            return User::select('id', 'name', 'email')->orderBy('name')->get();
        });

        return view('admin.reports.user-sessions', compact('sessions', 'stats', 'users'));
    }

    /**
     * Get user sessions data via AJAX
     */
    public function getDataAjax(Request $request)
    {
        // Build optimized query with specific field selection
        $query = UserSession::with(['user:id,name,email']);

        // Apply filters efficiently
        $this->applyUserSessionFilters($query, $request);

        // Get paginated results
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Get user sessions statistics via AJAX
     */
    public function getStatsAjax()
    {
        $stats = $this->getUserSessionStatistics();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Export user sessions data
     */
    public function export(Request $request)
    {
        // Build query with filters
        $query = UserSession::with(['user:id,name,email']);
        $this->applyUserSessionFilters($query, $request);
        
        $sessions = $query->orderBy('created_at', 'desc')->get();
        
        // Generate CSV content
        $csv = "Session ID,User,Email,IP Address,Device,Platform,Browser,Status,Login Time,Last Activity\n";
        
        foreach ($sessions as $session) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $session->session_id,
                $session->user->name,
                $session->user->email,
                $session->ip_address ?? 'N/A',
                $session->device_name ?? 'Unknown',
                $session->platform ?? 'N/A',
                $session->browser ?? 'N/A',
                $session->is_active ? 'Active' : 'Inactive',
                $session->login_at ? $session->login_at->format('Y-m-d H:i:s') : 'N/A',
                $session->last_activity ? $session->last_activity->format('Y-m-d H:i:s') : 'N/A'
            );
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="user-sessions-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Apply filters to user sessions query efficiently.
     */
    private function applyUserSessionFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
    }

    /**
     * Get optimized user session statistics.
     */
    private function getUserSessionStatistics(): array
    {
        try {
            // Use cached statistics for better performance
            return cache()->remember('user_session_statistics', 300, function () {
                $today = today();
                
                return [
                    'total_sessions' => UserSession::count(),
                    'active_sessions' => UserSession::where('is_active', true)->count(),
                    'inactive_sessions' => UserSession::where('is_active', false)->count(),
                    'unique_users' => UserSession::distinct('user_id')->count(),
                    'today_sessions' => UserSession::whereDate('created_at', $today)->count(),
                    'sessions_today' => UserSession::whereDate('created_at', $today)->count(), // Alias for compatibility
                    'unique_users_today' => UserSession::whereDate('created_at', $today)->distinct('user_id')->count(),
                ];
            });
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'total_sessions' => 0,
                'active_sessions' => 0,
                'inactive_sessions' => 0,
                'unique_users' => 0,
                'today_sessions' => 0,
                'sessions_today' => 0,
                'unique_users_today' => 0,
            ];
        }
    }

    /**
     * Display network sessions report.
     */
    public function networkSessions(Request $request)
    {
        // Build optimized query
        $query = NetworkSession::query();

        // Apply filters efficiently
        $this->applyNetworkSessionFilters($query, $request);

        // Get paginated results
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics with caching
        $stats = $this->getNetworkSessionStatistics();

        // Get networks list for filter dropdown (cached for better performance)
        $networks = cache()->remember('network_names_for_filter', 300, function () {
            return NetworkSession::distinct('network_name')->pluck('network_name');
        });

        return view('admin.reports.network-sessions', compact('sessions', 'stats', 'networks'));
    }

    /**
     * Apply filters to network sessions query efficiently.
     */
    private function applyNetworkSessionFilters($query, Request $request): void
    {
        if ($request->filled('network_name')) {
            $query->where('network_name', 'like', '%' . $request->network_name . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    /**
     * Get optimized network session statistics.
     */
    private function getNetworkSessionStatistics(): array
    {
        try {
            // Use cached statistics for better performance
            return cache()->remember('network_session_statistics', 300, function () {
                $now = now();
                $today = today();
                
                return [
                    'total_sessions' => NetworkSession::count(),
                    'active_sessions' => NetworkSession::where('expires_at', '>', $now)->count(),
                    'expired_sessions' => NetworkSession::where('expires_at', '<=', $now)->count(),
                    'sessions_today' => NetworkSession::whereDate('created_at', $today)->count(),
                    'unique_networks' => NetworkSession::distinct('network_name')->count(),
                ];
            });
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'total_sessions' => 0,
                'active_sessions' => 0,
                'expired_sessions' => 0,
                'sessions_today' => 0,
                'unique_networks' => 0,
            ];
        }
    }

    /**
     * Display sync logs report.
     */
    public function syncLogs(Request $request)
    {
        // Build optimized query with specific field selection
        $query = SyncLog::with(['user:id,name,email', 'network:id,display_name']);

        // Apply filters efficiently
        $this->applySyncLogFilters($query, $request);

        // Get paginated results
        $logs = $query->orderBy('started_at', 'desc')->paginate(50);

        // Get statistics with caching
        $stats = $this->getSyncLogStatistics();

        // Get users and networks lists for filter dropdowns (cached for better performance)
        $users = cache()->remember('users_for_sync_logs_filter', 300, function () {
            return User::select('id', 'name', 'email')->orderBy('name')->get();
        });
        
        $networks = cache()->remember('networks_for_sync_logs_filter', 300, function () {
            return Network::select('id', 'display_name')->orderBy('display_name')->get();
        });

        return view('admin.reports.sync-logs', compact('logs', 'stats', 'users', 'networks'));
    }

    /**
     * Apply filters to sync logs query efficiently.
     */
    private function applySyncLogFilters($query, Request $request): void
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('network_id')) {
            $query->where('network_id', $request->network_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sync_type')) {
            $query->where('sync_type', $request->sync_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }
    }

    /**
     * Get optimized sync log statistics.
     */
    private function getSyncLogStatistics(): array
    {
        try {
            // Use cached statistics for better performance
            return cache()->remember('sync_log_statistics', 300, function () {
                $today = today();
                
                return [
                    'total_syncs' => SyncLog::count(),
                    'successful_syncs' => SyncLog::where('status', 'completed')->count(),
                    'failed_syncs' => SyncLog::where('status', 'failed')->count(),
                    'processing_syncs' => SyncLog::where('status', 'processing')->count(),
                    'syncs_today' => SyncLog::whereDate('started_at', $today)->count(),
                    'avg_duration' => SyncLog::where('status', 'completed')->avg('duration_seconds') ?? 0,
                ];
            });
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'total_syncs' => 0,
                'successful_syncs' => 0,
                'failed_syncs' => 0,
                'processing_syncs' => 0,
                'syncs_today' => 0,
                'avg_duration' => 0,
            ];
        }
    }

    /**
     * Display sync statistics.
     */
    public function syncStatistics(Request $request)
    {
        $dateRange = $request->get('date_range', '30'); // Default to last 30 days
        $startDate = Carbon::now()->subDays($dateRange);

        // Overall statistics
        $overallStats = [
            'total_syncs' => SyncLog::where('started_at', '>=', $startDate)->count(),
            'successful_syncs' => SyncLog::where('started_at', '>=', $startDate)->where('status', 'completed')->count(),
            'failed_syncs' => SyncLog::where('started_at', '>=', $startDate)->where('status', 'failed')->count(),
            'success_rate' => 0,
            'avg_duration' => SyncLog::where('started_at', '>=', $startDate)->where('status', 'completed')->avg('duration_seconds'),
            'total_records_synced' => SyncLog::where('started_at', '>=', $startDate)->where('status', 'completed')->sum('records_synced'),
        ];

        if ($overallStats['total_syncs'] > 0) {
            $overallStats['success_rate'] = round(($overallStats['successful_syncs'] / $overallStats['total_syncs']) * 100, 2);
        }

        // Daily sync trends
        $dailyTrends = SyncLog::where('started_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('COUNT(*) as total_syncs'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_syncs'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_syncs'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN duration_seconds END) as avg_duration')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Network performance
        $networkPerformance = SyncLog::where('started_at', '>=', $startDate)
            ->with('network:id,display_name')
            ->select(
                'network_id',
                DB::raw('COUNT(*) as total_syncs'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_syncs'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_syncs'),
                DB::raw('AVG(CASE WHEN status = "completed" THEN duration_seconds END) as avg_duration'),
                DB::raw('SUM(records_synced) as total_records')
            )
            ->groupBy('network_id')
            ->orderByDesc('total_syncs')
            ->get();

        // User activity
        $userActivity = SyncLog::where('started_at', '>=', $startDate)
            ->with('user:id,name,email')
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_syncs'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_syncs'),
                DB::raw('SUM(records_synced) as total_records')
            )
            ->groupBy('user_id')
            ->orderByDesc('total_syncs')
            ->limit(20)
            ->get();

        // Error analysis
        $errorAnalysis = SyncLog::where('started_at', '>=', $startDate)
            ->where('status', 'failed')
            ->whereNotNull('error_message')
            ->select(
                'error_message',
                DB::raw('COUNT(*) as error_count')
            )
            ->groupBy('error_message')
            ->orderByDesc('error_count')
            ->limit(10)
            ->get();

        return view('admin.reports.sync-statistics', compact(
            'overallStats',
            'dailyTrends',
            'networkPerformance',
            'userActivity',
            'errorAnalysis',
            'dateRange'
        ));
    }

    /**
     * Get sync statistics data for charts (AJAX).
     */
    public function getSyncChartData(Request $request)
    {
        $dateRange = $request->get('date_range', '30');
        $startDate = Carbon::now()->subDays($dateRange);

        $data = SyncLog::where('started_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * Get session details for modal display
     */
    public function getSessionDetails($id)
    {
        try {
            $session = UserSession::with(['user:id,name,email'])
                ->findOrFail($id);

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
     * Terminate a user session
     */
    public function terminateSession($id)
    {
        try {
            $session = UserSession::findOrFail($id);
            
            // Terminate the session
            $session->update([
                'is_active' => false,
                'logout_at' => now()
            ]);

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

    /**
     * Advanced Reports Dashboard
     */
    public function advanced(Request $request)
    {
        $title = 'Advanced Reports';
        $subtitle = 'Comprehensive analytics and insights';

        // Get comprehensive statistics
        $stats = [
            'total_reports' => 4,
            'active_reports' => 3,
            'total_data_points' => $this->getTotalDataPoints(),
            'last_updated' => now()->format('Y-m-d H:i:s')
        ];

        // Get report summaries
        $reportSummaries = [
            'user_sessions' => $this->getUserSessionSummary(),
            'network_sessions' => $this->getNetworkSessionSummary(),
            'sync_logs' => $this->getSyncLogSummary(),
            'sync_statistics' => $this->getSyncStatisticsSummary()
        ];

        return view('admin.reports.advanced', compact('title', 'subtitle', 'stats', 'reportSummaries'));
    }

    /**
     * Get total data points across all reports
     */
    private function getTotalDataPoints()
    {
        return Cache::remember('total_data_points', 300, function () {
            return UserSession::count() + 
                   NetworkSession::count() + 
                   SyncLog::count() + 
                   Purchase::count();
        });
    }

    /**
     * Get user session summary
     */
    private function getUserSessionSummary()
    {
        return Cache::remember('user_session_summary', 300, function () {
            return [
                'total_sessions' => UserSession::count(),
                'active_sessions' => UserSession::where('is_active', true)->count(),
                'unique_users' => UserSession::distinct('user_id')->count(),
                'avg_session_duration' => UserSession::whereNotNull('expires_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, login_at, expires_at)) as avg_duration')
                    ->value('avg_duration') ?? 0
            ];
        });
    }

    /**
     * Get network session summary
     */
    private function getNetworkSessionSummary()
    {
        return Cache::remember('network_session_summary', 300, function () {
            return [
                'total_sessions' => NetworkSession::count(),
                'active_sessions' => NetworkSession::where('expires_at', '>', now())->count(),
                'unique_networks' => NetworkSession::distinct('network_name')->count(),
                'avg_session_duration' => NetworkSession::where('expires_at', '>', now())
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, expires_at)) as avg_duration')
                    ->value('avg_duration') ?? 0
            ];
        });
    }

    /**
     * Get sync log summary
     */
    private function getSyncLogSummary()
    {
        return Cache::remember('sync_log_summary', 300, function () {
            return [
                'total_syncs' => SyncLog::count(),
                'successful_syncs' => SyncLog::where('status', 'success')->count(),
                'failed_syncs' => SyncLog::where('status', 'failed')->count(),
                'avg_duration' => SyncLog::where('status', 'success')
                    ->avg('duration_seconds') ?? 0
            ];
        });
    }

    /**
     * Get sync statistics summary
     */
    private function getSyncStatisticsSummary()
    {
        return Cache::remember('sync_statistics_summary', 300, function () {
            return [
                'total_records' => SyncLog::sum('records_synced') ?? 0,
                'total_revenue' => Purchase::sum('revenue') ?? 0,
                'avg_records_per_sync' => SyncLog::where('status', 'success')
                    ->avg('records_synced') ?? 0,
                'success_rate' => SyncLog::count() > 0 ? 
                    (SyncLog::where('status', 'success')->count() / SyncLog::count()) * 100 : 0
            ];
        });
    }
}