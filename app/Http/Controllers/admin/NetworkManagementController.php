<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Network;
use App\Models\NetworkConnection;
use App\Models\NetworkProxy;
use App\Models\Campaign;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NetworkManagementController extends Controller
{
    /**
     * Display a listing of all networks with statistics.
     */
    public function index()
    {
        try {
            $networks = cache()->remember('admin_networks_list', 300, function () {
                return Network::withCount([
                    'connections as connected_users_count' => function ($query) {
                        $query->where('is_connected', true);
                    },
                    'campaigns as campaigns_count',
                ])
                ->withSum('purchases as total_revenue', 'revenue')
                ->withCount('purchases as total_orders')
                ->orderBy('display_name')
                ->get();
            });

            // Get overall statistics
            $stats = $this->getOverallNetworkStatistics();

            return view('admin.networks.index', compact('networks', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load networks: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified network with detailed information.
     */
    public function show($id)
    {
        try {
            $network = Network::with([
                'connections' => function ($query) {
                    $query->with('user:id,name,email')
                          ->where('is_connected', true);
                },
                'campaigns' => function ($query) {
                    $query->with('user:id,name,email')
                          ->withCount('coupons')
                          ->withCount('purchases');
                }
            ])
            ->withSum('purchases as total_revenue', 'revenue')
            ->withCount('purchases as total_orders')
            ->findOrFail($id);

            // Get comprehensive statistics
            $stats = $this->getNetworkStatistics($id);

            // Get recent activity
            $recentPurchases = Purchase::where('network_id', $id)
                ->with(['user:id,name,email', 'campaign:id,name'])
                ->latest('order_date')
                ->limit(10)
                ->get();

            // Get monthly revenue trend
            $monthlyRevenue = Purchase::where('network_id', $id)
                ->select(
                    DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                    DB::raw('SUM(revenue) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();

            return view('admin.networks.show', compact('network', 'stats', 'recentPurchases', 'monthlyRevenue'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load network: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified network.
     */
    public function edit($id)
    {
        try {
            $network = Network::findOrFail($id);
            $stats = $this->getNetworkStatistics($id);
            
            return view('admin.networks.edit', compact('network', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load network for editing: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified network.
     */
    public function update(Request $request, $id)
    {
        try {
            $network = Network::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'website_url' => 'nullable|url|max:255',
                'api_endpoint' => 'nullable|url|max:255',
                'is_active' => 'boolean',
                'commission_rate' => 'nullable|numeric|min:0|max:100',
                'payout_threshold' => 'nullable|numeric|min:0',
                'payout_frequency' => 'nullable|string|in:weekly,monthly,quarterly',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:2000',
            ]);

            $network->update($request->only([
                'name', 'display_name', 'description', 'website_url', 'api_endpoint',
                'is_active', 'commission_rate', 'payout_threshold', 'payout_frequency',
                'contact_email', 'contact_phone', 'notes'
            ]));

            // Clear cache after updating network
            cache()->forget("network_statistics_{$id}");

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Network updated successfully'
                ]);
            }

            return redirect()->route('admin.legacy.networks.show', $id)
                ->with('success', 'Network updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update network: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to update network: ' . $e->getMessage());
        }
    }

    /**
     * Toggle network status (active/inactive).
     */
    public function updateStatus(Request $request, $id)
    {
        $network = Network::findOrFail($id);
        
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $network->update([
            'is_active' => $request->is_active
        ]);

        $status = $request->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Network {$status} successfully.");
    }

    /**
     * Display network proxies management.
     */
    public function proxies()
    {
        $proxies = NetworkProxy::with('network:id,display_name')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $networks = Network::where('is_active', true)
            ->orderBy('display_name')
            ->get();

        return view('admin.networks.proxies', compact('proxies', 'networks'));
    }

    /**
     * Store a new network proxy.
     */
    public function storeProxy(Request $request)
    {
        $request->validate([
            'network_id' => 'required|exists:networks,id',
            'proxy_url' => 'required|url',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        NetworkProxy::create([
            'network_id' => $request->network_id,
            'proxy_url' => $request->proxy_url,
            'username' => $request->username,
            'password' => $request->password,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Proxy added successfully.');
    }

    /**
     * Update network proxy.
     */
    public function updateProxy(Request $request, $id)
    {
        $proxy = NetworkProxy::findOrFail($id);

        $request->validate([
            'network_id' => 'required|exists:networks,id',
            'proxy_url' => 'required|url',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $proxy->update([
            'network_id' => $request->network_id,
            'proxy_url' => $request->proxy_url,
            'username' => $request->username,
            'password' => $request->password,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Proxy updated successfully.');
    }

    /**
     * Delete network proxy.
     */
    public function destroyProxy($id)
    {
        $proxy = NetworkProxy::findOrFail($id);
        $proxy->delete();

        return back()->with('success', 'Proxy deleted successfully.');
    }

    /**
     * Get network statistics for AJAX requests.
     */
    public function getNetworkStats($id)
    {
        $network = Network::findOrFail($id);

        $stats = [
            'connected_users' => $network->connections()->where('is_connected', true)->count(),
            'total_campaigns' => $network->campaigns()->count(),
            'total_revenue' => $network->purchases()->sum('revenue'),
            'total_orders' => $network->purchases()->count(),
            'this_month_revenue' => $network->purchases()
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->sum('revenue'),
            'this_month_orders' => $network->purchases()
                ->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get comprehensive network statistics.
     */
    private function getNetworkStatistics($id)
    {
        try {
            return cache()->remember("network_statistics_{$id}", 300, function () use ($id) {
                $today = today();
                $thisMonth = now()->month;
                $thisYear = now()->year;
                $lastMonth = now()->subMonth();
                
                return [
                    // Basic counts
                    'total_connections' => NetworkConnection::where('network_id', $id)->count(),
                    'active_connections' => NetworkConnection::where('network_id', $id)->where('is_connected', true)->count(),
                    'total_campaigns' => Campaign::where('network_id', $id)->count(),
                    'active_campaigns' => Campaign::where('network_id', $id)->where('status', 'active')->count(),
                    
                    // Revenue statistics
                    'total_revenue' => Purchase::where('network_id', $id)->sum('revenue') ?? 0,
                    'total_orders' => Purchase::where('network_id', $id)->count(),
                    'avg_order_value' => Purchase::where('network_id', $id)->avg('revenue') ?? 0,
                    
                    // Time-based statistics
                    'today_revenue' => Purchase::where('network_id', $id)->whereDate('order_date', $today)->sum('revenue') ?? 0,
                    'today_orders' => Purchase::where('network_id', $id)->whereDate('order_date', $today)->count(),
                    'this_month_revenue' => Purchase::where('network_id', $id)
                        ->whereMonth('order_date', $thisMonth)
                        ->whereYear('order_date', $thisYear)
                        ->sum('revenue') ?? 0,
                    'this_month_orders' => Purchase::where('network_id', $id)
                        ->whereMonth('order_date', $thisMonth)
                        ->whereYear('order_date', $thisYear)
                        ->count(),
                    'last_month_revenue' => Purchase::where('network_id', $id)
                        ->whereMonth('order_date', $lastMonth->month)
                        ->whereYear('order_date', $lastMonth->year)
                        ->sum('revenue') ?? 0,
                    'last_month_orders' => Purchase::where('network_id', $id)
                        ->whereMonth('order_date', $lastMonth->month)
                        ->whereYear('order_date', $lastMonth->year)
                        ->count(),
                    'this_year_revenue' => Purchase::where('network_id', $id)
                        ->whereYear('order_date', $thisYear)
                        ->sum('revenue') ?? 0,
                    'this_year_orders' => Purchase::where('network_id', $id)
                        ->whereYear('order_date', $thisYear)
                        ->count(),
                    
                    // Growth calculations
                    'monthly_growth' => $this->calculateGrowth(
                        Purchase::where('network_id', $id)
                            ->whereMonth('order_date', $lastMonth->month)
                            ->whereYear('order_date', $lastMonth->year)
                            ->sum('revenue') ?? 0,
                        Purchase::where('network_id', $id)
                            ->whereMonth('order_date', $thisMonth)
                            ->whereYear('order_date', $thisYear)
                            ->sum('revenue') ?? 0
                    ),
                    'yearly_growth' => $this->calculateGrowth(
                        Purchase::where('network_id', $id)
                            ->whereYear('order_date', $thisYear - 1)
                            ->sum('revenue') ?? 0,
                        Purchase::where('network_id', $id)
                            ->whereYear('order_date', $thisYear)
                            ->sum('revenue') ?? 0
                    ),
                    
                    // Top performers
                    'top_users' => Purchase::where('network_id', $id)
                        ->select('user_id', DB::raw('SUM(revenue) as total_revenue'), DB::raw('COUNT(*) as total_orders'))
                        ->with('user:id,name,email')
                        ->groupBy('user_id')
                        ->orderBy('total_revenue', 'desc')
                        ->limit(5)
                        ->get(),
                    'top_campaigns' => Purchase::where('network_id', $id)
                        ->select('campaign_id', DB::raw('SUM(revenue) as total_revenue'), DB::raw('COUNT(*) as total_orders'))
                        ->with('campaign:id,name')
                        ->groupBy('campaign_id')
                        ->orderBy('total_revenue', 'desc')
                        ->limit(5)
                        ->get(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'total_connections' => 0,
                'active_connections' => 0,
                'total_campaigns' => 0,
                'active_campaigns' => 0,
                'total_revenue' => 0,
                'total_orders' => 0,
                'avg_order_value' => 0,
                'today_revenue' => 0,
                'today_orders' => 0,
                'this_month_revenue' => 0,
                'this_month_orders' => 0,
                'last_month_revenue' => 0,
                'last_month_orders' => 0,
                'this_year_revenue' => 0,
                'this_year_orders' => 0,
                'monthly_growth' => 0,
                'yearly_growth' => 0,
                'top_users' => collect(),
                'top_campaigns' => collect(),
            ];
        }
    }

    /**
     * Calculate growth percentage.
     */
    private function calculateGrowth($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $oldValue) / $oldValue) * 100, 2);
    }

    /**
     * Get overall network statistics.
     */
    private function getOverallNetworkStatistics()
    {
        try {
            return cache()->remember('overall_network_statistics', 300, function () {
                $today = today();
                $thisMonth = now()->month;
                $thisYear = now()->year;
                
                return [
                    'total_networks' => Network::count(),
                    'active_networks' => Network::where('is_active', true)->count(),
                    'inactive_networks' => Network::where('is_active', false)->count(),
                    'total_connections' => NetworkConnection::count(),
                    'active_connections' => NetworkConnection::where('is_connected', true)->count(),
                    'total_campaigns' => Campaign::count(),
                    'active_campaigns' => Campaign::where('status', 'active')->count(),
                    'total_revenue' => Purchase::sum('revenue') ?? 0,
                    'total_orders' => Purchase::count(),
                    'today_revenue' => Purchase::whereDate('order_date', $today)->sum('revenue') ?? 0,
                    'today_orders' => Purchase::whereDate('order_date', $today)->count(),
                    'this_month_revenue' => Purchase::whereMonth('order_date', $thisMonth)
                        ->whereYear('order_date', $thisYear)
                        ->sum('revenue') ?? 0,
                    'this_month_orders' => Purchase::whereMonth('order_date', $thisMonth)
                        ->whereYear('order_date', $thisYear)
                        ->count(),
                    'this_year_revenue' => Purchase::whereYear('order_date', $thisYear)->sum('revenue') ?? 0,
                    'this_year_orders' => Purchase::whereYear('order_date', $thisYear)->count(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'total_networks' => 0,
                'active_networks' => 0,
                'inactive_networks' => 0,
                'total_connections' => 0,
                'active_connections' => 0,
                'total_campaigns' => 0,
                'active_campaigns' => 0,
                'total_revenue' => 0,
                'total_orders' => 0,
                'today_revenue' => 0,
                'today_orders' => 0,
                'this_month_revenue' => 0,
                'this_month_orders' => 0,
                'this_year_revenue' => 0,
                'this_year_orders' => 0,
            ];
        }
    }
}