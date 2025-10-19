<?php

namespace App\Jobs;

use App\Models\SyncUsage;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RotateSyncUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting sync usage rotation job');

        try {
            // Get all active subscriptions
            $subscriptions = Subscription::where('status', 'active')
                ->with('plan')
                ->get();

            foreach ($subscriptions as $subscription) {
                $this->rotateUsageForSubscription($subscription);
            }

            // Also handle trial subscriptions
            $trialSubscriptions = Subscription::where('status', 'trial')
                ->with('plan')
                ->get();

            foreach ($trialSubscriptions as $subscription) {
                $this->rotateUsageForSubscription($subscription);
            }

            Log::info('Sync usage rotation job completed successfully');
        } catch (\Exception $e) {
            Log::error('Sync usage rotation job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Rotate usage for a specific subscription
     */
    private function rotateUsageForSubscription(Subscription $subscription): void
    {
        $plan = $subscription->plan;
        $userId = $subscription->user_id;

        // Rotate daily usage to monthly
        $this->rotateDailyToMonthly($userId, $plan);

        // Reset daily counters
        $this->resetDailyCounters($userId, $plan);

        // Reset monthly counters if it's a new month
        $this->resetMonthlyCountersIfNeeded($userId, $plan);
    }

    /**
     * Rotate daily usage to monthly
     */
    private function rotateDailyToMonthly(int $userId, $plan): void
    {
        $yesterday = Carbon::yesterday();
        $startOfMonth = $yesterday->copy()->startOfMonth();

        // Get yesterday's daily usage
        $dailyUsage = SyncUsage::where('user_id', $userId)
            ->where('period', 'daily')
            ->whereDate('window_start', $yesterday)
            ->first();

        if ($dailyUsage) {
            // Get or create monthly usage for this month
            $monthlyUsage = SyncUsage::firstOrCreate([
                'user_id' => $userId,
                'period' => 'monthly',
                'window_start' => $startOfMonth,
                'window_end' => $startOfMonth->copy()->endOfMonth(),
            ], [
                'sync_count' => 0,
                'revenue_count' => 0,
                'orders_count' => 0,
            ]);

            // Add daily usage to monthly
            $monthlyUsage->increment('sync_count', $dailyUsage->sync_count);
            $monthlyUsage->increment('revenue_count', $dailyUsage->revenue_count);
            $monthlyUsage->increment('orders_count', $dailyUsage->orders_count);

            Log::info("Rotated daily usage to monthly for user {$userId}", [
                'daily_sync_count' => $dailyUsage->sync_count,
                'daily_revenue_count' => $dailyUsage->revenue_count,
                'daily_orders_count' => $dailyUsage->orders_count,
                'monthly_sync_count' => $monthlyUsage->sync_count,
                'monthly_revenue_count' => $monthlyUsage->revenue_count,
                'monthly_orders_count' => $monthlyUsage->orders_count,
            ]);
        }
    }

    /**
     * Reset daily counters
     */
    private function resetDailyCounters(int $userId, $plan): void
    {
        $today = Carbon::today();

        // Delete old daily usage records (older than 7 days)
        SyncUsage::where('user_id', $userId)
            ->where('period', 'daily')
            ->where('window_start', '<', $today->subDays(7))
            ->delete();

        // Create today's usage record if it doesn't exist
        SyncUsage::firstOrCreate([
            'user_id' => $userId,
            'period' => 'daily',
            'window_start' => $today,
            'window_end' => $today->copy()->endOfDay(),
        ], [
            'sync_count' => 0,
            'revenue_count' => 0,
            'orders_count' => 0,
        ]);
    }

    /**
     * Reset monthly counters if it's a new month
     */
    private function resetMonthlyCountersIfNeeded(int $userId, $plan): void
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Check if we have a monthly record for the current month
        $currentMonthlyUsage = SyncUsage::where('user_id', $userId)
            ->where('period', 'monthly')
            ->where('window_start', $currentMonth)
            ->first();

        if (!$currentMonthlyUsage) {
            // Create new monthly usage record for current month
            SyncUsage::create([
                'user_id' => $userId,
                'period' => 'monthly',
                'window_start' => $currentMonth,
                'window_end' => $currentMonth->copy()->endOfMonth(),
                'sync_count' => 0,
                'revenue_count' => 0,
                'orders_count' => 0,
            ]);

            Log::info("Created new monthly usage record for user {$userId} for month {$currentMonth->format('Y-m')}");
        }

        // Clean up old monthly records (older than 12 months)
        SyncUsage::where('user_id', $userId)
            ->where('period', 'monthly')
            ->where('window_start', '<', $currentMonth->subMonths(12))
            ->delete();
    }
}