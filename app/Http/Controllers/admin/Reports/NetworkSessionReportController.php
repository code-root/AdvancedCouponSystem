<?php

namespace App\Http\Controllers\admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\NetworkSession;
use App\Models\Network;
use Illuminate\Http\Request;

class NetworkSessionReportController extends Controller
{
    /**
     * Display network sessions report.
     */
    public function index(Request $request)
    {
        // Build optimized query with specific field selection
        $query = NetworkSession::select('id', 'network_name', 'session_key', 'expires_at', 'created_at', 'updated_at');

        // Apply filters efficiently
        $this->applyNetworkSessionFilters($query, $request);

        // Get paginated results
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get statistics with optimized queries
        $stats = $this->getNetworkSessionStatistics();

        // Get networks for filter dropdown with error handling
        try {
            $networks = Network::where('is_active', true)
                ->orderBy('display_name')
                ->get(['id', 'display_name', 'name']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to load networks for network-sessions report: ' . $e->getMessage());
            $networks = collect([]); // Empty collection as fallback
        }

        return view('admin.reports.network-sessions', compact('sessions', 'stats', 'networks'));
    }

    /**
     * Apply filters to network sessions query efficiently.
     */
    private function applyNetworkSessionFilters($query, Request $request): void
    {
        if ($request->filled('network_id')) {
            // Get network name from network_id
            $network = Network::find($request->network_id);
            if ($network) {
                $query->where('network_name', $network->name);
            }
        }

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
            return cache()->remember('network_session_report_statistics', 300, function () {
                $now = now();
                $today = today();
                
                return [
                    'total_sessions' => NetworkSession::count(),
                    'active_sessions' => NetworkSession::where('expires_at', '>', $now)->count(),
                    'expired_sessions' => NetworkSession::where('expires_at', '<=', $now)->count(),
                    'sessions_today' => NetworkSession::whereDate('created_at', $today)->count(),
                    'connected_networks' => NetworkSession::distinct('network_name')->count(),
                    'unique_networks' => NetworkSession::distinct('network_name')->count(), // Alias for compatibility
                    'sessions_this_week' => NetworkSession::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'sessions_this_month' => NetworkSession::whereMonth('created_at', now()->month)->count(),
                ];
            });
        } catch (\Exception $e) {
            // Return default values if there's an error
            return [
                'total_sessions' => 0,
                'active_sessions' => 0,
                'expired_sessions' => 0,
                'sessions_today' => 0,
                'connected_networks' => 0,
                'unique_networks' => 0,
                'sessions_this_week' => 0,
                'sessions_this_month' => 0,
            ];
        }
    }

    /**
     * AJAX get network sessions data.
     */
    public function getDataAjax(Request $request)
    {
        // Build optimized query with specific field selection
        $query = NetworkSession::select('id', 'network_name', 'session_key', 'expires_at', 'created_at', 'updated_at');

        // Apply filters efficiently
        $this->applyNetworkSessionFilters($query, $request);

        // Get paginated results
        $sessions = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Get network session statistics.
     */
    public function getStatsAjax()
    {
        // Use the same optimized method as index()
        $stats = $this->getNetworkSessionStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export network sessions.
     */
    public function export(Request $request)
    {
        // Build optimized query with specific field selection
        $query = NetworkSession::select('id', 'network_name', 'session_key', 'expires_at', 'created_at', 'updated_at');

        // Apply filters efficiently
        $this->applyNetworkSessionFilters($query, $request);

        // Get all results for export
        $sessions = $query->orderBy('created_at', 'desc')->get();

        // Transform data efficiently
        $data = $sessions->map(function ($session) {
            return [
                'Network Name' => $session->network_name,
                'Session ID' => $session->session_key,
                'Status' => $session->expires_at > now() ? 'Active' : 'Expired',
                'Created At' => $session->created_at->format('Y-m-d H:i:s'),
                'Expires At' => $session->expires_at->format('Y-m-d H:i:s'),
                'Last Updated' => $session->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => 'network_sessions_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }
}
