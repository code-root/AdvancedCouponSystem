<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Revolution\Google\Sheets\Facades\Sheets;

class OmolaatService extends BaseNetworkService
{
    protected string $networkName = 'Omolaat';

    protected array $requiredFields = [
        'mapping_sheet_id' => [
            'label' => 'Campaign Mapping Sheet ID',
            'type' => 'text',
            'required' => true,
            'placeholder' => '198PFR-xO0QESiCNuFvH84rLgZCqEPoWeB4uy8ysWlo4',
            'help' => 'Google Sheets ID for Arabic to English campaign name mapping - Share with: swaqny@swaqnydeveloper.iam.gserviceaccount.com',
        ],
    ];

    /**
     * Campaign name mapping from Arabic to English
     * Can be loaded from Google Sheets or static array
     */
    protected array $campaignNameMapping = [
        'الحواج للصناعات الغذائية' => 'Alhawwaj',
        'الحواج' => 'Alhawwaj',
        'Eseven Store' => 'Eseven',
        'فيافي ديرتي' => 'Fayafi Dirati',
        'الركن السويسري' => 'Swiss Corner',
        'ريفال' => 'Ryefal',
        'شيفت' => 'Shift',
        'ريبون العالمية للتجارة' => 'Rebune',
        'فون زون' => 'Phone Zone',
        'سيزون - SEASON' => 'Season',
        'البارو - Albaroo' => 'Albaroo',
        'متجر تام' => 'Taam',
        'تريجر ايلاند' => 'Treasure Island',
        'مزيد' => 'Mazeed',
        'WADI' => 'WADI',
        'X44' => 'X44',
    ];

    /**
     * Fetch data from Omolaat (accepts data from Chrome Extension)
     * This service is primarily used via API endpoint to receive extension data
     */
    public function fetchData($connection, Carbon $startDate, Carbon $endDate): array
    {
        // Omolaat uses Chrome Extension to send data
        // Data is received via API endpoint: /api/networks/omolaat/receive-data
        // This method returns empty as data comes from external source
        
        Log::info("Omolaat: Data is pushed from Chrome Extension, not pulled from API");
        
        return [];
    }

