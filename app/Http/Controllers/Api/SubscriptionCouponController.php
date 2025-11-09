<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanCoupon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionCouponController extends Controller
{
    /**
     * Validate subscription coupon
     */
    public function validate(Request $request)
    {
        $request->validate([
            'coupon' => ['required', 'string'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $coupon = PlanCoupon::where('code', $request->coupon)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code.',
            ]);
        }

        // Check if coupon is active
        if (!$coupon->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is not active.',
            ]);
        }

        // Check validity period
        if (now()->lt($coupon->valid_from) || now()->gt($coupon->expires_at)) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is not valid at this time.',
            ]);
        }

        // Check max uses
        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon has reached its maximum uses.',
            ]);
        }

        // Check if coupon is applicable to this plan
        if ($coupon->applicable_plans && !in_array($request->plan_id, $coupon->applicable_plans)) {
            return response()->json([
                'valid' => false,
                'message' => 'This coupon is not applicable to the selected plan.',
            ]);
        }

        // Get plan details
        $plan = \App\Models\Plan::find($request->plan_id);
        if (!$plan) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid plan selected.',
            ]);
        }

        // Calculate discount
        $discount = 0;
        $discountPercentage = 0;
        
        if ($coupon->type === 'percentage') {
            $discountPercentage = $coupon->value;
            $discount = ($plan->price * $coupon->value / 100);
        } else {
            $discount = $coupon->value;
            $discountPercentage = ($coupon->value / $plan->price) * 100;
        }

        // Ensure discount doesn't exceed plan price
        $discount = min($discount, $plan->price);

        return response()->json([
            'valid' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'discount' => $discountPercentage,
            'discount_amount' => $discount,
            'original_price' => $plan->price,
            'discounted_price' => $plan->price - $discount,
            'billing_cycle' => $plan->billing_cycle,
        ]);
    }
}







