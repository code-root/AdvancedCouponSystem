<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\NetworkSession;
use App\Models\SyncLog;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display user sessions report.
     */
    public function userSessions(Request $request)
    {
        $query = UserSession::with('user:id,name,email');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
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

        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics
        $stats = [
            'total_sessions' => UserSession::count(),
            'active_sessions' => UserSession::where('is_active', true)->count(),
            'inactive_sessions' => UserSession::where('is_active', false)->count(),
            'sessions_today' => UserSession::whereDate('created_at', today())->count(),
            'unique_users_today' => UserSession::whereDate('created_at', today())->distinct('user_id')->count(),
        ];

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.reports.user-sessions', compact('sessions', 'stats', 'users'));
    }

    /**
     * Display network sessions report.
     */
    public function networkSessions(Request $request)
    {
        $query = NetworkSession::query();

        // Apply filters
        if ($request->filled('network_name')) {
            $query->where('network_name', 'like', '%' . $request->network_name . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics
        $stats = [
            'total_sessions' => NetworkSession::count(),
            'active_sessions' => NetworkSession::where('expires_at', '>', now())->count(),
            'expired_sessions' => NetworkSession::where('expires_at', '<=', now())->count(),
            'sessions_today' => NetworkSession::whereDate('created_at', today())->count(),
            'unique_networks' => NetworkSession::distinct('network_name')->count(),
        ];

        $networks = NetworkSession::distinct('network_name')->pluck('network_name');

        return view('admin.reports.network-sessions', compact('sessions', 'stats', 'networks'));
    }

    /**
     * Display sync logs report.
     */
    public function syncLogs(Request $request)
    {
        $query = SyncLog::with(['user:id,name,email', 'network:id,display_name']);

        // Apply filters
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

        $logs = $query->orderBy('started_at', 'desc')->paginate(50);

        // Get statistics
        $stats = [
            'total_syncs' => SyncLog::count(),
            'successful_syncs' => SyncLog::where('status', 'completed')->count(),
            'failed_syncs' => SyncLog::where('status', 'failed')->count(),
            'processing_syncs' => SyncLog::where('status', 'processing')->count(),
            'syncs_today' => SyncLog::whereDate('started_at', today())->count(),
            'avg_duration' => SyncLog::where('status', 'completed')->avg('duration_seconds'),
        ];

        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $networks = Network::select('id', 'display_name')->orderBy('display_name')->get();

        return view('admin.reports.sync-logs', compact('logs', 'stats', 'users', 'networks'));
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
}