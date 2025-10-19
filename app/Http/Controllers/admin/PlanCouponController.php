<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PlanCoupon;
use Illuminate\Http\Request;

class PlanCouponController extends Controller
{
    public function index()
    {
        $coupons = PlanCoupon::latest()->paginate(20);
        return view('admin.plan-coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.plan-coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:plan_coupons,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'max_redemptions' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        PlanCoupon::create($validated);
        return redirect()->route('admin.plan-coupons.index')->with('success', 'Coupon created successfully');
    }

    public function edit(PlanCoupon $plan_coupon)
    {
        return view('admin.plan-coupons.edit', ['coupon' => $plan_coupon]);
    }

    public function update(Request $request, PlanCoupon $plan_coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:plan_coupons,code,' . $plan_coupon->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'max_redemptions' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $plan_coupon->update($validated);
        return redirect()->route('admin.plan-coupons.index')->with('success', 'Coupon updated successfully');
    }

    public function destroy(PlanCoupon $plan_coupon)
    {
        $plan_coupon->delete();
        return redirect()->route('admin.plan-coupons.index')->with('success', 'Coupon deleted successfully');
    }
}


