<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Campaign;

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
        
        // Filter by network
        if ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        
        // Filter by campaign
        if ($request->campaign_id) {
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
        
        $purchases = $query->latest('order_date')->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => $purchases
        ]);
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
            'total_revenue' => Purchase::where('user_id', $userId)->where('status', 'approved')->sum('revenue'),
            'total_commission' => Purchase::where('user_id', $userId)->where('status', 'approved')->sum('commission'),
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
     * Get purchase statistics
     */
    public function statistics()
    {
        $stats = [
            'total_purchases' => Purchase::count(),
            'completed_purchases' => Purchase::where('status', 'completed')->count(),
            'pending_purchases' => Purchase::where('status', 'pending')->count(),
            'cancelled_purchases' => Purchase::where('status', 'cancelled')->count(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('final_amount'),
            'total_discounts' => Purchase::where('status', 'completed')->sum('discount_amount'),
            'average_purchase' => Purchase::where('status', 'completed')->avg('final_amount'),
            'daily_stats' => Purchase::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(final_amount) as revenue')
                ->where('status', 'completed')
                ->groupBy('date')
                ->latest('date')
                ->limit(30)
                ->get(),
            'monthly_stats' => Purchase::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(final_amount) as revenue')
                ->where('status', 'completed')
                ->groupBy('month')
                ->latest('month')
                ->limit(12)
                ->get(),
        ];

        return response()->json($stats);
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

