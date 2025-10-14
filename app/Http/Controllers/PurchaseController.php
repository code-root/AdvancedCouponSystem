<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getPurchasesData($request);
        }
        
        // For web requests
        $user = Auth::user();
        $networks = $user ? $user->connectedNetworks : collect();
        $campaigns = Campaign::where('user_id', $user ? $user->id : 0)->get();
        $stats = $this->getPurchaseStats();
        
        return view('dashboard.purchases.index', compact('networks', 'campaigns', 'stats'));
    }
    
    /**
     * Get purchases data with filters (AJAX) - DataTables Server-side Processing
     */
    private function getPurchasesData(Request $request)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : 0;
        
        // Base query with optimized relationships
        $query = Purchase::where('user_id', $userId)
            ->with([
                'coupon:id,code,campaign_id',
                'campaign:id,name,logo_url',
                'network:id,display_name'
            ]);
        
        // Apply filters
        $this->applyPurchaseFilters($query, $request);
        
        // Get filtered statistics efficiently
        $filteredStats = $this->getFilteredStats($query);
        
        // DataTables Server-side Processing
        $totalRecords = Purchase::where('user_id', $userId)->count();
        $filteredRecords = $query->count();
        
        // Ordering
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            
            $dateColumn = Schema::hasColumn('purchases', 'order_date') ? 'order_date' : 'created_at';
            $columns = [
                0 => 'order_id',
                1 => 'campaigns.name',
                2 => 'networks.display_name',
                3 => 'purchase_type',
                4 => 'coupons.code',
                5 => 'customer_type',
                6 => 'order_value',
                7 => 'commission',
                8 => $dateColumn,
                9 => 'status'
            ];
            
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $dateColumn = Schema::hasColumn('purchases', 'order_date') ? 'order_date' : 'created_at';
            $query->orderBy($dateColumn, 'desc');
        }
        
        // Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        // Handle -1 length (get all records) by setting a reasonable limit
        if ($length == -1) {
            $length = 1000; // Reduced from 10000 to 1000 for better performance
        }
        
        try {
            $purchases = $query->skip($start)->take($length)->get();
        } catch (\Exception $e) {
            Log::error('Error fetching purchases: ' . $e->getMessage(), [
                'user_id' => $user ? $user->id : 0,
                'request_params' => $request->all()
            ]);
            
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching data'
            ]);
        }
        
        // Format data for DataTables
        $data = $purchases->map(function($purchase) {
            return [
                'DT_RowId' => 'row_' . $purchase->id,
                'order_id' => $purchase->network_order_id ?: $purchase->order_id ?: $purchase->id ?: 'N/A',
                'campaign' => [
                    'name' => $purchase->campaign->name ?? 'N/A',
                    'logo_url' => $purchase->campaign->logo_url ?? '/images/placeholder.png'
                ],
                'network' => $purchase->network->display_name ?? 'N/A',
                'purchase_type' => $purchase->purchase_type ?? 'coupon',
                'coupon_code' => $purchase->coupon->code ?? 'N/A',
                'customer_type' => $purchase->customer_type ?? 'new',
                'order_value' => number_format($purchase->order_value ?? 0, 2, '.', ','),
                'commission' => number_format($purchase->commission ?? 0, 2, '.', ','),
                'order_date' => $purchase->order_date ? $purchase->order_date->format('Y-m-d') : ($purchase->created_at ? $purchase->created_at->format('Y-m-d') : 'N/A'),
                'status' => $purchase->status ?? 'pending',
                'id' => $purchase->id
            ];
        });
        
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
            'stats' => $filteredStats
        ]);
    }
    
    /**
     * Apply filters to purchase query
     */
    private function applyPurchaseFilters($query, Request $request)
    {
        // Filter by network (support multiple)
        // Laravel converts network_ids[] to network_ids array automatically
        $networkIds = $request->input('network_ids', []);
        
        // Ensure it's an array
        if (!is_array($networkIds)) {
            $networkIds = $networkIds ? [$networkIds] : [];
        }
        
        // Clean up: remove empty values
        $networkIds = array_filter($networkIds, function($id) {
            return !empty($id) && $id !== 'null' && $id !== null && $id !== '';
        });
        
        // Reset array keys
        $networkIds = array_values($networkIds);
        
        if (!empty($networkIds)) {
            $query->whereIn('network_id', $networkIds);
        } elseif ($request->has('network_id')) {
            $query->where('network_id', $request->network_id);
        }
        
        // Filter by campaign (support multiple)
        $campaignIds = $request->input('campaign_ids', []);
        
        // Ensure it's an array
        if (!is_array($campaignIds)) {
            $campaignIds = $campaignIds ? [$campaignIds] : [];
        }
        
        // Clean up
        $campaignIds = array_filter($campaignIds, function($id) {
            return !empty($id) && $id !== 'null' && $id !== null && $id !== '';
        });
        
        $campaignIds = array_values($campaignIds);
        
        if (!empty($campaignIds)) {
            $query->whereIn('campaign_id', $campaignIds);
        } elseif ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by customer type
        if ($request->customer_type) {
            $query->where('customer_type', $request->customer_type);
        }
        
        // Filter by purchase type (coupon vs direct link)
        if ($request->purchase_type) {
            $query->where('purchase_type', $request->purchase_type);
        }
        
        // Filter by date range - use order_date if exists, otherwise created_at
        $dateColumn = Schema::hasColumn('purchases', 'order_date') ? 'order_date' : 'created_at';
        
        if ($request->date_from) {
            $query->where($dateColumn, '>=', $request->date_from . ' 00:00:00');
        }
        
        if ($request->date_to) {
            $query->where($dateColumn, '<=', $request->date_to . ' 23:59:59');
        }
        
        // Filter by revenue range
        if ($request->revenue_min) {
            $query->where('revenue', '>=', $request->revenue_min);
        }
        
        if ($request->revenue_max) {
            $query->where('revenue', '<=', $request->revenue_max);
        }
        
        // Search - handle both DataTables search and custom search
        $searchValue = null;
        
        // DataTables search
        if ($request->has('search') && is_array($request->search) && isset($request->search['value'])) {
            $searchValue = $request->search['value'];
        }
        // Custom search
        elseif ($request->has('search_text') && $request->search_text) {
            $searchValue = $request->search_text;
        }
        
        if ($searchValue) {
            $query->where(function($q) use ($searchValue) {
                $q->where('order_id', 'like', "%{$searchValue}%")
                  ->orWhere('network_order_id', 'like', "%{$searchValue}%")
                  ->orWhere('id', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%")
                  ->orWhere('customer_type', 'like', "%{$searchValue}%")
                  ->orWhere('purchase_type', 'like', "%{$searchValue}%")
                  ->orWhereHas('campaign', function($campaignQuery) use ($searchValue) {
                      $campaignQuery->where('name', 'like', "%{$searchValue}%");
                  })
                  ->orWhereHas('network', function($networkQuery) use ($searchValue) {
                      $networkQuery->where('display_name', 'like', "%{$searchValue}%");
                  })
                  ->orWhereHas('coupon', function($couponQuery) use ($searchValue) {
                      $couponQuery->where('code', 'like', "%{$searchValue}%");
                  });
            });
        }
        
        return $query;
    }
    
    /**
     * Get purchase statistics
     */
    private function getPurchaseStats()
    {
        $user = Auth::user();
        $userId = $user ? $user->id : 0;
        
        return [
            'total' => Purchase::where('user_id', $userId)->count(),
            'approved' => Purchase::where('user_id', $userId)->where('status', 'approved')->count(),
            'pending' => Purchase::where('user_id', $userId)->where('status', 'pending')->count(),
            'rejected' => Purchase::where('user_id', $userId)->where('status', 'rejected')->count(),
            'total_revenue' => number_format(Purchase::where('user_id', $userId)->sum('revenue'), 2, '.', ','),
            'total_commission' => number_format(Purchase::where('user_id', $userId)->sum('commission'), 2, '.', ','),
        ];
    }
    
    /**
     * Get filtered statistics efficiently
     */
    private function getFilteredStats($query)
    {
        // Clone the query for stats calculation
        $statsQuery = clone $query;
        
        // Get all stats in one query using aggregation
        $stats = $statsQuery->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid,
            COALESCE(SUM(revenue), 0) as total_revenue,
            COALESCE(SUM(commission), 0) as total_commission,
            COALESCE(SUM(order_value), 0) as total_order_value,
            SUM(CASE WHEN purchase_type = "coupon" THEN 1 ELSE 0 END) as coupon_count,
            SUM(CASE WHEN purchase_type = "link" THEN 1 ELSE 0 END) as link_count,
            SUM(CASE WHEN purchase_type = "coupon" THEN COALESCE(revenue, 0) ELSE 0 END) as coupon_revenue,
            SUM(CASE WHEN purchase_type = "link" THEN COALESCE(revenue, 0) ELSE 0 END) as link_revenue,
            SUM(CASE WHEN purchase_type = "coupon" THEN COALESCE(order_value, 0) ELSE 0 END) as coupon_order_value,
            SUM(CASE WHEN purchase_type = "link" THEN COALESCE(order_value, 0) ELSE 0 END) as link_order_value
        ')->first();
        
        return [
            'total' => $stats->total ?? 0,
            'approved' => $stats->approved ?? 0,
            'pending' => $stats->pending ?? 0,
            'rejected' => $stats->rejected ?? 0,
            'paid' => $stats->paid ?? 0,
            'total_revenue' => number_format($stats->total_revenue ?? 0, 2, '.', ','),
            'total_commission' => number_format($stats->total_commission ?? 0, 2, '.', ','),
            'total_order_value' => number_format($stats->total_order_value ?? 0, 2, '.', ','),
            'purchase_type_breakdown' => [
                'coupon' => [
                    'count' => $stats->coupon_count ?? 0,
                    'revenue' => number_format($stats->coupon_revenue ?? 0, 2, '.', ','),
                    'order_value' => number_format($stats->coupon_order_value ?? 0, 2, '.', ','),
                ],
                'link' => [
                    'count' => $stats->link_count ?? 0,
                    'revenue' => number_format($stats->link_revenue ?? 0, 2, '.', ','),
                    'order_value' => number_format($stats->link_order_value ?? 0, 2, '.', ','),
                ]
            ]
        ];
    }

    /**
     * Show the form for creating a new purchase
     */
    public function create()
    {
        $users = User::all();
        $coupons = Coupon::where('is_active', true)->get();
        return view('dashboard.purchases.create', compact('users', 'coupons'));
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'coupon_id' => ['required', 'exists:coupons,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,completed,cancelled,refunded'],
            'notes' => ['nullable', 'string'],
        ]);

        $coupon = Coupon::find($validated['coupon_id']);
        
        $discount = $coupon->discount_type === 'percentage'
            ? ($validated['amount'] * $coupon->discount_value / 100)
            : $coupon->discount_value;

        $purchase = Purchase::create([
            'user_id' => $validated['user_id'],
            'coupon_id' => $validated['coupon_id'],
            'campaign_id' => $coupon->campaign_id,
            'amount' => $validated['amount'],
            'discount_amount' => $discount,
            'final_amount' => $validated['amount'] - $discount,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($validated['status'] === 'completed') {
            $coupon->increment('times_used');
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully');
    }

    /**
     * Display the specified purchase
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['user', 'coupon', 'campaign']);
        return view('dashboard.purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the purchase
     */
    public function edit(Purchase $purchase)
    {
        $users = User::all();
        $coupons = Coupon::where('is_active', true)->get();
        return view('dashboard.purchases.edit', compact('purchase', 'users', 'coupons'));
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,cancelled,refunded'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldStatus = $purchase->status;
        $purchase->update($validated);

        // Update coupon usage if status changed
        if ($oldStatus !== $validated['status']) {
            if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
                $purchase->coupon->increment('times_used');
            } elseif ($oldStatus === 'completed' && $validated['status'] !== 'completed') {
                $purchase->coupon->decrement('times_used');
            }
        }

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully');
    }

    /**
     * Remove the specified purchase
     */
    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'completed') {
            $purchase->coupon->decrement('times_used');
        }

        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully');
    }

    /**
     * Confirm purchase
     */
    public function confirm(Purchase $purchase)
    {
        if ($purchase->status !== 'completed') {
            $purchase->update(['status' => 'completed']);
            $purchase->coupon->increment('times_used');
        }

        return back()->with('success', 'Purchase confirmed successfully');
    }

    /**
     * Cancel purchase
     */
    public function cancel(Purchase $purchase)
    {
        $oldStatus = $purchase->status;
        $purchase->update(['status' => 'cancelled']);

        if ($oldStatus === 'completed') {
            $purchase->coupon->decrement('times_used');
        }

        return back()->with('success', 'Purchase cancelled successfully');
    }
    
    /**
     * Show statistics page
     */
    public function statisticsPage()
    {
        return view('dashboard.purchases.statistics');
    }

    /**
     * Get purchase statistics (API)
     */
    public function statistics(Request $request)
    {
        $targetUserId = UserHelper::getTargetUserId();
        
        $query = Purchase::where('user_id', $targetUserId);
        
        // Apply filters
        $dateColumn = Schema::hasColumn('purchases', 'order_date') ? 'order_date' : 'created_at';
        if ($request->filled('date_from')) {
            $query->where($dateColumn, '>=', (string) $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where($dateColumn, '<=', (string) $request->date_to . ' 23:59:59');
        }
        
        // Support multiple network IDs
        if ($request->filled('network_ids')) {
            $networkIds = is_array($request->network_ids) ? $request->network_ids : [$request->network_ids];
            if (!empty($networkIds)) {
                $query->whereIn('network_id', $networkIds);
            }
        }
        
        // Support multiple campaign IDs
        if ($request->filled('campaign_ids')) {
            $campaignIds = is_array($request->campaign_ids) ? $request->campaign_ids : [$request->campaign_ids];
            if (!empty($campaignIds)) {
                $query->whereIn('campaign_id', $campaignIds);
            }
        }
        
        // Filter by purchase type (coupon vs direct link)
        if ($request->filled('purchase_type')) {
            $query->where('purchase_type', $request->purchase_type);
        }
        
        $stats = [
            'total_orders' => (clone $query)->count(),
            'approved_purchases' => (clone $query)->where('status', 'approved')->count(),
            'pending_purchases' => (clone $query)->where('status', 'pending')->count(),
            'rejected_purchases' => (clone $query)->where('status', 'rejected')->count(),
            'paid_purchases' => (clone $query)->where('status', 'paid')->count(),
            'total_revenue' => number_format((clone $query)->where('status', 'approved')->sum('revenue'), 2, '.', ','),
            'total_commission' => number_format((clone $query)->where('status', 'approved')->sum('order_value'), 2, '.', ','),
            'total_order_value' => number_format((clone $query)->where('status', 'approved')->sum('order_value'), 2, '.', ','),
            'average_purchase' => number_format((clone $query)->where('status', 'approved')->avg('order_value') ?: 0, 2, '.', ','),
            'average_revenue' => number_format((clone $query)->where('status', 'approved')->avg('revenue') ?: 0, 2, '.', ','),
            'daily_stats' => (clone $query)->selectRaw("DATE($dateColumn) as date, COUNT(*) as count, FORMAT(SUM(order_value), 2) as order_value, FORMAT(SUM(revenue), 2) as revenue, FORMAT(SUM(commission), 2) as commission")
                ->where('status', 'approved')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
            'monthly_stats' => (clone $query)->selectRaw("DATE_FORMAT($dateColumn, '%Y-%m') as month, COUNT(*) as count, FORMAT(SUM(order_value), 2) as order_value, FORMAT(SUM(revenue), 2) as revenue, FORMAT(SUM(commission), 2) as commission")
                ->where('status', 'approved')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
            'purchase_type_breakdown' => [
                'coupon' => [
                    'count' => (clone $query)->where('purchase_type', 'coupon')->where('status', 'approved')->count(),
                    'revenue' => number_format((clone $query)->where('purchase_type', 'coupon')->where('status', 'approved')->sum('revenue'), 2, '.', ','),
                    'order_value' => number_format((clone $query)->where('purchase_type', 'coupon')->where('status', 'approved')->sum('order_value'), 2, '.', ','),
                ],
                'link' => [
                    'count' => (clone $query)->where('purchase_type', 'link')->where('status', 'approved')->count(),
                    'revenue' => number_format((clone $query)->where('purchase_type', 'link')->where('status', 'approved')->sum('revenue'), 2, '.', ','),
                    'order_value' => number_format((clone $query)->where('purchase_type', 'link')->where('status', 'approved')->sum('order_value'), 2, '.', ','),
                ]
            ]
        ];

        return response()->json($stats);
    }
    
    /**
     * Get network comparison statistics
     */
    public function networkComparison()
    {
        $targetUserId = UserHelper::getTargetUserId();
        
        // Get all connected networks for this user
        $connectedNetworks = \App\Models\NetworkConnection::where('user_id', $targetUserId)
            ->where('is_connected', true)
            ->pluck('network_id');
        
        // Get stats for all connected networks (including those with 0 purchases)
        $networkStats = \App\Models\Network::select('networks.id', 'networks.display_name as network_name')
            ->selectRaw('COALESCE(COUNT(purchases.id), 0) as count')
            ->selectRaw('COALESCE(SUM(purchases.order_value), 0) as order_value')
            ->selectRaw('COALESCE(SUM(purchases.revenue), 0) as revenue')
            ->selectRaw('COALESCE(SUM(purchases.commission), 0) as commission')
            ->leftJoin('purchases', function($join) use ($targetUserId) {
                $join->on('networks.id', '=', 'purchases.network_id')
                     ->where('purchases.user_id', '=', $targetUserId)
                     ->where('purchases.status', '=', 'approved');
            })
            ->whereIn('networks.id', $connectedNetworks)
            ->groupBy('networks.id', 'networks.display_name')
            ->orderByRaw('COALESCE(SUM(purchases.revenue), 0) DESC')
            ->get();
        
        return response()->json($networkStats);
    }

    /**
     * Export purchases
     */
    public function export(Request $request)
    {
        $query = Purchase::with(['user', 'coupon', 'campaign']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $purchases = $query->get();

        // Format data for export with proper order_id
        $formattedPurchases = $purchases->map(function($purchase) {
            return [
                'id' => $purchase->id,
                'order_id' => $purchase->network_order_id ?: $purchase->order_id ?: $purchase->id,
                'network_order_id' => $purchase->network_order_id,
                'campaign_name' => $purchase->campaign->name ?? 'N/A',
                'network_name' => $purchase->network->display_name ?? 'N/A',
                'coupon_code' => $purchase->coupon->code ?? 'N/A',
                'purchase_type' => $purchase->purchase_type,
                'customer_type' => $purchase->customer_type,
                'order_value' => $purchase->order_value,
                'commission' => $purchase->commission,
                'revenue' => $purchase->revenue,
                'status' => $purchase->status,
                'order_date' => $purchase->order_date,
                'created_at' => $purchase->created_at,
            ];
        });

        return response()->json($formattedPurchases);
    }
}

