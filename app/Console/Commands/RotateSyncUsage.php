<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncUsage;
use Carbon\Carbon;

class RotateSyncUsage extends Command
{
    protected $signature = 'usage:rotate {period=daily}';
    protected $description = 'Rotate SyncUsage windows for daily/monthly';

    public function handle(): int
    {
        $period = $this->argument('period');
        $now = Carbon::now();
        $query = SyncUsage::query();
        if ($period === 'daily') {
            $query->where('period', 'daily');
        } else {
            $query->where('period', 'monthly');
        }
        $updated = 0;
        foreach ($query->cursor() as $usage) {
            [$start, $end] = $period === 'daily'
                ? [$now->copy()->startOfDay(), $now->copy()->endOfDay()]
                : [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            $usage->update([
                'window_start' => $start,
                'window_end' => $end,
                'sync_count' => 0,
                'revenue_sum' => 0,
                'orders_count' => 0,
            ]);
            $updated++;
        }
        $this->info("Rotated {$updated} usage rows for {$period}");
        return self::SUCCESS;
    }
}


