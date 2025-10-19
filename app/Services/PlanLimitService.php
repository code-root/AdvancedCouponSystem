<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SyncUsage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlanLimitService
{
    public function getActiveSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)->first();
    }

    public function assertSubscribed(User $user): void
    {
        $sub = $this->getActiveSubscription($user);
        if (!$sub) {
            throw new RuntimeException('Subscription required');
        }
        $now = Carbon::now();
        if ($sub->status !== 'active') {
            if ($sub->status === 'trialing' && $now->lt(Carbon::parse($sub->trial_ends_at))) {
                return; // allowed in trial
            }
            throw new RuntimeException('Subscription inactive or expired');
        }
    }

    public function assertCanAddNetwork(User $user): void
    {
        $sub = $this->getActiveSubscription($user);
        if (!$sub) throw new RuntimeException('Subscription required');

        $plan = $sub->plan;
        if (!$plan) throw new RuntimeException('Plan not found');

        $current = $user->getActiveNetworkConnectionsCount();
        if ($current >= (int) $plan->max_networks) {
            throw new RuntimeException('Plan limit: max networks reached');
        }
    }

    public function assertCanSync(User $user): void
    {
        $sub = $this->getActiveSubscription($user);
        if (!$sub) throw new RuntimeException('Subscription required');
        $plan = $sub->plan;
        if (!$plan) throw new RuntimeException('Plan not found');

        // Optional allowed time window inside day
        if ($plan->sync_allowed_from_time && $plan->sync_allowed_to_time) {
            $nowTime = Carbon::now()->format('H:i:s');
            if ($nowTime < $plan->sync_allowed_from_time || $nowTime > $plan->sync_allowed_to_time) {
                throw new RuntimeException('Sync not allowed at this time');
            }
        }

        // Determine current window
        $now = Carbon::now();
        $unit = $plan->sync_window_unit ?: 'day';
        $size = max(1, (int) ($plan->sync_window_size ?: 1));

        $windowStart = match ($unit) {
            'minute' => $now->copy()->subMinutes($size),
            'hour' => $now->copy()->subHours($size),
            default => $now->copy()->subDays($size),
        };
        $windowEnd = $now;

        // Check sync_count within window against daily/monthly limits
        $periods = [];
        if (!is_null($plan->daily_sync_limit)) $periods[] = ['daily', $plan->daily_sync_limit];
        if (!is_null($plan->monthly_sync_limit)) $periods[] = ['monthly', $plan->monthly_sync_limit];

        foreach ($periods as [$period, $limit]) {
            [$pStart, $pEnd] = $this->getPeriodWindow($period, $now);
            $usage = SyncUsage::firstOrCreate([
                'user_id' => $user->id,
                'period' => $period,
                'window_start' => $pStart,
                'window_end' => $pEnd,
            ], [
                'sync_count' => 0,
                'revenue_sum' => 0,
                'orders_count' => 0,
            ]);

            if ($usage->sync_count >= (int) $limit) {
                throw new RuntimeException("Plan limit: {$period} sync limit reached");
            }
        }

        // Additional caps can be enforced during ingestion (revenue/orders)
    }

    public function incrementSyncCount(User $user, float $revenueDelta = 0, int $ordersDelta = 0, int $syncIncrements = 1): void
    {
        $now = Carbon::now();
        foreach (['daily', 'monthly'] as $period) {
            [$pStart, $pEnd] = $this->getPeriodWindow($period, $now);
            $usage = SyncUsage::firstOrCreate([
                'user_id' => $user->id,
                'period' => $period,
                'window_start' => $pStart,
                'window_end' => $pEnd,
            ], [
                'sync_count' => 0,
                'revenue_sum' => 0,
                'orders_count' => 0,
            ]);

            // Increment individually to avoid race on non-existent incrementEach
            $usage->increment('sync_count', max(1, $syncIncrements));
            if ($revenueDelta !== 0) {
                $usage->increment('revenue_sum', $revenueDelta);
            }
            if ($ordersDelta !== 0) {
                $usage->increment('orders_count', $ordersDelta);
            }
        }
    }

    private function getPeriodWindow(string $period, Carbon $ref): array
    {
        if ($period === 'daily') {
            return [$ref->copy()->startOfDay(), $ref->copy()->endOfDay()];
        }
        return [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth()];
    }

    /**
     * Increment revenue count for user
     */
    public function incrementRevenueCount(User $user, int $count = 1): void
    {
        $this->incrementUsageCount($user, 'revenue_count', $count);
    }

    /**
     * Increment orders count for user
     */
    public function incrementOrdersCount(User $user, int $count = 1): void
    {
        $this->incrementUsageCount($user, 'orders_count', $count);
    }

    /**
     * Generic method to increment usage count
     */
    private function incrementUsageCount(User $user, string $field, int $count): void
    {
        $now = Carbon::now();
        
        // Update daily usage
        $dailyUsage = $this->getOrCreateUsage($user, 'daily', $now);
        $dailyUsage->increment($field, $count);
        
        // Update monthly usage
        $monthlyUsage = $this->getOrCreateUsage($user, 'monthly', $now);
        $monthlyUsage->increment($field, $count);
    }
}