    /**
     * Process data received from Chrome Extension
     */
    public function processExtensionData(array $extensionData, $connection): array
    {
        try {
            $processedData = [];
            $orders = $extensionData['orders'] ?? [];

            // Load campaign name mapping
            $mapping = $this->fetchCampaignNamesFromSheets($connection);

            foreach ($orders as $order) {
                $processedOrder = $this->processOrderData($order, $mapping);
                
                if ($processedOrder !== null) {
                    $processedData[] = $processedOrder;
                }
            }

            return $processedData;

        } catch (\Exception $e) {
            Log::error("Omolaat: Error processing extension data: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Process individual order from extension
     */
    protected function processOrderData(array $order, array $mapping): ?array
    {
        // Validate required fields
        if (empty($order['posOrderId']) && empty($order['orderId'])) {
            return null;
        }

        // Extract and clean store name
        $arabicStoreName = $this->extractAndCleanStoreName($order['storeName'] ?? '');
        
        // Convert to English
        $englishCampaignName = $this->convertCampaignName($arabicStoreName, $mapping);
        
        $transactionId = $order['orderId'] ?? $order['posOrderId'] ?? '';
        
        if (empty($transactionId)) {
            return null;
        }

        return [
            'campaign_name' => $englishCampaignName,
            'coupon_code' => $order['marketingCode'] ?? '',
            'country' => 'KSA',
            'sale_amount' => $this->processExtensionNumber($order['orderAmount'] ?? 0) / 3.75, // AED to SAR
            'commission' => $this->processExtensionNumber($order['commission'] ?? 0) / 3.75,
            'clicks' => 0,
            'conversions' => 1,
            'customer_type' => 'new',
            'transaction_id' => $transactionId,
            'date' => $this->parseOrderDate($order['orderDate'] ?? ''),
            'status' => 'approved',
        ];
    }

    /**
     * Fetch campaign name mapping from Google Sheets
     */
    protected function fetchCampaignNamesFromSheets($connection): array
    {
        $cacheKey = 'omolaat_campaign_mapping_v2';
        $cachedData = Cache::get($cacheKey);
        
        if ($cachedData && is_array($cachedData)) {
            return $cachedData;
        }

        try {
            // Google Sheets ID from credentials
            $sheetId = $connection->credentials['mapping_sheet_id'] ?? '198PFR-xO0QESiCNuFvH84rLgZCqEPoWeB4uy8ysWlo4';
            $sheetName = '0';
            
            $values = Sheets::spreadsheet($sheetId)->sheet($sheetName)->all();
            
            if (empty($values)) {
                return $this->campaignNameMapping;
            }

            $mapping = [];
            $headerSkipped = false;

            foreach ($values as $row) {
                if (empty($row) || !is_array($row)) {
                    continue;
                }

                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }

                if (count($row) >= 2 && isset($row[0]) && isset($row[1])) {
                    $arabicName = trim($row[0] ?? '');
                    $englishName = trim($row[1] ?? '');
                    
                    if (!empty($arabicName) && !empty($englishName)) {
                        $mapping[$arabicName] = $englishName;
                    }
                }
            }

            // Cache for 1 hour
            Cache::put($cacheKey, $mapping, 3600);
            
            return $mapping;

        } catch (\Exception $e) {
            Log::error("Failed to fetch Omolaat campaign mapping: {$e->getMessage()}");
            return $this->campaignNameMapping;
        }
    }

    /**
     * Clean store name
     */
    protected function extractAndCleanStoreName(string $storeName): string
    {
        if (empty($storeName)) {
            return 'Unknown Store';
        }

        $cleanName = strip_tags($storeName);
        $cleanName = preg_replace('/\s+/', ' ', trim($cleanName));
        $cleanName = preg_replace('/^(اسم المتجر|اسم|متجر|store|Store)\s*:?\s*/i', '', $cleanName);
        $cleanName = preg_replace('/\s*(store|Store|متجر)$/i', '', $cleanName);
        $cleanName = preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $cleanName);
        $cleanName = trim($cleanName);

        return empty($cleanName) ? trim($storeName) : $cleanName;
    }

    /**
     * Convert Arabic campaign name to English
     */
    protected function convertCampaignName(string $arabicName, array $mapping): string
    {
        $cleanName = trim($arabicName);
        
        if (empty($cleanName)) {
            return 'Unknown Store';
        }

        // Direct match
        if (isset($mapping[$cleanName])) {
            return $mapping[$cleanName];
        }

        // Case-insensitive match
        $lowerCleanName = mb_strtolower($cleanName, 'UTF-8');
        foreach ($mapping as $arabic => $english) {
            if (mb_strtolower($arabic, 'UTF-8') === $lowerCleanName) {
                return $english;
            }
        }

        // Partial matching
        foreach ($mapping as $arabic => $english) {
            if (strpos($cleanName, $arabic) !== false || strpos($arabic, $cleanName) !== false) {
                return $english;
            }
        }

        // Return original if no mapping found
        return $cleanName;
    }

    /**
     * Parse Arabic date format
     */
    protected function parseOrderDate(string $dateString): string
    {
        if (empty($dateString)) {
            return now()->format('Y-m-d');
        }

        $arabicMonths = [
            'يناير' => 'January', 'فبراير' => 'February', 'مارس' => 'March',
            'أبريل' => 'April', 'مايو' => 'May', 'يونيو' => 'June',
            'يوليو' => 'July', 'أغسطس' => 'August', 'سبتمبر' => 'September',
            'أكتوبر' => 'October', 'نوفمبر' => 'November', 'ديسمبر' => 'December'
        ];

        try {
            $englishDateString = strtr($dateString, $arabicMonths);
            return Carbon::parse($englishDateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    /**
     * Process number from extension
     */
    protected function processExtensionNumber($value): float
    {
        if (empty($value)) {
            return 0;
        }

        if (is_numeric($value)) {
            return floatval($value);
        }

        if (is_string($value)) {
            $cleanValue = preg_replace('/[^\d.,]/', '', $value);
            $cleanValue = str_replace(',', '', $cleanValue);
            
            if (is_numeric($cleanValue)) {
                return floatval($cleanValue);
            }
        }

        return 0;
    }

    /**
     * Transform Omolaat data to standard format
     */
    protected function transformData(array $item, $connection): array
    {
        return [
            'network_id' => $connection->network_id,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_external_id' => null,
            'coupon_code' => $item['coupon_code'] ?? '',
            'purchase_type' => 'coupon', // Omolaat is typically coupon-based
            'country' => $item['country'] ?? 'KSA',
            'sale_amount' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'clicks' => $item['clicks'] ?? 0,
            'conversions' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'new',
            'transaction_id' => $item['transaction_id'] ?? '',
            'date' => $item['date'] ?? now()->format('Y-m-d'),
            'status' => $item['status'] ?? 'approved',
        ];
    }

    /**
     * Test Omolaat connection
     */
    public function testConnection(array $credentials): array
    {
        try {
            $sheetId = $credentials['mapping_sheet_id'] ?? '198PFR-xO0QESiCNuFvH84rLgZCqEPoWeB4uy8ysWlo4';
            $sheets = Sheets::spreadsheet($sheetId)->sheetList();

            if (!empty($sheets)) {
                return ['success' => true, 'message' => 'Mapping sheet accessible', 'data' => ['sheets' => $sheets]];
            }

            return ['success' => false, 'message' => 'Cannot access mapping sheet', 'data' => null];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Sync data from Omolaat (Extension-based)
     */
    public function syncData(array $credentials, array $config = []): array
    {
        return [
            'success' => true,
            'message' => 'Omolaat uses Chrome Extension - data is pushed, not pulled',
            'data' => [],
            'count' => 0,
        ];
    }
    
    /**
     * Validate Omolaat connection
     */
    public function validateConnection($connection): bool
    {
        // Omolaat uses Chrome Extension, so validate Google Sheets access for mapping
        try {
            $sheetId = $connection->credentials['mapping_sheet_id'] ?? '198PFR-xO0QESiCNuFvH84rLgZCqEPoWeB4uy8ysWlo4';
            $sheets = Sheets::spreadsheet($sheetId)->sheetList();

            return !empty($sheets);

        } catch (\Exception $e) {
            Log::error("Omolaat connection validation failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Convert string to decimal
     */
    protected function convertToDecimal($value): float
    {
        if (is_numeric($value)) {
            return floatval($value);
        }

        $cleaned = preg_replace('/[^\d.,\-]/', '', $value);
        $cleaned = str_replace(',', '', $cleaned);

        return is_numeric($cleaned) ? floatval($cleaned) : 0;
    }
}

