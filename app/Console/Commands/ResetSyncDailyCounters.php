<?php

namespace App\Console\Commands;

use App\Services\DataSyncService;
use Illuminate\Console\Command;

class ResetSyncDailyCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:reset-daily-counters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily run counters for all sync schedules';

    /**
     * Execute the console command.
     */
    public function handle(DataSyncService $syncService)
    {
        $this->info('Resetting daily counters for all sync schedules...');
        
        $syncService->resetDailyCounters();
        
        $this->info('Daily counters reset successfully.');
        
        return Command::SUCCESS;
    }
}
