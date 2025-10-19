<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\PlanCoupon;
use App\Models\PlanCouponRedemption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function startTrial(User $user, Plan $plan): Subscription
    {
        return Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'starts_at' => now(),
                'trial_ends_at' => now()->addDays($plan->trial_days ?? 14),
                'billing_interval' => 'monthly',
            ]
        );
    }

    public function activate(User $user, Plan $plan, ?string $couponCode = null): Subscription
    {
        return DB::transaction(function () use ($user, $plan, $couponCode) {
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays($plan->trial_days ?? 14),
                    'billing_interval' => 'monthly',
                ]
            );

            if ($couponCode) {
                $this->redeemCoupon($user, $subscription, $couponCode, $plan);
            }

            return $subscription;
        });
    }

    public function changePlan(Subscription $subscription, Plan $newPlan): Subscription
    {
        $subscription->update([
            'plan_id' => $newPlan->id,
            'status' => 'active',
        ]);
        return $subscription;
    }

    public function cancel(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'canceled',
            'cancelled_at' => now(),
        ]);
    }

    public function isActiveOrTrial(User $user): bool
    {
        $sub = Subscription::where('user_id', $user->id)->first();
        if (!$sub) return false;
        if ($sub->status === 'active') return true;
        if ($sub->status === 'trialing' && Carbon::now()->lt(Carbon::parse($sub->trial_ends_at))) return true;
        return false;
    }

    public function redeemCoupon(User $user, Subscription $subscription, string $code, Plan $plan): ?PlanCouponRedemption
    {
        $coupon = PlanCoupon::where('code', $code)
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$coupon) return null;

        if (!is_null($coupon->max_redemptions) && $coupon->redemptions_count >= $coupon->max_redemptions) {
            return null;
        }

        // Compute discount locally (no gateway logic here)
        $price = (float) $plan->price;
        $discount = $coupon->type === 'percent' ? ($price * ((float) $coupon->value / 100.0)) : (float) $coupon->value;
        $discount = max(0.0, min($discount, $price));

        $redemption = PlanCouponRedemption::create([
            'plan_coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'discount_applied' => $discount,
            'redeemed_at' => now(),
        ]);

        $coupon->increment('redemptions_count');

        return $redemption;
    }
}




