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

class ResetDailyCountersJob implements ShouldQueue
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
        Log::info('Starting daily counters reset job');

        try {
            // Get all active and trial subscriptions
            $subscriptions = Subscription::whereIn('status', ['active', 'trial'])
                ->with('plan')
                ->get();

            foreach ($subscriptions as $subscription) {
                $this->resetDailyCountersForUser($subscription->user_id, $subscription->plan);
            }

            // Clean up old daily usage records
            $this->cleanupOldDailyRecords();

            Log::info('Daily counters reset job completed successfully');
        } catch (\Exception $e) {
            Log::error('Daily counters reset job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset daily counters for a specific user
     */
    private function resetDailyCountersForUser(int $userId, $plan): void
    {
        $today = Carbon::today();

        // Create or update today's usage record
        $dailyUsage = SyncUsage::firstOrCreate([
            'user_id' => $userId,
            'period' => 'daily',
            'window_start' => $today,
            'window_end' => $today->copy()->endOfDay(),
        ], [
            'sync_count' => 0,
            'revenue_count' => 0,
            'orders_count' => 0,
        ]);

        // Reset counters to 0 if they're not already 0
        if ($dailyUsage->sync_count > 0 || $dailyUsage->revenue_count > 0 || $dailyUsage->orders_count > 0) {
            $dailyUsage->update([
                'sync_count' => 0,
                'revenue_count' => 0,
                'orders_count' => 0,
            ]);

            Log::info("Reset daily counters for user {$userId}", [
                'sync_count' => 0,
                'revenue_count' => 0,
                'orders_count' => 0,
            ]);
        }
    }

    /**
     * Clean up old daily usage records
     */
    private function cleanupOldDailyRecords(): void
    {
        $cutoffDate = Carbon::now()->subDays(30);

        $deletedCount = SyncUsage::where('period', 'daily')
            ->where('window_start', '<', $cutoffDate)
            ->delete();

        if ($deletedCount > 0) {
            Log::info("Cleaned up {$deletedCount} old daily usage records");
        }
    }
}