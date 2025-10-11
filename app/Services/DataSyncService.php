<?php

namespace App\Services;

use App\Models\Network;
use App\Models\NetworkConnection;
use App\Models\SyncSchedule;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Services\Networks\BoostinyService;
use App\Services\Networks\AdmitadService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class DataSyncService
{
    /**
     * Sync data from a single network.
     */
    public function syncNetwork(int $networkId, int $userId, array $config = []): array
    {
        try {
            $network = Network::find($networkId);
            if (!$network) {
                return [
                    'success' => false,
                    'message' => 'Network not found',
                ];
            }

            $connection = $config['connection'] ?? NetworkConnection::where('user_id', $userId)
                ->where('network_id', $networkId)
                ->where('is_connected', true)
                ->first();

            if (!$connection) {
                return [
                    'success' => false,
                    'message' => 'No active connection found',
                ];
            }

            // Get appropriate service based on network
            $service = $this->getNetworkService($network);
            if (!$service) {
                return [
                    'success' => false,
                    'message' => 'Network service not available',
                ];
            }

            // Prepare credentials
            $credentials = $connection->credentials ?? [];
            
            // Prepare sync config
            $syncConfig = [
                'date_from' => $config['date_from'] ?? Carbon::today()->format('Y-m-d'),
                'date_to' => $config['date_to'] ?? Carbon::today()->format('Y-m-d'),
                'sync_type' => $config['sync_type'] ?? 'all',
            ];

            // Perform sync based on type
            $result = match($syncConfig['sync_type']) {
                'campaigns' => $this->syncCampaignsOnly($service, $credentials, $syncConfig, $userId, $network),
                'coupons' => $this->syncCouponsOnly($service, $credentials, $syncConfig, $userId, $network),
                'purchases' => $this->syncPurchasesOnly($service, $credentials, $syncConfig, $userId, $network),
                'all' => $this->syncAll($service, $credentials, $syncConfig, $userId, $network),
                default => $this->syncAll($service, $credentials, $syncConfig, $userId, $network),
            };

            return $result;

        } catch (Exception $e) {
            Log::error("Error syncing network {$networkId}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync data from multiple networks.
     */
    public function syncMultipleNetworks(array $networkIds, int $userId, array $config = []): array
    {
        $results = [];
        $totalRecords = 0;
        $totalCampaigns = 0;
        $totalCoupons = 0;
        $totalPurchases = 0;

        foreach ($networkIds as $networkId) {
            $result = $this->syncNetwork($networkId, $userId, $config);
            $results[$networkId] = $result;

            if ($result['success']) {
                $totalRecords += $result['total_records'] ?? 0;
                $totalCampaigns += $result['campaigns_count'] ?? 0;
                $totalCoupons += $result['coupons_count'] ?? 0;
                $totalPurchases += $result['purchases_count'] ?? 0;
            }
        }

        return [
            'success' => true,
            'message' => "Synced {$totalRecords} records from " . count($networkIds) . " networks",
            'total_records' => $totalRecords,
            'campaigns_count' => $totalCampaigns,
            'coupons_count' => $totalCoupons,
            'purchases_count' => $totalPurchases,
            'results' => $results,
        ];
    }

    /**
     * Get network service instance.
     */
    protected function getNetworkService(Network $network)
    {
        $networkName = strtolower($network->name);
        
        return match($networkName) {
            'boostiny' => app(BoostinyService::class),
            'admitad' => app(AdmitadService::class),
            'digizag' => app(\App\Services\Networks\DigizagService::class),
            'platformance' => app(\App\Services\Networks\PlatformanceService::class),
            'optimisemedia' => app(\App\Services\Networks\OptimiseMediaService::class),
            'clickdealer' => app(\App\Services\Networks\ClickDealerService::class),
            default => null,
        };
    }

    /**
     * Sync all data types.
     */
    protected function syncAll($service, array $credentials, array $config, int $userId, Network $network): array
    {
        $result = $service->syncData($credentials, $config);
        
        if (!$result['success']) {
            return $result;
        }

        // Count synced records
        $campaignsCount = $result['data']['coupons']['campaigns'] ?? 0;
        $couponsCount = $result['data']['coupons']['coupons'] ?? 0;
        $purchasesCount = $result['data']['coupons']['purchases'] ?? 0;

        return [
            'success' => true,
            'message' => $result['message'] ?? 'Data synced successfully',
            'total_records' => $campaignsCount + $couponsCount + $purchasesCount,
            'campaigns_count' => $campaignsCount,
            'coupons_count' => $couponsCount,
            'purchases_count' => $purchasesCount,
            'metadata' => $result['data'] ?? null,
        ];
    }

    /**
     * Sync campaigns only.
     */
    protected function syncCampaignsOnly($service, array $credentials, array $config, int $userId, Network $network): array
    {
        // Implementation depends on network service capabilities
        return $this->syncAll($service, $credentials, $config, $userId, $network);
    }

    /**
     * Sync coupons only.
     */
    protected function syncCouponsOnly($service, array $credentials, array $config, int $userId, Network $network): array
    {
        // Implementation depends on network service capabilities
        return $this->syncAll($service, $credentials, $config, $userId, $network);
    }

    /**
     * Sync purchases only.
     */
    protected function syncPurchasesOnly($service, array $credentials, array $config, int $userId, Network $network): array
    {
        // Implementation depends on network service capabilities
        return $this->syncAll($service, $credentials, $config, $userId, $network);
    }

    /**
     * Calculate next run time for a schedule.
     */
    public function calculateNextRunTime(SyncSchedule $schedule): Carbon
    {
        return Carbon::now()->addMinutes($schedule->interval_minutes);
    }

    /**
     * Check if a schedule can run.
     */
    public function canRunSchedule(SyncSchedule $schedule): bool
    {
        return $schedule->canRun();
    }

    /**
     * Reset daily counters for all schedules.
     */
    public function resetDailyCounters(): void
    {
        SyncSchedule::query()->update(['runs_today' => 0]);
        Log::info('Daily counters reset for all sync schedules');
    }
}

