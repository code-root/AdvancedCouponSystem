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
        $networks = Network::withCount([
            'connections as connected_users_count' => function ($query) {
                $query->where('is_connected', true);
            },
            'campaigns as campaigns_count',
        ])
        ->withSum('purchases as total_revenue', 'revenue')
        ->withCount('purchases as total_orders')
        ->orderBy('display_name')
        ->get();

        return view('admin.networks.index', compact('networks'));
    }

    /**
     * Display the specified network with detailed information.
     */
    public function show($id)
    {
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

        return view('admin.networks.show', compact('network', 'recentPurchases', 'monthlyRevenue'));
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
}