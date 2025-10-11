<?php

namespace App\Console\Commands;

use App\Models\SyncSchedule;
use App\Models\SyncLog;
use App\Jobs\ProcessNetworkSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledSyncs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled data syncs from networks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled syncs...');

        // Get all schedules ready to run
        $schedules = SyncSchedule::readyToRun()->get();

        if ($schedules->isEmpty()) {
            $this->info('No schedules ready to run.');
            return Command::SUCCESS;
        }

        $this->info("Found {$schedules->count()} schedule(s) ready to run.");

        foreach ($schedules as $schedule) {
            $this->info("Processing schedule: {$schedule->name} (ID: {$schedule->id})");

            try {
                // Get networks for this schedule
                $networkIds = $schedule->network_ids ?? [];

                if (empty($networkIds)) {
                    $this->warn("Schedule {$schedule->id} has no networks configured.");
                    continue;
                }

                // Create sync logs and dispatch jobs for each network
                foreach ($networkIds as $networkId) {
                    // Create sync log
                    $syncLog = SyncLog::create([
                        'sync_schedule_id' => $schedule->id,
                        'user_id' => $schedule->user_id,
                        'network_id' => $networkId,
                        'sync_type' => $schedule->sync_type,
                        'status' => 'pending',
                    ]);

                    // Dispatch job to queue
                    ProcessNetworkSync::dispatch($syncLog->id, $schedule->id);

                    $this->info("  - Dispatched sync job for network ID: {$networkId}, Log ID: {$syncLog->id}");
                }

                $this->info("Successfully dispatched {$schedule->name}");

            } catch (\Exception $e) {
                $this->error("Error processing schedule {$schedule->id}: " . $e->getMessage());
                Log::error("Error in ProcessScheduledSyncs for schedule {$schedule->id}: " . $e->getMessage());
            }
        }

        $this->info('Scheduled syncs processing completed.');
        return Command::SUCCESS;
    }
}
