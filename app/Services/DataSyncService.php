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
                $totalPurchases += $result['orders_count'] ?? 0;
            }
        }

        return [
            'success' => true,
            'message' => "Synced {$totalRecords} records from " . count($networkIds) . " networks",
            'total_records' => $totalRecords,
            'campaigns_count' => $totalCampaigns,
            'coupons_count' => $totalCoupons,
            'orders_count' => $totalPurchases,
            'results' => $results,
        ];
    }

    /**
     * Get network service instance.
     */
    protected function getNetworkService(Network $network)
    {
        try {
            return \App\Services\Networks\NetworkServiceFactory::create($network->name);
        } catch (\Exception $e) {
            Log::error("Failed to create network service", [
                'network_name' => $network->name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
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



        // Process coupon data using NetworkDataProcessor
        $couponStats = ['campaigns' => 0, 'coupons' => 0, 'purchases' => 0];
        if (!empty($result['data']['coupons']['data'])) {
            $couponResult = \App\Helpers\NetworkDataProcessor::processCouponData(
                $result['data']['coupons']['data'],
                $network->id,
                $userId,
                $config['date_from'],
                $config['date_to'],
                $network->name
            );
            $couponStats = $couponResult['processed'] ?? $couponStats;
        }

        // Process link data if exists
        $linkStats = ['campaigns' => 0, 'purchases' => 0];
        if (!empty($result['data']['links']['data'])) {
            $linkResult = \App\Helpers\NetworkDataProcessor::processLinkData(
                $result['data']['links']['data'],
                $network->id,
                $userId,
                $config['date_from'],
                $config['date_to']
            );
            $linkStats = $linkResult['processed'] ?? $linkStats;
        }

        $totalRecords = ($couponStats['purchases'] ?? 0) + ($linkStats['purchases'] ?? 0);

        return [
            'success' => true,
            'message' => $result['message'] ?? 'Data synced successfully',
            'total_records' => $totalRecords,
            'campaigns_count' => $couponStats['campaigns'] ?? 0,
            'coupons_count' => $couponStats['coupons'] ?? 0,
            'orders_count' => $couponStats['purchases'] ?? 0,
            'metadata' => [
                'coupon_stats' => $couponStats,
                'link_stats' => $linkStats,
            ],
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
    }
}

