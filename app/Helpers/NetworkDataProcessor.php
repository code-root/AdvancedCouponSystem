<?php

namespace App\Helpers;

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Country;
use App\Enums\OmolaatCampaigns;
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
                $deletedCount = Purchase::where('network_id', $networkId)
                    ->where('user_id', $userId)
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->delete();
                
                
                foreach ($data as $index => $item) {
                    try {
                        // Validate item structure before processing
                        if (!is_array($item) || empty($item)) {
                            $processed['errors'][] = "Invalid item structure at index $index";
                            continue;
                        }

                        // Handle different network formats
                        if ($networkName === 'digizag') {
                            $item = self::normalizeDigizagData($item);
                        } elseif ($networkName === 'globalnetwork') {
                            $item = self::normalizeGlobalNetworkData($item);
                        } elseif ($networkName === 'arabclicks') {
                            $item = self::normalizeArabclicksData($item);
                        } elseif ($networkName === 'linkaraby') {
                            $item = self::normalizeLinkArabyData($item);
                        } elseif ($networkName === 'icw') {
                            $item = self::normalizeICWData($item);
                        } elseif ($networkName === 'mediamak') {
                            $item = self::normalizeMediaMakData($item);
                        } elseif ($networkName === 'marketeers') {
                            $item = self::normalizeMarketeersData($item);
                        } elseif ($networkName === 'cpx') {
                            $item = self::normalizeCPXData($item);
                        } elseif ($networkName === 'platformance') {
                            $item = self::normalizePlatformanceData($item);
                        } elseif ($networkName === 'globalemedia') {
                            $item = self::normalizePlatformanceData($item); // Same format as Platformance
                        } elseif ($networkName === 'optimisemedia') {
                            $item = self::normalizeOptimiseMediaData($item);
                        } elseif ($networkName === 'omolaat') {
                            $item = self::normalizeOmolaatData($item);
                        } else {
                            $item = self::normalizeBoostinyData($item);
                        }

                        // Validate normalized data
                        if (!self::validateNormalizedData($item, $index)) {
                            $processed['errors'][] = "Invalid normalized data at index $index";
                            continue;
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
                            'purchase_type' => $item['purchase_type'] ?? 'coupon', // Use purchase_type from data
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
                        $errorMessage = "Error processing item at index $index: " . $e->getMessage();
                        $processed['errors'][] = [
                            'index' => $index,
                            'campaign' => $item['campaign_name'] ?? 'Unknown',
                            'error' => $e->getMessage()
                        ];
                        
                        Log::error('Error processing coupon data', [
                            'index' => $index,
                            'item' => $item,
                            'network' => $networkName,
                            'user_id' => $userId,
                            'network_id' => $networkId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        // Continue processing other items instead of failing completely
                        continue;
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
        $orderDate = isset($item['date']) ? self::formatDateYmd($item['date']) : now()->format('Y-m-d');
        $purchaseDate = isset($item['last_updated_at']) ? self::formatDateYmd($item['last_updated_at']) : $orderDate;

        // Generate a unique campaign_id if not provided
        $campaignId = $item['campaign_id'] ?? null;
        if (empty($campaignId)) {
            // Use campaign_name to generate a consistent ID
            $campaignName = $item['campaign_name'] ?? 'Unknown';
            $campaignId = 'BOOSTINY_' . md5($campaignName);
        }

        return [
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_id' => $campaignId,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => $item['campaign_logo'] ?? null,
            'code' => $item['code'] ?? null,
            'country' => $item['country'] ?? 'NA',
            'country_code' => strtoupper($item['country'] ?? 'NA'),
            'order_id' => $item['order_id'] ?? null,
            'network_order_id' => $item['network_order_id'] ?? null,
            'order_value' => $item['sales_amount_usd'] ?? 0,
            'commission' => $item['revenue'] ?? 0,
            'revenue' => $item['revenue'] ?? 0,
            'quantity' => $item['orders'] ?? 1,
            'customer_type' => strtolower(trim($item['customer_type'] ?? 'unknown')),
            'status' => 'approved',
            'order_date' => $orderDate, // تاريخ الطلب من API
            'purchase_date' => $purchaseDate, // تاريخ آخر تحديث
        ];
    }

    /**
     * Format any date/time string to Y-m-d safely
     */
    private static function formatDateYmd($value): string
    {
        if (empty($value)) {
            return now()->format('Y-m-d');
        }
        // Handle timestamps or string dates
        if (is_numeric($value)) {
            $ts = (int)$value;
            // If value looks like milliseconds, convert
            if ($ts > 2000000000) { // larger than year ~2033 seconds
                $ts = (int) round($ts / 1000);
            }
            return date('Y-m-d', $ts) ?: now()->format('Y-m-d');
        }
        $ts = strtotime((string)$value);
        if ($ts === false) {
            return now()->format('Y-m-d');
        }
        return date('Y-m-d', $ts);
    }
    
    /**
     * Normalize Digizag data format
     */
    private static function normalizeDigizagData(array $item): array
    {
        $stat = $item['Stat'] ?? [];
        $offer = $item['Offer'] ?? [];
        
        // Generate a unique campaign_id if not provided
        $campaignId = $stat['offer_id'] ?? null;
        if (empty($campaignId)) {
            $campaignName = $offer['name'] ?? 'Unknown';
            $campaignId = 'DIGIZAG_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
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
            'order_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'), // تاريخ العملية من API
            'purchase_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize GlobalNetwork data format (HasOffers - same as Digizag)
     */
    private static function normalizeGlobalNetworkData(array $item): array
    {
        $stat = $item['Stat'] ?? [];
        $offer = $item['Offer'] ?? [];
        
        // Generate a unique campaign_id if not provided
        $campaignId = $stat['offer_id'] ?? null;
        if (empty($campaignId)) {
            $campaignName = $offer['name'] ?? 'Unknown';
            $campaignId = 'GLOBALNETWORK_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $offer['name'] ?? 'Unknown',
            'campaign_logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTdzx401xR8M7OJQ5ptWLX6p1LhUoOq7iEgWw&usqp=CAU',
            'code' => $stat['promo_code'] ?? ($stat['affiliate_info1'] ?? 'NA'),
            'country_code' => 'NA',
            'order_id' => $stat['id'] ?? null,
            'network_order_id' => $stat['id'] ?? null,
            'order_value' => $stat['conversion_sale_amount'] ?? 0,
            'commission' => $stat['payout'] ?? 0,
            'revenue' => $stat['payout'] ?? 0,
            'quantity' => 1,
            'customer_type' => 'unknown',
            'status' => $stat['conversion_status'] ?? 'approved',
            'order_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'), // تاريخ العملية من API
            'purchase_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize Arabclicks data format (HasOffers - same as Digizag/GlobalNetwork)
     */
    private static function normalizeArabclicksData(array $item): array
    {
        $stat = $item['Stat'] ?? [];
        $offer = $item['Offer'] ?? [];
        
        // Generate a unique campaign_id if not provided
        $campaignId = $stat['offer_id'] ?? null;
        if (empty($campaignId)) {
            $campaignName = $offer['name'] ?? 'Unknown';
            $campaignId = 'ARABCLICKS_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $offer['name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $stat['affiliate_info1'] ?? $stat['affiliate_info5'] ?? 'NA',
            'country' => 'NA',
            'order_id' => $stat['id'] ?? null,
            'network_order_id' => $stat['id'] ?? null,
            'order_value' => $stat['conversion_sale_amount'] ?? 0,
            'commission' => $stat['payout'] ?? 0,
            'revenue' => $stat['payout'] ?? 0,
            'quantity' => 1,
            'customer_type' => 'unknown',
            'status' => $stat['conversion_status'] ?? 'approved',
            'order_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'), // تاريخ العملية من API
            'purchase_date' => isset($stat['datetime']) ? date('Y-m-d', strtotime($stat['datetime'])) : now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize LinkAraby data format (Google Sheets)
     */
    private static function normalizeLinkArabyData(array $item): array
    {
        // Generate a unique campaign_id if not provided
        $campaignId = $item['campaign_id'] ?? null;
        if (empty($campaignId) || $campaignId === 'NA') {
            $campaignName = $item['campaign_name'] ?? 'Unknown';
            $campaignId = 'LINKARABY_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $item['coupon_code'] ?? 'NA',
            'country' => $item['country'] ?? 'NA',
            'order_id' => $item['transaction_id'] ?? null,
            'network_order_id' => $item['transaction_id'] ?? null,
            'order_value' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'revenue' => $item['commission'] ?? 0,
            'quantity' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'unknown',
            'status' => $item['status'] ?? 'approved',
            'order_date' => $item['date'] ?? now()->format('Y-m-d'), // تاريخ الطلب من API
            'purchase_date' => $item['date'] ?? now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize ICW data format (Google Sheets)
     */
    private static function normalizeICWData(array $item): array
    {
        // Generate a unique campaign_id if not provided
        $campaignId = $item['campaign_id'] ?? null;
        if (empty($campaignId) || $campaignId === 'NA') {
            $campaignName = $item['campaign_name'] ?? 'Unknown';
            $campaignId = 'ICW_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $item['coupon_code'] ?? 'NA',
            'country' => $item['country'] ?? 'NA',
            'order_id' => $item['transaction_id'] ?? null,
            'network_order_id' => $item['transaction_id'] ?? null,
            'order_value' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'revenue' => $item['commission'] ?? 0,
            'quantity' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'unknown',
            'status' => $item['status'] ?? 'approved',
            'order_date' => $item['date'] ?? now()->format('Y-m-d'), // تاريخ الطلب من API
            'purchase_date' => $item['date'] ?? now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize MediaMak data format (Google Sheets)
     */
    private static function normalizeMediaMakData(array $item): array
    {
        // Generate a unique campaign_id if not provided
        $campaignId = $item['campaign_id'] ?? null;
        if (empty($campaignId) || $campaignId === 'NA') {
            $campaignName = $item['campaign_name'] ?? 'Unknown';
            $campaignId = 'MEDIAMAK_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $item['coupon_code'] ?? 'NA',
            'country' => $item['country'] ?? 'NA',
            'order_id' => $item['transaction_id'] ?? null,
            'network_order_id' => $item['transaction_id'] ?? null,
            'order_value' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'revenue' => $item['commission'] ?? 0,
            'quantity' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'unknown',
            'status' => $item['status'] ?? 'approved',
            'order_date' => $item['date'] ?? now()->format('Y-m-d'), // تاريخ الطلب من API
            'purchase_date' => $item['date'] ?? now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize Marketeers data format
     */
    private static function normalizeMarketeersData(array $item): array
    {
        $orderDate = self::formatDateYmd($item['order_date'] ?? null);
        
        return [
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_id' => $item['campaign_id'] ?? ($item['campaign']['id'] ?? null),
            'campaign_name' => $item['campaign_name'] ?? ($item['campaign']['title'] ?? 'Unknown'),
            'campaign_logo' => $item['campaign_logo'] ?? null,
            'code' => $item['code'] ?? ($item['coupon']['code'] ?? 'NA'),
            'country' => $item['country'] ?? ($item['country_code'] ?? 'NA'),
            'country_code' => strtoupper($item['country_code'] ?? ($item['country'] ?? 'NA')),
            'order_id' => $item['order_id'] ?? ($item['advertiser_order_id'] ?? null),
            'network_order_id' => $item['network_order_id'] ?? ($item['markteers_order_id'] ?? null),
            'order_value' => (float)($item['order_value'] ?? ($item['order_amount_usd'] ?? $item['order_amount'] ?? 0)),
            'commission' => (float)($item['commission'] ?? ($item['order_amount_usd'] ?? $item['order_amount'] ?? 0)),
            'revenue' => (float)($item['revenue'] ?? ($item['payout'] ?? $item['payout_usd'] ?? 0)),
            'quantity' => (int)($item['quantity'] ?? ($item['order_quantity'] ?? 1)),
            'customer_type' => strtolower(trim($item['customer_type'] ?? 'unknown')),
            'status' => strtolower(trim($item['status'] ?? 'approved')),
            'order_date' => $orderDate,
            'purchase_date' => $orderDate,
        ];
    }
    
    /**
     * Normalize CPX data format
     */
    private static function normalizeCPXData(array $item): array
    {
        // Generate a unique campaign_id if not provided
        $campaignId = $item['campaign_id'] ?? null;
        if (empty($campaignId) || $campaignId === 'NA') {
            $campaignName = $item['campaign_name'] ?? 'Unknown';
            $campaignId = 'CPX_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_logo' => null,
            'code' => $item['coupon_code'] ?? 'NA',
            'country' => $item['country'] ?? 'NA',
            'order_id' => $item['transaction_id'] ?? null,
            'network_order_id' => $item['transaction_id'] ?? null,
            'order_value' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'revenue' => $item['commission'] ?? 0,
            'quantity' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'unknown',
            'status' => $item['status'] ?? 'approved',
            'order_date' => $item['date'] ?? now()->format('Y-m-d'), // تاريخ الطلب من API
            'purchase_date' => $item['date'] ?? now()->format('Y-m-d'), // نفس التاريخ
        ];
    }
    
    /**
     * Normalize Platformance data format
     */
    private static function normalizePlatformanceData(array $item): array
    {
        // Generate a unique campaign_id if not provided
        $campaignId = $item['campaign_id'] ?? null;
        if (empty($campaignId)) {
            // Use campaign_name to generate a consistent ID
            $campaignName = $item['campaign_name'] ?? 'Unknown';
            $campaignId = 'PLATFORMANCE_' . md5($campaignName);
        }

        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
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
            'order_date' => $item['order_date'] ?? now()->format('Y-m-d'), // تاريخ الطلب من API
            'purchase_date' => $item['purchase_date'] ?? now()->format('Y-m-d'), // تاريخ الشراء من API
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
        
        // Generate a unique campaign_id if not provided
        $campaignId = $item['advertiserId'] ?? null;
        if (empty($campaignId)) {
            $campaignName = $item['advertiserName'] ?? 'Unknown';
            $campaignId = 'OPTIMISEMEDIA_' . md5($campaignName);
        }
        
        return [
            'campaign_id' => $campaignId,
            'purchase_type' => $item['purchase_type'] ?? 'coupon',
            'campaign_name' => $item['advertiserName'] ?? 'Unknown',
            'campaign_logo' => 'https://www.optimisemedia.com/assets/icons/logo-circle.svg',
            'code' => $item['voucherCode'] ?? 'NA',
            'country_code' => $countryCode,
            'order_id' => null, // OptimiseMedia doesn't provide individual order IDs
            'network_order_id' => null,
            'order_value' => $item['originalOrderValue'] ?? 0,
            'commission' => $revenue,
            'revenue' => $revenue,
            'quantity' => $countOrders,
            'customer_type' => strtolower(trim($item['campaignName'] ?? 'unknown')),
            'status' => $status,
            'order_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : now()->format('Y-m-d'), // تاريخ الطلب من API
            'purchase_date' => isset($item['date']) ? date('Y-m-d', strtotime($item['date'])) : now()->format('Y-m-d'), // نفس التاريخ
        ];
    }

    /**
     * Normalize Omolaat Elasticsearch hit to standard format
     * Improved date handling and data validation
     */
    private static function normalizeOmolaatData(array $hit): array
    {
        $src = $hit['_source'] ?? [];
        
        // Extract campaign ID from store_custom_services_stores and find campaign info
        $storeLookup = $src['store1_custom_services_stores'] ?? $src['store_custom_services_stores'] ?? null;
        $campaignId = self::extractCampaignIdFromLookup($storeLookup);
        $campaignName = 'Unknown';
        $campaignLogo = null;
        
        // Find campaign in enum if ID was extracted
        if ($campaignId) {
            $campaign = OmolaatCampaigns::findById($campaignId);
            if ($campaign) {
                $campaignName = $campaign->getClientName();
                $campaignLogo = $campaign->getLogoUrl();
            }
        }
        
        // Extract coupon code from sales_id_text (first part before first dash)
        $salesId = $src['sales_id_text'] ?? '';
        $code = self::extractCouponCode($salesId);

        // Improved date handling with validation
        $orderDateMs = $src['order_date_date'] ?? null;
        $createdDateMs = $src['Created Date'] ?? null;
        
        // Validate and convert dates
        $orderDate = self::convertOmolaatDate($orderDateMs);
        $purchaseDate = self::convertOmolaatDate($createdDateMs);

        // Validate numeric values
        $orderValue = self::validateNumericValue($src['order_amount_number'] ?? 0);
        $commission = self::validateNumericValue($src['affiliate_amount_number'] ?? 0);
        $quantity = self::validateIntegerValue($src['quantity_number'] ?? 1);

        return [
            'campaign_id' => is_string($campaignId) ? $campaignId : (string) ($campaignId ?? ''),
            'purchase_type' => 'coupon',
            'campaign_name' => is_string($campaignName) ? $campaignName : 'Unknown',
            'campaign_logo' => $campaignLogo,
            'code' => is_string($code) ? $code : 'NA',
            'country_code' => $src['country_text'] ?? 'NA',
            'order_id' => $hit['pos_order_id_text'] ?? null,
            'network_order_id' => $src['pos_order_id_text'] ?? null,
            'order_value' => $orderValue,
            'commission' => $commission,
            'revenue' => $commission, // Same as commission for Omolaat
            'quantity' => 1,
            'customer_type' => strtolower(trim((string) ($src['customer_type_text'] ?? 'unknown'))),
            'status' => (string) ($src['status_option_opt__order_status'] ?? 'approved'),
            'order_date' => $orderDate,
            'purchase_date' => $purchaseDate,
        ];
    }

    /**
     * Convert Omolaat date (milliseconds) to Y-m-d format with validation
     */
    private static function convertOmolaatDate($dateMs): string
    {
        if ($dateMs === null || $dateMs === '') {
            return now()->format('Y-m-d');
        }

        // Handle both string and numeric values
        $ms = is_numeric($dateMs) ? (int) $dateMs : 0;
        
        // Validate timestamp range (reasonable date range)
        if ($ms < 1000000000000) { // Less than year 2001
            return now()->format('Y-m-d');
        }
        
        if ($ms > 4102444800000) { // Greater than year 2100
            return now()->format('Y-m-d');
        }

        try {
            $timestamp = (int) ($ms / 1000);
            $date = date('Y-m-d', $timestamp);
            
            // Validate the generated date
            if ($date === '1970-01-01' || $date === false) {
                return now()->format('Y-m-d');
            }
            
            return $date;
        } catch (\Throwable $e) {
            Log::warning('Invalid Omolaat date conversion: ' . $e->getMessage(), ['dateMs' => $dateMs]);
            return now()->format('Y-m-d');
        }
    }

    /**
     * Validate and convert numeric value
     */
    private static function validateNumericValue($value): float
    {
        if (is_numeric($value)) {
            $floatValue = (float) $value;
            // Ensure reasonable range
            return max(0, min($floatValue, 999999999)); // Max 999M
        }
        return 0.0;
    }

    /**
     * Validate and convert integer value
     */
    private static function validateIntegerValue($value): int
    {
        if (is_numeric($value)) {
            $intValue = (int) $value;
            // Ensure reasonable range
            return max(1, min($intValue, 999999)); // Max 999K
        }
        return 1;
    }

    /**
     * Extract coupon code from sales_id_text (first part before first dash)
     * استخراج كود الكوبون من sales_id_text (الجزء الأول قبل الشرطة الأولى)
     * 
     * @param string $salesId The sales_id_text value
     * @return string The extracted coupon code
     */
    private static function extractCouponCode(string $salesId): string
    {
        if (empty($salesId)) {
            return 'NA';
        }

        // Split by dash and take the first part
        $parts = explode('-', $salesId, 2);
        $couponCode = trim($parts[0]);

        // Validate the extracted code
        if (empty($couponCode)) {
            return 'NA';
        }

     
        return $couponCode;
    }

    /**
     * Extract campaign ID from store lookup string
     * استخراج معرف الحملة من نص store_custom_services_stores
     * 
     * @param string|null $storeLookup The store lookup string
     * @return string|null The extracted campaign ID
     */
    private static function extractCampaignIdFromLookup(?string $storeLookup): ?string
    {
        if (empty($storeLookup)) {
            return null;
        }

        // Look for pattern: "1348695171700984260__LOOKUP__1747810598320x156968070750276060"
        if (preg_match('/__LOOKUP__(.+)$/', $storeLookup, $matches)) {
            $campaignId = trim($matches[1]);
            
            // Validate the extracted ID format (should contain 'x' and be reasonable length)
            if (!empty($campaignId) && strpos($campaignId, 'x') !== false && strlen($campaignId) > 10) {
                return $campaignId;
            }
        }

        return null;
    }

    /**
     * Validate normalized data structure
     */
    private static function validateNormalizedData(array $item, int $index): bool
    {
        $requiredFields = [
            'campaign_id',
            'campaign_name',
            'order_date',
            'purchase_date',
            'order_value',
            'commission',
            'revenue',
            'status'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($item[$field]) || $item[$field] === null || $item[$field] === '') {
                Log::warning("Missing required field '$field' in normalized data at index $index", [
                    'item' => $item,
                    'index' => $index
                ]);
                return false;
            }
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $item['order_date'])) {
            Log::warning("Invalid order_date format in normalized data at index $index", [
                'order_date' => $item['order_date'],
                'index' => $index
            ]);
            return false;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $item['purchase_date'])) {
            Log::warning("Invalid purchase_date format in normalized data at index $index", [
                'purchase_date' => $item['purchase_date'],
                'index' => $index
            ]);
            return false;
        }

        // Validate numeric values
        if (!is_numeric($item['order_value']) || $item['order_value'] < 0) {
            Log::warning("Invalid order_value in normalized data at index $index", [
                'order_value' => $item['order_value'],
                'index' => $index
            ]);
            return false;
        }

        if (!is_numeric($item['commission']) || $item['commission'] < 0) {
            Log::warning("Invalid commission in normalized data at index $index", [
                'commission' => $item['commission'],
                'index' => $index
            ]);
            return false;
        }

        return true;
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
                            'purchase_type' => $item['purchase_type'] ?? 'link', // Use purchase_type from data
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

