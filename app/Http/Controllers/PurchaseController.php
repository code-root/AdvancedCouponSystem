<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Log;

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
        $networks = auth()->user()->connectedNetworks;
        $campaigns = Campaign::where('user_id', auth()->id())->get();
        $stats = $this->getPurchaseStats();
        
        return view('dashboard.purchases.index', compact('networks', 'campaigns', 'stats'));
    }
    
    /**
     * Get purchases data with filters (AJAX)
     */
    private function getPurchasesData(Request $request)
    {
        $query = Purchase::where('user_id', auth()->id())
            ->with(['coupon', 'campaign', 'network']);
        
        // Apply filters
        $this->applyPurchaseFilters($query, $request);
        
        // Clone query for stats (before pagination)
        $statsQuery = clone $query;
        
        // Get filtered statistics
        $filteredStats = [
            'total' => $statsQuery->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
            'total_revenue' => (clone $statsQuery)->sum('revenue'),
            'total_commission' => (clone $statsQuery)->sum('commission'),
            'total_order_value' => (clone $statsQuery)->sum('order_value'),
        ];
        
        $purchases = $query->latest('order_date')->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => $purchases,
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
        
        Log::info('Network Filter', [
            'raw_input' => $request->all(),
            'network_ids' => $networkIds,
            'count' => count($networkIds)
        ]);
        
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
        
        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        
        // Filter by revenue range
        if ($request->revenue_min) {
            $query->where('revenue', '>=', $request->revenue_min);
        }
        
        if ($request->revenue_max) {
            $query->where('revenue', '<=', $request->revenue_max);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_id', 'like', '%' . $request->search . '%')
                  ->orWhere('network_order_id', 'like', '%' . $request->search . '%');
            });
        }
        
        return $query;
    }
    
    /**
     * Get purchase statistics
     */
    private function getPurchaseStats()
    {
        $userId = auth()->id();
        
        return [
            'total' => Purchase::where('user_id', $userId)->count(),
            'approved' => Purchase::where('user_id', $userId)->where('status', 'approved')->count(),
            'pending' => Purchase::where('user_id', $userId)->where('status', 'pending')->count(),
            'rejected' => Purchase::where('user_id', $userId)->where('status', 'rejected')->count(),
            'total_revenue' => Purchase::where('user_id', $userId)->sum('revenue'),
            'total_commission' => Purchase::where('user_id', $userId)->sum('commission'),
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
        if ($request->filled('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
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
        
        $stats = [
            'total_purchases' => (clone $query)->count(),
            'approved_purchases' => (clone $query)->where('status', 'approved')->count(),
            'pending_purchases' => (clone $query)->where('status', 'pending')->count(),
            'rejected_purchases' => (clone $query)->where('status', 'rejected')->count(),
            'paid_purchases' => (clone $query)->where('status', 'paid')->count(),
            'total_revenue' => (clone $query)->where('status', 'approved')->sum('revenue'),
            'total_commission' => (clone $query)->where('status', 'approved')->sum('commission'),
            'total_order_value' => (clone $query)->where('status', 'approved')->sum('order_value'),
            'average_purchase' => (clone $query)->where('status', 'approved')->avg('order_value') ?: 0,
            'average_revenue' => (clone $query)->where('status', 'approved')->avg('revenue') ?: 0,
            'daily_stats' => (clone $query)->selectRaw('DATE(order_date) as date, COUNT(*) as count, SUM(order_value) as order_value, SUM(revenue) as revenue, SUM(commission) as commission')
                ->where('status', 'approved')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
            'monthly_stats' => (clone $query)->selectRaw('DATE_FORMAT(order_date, "%Y-%m") as month, COUNT(*) as count, SUM(order_value) as order_value, SUM(revenue) as revenue, SUM(commission) as commission')
                ->where('status', 'approved')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
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

        // Implementation depends on export format (CSV, Excel, etc.)
        return response()->json($purchases);
    }
}

