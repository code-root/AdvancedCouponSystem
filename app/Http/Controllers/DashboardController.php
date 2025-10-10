<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerConnection;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\BrokerData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard overview data.
     */
    public function overview(Request $request)
    {
        $user = Auth::user();
        
        // Get date range (default to current month)
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get connected brokers
        $connectedBrokers = BrokerConnection::with('broker')
            ->where('user_id', $user->id)
            ->where('is_connected', true)
            ->get();

        // Get campaigns count
        $campaignsCount = Campaign::where('user_id', $user->id)->count();

        // Get coupons count
        $couponsCount = Coupon::whereHas('campaign', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Get purchases statistics
        $purchasesStats = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(quantity) as total_quantity,
                SUM(order_value) as total_order_value,
                SUM(commission) as total_commission,
                SUM(revenue) as total_revenue
            ')
            ->first();

        // Get broker performance
        $brokerPerformance = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->with('broker')
            ->selectRaw('
                broker_id,
                COUNT(*) as orders_count,
                SUM(quantity) as total_quantity,
                SUM(order_value) as total_order_value,
                SUM(commission) as total_commission,
                SUM(revenue) as total_revenue
            ')
            ->groupBy('broker_id')
            ->get();

        // Get recent purchases
        $recentPurchases = Purchase::with(['broker', 'campaign', 'coupon'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get monthly revenue chart data
        $monthlyRevenue = Purchase::where('user_id', $user->id)
            ->selectRaw('
                DATE_FORMAT(order_date, "%Y-%m") as month,
                SUM(revenue) as revenue,
                SUM(commission) as commission,
                COUNT(*) as orders_count
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'connected_brokers' => $connectedBrokers->count(),
                    'campaigns_count' => $campaignsCount,
                    'coupons_count' => $couponsCount,
                    'total_orders' => $purchasesStats->total_orders ?? 0,
                    'total_quantity' => $purchasesStats->total_quantity ?? 0,
                    'total_order_value' => $purchasesStats->total_order_value ?? 0,
                    'total_commission' => $purchasesStats->total_commission ?? 0,
                    'total_revenue' => $purchasesStats->total_revenue ?? 0,
                ],
                'connected_brokers' => $connectedBrokers,
                'broker_performance' => $brokerPerformance,
                'recent_purchases' => $recentPurchases,
                'monthly_revenue' => $monthlyRevenue,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Get broker data overview.
     */
    public function brokerData(Request $request, Broker $broker)
    {
        $user = Auth::user();
        
        // Check if user has connection to this broker
        $connection = BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->where('is_connected', true)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found for this broker',
            ], 404);
        }

        // Get date range
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get campaigns for this broker
        $campaigns = Campaign::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->with(['coupons', 'purchases'])
            ->get();

        // Get purchases statistics
        $purchasesStats = Purchase::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(quantity) as total_quantity,
                SUM(order_value) as total_order_value,
                SUM(commission) as total_commission,
                SUM(revenue) as total_revenue,
                AVG(commission) as avg_commission,
                AVG(revenue) as avg_revenue
            ')
            ->first();

        // Get top performing campaigns
        $topCampaigns = Purchase::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->with('campaign')
            ->selectRaw('
                campaign_id,
                COUNT(*) as orders_count,
                SUM(quantity) as total_quantity,
                SUM(order_value) as total_order_value,
                SUM(commission) as total_commission,
                SUM(revenue) as total_revenue
            ')
            ->groupBy('campaign_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Get daily revenue chart data
        $dailyRevenue = Purchase::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                order_date,
                SUM(revenue) as revenue,
                SUM(commission) as commission,
                COUNT(*) as orders_count
            ')
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        // Get country performance
        $countryPerformance = Purchase::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                country_code,
                COUNT(*) as orders_count,
                SUM(revenue) as total_revenue,
                SUM(commission) as total_commission
            ')
            ->groupBy('country_code')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'broker' => $broker,
                'connection' => $connection,
                'campaigns' => $campaigns,
                'statistics' => [
                    'total_orders' => $purchasesStats->total_orders ?? 0,
                    'total_quantity' => $purchasesStats->total_quantity ?? 0,
                    'total_order_value' => $purchasesStats->total_order_value ?? 0,
                    'total_commission' => $purchasesStats->total_commission ?? 0,
                    'total_revenue' => $purchasesStats->total_revenue ?? 0,
                    'avg_commission' => $purchasesStats->avg_commission ?? 0,
                    'avg_revenue' => $purchasesStats->avg_revenue ?? 0,
                ],
                'top_campaigns' => $topCampaigns,
                'daily_revenue' => $dailyRevenue,
                'country_performance' => $countryPerformance,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Get campaigns overview.
     */
    public function campaigns(Request $request)
    {
        $user = Auth::user();
        
        // Get date range
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get campaigns with statistics
        $campaigns = Campaign::where('user_id', $user->id)
            ->with(['broker', 'coupons', 'purchases' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($campaign) {
                $purchases = $campaign->purchases;
                
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'broker' => $campaign->broker,
                    'status' => $campaign->status,
                    'campaign_type' => $campaign->campaign_type,
                    'coupons_count' => $campaign->coupons->count(),
                    'statistics' => [
                        'total_orders' => $purchases->count(),
                        'total_quantity' => $purchases->sum('quantity'),
                        'total_order_value' => $purchases->sum('order_value'),
                        'total_commission' => $purchases->sum('commission'),
                        'total_revenue' => $purchases->sum('revenue'),
                    ],
                    'created_at' => $campaign->created_at,
                    'updated_at' => $campaign->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'campaigns' => $campaigns,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Get purchases overview.
     */
    public function purchases(Request $request)
    {
        $user = Auth::user();
        
        // Get date range
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get pagination parameters
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        // Get purchases with relationships
        $purchases = Purchase::with(['broker', 'campaign', 'coupon', 'country'])
            ->where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Get summary statistics
        $summary = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(quantity) as total_quantity,
                SUM(order_value) as total_order_value,
                SUM(commission) as total_commission,
                SUM(revenue) as total_revenue,
                AVG(commission) as avg_commission,
                AVG(revenue) as avg_revenue
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'purchases' => $purchases,
                'summary' => [
                    'total_orders' => $summary->total_orders ?? 0,
                    'total_quantity' => $summary->total_quantity ?? 0,
                    'total_order_value' => $summary->total_order_value ?? 0,
                    'total_commission' => $summary->total_commission ?? 0,
                    'total_revenue' => $summary->total_revenue ?? 0,
                    'avg_commission' => $summary->avg_commission ?? 0,
                    'avg_revenue' => $summary->avg_revenue ?? 0,
                ],
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Get analytics data.
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        // Get date range
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get revenue trend (daily)
        $revenueTrend = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                order_date,
                SUM(revenue) as revenue,
                SUM(commission) as commission,
                COUNT(*) as orders_count
            ')
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        // Get broker performance
        $brokerPerformance = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->with('broker')
            ->selectRaw('
                broker_id,
                COUNT(*) as orders_count,
                SUM(revenue) as total_revenue,
                SUM(commission) as total_commission,
                AVG(revenue) as avg_revenue
            ')
            ->groupBy('broker_id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Get country performance
        $countryPerformance = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->selectRaw('
                country_code,
                COUNT(*) as orders_count,
                SUM(revenue) as total_revenue,
                SUM(commission) as total_commission
            ')
            ->groupBy('country_code')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Get campaign performance
        $campaignPerformance = Purchase::where('user_id', $user->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->with('campaign')
            ->selectRaw('
                campaign_id,
                COUNT(*) as orders_count,
                SUM(revenue) as total_revenue,
                SUM(commission) as total_commission
            ')
            ->groupBy('campaign_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'revenue_trend' => $revenueTrend,
                'broker_performance' => $brokerPerformance,
                'country_performance' => $countryPerformance,
                'campaign_performance' => $campaignPerformance,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Show user profile page.
     */
    public function profile()
    {
        return view('dashboard.profile.index');
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'bio' => ['nullable', 'string', 'max:500'],
            'email_notifications' => ['boolean']
        ]);

        $user->update($request->only([
            'name', 'email', 'phone', 'country_id', 'bio', 'email_notifications'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!'
        ]);
    }

    /**
     * Show change password page.
     */
    public function changePassword()
    {
        return view('dashboard.profile.change-password');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()]
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
    }

    /**
     * Show broker data page.
     */
    public function brokerDataView($broker)
    {
        $brokerModel = Broker::where('name', $broker)->first();
        
        if (!$brokerModel) {
            abort(404);
        }

        $connection = BrokerConnection::where('user_id', Auth::id())
            ->where('broker_id', $brokerModel->id)
            ->first();

        $brokerData = [
            'api_key' => $connection ? $connection->connection_details['api_key'] ?? '' : '',
            'api_secret' => $connection ? $connection->connection_details['api_secret'] ?? '' : '',
            'access_token' => $connection ? $connection->connection_details['access_token'] ?? '' : '',
            'refresh_token' => $connection ? $connection->connection_details['refresh_token'] ?? '' : '',
            'api_url' => $brokerModel->url_api ?? '',
            'last_sync' => $connection ? $connection->updated_at->diffForHumans() : 'Never',
            'campaigns_count' => Campaign::where('broker_id', $brokerModel->id)->count(),
            'total_revenue' => Purchase::where('broker_id', $brokerModel->id)->sum('revenue')
        ];

        return view('dashboard.brokers.show', compact('broker', 'brokerData'));
    }

    /**
     * Show revenue report page.
     */
    public function revenueReport()
    {
        return view('dashboard.reports.revenue');
    }

    /**
     * Show commissions report page.
     */
    public function commissionsReport()
    {
        return view('dashboard.reports.commissions');
    }

    /**
     * Show performance report page.
     */
    public function performanceReport()
    {
        return view('dashboard.reports.performance');
    }

    /**
     * Show notifications settings page.
     */
    public function notifications()
    {
        return view('dashboard.settings.notifications');
    }

    /**
     * Show preferences settings page.
     */
    public function preferences()
    {
        return view('dashboard.settings.preferences');
    }
}
