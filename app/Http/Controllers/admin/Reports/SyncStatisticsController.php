<?php

namespace App\Http\Controllers\admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\SyncLog;
use App\Models\User;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncStatisticsController extends Controller
{
    /**
     * Display sync statistics report.
     */
    public function index()
    {
        try {
            // Get overall statistics
            $stats = $this->getOverallStats();
            
            // Get top users by sync count
            $topUsers = $this->getTopUsers();
            
            // Get sync trends
            $trends = $this->getSyncTrends();
            
            // Get status distribution
            $statusDistribution = $this->getStatusDistribution();
            
            // Get network statistics
            $networkStats = $this->getNetworkStats();
            
            // Get recent activity
            $recentActivity = $this->getRecentActivity();
            
            // Prepare chart data
            $chartData = $this->prepareChartData($trends);
            
            // Update stats with additional fields for the view
            $stats['today_syncs'] = $stats['syncs_today'];
            $stats['today_syncs_growth'] = $this->calculateTodayGrowth();
            $stats['total_records'] = $stats['total_records_synced'];
            $stats['in_progress_syncs'] = $stats['running_syncs'];

            return view('admin.reports.sync-statistics', compact(
                'stats', 
                'topUsers', 
                'trends', 
                'statusDistribution',
                'networkStats',
                'recentActivity',
                'chartData'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load sync statistics: ' . $e->getMessage());
        }
    }

    /**
     * AJAX get overall statistics.
     */
    public function getOverallStatsAjax()
    {
        $stats = $this->getOverallStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * AJAX get top users.
     */
    public function getTopUsersAjax()
    {
        $topUsers = $this->getTopUsers();
        
        return response()->json([
            'success' => true,
            'data' => $topUsers
        ]);
    }

    /**
     * AJAX get sync trends.
     */
    public function getSyncTrendsAjax(Request $request)
    {
        $days = $request->get('days', 30);
        $trends = $this->getSyncTrends($days);
        
        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }

    /**
     * AJAX get status distribution.
     */
    public function getStatusDistributionAjax()
    {
        $statusDistribution = $this->getStatusDistribution();
        
        return response()->json([
            'success' => true,
            'data' => $statusDistribution
        ]);
    }

    /**
     * Get overall statistics.
     */
    private function getOverallStats()
    {
        try {
            // Use cached statistics for better performance
            return cache()->remember('sync_statistics_overall', 300, function () {
                $today = today();
                
                return [
                    'total_syncs' => SyncLog::count(),
                    'successful_syncs' => SyncLog::where('status', 'completed')->count(),
                    'failed_syncs' => SyncLog::where('status', 'failed')->count(),
                    'running_syncs' => SyncLog::where('status', 'processing')->count(), // Fixed: 'processing' instead of 'running'
                    'avg_duration' => round(SyncLog::where('status', 'completed')->avg('duration_seconds') ?? 0, 2),
                    'total_records_synced' => SyncLog::sum('records_synced'), // Fixed: 'records_synced' instead of 'records_processed'
                    'total_campaigns_synced' => SyncLog::sum('campaigns_count'), // Fixed: 'campaigns_count' instead of 'records_created'
                    'total_coupons_synced' => SyncLog::sum('coupons_count'), // Fixed: 'coupons_count' instead of 'records_updated'
                    'total_orders_synced' => SyncLog::sum('orders_count'), // Fixed: 'orders_count' instead of 'records_failed'
                    'success_rate' => $this->calculateSuccessRate(),
                    'syncs_today' => SyncLog::whereDate('started_at', $today)->count(),
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
                'running_syncs' => 0,
                'avg_duration' => 0,
                'total_records_synced' => 0,
                'total_campaigns_synced' => 0,
                'total_coupons_synced' => 0,
                'total_orders_synced' => 0,
                'success_rate' => 0,
                'syncs_today' => 0,
                'syncs_this_week' => 0,
                'syncs_this_month' => 0,
            ];
        }
    }

    /**
     * Get top users by sync count.
     */
    private function getTopUsers()
    {
        try {
            return cache()->remember('sync_statistics_top_users', 300, function () {
                return User::select('id', 'name', 'email')
                    ->withCount('syncLogs')
                    ->orderBy('sync_logs_count', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($user) {
                        $userStats = SyncLog::where('user_id', $user->id)
                            ->selectRaw('
                                COUNT(*) as total_syncs,
                                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_syncs,
                                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_syncs,
                                AVG(CASE WHEN status = "completed" THEN duration_seconds END) as avg_duration,
                                SUM(records_synced) as total_records_synced,
                                SUM(campaigns_count) as total_campaigns,
                                SUM(coupons_count) as total_coupons,
                                SUM(orders_count) as total_orders
                            ')
                            ->first();

                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'total_syncs' => $userStats->total_syncs ?? 0,
                            'successful_syncs' => $userStats->successful_syncs ?? 0,
                            'failed_syncs' => $userStats->failed_syncs ?? 0,
                            'avg_duration' => round($userStats->avg_duration ?? 0, 2),
                            'total_records_synced' => $userStats->total_records_synced ?? 0,
                            'total_campaigns' => $userStats->total_campaigns ?? 0,
                            'total_coupons' => $userStats->total_coupons ?? 0,
                            'total_orders' => $userStats->total_orders ?? 0,
                            'success_rate' => $userStats->total_syncs > 0 ? round(($userStats->successful_syncs / $userStats->total_syncs) * 100, 2) : 0,
                        ];
                    });
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get sync trends.
     */
    private function getSyncTrends($days = 30)
    {
        try {
            $cacheKey = "sync_statistics_trends_{$days}";
            return cache()->remember($cacheKey, 300, function () use ($days) {
                $startDate = now()->subDays($days);

                $data = SyncLog::where('started_at', '>=', $startDate)
                    ->selectRaw('
                        DATE(started_at) as date,
                        COUNT(*) as total_syncs,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_syncs,
                        SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_syncs,
                        SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_syncs,
                        AVG(CASE WHEN status = "completed" THEN duration_seconds END) as avg_duration,
                        SUM(records_synced) as total_records_synced,
                        SUM(campaigns_count) as total_campaigns,
                        SUM(coupons_count) as total_coupons,
                        SUM(orders_count) as total_orders
                    ')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $trends = [];
                for ($i = 0; $i < $days; $i++) {
                    $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                    $dayData = $data->where('date', $date)->first();
                    
                    $trends[] = [
                        'date' => $date,
                        'total_syncs' => $dayData->total_syncs ?? 0,
                        'successful_syncs' => $dayData->successful_syncs ?? 0,
                        'failed_syncs' => $dayData->failed_syncs ?? 0,
                        'processing_syncs' => $dayData->processing_syncs ?? 0,
                        'avg_duration' => round($dayData->avg_duration ?? 0, 2),
                        'total_records_synced' => $dayData->total_records_synced ?? 0,
                        'total_campaigns' => $dayData->total_campaigns ?? 0,
                        'total_coupons' => $dayData->total_coupons ?? 0,
                        'total_orders' => $dayData->total_orders ?? 0,
                        'success_rate' => $dayData->total_syncs > 0 ? round(($dayData->successful_syncs / $dayData->total_syncs) * 100, 2) : 0,
                    ];
                }

                return $trends;
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get status distribution.
     */
    private function getStatusDistribution()
    {
        try {
            return cache()->remember('sync_statistics_status_distribution', 300, function () {
                $totalCount = SyncLog::count();
                if ($totalCount === 0) {
                    return collect([]);
                }

                return SyncLog::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->map(function ($item) use ($totalCount) {
                        return [
                            'status' => ucfirst($item->status),
                            'count' => $item->count,
                            'percentage' => round(($item->count / $totalCount) * 100, 2)
                        ];
                    });
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Calculate success rate.
     */
    private function calculateSuccessRate()
    {
        try {
            $total = SyncLog::count();
            if ($total === 0) return 0;
            
            $successful = SyncLog::where('status', 'completed')->count();
            return round(($successful / $total) * 100, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Export sync statistics.
     */
    public function export()
    {
        try {
            $stats = $this->getOverallStats();
            $topUsers = $this->getTopUsers();
            $trends = $this->getSyncTrends(30);
            $statusDistribution = $this->getStatusDistribution();

            $data = [
                'overall_stats' => $stats,
                'top_users' => $topUsers,
                'trends' => $trends,
                'status_distribution' => $statusDistribution,
                'exported_at' => now()->toISOString(),
                'exported_by' => auth('admin')->user()->name ?? 'System',
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => 'sync_statistics_' . now()->format('Y-m-d_H-i-s') . '.json'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export sync statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get network statistics.
     */
    private function getNetworkStats()
    {
        try {
            return cache()->remember('sync_statistics_network_stats', 300, function () {
                return Network::select('id', 'display_name')
                    ->withCount('syncLogs')
                    ->get()
                    ->map(function ($network) {
                        $networkStats = SyncLog::where('network_id', $network->id)
                            ->selectRaw('
                                COUNT(*) as total_syncs,
                                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_syncs,
                                AVG(CASE WHEN status = "completed" THEN duration_seconds END) as avg_duration,
                                MAX(started_at) as last_sync
                            ')
                            ->first();

                        $successRate = $networkStats->total_syncs > 0 
                            ? round(($networkStats->successful_syncs / $networkStats->total_syncs) * 100, 2) 
                            : 0;

                        return (object) [
                            'id' => $network->id,
                            'display_name' => $network->display_name,
                            'total_syncs' => $networkStats->total_syncs ?? 0,
                            'success_rate' => $successRate,
                            'avg_duration' => $networkStats->avg_duration ? round($networkStats->avg_duration, 2) . 's' : 'N/A',
                            'last_sync' => $networkStats->last_sync ? \Carbon\Carbon::parse($networkStats->last_sync) : null,
                        ];
                    });
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get recent sync activity.
     */
    private function getRecentActivity()
    {
        try {
            return cache()->remember('sync_statistics_recent_activity', 60, function () {
                return SyncLog::with(['user:id,name,email', 'network:id,display_name'])
                    ->select('id', 'user_id', 'network_id', 'sync_type', 'status', 'started_at', 'completed_at', 'duration_seconds', 'records_synced')
                    ->latest('started_at')
                    ->limit(20)
                    ->get()
                    ->map(function ($log) {
                        return (object) [
                            'id' => $log->id,
                            'user' => $log->user,
                            'network' => $log->network,
                            'sync_type' => $log->sync_type,
                            'status' => $log->status,
                            'started_at' => $log->started_at,
                            'completed_at' => $log->completed_at,
                            'duration_seconds' => $log->duration_seconds,
                            'records_processed' => $log->records_synced,
                        ];
                    });
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Prepare chart data from trends.
     */
    private function prepareChartData($trends)
    {
        try {
            $labels = [];
            $successful = [];
            $failed = [];

            foreach ($trends as $trend) {
                $labels[] = \Carbon\Carbon::parse($trend['date'])->format('M d');
                $successful[] = $trend['successful_syncs'];
                $failed[] = $trend['failed_syncs'];
            }

            return [
                'labels' => $labels,
                'successful' => $successful,
                'failed' => $failed,
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'successful' => [],
                'failed' => [],
            ];
        }
    }

    /**
     * Calculate today's growth percentage.
     */
    private function calculateTodayGrowth()
    {
        try {
            $today = SyncLog::whereDate('started_at', today())->count();
            $yesterday = SyncLog::whereDate('started_at', today()->subDay())->count();
            
            if ($yesterday === 0) {
                return $today > 0 ? 100 : 0;
            }
            
            return round((($today - $yesterday) / $yesterday) * 100, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
