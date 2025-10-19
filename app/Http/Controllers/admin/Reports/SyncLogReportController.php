<?php

namespace App\Http\Controllers\admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use App\Models\User;
use App\Models\Network;
use Illuminate\Http\Request;

class SyncLogReportController extends Controller
{
    /**
     * Display sync logs report.
     */
    public function index(Request $request)
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
            return cache()->remember('sync_log_report_statistics', 300, function () {
                $today = today();
                
                return [
                    'total_syncs' => SyncLog::count(),
                    'successful_syncs' => SyncLog::where('status', 'completed')->count(),
                    'failed_syncs' => SyncLog::where('status', 'failed')->count(),
                    'in_progress_syncs' => SyncLog::where('status', 'running')->count(),
                    'syncs_today' => SyncLog::whereDate('started_at', $today)->count(),
                    'avg_duration' => SyncLog::where('status', 'completed')->avg('duration_seconds') ?? 0,
                    'syncs_this_week' => SyncLog::whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'syncs_this_month' => SyncLog::whereMonth('started_at', now()->month)->count(),
                ];
            });
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'total_syncs' => 0,
                'successful_syncs' => 0,
                'failed_syncs' => 0,
                'in_progress_syncs' => 0,
                'syncs_today' => 0,
                'avg_duration' => 0,
                'syncs_this_week' => 0,
                'syncs_this_month' => 0,
            ];
        }
    }

    /**
     * AJAX get sync logs data.
     */
    public function getDataAjax(Request $request)
    {
        // Build optimized query with specific field selection
        $query = SyncLog::with(['user:id,name,email', 'network:id,display_name']);

        // Apply filters efficiently
        $this->applySyncLogFilters($query, $request);

        // Get paginated results
        $logs = $query->orderBy('started_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get sync log statistics.
     */
    public function getStatsAjax()
    {
        // Use the same optimized method as index()
        $stats = $this->getSyncLogStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get sync chart data.
     */
    public function getChartDataAjax(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $data = SyncLog::where('started_at', '>=', $startDate)
            ->selectRaw('DATE(started_at) as date, COUNT(*) as count, status')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $chartData = [];
        $dates = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dates[] = $date;
            $chartData[$date] = [
                'completed' => 0,
                'failed' => 0,
                'running' => 0,
            ];
        }

        foreach ($data as $item) {
            if (isset($chartData[$item->date])) {
                $chartData[$item->date][$item->status] = $item->count;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $dates,
                'datasets' => [
                    [
                        'label' => 'Completed',
                        'data' => array_values(array_column($chartData, 'completed')),
                        'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                        'borderColor' => 'rgba(34, 197, 94, 1)',
                    ],
                    [
                        'label' => 'Failed',
                        'data' => array_values(array_column($chartData, 'failed')),
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'borderColor' => 'rgba(239, 68, 68, 1)',
                    ],
                    [
                        'label' => 'Running',
                        'data' => array_values(array_column($chartData, 'running')),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                    ],
                ]
            ]
        ]);
    }

    /**
     * Export sync logs.
     */
    public function export(Request $request)
    {
        // Build optimized query with specific field selection
        $query = SyncLog::with(['user:id,name,email', 'network:id,display_name']);

        // Apply filters efficiently
        $this->applySyncLogFilters($query, $request);

        // Get all results for export
        $logs = $query->orderBy('started_at', 'desc')->get();

        // Transform data efficiently
        $data = $logs->map(function ($log) {
            return [
                'User' => $log->user->name ?? 'N/A',
                'Email' => $log->user->email ?? 'N/A',
                'Network' => $log->network->display_name ?? 'N/A',
                'Status' => ucfirst($log->status),
                'Started At' => $log->started_at->format('Y-m-d H:i:s'),
                'Completed At' => $log->completed_at ? $log->completed_at->format('Y-m-d H:i:s') : 'N/A',
                'Duration (seconds)' => $log->duration_seconds ?? 'N/A',
                'Records Processed' => $log->records_processed ?? 0,
                'Records Created' => $log->records_created ?? 0,
                'Records Updated' => $log->records_updated ?? 0,
                'Records Failed' => $log->records_failed ?? 0,
                'Error Message' => $log->error_message ?? 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => 'sync_logs_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }
}
