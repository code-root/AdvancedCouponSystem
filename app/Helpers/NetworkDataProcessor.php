<?php

namespace App\Helpers;

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NetworkDataProcessor
{
    /**
     * Process coupon data from network
     */
    public static function processCouponData(array $data, int $networkId, int $userId, string $startDate, string $endDate, string $networkName = 'boostiny'): array
    {
        $processed = [
            'campaigns' => 0,
            'coupons' => 0,
            'purchases' => 0,
            'errors' => []
        ];
        
        try {
            DB::transaction(function () use ($data, $networkId, $userId, $startDate, $endDate, $networkName, &$processed) {
                // Delete existing purchases for this date range
                Purchase::where('network_id', $networkId)
                    ->where('user_id', $userId)
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->delete();
                
                foreach ($data as $item) {
                    try {
                        // Handle different network formats
                        if ($networkName === 'digizag') {
                            $item = self::normalizeDigizagData($item);
                        } elseif ($networkName === 'platformance') {
                            $item = self::normalizePlatformanceData($item);
                        } elseif ($networkName === 'optimisemedia') {
                            $item = self::normalizeOptimiseMediaData($item);
                        } else {
                            $item = self::normalizeBoostinyData($item);
                        }
                        
                        // Get or create country
                        $country = Country::firstOrCreate(
                            ['code' => strtoupper($item['country_code'] ?? 'NA')],
                            ['name' => $item['country_code'] ?? 'Not Available']
                        );
                        
                        // Create or update campaign
                        $campaign = Campaign::updateOrCreate(
                            [
                                'network_id' => $networkId,
                                'user_id' => $userId,
                                'network_campaign_id' => $item['campaign_id'],
                            ],
                            [
                                'name' => $item['campaign_name'],
                                'logo_url' => $item['campaign_logo'] ?? null,
                                'campaign_type' => 'coupon',
                                'status' => 'active',
                            ]
                        );
                        $processed['campaigns']++;
                        
                        // Create or update coupon
                        $coupon = Coupon::updateOrCreate(
                            [
                                'campaign_id' => $campaign->id,
                                'code' => $item['code'] ?? 'NA-' . $campaign->id,
                            ],
                            [
                                'description' => 'Auto-synced from network',
                                'status' => 'active',
                                'used_count' => $item['orders'] ?? 0,
                            ]
                        );
                        $processed['coupons']++;
                        
                        // Create purchase record
                        Purchase::create([
                            'coupon_id' => $coupon->id,
                            'campaign_id' => $campaign->id,
                            'network_id' => $networkId,
                            'user_id' => $userId,
                            'order_id' => $item['order_id'] ?? null,
                            'network_order_id' => $item['network_order_id'] ?? null,
                            'order_value' => $item['order_value'],
                            'commission' => $item['commission'],
                            'revenue' => $item['revenue'],
                            'quantity' => $item['quantity'],
                            'currency' => 'USD',
                            'country_code' => $country->code,
                            'customer_type' => $item['customer_type'],
                            'status' => $item['status'],
                            'order_date' => $item['order_date'],
                            'purchase_date' => $item['purchase_date'],
                        ]);
                        $processed['purchases']++;
                        
                    } catch (\Exception $e) {
                        $processed['errors'][] = [
                            'campaign' => $item['campaign_name'] ?? 'Unknown',
                            'error' => $e->getMessage()
                        ];
                        Log::error('Error processing coupon data: ' . $e->getMessage());
                    }
                }
            }, 2);
            
            return [
                'success' => true,
                'processed' => $processed
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in processCouponData: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'processed' => $processed
            ];
        }
    }
    
    /**
     * Normalize Boostiny data format
     */
    private static function normalizeBoostinyData(array $item): array
    {
        return [
            'campaign_id' => $item['campaign_id'] ?? null,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => $item['campaign_logo'] ?? null,
            'code' => $item['code'] ?? null,
            'country' => $item['country'] ?? 'NA',
            'order_id' => $item['order_id'] ?? null,
            'network_order_id' => $item['network_order_id'] ?? null,
            'order_value' => $item['sales_amount_usd'] ?? 0,
            'commission' => $item['revenue'] ?? 0,
            'revenue' => $item['revenue'] ?? 0,
            'quantity' => $item['orders'] ?? 1,
            'customer_type' => strtolower(trim($item['customer_type'] ?? 'unknown')),
            'status' => 'approved',
            'order_date' => $item['date'] ?? now()->format('Y-m-d'),
            'purchase_date' => $item['last_updated_at'] ?? now()->format('Y-m-d'),
        ];
    }
    
    /**
     * Normalize Digizag data format
     */
    private static function normalizeDigizagData(array $item): array
    {
        $stat = $item['Stat'] ?? [];
        $offer = $item['Offer'] ?? [];
        
        return [
            'campaign_id' => $stat['offer_id'] ?? null,
            'campaign_name' => $offer['name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $stat['affiliate_info1'] ?? 'NA',
            'country' => 'NA',
            'order_id' => $stat['id'] ?? null,
            'network_order_id' => $stat['id'] ?? null,
            'order_value' => $stat['conversion_sale_amount'] ?? 0,
            'commission' => $stat['payout'] ?? 0,
            'revenue' => $stat['payout'] ?? 0,
            'quantity' => 1,
            'customer_type' => 'unknown',
            'status' => $stat['conversion_status'] ?? 'approved',
            'order_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'),
            'purchase_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'),
        ];
    }
    
    /**
     * Normalize Platformance data format
     */
    private static function normalizePlatformanceData(array $item): array
    {
        return [
            'campaign_id' => $item['campaign_id'] ?? null,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $item['code'] ?? 'NA',
            'country_code' => $item['country'] ?? 'NA',
            'order_id' => $item['order_id'] ?? null,
            'network_order_id' => $item['network_order_id'] ?? null,
            'order_value' => $item['order_value'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'revenue' => $item['revenue'] ?? 0,
            'quantity' => $item['quantity'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'unknown',
            'status' => $item['status'] ?? 'approved',
            'order_date' => $item['order_date'] ?? now()->format('Y-m-d'),
            'purchase_date' => $item['purchase_date'] ?? now()->format('Y-m-d'),
        ];
    }
    
    /**
     * Normalize OptimiseMedia data to standard format
     */
    private static function normalizeOptimiseMediaData(array $item): array
    {
        // Calculate total conversions count
        $countOrders = ($item['pendingConversions'] ?? 0) + 
                      ($item['validatedConversions'] ?? 0) + 
                      ($item['rejectedConversions'] ?? 0);
        
        // Calculate total revenue (commission)
        $rejectedCommission = $item['rejectedCommission'] ?? 0;
        $pendingCommission = $item['pendingCommission'] ?? 0;
        $validatedCommission = $item['validatedCommission'] ?? 0;
        $revenue = $rejectedCommission + $pendingCommission + $validatedCommission;
        
        // Use 'US' as default if countryCode is '-' or missing
        $countryCode = ($item['countryCode'] && $item['countryCode'] !== '-') 
            ? $item['countryCode'] 
            : 'US';
        
        // Determine status based on commission type
        $status = 'pending';
        if ($validatedCommission > 0) {
            $status = 'approved';
        } elseif ($rejectedCommission > 0) {
            $status = 'rejected';
        }
        
        return [
            'campaign_id' => $item['advertiserId'] ?? null,
            'campaign_name' => $item['advertiserName'] ?? 'Unknown',
            'campaign_logo' => 'https://www.optimisemedia.com/assets/icons/logo-circle.svg',
            'code' => $item['voucherCode'] ?? 'NA',
            'country_code' => $countryCode,
            'order_id' => null, // OptimiseMedia doesn't provide individual order IDs
            'network_order_id' => null,
            'order_value' => $item['originalOrderValue'] ?? 0,
            'commission' => $revenue,
            'revenue' => $revenue,
            'quantity' => $countOrders > 0 ? $countOrders : 1,
            'customer_type' => strtolower(trim($item['campaignName'] ?? 'unknown')),
            'status' => $status,
            'order_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : now()->format('Y-m-d'),
            'purchase_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : now()->format('Y-m-d'),
        ];
    }
    
    /**
     * Process link performance data from network
     */
    public static function processLinkData(array $data, int $networkId, int $userId, string $startDate, string $endDate): array
    {
        $processed = [
            'campaigns' => 0,
            'purchases' => 0,
            'errors' => []
        ];
        
        try {
            DB::transaction(function () use ($data, $networkId, $userId, $startDate, $endDate, &$processed) {
                foreach ($data as $item) {
                    try {
                        // Get campaign name (could be campaign_networks or campaign_name)
                        $campaignName = $item['campaign_networks'] ?? $item['campaign_name'] ?? 'Unknown Campaign';
                        
                        // Create or update campaign
                        $campaign = Campaign::updateOrCreate(
                            [
                                'network_id' => $networkId,
                                'user_id' => $userId,
                                'network_campaign_id' => $item['campaign_id'] ?? null,
                            ],
                            [
                                'name' => $campaignName,
                                'campaign_type' => 'link',
                                'status' => 'active',
                            ]
                        );
                        $processed['campaigns']++;
                        
                        // Create purchase record for link
                        Purchase::create([
                            'campaign_id' => $campaign->id,
                            'network_id' => $networkId,
                            'user_id' => $userId,
                            'order_value' => $item['sales_amount_usd'] ?? 0,
                            'commission' => $item['revenue'] ?? 0,
                            'revenue' => $item['revenue'] ?? 0,
                            'quantity' => $item['orders'] ?? 1,
                            'currency' => 'USD',
                            'country_code' => 'NA',
                            'status' => 'approved',
                            'order_date' => $startDate,
                            'purchase_date' => $startDate,
                            'metadata' => [
                                'traffic_source' => $item['traffic_source'] ?? null,
                                'sub_id' => is_numeric($item['traffic_source'] ?? null) ? $item['traffic_source'] : null,
                            ]
                        ]);
                        $processed['purchases']++;
                        
                    } catch (\Exception $e) {
                        $processed['errors'][] = [
                            'campaign' => $campaignName ?? 'Unknown',
                            'error' => $e->getMessage()
                        ];
                        Log::error('Error processing link data: ' . $e->getMessage());
                    }
                }
            }, 2);
            
            return [
                'success' => true,
                'processed' => $processed
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in processLinkData: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'processed' => $processed
            ];
        }
    }
}

