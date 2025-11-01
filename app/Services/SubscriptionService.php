<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\SubscriptionCancelledNotification;
use App\Notifications\SubscriptionUpgradedNotification;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionUpgraded;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription.
     */
    public function createSubscription(User $user, Plan $plan, array $options = []): Subscription
    {
        return DB::transaction(function () use ($user, $plan, $options) {
            // Cancel any existing active subscription
            $user->subscriptions()
                ->where('status', 'active')
                ->update([
                    'status' => 'canceled',
                    'cancelled_at' => now(),
                ]);

            // Calculate dates
            $startsAt = now();
            $endsAt = $this->calculateEndDate($startsAt, $plan->billing_cycle);
            $trialEndsAt = $plan->trial_days > 0 ? $startsAt->copy()->addDays((int) $plan->trial_days) : null;

            // Create subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $plan->trial_days > 0 ? 'trialing' : 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'trial_ends_at' => $trialEndsAt,
                'billing_interval' => $plan->billing_cycle,
                'gateway' => $options['gateway'] ?? 'manual',
                'gateway_customer_id' => $options['gateway_customer_id'] ?? null,
                'gateway_subscription_id' => $options['gateway_subscription_id'] ?? null,
                'meta' => [
                    'coupon_code' => $options['coupon_code'] ?? null,
                    'created_by_admin' => Auth::guard('admin')->check(),
                    'admin_id' => Auth::guard('admin')->id(),
                ],
            ]);

            // Notify admins
            try {
                $this->notifyAdmins(new NewSubscriptionNotification($subscription));
                Log::info('SubscriptionService: New subscription notification sent', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id
                ]);
            } catch (\Exception $e) {
                Log::error('SubscriptionService: Failed to send new subscription notification', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return $subscription;
        });
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Subscription $subscription, ?string $reason = null): void
    {
        DB::transaction(function () use ($subscription, $reason) {
            $subscription->update([
                'status' => 'canceled',
                'cancelled_at' => now(),
                'meta' => array_merge($subscription->meta ?? [], [
                    'cancellation_reason' => $reason,
                    'cancelled_by_admin' => Auth::guard('admin')->check(),
                    'cancelled_by_admin_id' => Auth::guard('admin')->id(),
                ]),
            ]);

            // Notify admins
            try {
                $this->notifyAdmins(new SubscriptionCancelledNotification($subscription, $reason));
                Log::info('SubscriptionService: Subscription cancelled notification sent', [
                    'subscription_id' => $subscription->id,
                    'reason' => $reason
                ]);
            } catch (\Exception $e) {
                Log::error('SubscriptionService: Failed to send cancellation notification', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Dispatch event
            try {
                event(new SubscriptionCancelled($subscription, $reason));
            } catch (\Exception $e) {
                Log::error('SubscriptionService: Failed to dispatch SubscriptionCancelled event', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resumeSubscription(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $plan = $subscription->plan;
            $newEndDate = $this->calculateEndDate(now(), $plan->billing_cycle);

            $subscription->update([
                'status' => 'active',
                'ends_at' => $newEndDate,
                'cancelled_at' => null,
                'meta' => array_merge($subscription->meta ?? [], [
                    'resumed_at' => now(),
                    'resumed_by_admin' => Auth::guard('admin')->check(),
                    'resumed_by_admin_id' => Auth::guard('admin')->id(),
                ]),
            ]);
        });
    }

    /**
     * Change subscription plan.
     */
    public function changePlan(Subscription $subscription, Plan $newPlan, bool $immediate = false): void
    {
        DB::transaction(function () use ($subscription, $newPlan, $immediate) {
            $oldPlan = $subscription->plan;
            
            if ($immediate) {
                // Immediate change
                $newEndDate = $this->calculateEndDate(now(), $newPlan->billing_cycle);
                
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'ends_at' => $newEndDate,
                    'billing_interval' => $newPlan->billing_cycle,
                    'meta' => array_merge($subscription->meta ?? [], [
                        'plan_changed_at' => now(),
                        'old_plan_id' => $oldPlan->id,
                        'changed_by_admin' => Auth::guard('admin')->check(),
                        'changed_by_admin_id' => Auth::guard('admin')->id(),
                    ]),
                ]);
            } else {
                // Change at next billing cycle
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'billing_interval' => $newPlan->billing_cycle,
                    'meta' => array_merge($subscription->meta ?? [], [
                        'plan_change_scheduled' => true,
                        'new_plan_id' => $newPlan->id,
                        'old_plan_id' => $oldPlan->id,
                        'scheduled_by_admin' => Auth::guard('admin')->check(),
                        'scheduled_by_admin_id' => Auth::guard('admin')->id(),
                    ]),
                ]);
            }

            // Notify admins
            try {
                $this->notifyAdmins(new SubscriptionUpgradedNotification($subscription, $oldPlan, $newPlan));
                Log::info('SubscriptionService: Subscription upgraded notification sent', [
                    'subscription_id' => $subscription->id,
                    'old_plan_id' => $oldPlan->id,
                    'new_plan_id' => $newPlan->id
                ]);
            } catch (\Exception $e) {
                Log::error('SubscriptionService: Failed to send upgrade notification', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Dispatch event
            try {
                event(new SubscriptionUpgraded($subscription, $oldPlan, $newPlan));
            } catch (\Exception $e) {
                Log::error('SubscriptionService: Failed to dispatch SubscriptionUpgraded event', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * Extend subscription.
     */
    public function extendSubscription(Subscription $subscription, int $days): void
    {
        DB::transaction(function () use ($subscription, $days) {
            $newEndDate = $subscription->ends_at->addDays($days);
            
            $subscription->update([
                'ends_at' => $newEndDate,
                'meta' => array_merge($subscription->meta ?? [], [
                    'extended_by_days' => $days,
                    'extended_at' => now(),
                    'extended_by_admin' => Auth::guard('admin')->check(),
                    'extended_by_admin_id' => Auth::guard('admin')->id(),
                ]),
            ]);
        });
    }

    /**
     * Activate trial subscription.
     */
    public function activateTrial(User $user, Plan $plan): Subscription
    {
        return $this->createSubscription($user, $plan, [
            'gateway' => 'trial',
            'trial' => true,
        ]);
    }

    /**
     * Manually activate subscription.
     */
    public function manualActivate(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $plan = $subscription->plan;
            $startsAt = now();
            $endsAt = $this->calculateEndDate($startsAt, $plan->billing_cycle);

            $subscription->update([
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'meta' => array_merge($subscription->meta ?? [], [
                    'manually_activated_at' => now(),
                    'manually_activated_by_admin' => Auth::guard('admin')->check(),
                    'manually_activated_by_admin_id' => Auth::guard('admin')->id(),
                ]),
            ]);

            // Notify admins
            $adminName = Auth::guard('admin')->user()->name ?? 'System';
            $this->notifyAdmins(new \App\Notifications\ManualSubscriptionActivatedNotification($subscription, $adminName));
        });
    }

    /**
     * Check if user has active subscription.
     */
    public function hasActiveSubscription(User $user): bool
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();
    }

    /**
     * Get user's active subscription.
     */
    public function getActiveSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Check if subscription is expiring soon.
     */
    public function isExpiringSoon(Subscription $subscription, int $days = 7): bool
    {
        return $subscription->ends_at->diffInDays(now()) <= $days;
    }

    /**
     * Get expiring subscriptions.
     */
    public function getExpiringSubscriptions(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->where('ends_at', '<=', now()->addDays($days))
            ->where('ends_at', '>', now())
            ->get();
    }

    /**
     * Calculate end date based on billing cycle.
     */
    private function calculateEndDate(Carbon $startDate, string $billingCycle): Carbon
    {
        return match ($billingCycle) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'semi_annually' => $startDate->copy()->addMonths(6),
            'annually' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };
    }

    /**
     * Notify all admins with error handling.
     */
    private function notifyAdmins($notification): void
    {
        try {
            $admins = \App\Models\Admin::all();
            
            Log::info('SubscriptionService: Notifying admins', [
                'admin_count' => $admins->count(),
                'notification_type' => get_class($notification)
            ]);
            
            foreach ($admins as $admin) {
                try {
                    $admin->notify($notification);
                    Log::debug('SubscriptionService: Notification sent to admin', [
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email
                    ]);
                } catch (\Exception $e) {
                    Log::warning('SubscriptionService: Failed to notify admin', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other admins even if one fails
                }
            }
        } catch (\Exception $e) {
            Log::error('SubscriptionService: Failed to load admins for notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get subscription statistics.
     */
    public function getStatistics(): array
    {
        $today = today();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        return [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'trial_subscriptions' => Subscription::where('status', 'trialing')->count(),
            'canceled_subscriptions' => Subscription::where('status', 'canceled')->count(),
            'monthly_revenue' => Subscription::where('status', 'active')
                ->whereMonth('created_at', $thisMonth)
                ->whereYear('created_at', $thisYear)
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->sum('plans.price'),
            'subscriptions_today' => Subscription::whereDate('created_at', $today)->count(),
            'subscriptions_this_month' => Subscription::whereMonth('created_at', $thisMonth)
                ->whereYear('created_at', $thisYear)->count(),
        ];
    }
}