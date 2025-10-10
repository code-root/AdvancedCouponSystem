<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases
     */
    public function index()
    {
        $purchases = Purchase::with(['user', 'coupon', 'campaign'])->latest()->paginate(15);
        return view('dashboard.purchases.index', compact('purchases'));
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

