<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataSyncService;

class ResetDailyCounters extends Command
{
    protected $signature = 'sync:reset-daily-counters';
    protected $description = 'Reset daily schedule counters (runs_today)';

    public function handle(DataSyncService $service): int
    {
        $service->resetDailyCounters();
        $this->info('Daily counters reset');
        return self::SUCCESS;
    }
}


