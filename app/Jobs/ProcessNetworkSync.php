<?php

namespace App\Jobs;

use App\Models\SyncLog;
use App\Models\SyncSchedule;
use App\Models\NetworkConnection;
use App\Services\DataSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessNetworkSync implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $syncLogId,
        public ?int $syncScheduleId = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DataSyncService $syncService): void
    {
        $syncLog = SyncLog::find($this->syncLogId);
        
        if (!$syncLog) {
            Log::error("SyncLog not found: {$this->syncLogId}");
            return;
        }

        try {
            // Mark as processing
            $syncLog->markAsProcessing();

            // Get network connection
            $connection = NetworkConnection::where('user_id', $syncLog->user_id)
                ->where('network_id', $syncLog->network_id)
                ->where('is_connected', true)
                ->first();

            if (!$connection) {
                throw new Exception("No active connection found for network ID: {$syncLog->network_id}");
            }

            // Get date range from schedule if exists
            $dateRange = $this->getDateRange($syncLog);

            // Perform sync
            $result = $syncService->syncNetwork(
                $syncLog->network_id,
                $syncLog->user_id,
                [
                    'sync_type' => $syncLog->sync_type,
                    'date_from' => $dateRange['from'],
                    'date_to' => $dateRange['to'],
                    'connection' => $connection,
                ]
            );

            if ($result['success']) {
                // Mark as completed
                $syncLog->markAsCompleted([
                    'records_synced' => $result['total_records'] ?? 0,
                    'campaigns_count' => $result['campaigns_count'] ?? 0,
                    'coupons_count' => $result['coupons_count'] ?? 0,
                    'purchases_count' => $result['purchases_count'] ?? 0,
                    'metadata' => $result['metadata'] ?? null,
                ]);

                // Update schedule if exists
                if ($this->syncScheduleId) {
                    $this->updateSchedule();
                }

                Log::info("Sync completed successfully for log ID: {$this->syncLogId}");
            } else {
                throw new Exception($result['message'] ?? 'Unknown sync error');
            }

        } catch (Exception $e) {
            Log::error("Sync failed for log ID {$this->syncLogId}: " . $e->getMessage());
            
            $syncLog->markAsFailed($e->getMessage());

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Get date range for sync.
     */
    protected function getDateRange(SyncLog $syncLog): array
    {
        // Check if manual sync with metadata
        if (!$this->syncScheduleId && $syncLog->metadata) {
            $metadata = $syncLog->metadata;
            if (isset($metadata['date_from']) && isset($metadata['date_to'])) {
                return [
                    'from' => $metadata['date_from'],
                    'to' => $metadata['date_to'],
                ];
            }
        }

        // Check if scheduled sync
        if ($this->syncScheduleId) {
            $schedule = SyncSchedule::find($this->syncScheduleId);
            if ($schedule) {
                return $schedule->getDateRange();
            }
        }

        // Default to today
        return [
            'from' => now()->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
        ];
    }

    /**
     * Update schedule after successful sync.
     */
    protected function updateSchedule(): void
    {
        $schedule = SyncSchedule::find($this->syncScheduleId);
        
        if ($schedule) {
            $schedule->incrementRunsToday();
            $schedule->update([
                'last_run_at' => now(),
                'next_run_at' => $schedule->calculateNextRunTime(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Job failed permanently for SyncLog ID {$this->syncLogId}: " . $exception->getMessage());
        
        $syncLog = SyncLog::find($this->syncLogId);
        if ($syncLog && $syncLog->status !== 'failed') {
            $syncLog->markAsFailed($exception->getMessage());
        }
    }
}
