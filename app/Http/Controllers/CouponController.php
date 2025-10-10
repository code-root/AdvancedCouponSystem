<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons
     */
    public function index(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getCouponsData($request);
        }
        
        // For web requests
        $networks = auth()->user()->connectedNetworks;
        $campaigns = Campaign::where('user_id', auth()->id())->get();
        $stats = $this->getCouponStats();
        
        return view('dashboard.coupons.index', compact('networks', 'campaigns', 'stats'));
    }
    
    /**
     * Get coupons data with filters (AJAX)
     */
    private function getCouponsData(Request $request)
    {
        $query = Coupon::whereHas('campaign', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->with(['campaign.network']);
        
        // Filter by campaign
        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        // Filter by network
        if ($request->network_id) {
            $query->whereHas('campaign', function($q) use ($request) {
                $q->where('network_id', $request->network_id);
            });
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search
        if ($request->search) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }
        
        $coupons = $query->latest()->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }
    
    /**
     * Get coupon statistics
     */
    private function getCouponStats()
    {
        $userId = auth()->id();
        
        return [
            'total' => Coupon::whereHas('campaign', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'active' => Coupon::whereHas('campaign', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'active')->count(),
            'used' => Coupon::whereHas('campaign', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('used_count', '>', 0)->count(),
            'expired' => Coupon::whereHas('campaign', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('expires_at', '<', now())->count(),
        ];
    }

    /**
     * Show the form for creating a new coupon
     */
    public function create()
    {
        $campaigns = Campaign::where('is_active', true)->get();
        return view('dashboard.coupons.create', compact('campaigns'));
    }

    /**
     * Store a newly created coupon
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:coupons'],
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'type' => ['required', 'in:single,multiple'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
        ]);

        $coupon = Coupon::create($validated);

        return redirect()->route('coupons.index')->with('success', 'Coupon created successfully');
    }

    /**
     * Display the specified coupon
     */
    public function show(Coupon $coupon)
    {
        $coupon->load(['campaign', 'purchases']);
        
        $stats = [
            'total_uses' => $coupon->times_used,
            'remaining_uses' => $coupon->max_uses ? ($coupon->max_uses - $coupon->times_used) : null,
            'total_revenue' => $coupon->purchases()->where('status', 'completed')->sum('amount'),
            'unique_users' => $coupon->purchases()->distinct('user_id')->count('user_id'),
        ];

        return view('dashboard.coupons.show', compact('coupon', 'stats'));
    }

    /**
     * Show the form for editing the coupon
     */
    public function edit(Coupon $coupon)
    {
        $campaigns = Campaign::where('is_active', true)->get();
        return view('dashboard.coupons.edit', compact('coupon', 'campaigns'));
    }

    /**
     * Update the specified coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:coupons,code,' . $coupon->id],
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'type' => ['required', 'in:single,multiple'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'is_active' => ['boolean'],
        ]);

        $coupon->update($validated);

        return redirect()->route('coupons.index')->with('success', 'Coupon updated successfully');
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')->with('success', 'Coupon deleted successfully');
    }

    /**
     * Validate coupon
     */
    public function validate(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            throw ValidationException::withMessages([
                'code' => ['Invalid coupon code.'],
            ]);
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            throw ValidationException::withMessages([
                'code' => ['This coupon is not active.'],
            ]);
        }

        // Check validity period
        if (now()->lt($coupon->valid_from) || now()->gt($coupon->valid_until)) {
            throw ValidationException::withMessages([
                'code' => ['This coupon is not valid at this time.'],
            ]);
        }

        // Check max uses
        if ($coupon->max_uses && $coupon->times_used >= $coupon->max_uses) {
            throw ValidationException::withMessages([
                'code' => ['This coupon has reached its maximum uses.'],
            ]);
        }

        // Check minimum purchase amount
        if ($request->amount && $coupon->min_purchase_amount && $request->amount < $coupon->min_purchase_amount) {
            throw ValidationException::withMessages([
                'code' => ['Minimum purchase amount not met.'],
            ]);
        }

        // Calculate discount
        $discount = 0;
        if ($request->amount) {
            $discount = $coupon->discount_type === 'percentage' 
                ? ($request->amount * $coupon->discount_value / 100)
                : $coupon->discount_value;
        }

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'final_amount' => $request->amount ? ($request->amount - $discount) : null,
        ]);
    }

    /**
     * Redeem coupon
     */
    public function redeem(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Validate coupon first
        $validationRequest = new Request([
            'code' => $coupon->code,
            'amount' => $validated['amount'],
            'user_id' => $validated['user_id'],
        ]);
        
        $this->validate($validationRequest);

        // Create purchase record
        $discount = $coupon->discount_type === 'percentage' 
            ? ($validated['amount'] * $coupon->discount_value / 100)
            : $coupon->discount_value;

        $purchase = $coupon->purchases()->create([
            'user_id' => $validated['user_id'],
            'campaign_id' => $coupon->campaign_id,
            'amount' => $validated['amount'],
            'discount_amount' => $discount,
            'final_amount' => $validated['amount'] - $discount,
            'status' => 'completed',
        ]);

        // Increment coupon usage
        $coupon->increment('times_used');

        return response()->json([
            'success' => true,
            'purchase' => $purchase,
            'message' => 'Coupon redeemed successfully',
        ]);
    }

    /**
     * Activate coupon
     */
    public function activate(Coupon $coupon)
    {
        $coupon->update(['is_active' => true]);
        return back()->with('success', 'Coupon activated successfully');
    }

    /**
     * Deactivate coupon
     */
    public function deactivate(Coupon $coupon)
    {
        $coupon->update(['is_active' => false]);
        return back()->with('success', 'Coupon deactivated successfully');
    }

    /**
     * Get coupon history
     */
    public function history(Coupon $coupon)
    {
        $history = $coupon->purchases()
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($history);
    }

    /**
     * Bulk generate coupons
     */
    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'prefix' => ['nullable', 'string', 'max:10'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
        ]);

        $coupons = [];
        $prefix = $validated['prefix'] ?? 'COUP';

        for ($i = 0; $i < $validated['quantity']; $i++) {
            $code = $prefix . '-' . strtoupper(Str::random(8));
            
            $coupons[] = Coupon::create([
                'code' => $code,
                'campaign_id' => $validated['campaign_id'],
                'type' => 'single',
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('coupons.index')->with('success', count($coupons) . ' coupons generated successfully');
    }

    /**
     * Export coupons
     */
    public function export(Request $request)
    {
        $query = Coupon::with('campaign');

        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $coupons = $query->get();

        // Implementation depends on export format (CSV, Excel, etc.)
        return response()->json($coupons);
    }
}

