<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Log;
use Revolution\Google\Sheets\Facades\Sheets;

class MediaMakService extends BaseNetworkService
{
    protected string $networkName = 'MediaMak';

    protected array $requiredFields = [
        'api_key' => [
            'label' => 'Google Sheets ID',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'Enter Google Sheets Spreadsheet ID',
            'help' => 'Share sheet with: swaqny@swaqnydeveloper.iam.gserviceaccount.com',
        ],
    ];

    /**
     * Fetch data from Google Sheets for MediaMak
     */
    public function fetchData($connection, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $spreadsheetId = $connection->api_key;
            
            if (empty($spreadsheetId)) {
                throw new \Exception("Google Sheets ID not configured");
            }

            // Get all sheets
            $sheetTitles = Sheets::spreadsheet($spreadsheetId)->sheetList();
            
            if (empty($sheetTitles)) {
                return [];
            }

            $allResults = [];

            foreach ($sheetTitles as $sheetTitle) {
                $values = Sheets::spreadsheet($spreadsheetId)->sheet($sheetTitle)->all();
                
                if (empty($values)) {
                    continue;
                }

                // Convert sheet title to date (format: "F j" like "January 1")
                try {
                    $dateObj = \DateTime::createFromFormat('F j', $sheetTitle);
                    $sheetDate = $dateObj ? $dateObj->format('Y-m-01') : null; // First day of month
                } catch (\Exception $e) {
                    $sheetDate = $startDate->format('Y-m-d');
                }

                $rowNumber = 0;

                foreach ($values as $row) {
                    $rowNumber++;
                    
                    // Skip header row
                    if ($rowNumber === 1) {
                        continue;
                    }

                    // Skip empty rows
                    if (empty($row[0])) {
                        continue;
                    }

                    // Columns: [0]=Campaign, [1]=Code, [2]=Orders, [3]=Revenue, [4]=SAU, [5]=Date
                    $campaignName = $row[0] ?? 'Unknown';
                    $couponCode = str_replace(' ', '', $row[1] ?? '');
                    $orders = str_replace(' ', '', $row[2] ?? 1);
                    $revenue = $this->convertToDecimal($row[3] ?? 0);
                    $saleAmount = $this->convertToDecimal($row[4] ?? 0);
                    
                    // Parse last_at date from column 5
                    $lastAt = $sheetDate;
                    if (isset($row[5]) && !empty($row[5])) {
                        try {
                            $lastAtObj = \DateTime::createFromFormat('m/d/Y', $row[5]);
                            $lastAt = $lastAtObj ? $lastAtObj->format('Y-m-d') : $sheetDate;
                        } catch (\Exception $e) {
                            // Keep sheet date
                        }
                    }

                    $allResults[] = [
                        'campaign_id' => $campaignName,
                        'campaign_name' => $campaignName,
                        'coupon_code' => $couponCode,
                        'country' => 'N/A',
                        'sale_amount' => $saleAmount,
                        'commission' => $revenue,
                        'clicks' => 0,
                        'conversions' => intval($orders),
                        'customer_type' => 'N/A',
                        'transaction_id' => '',
                        'date' => $sheetDate,
                        'last_at' => $lastAt,
                        'status' => 'approved',
                    ];
                }
            }

            return $allResults;

        } catch (\Exception $e) {
            Log::error("MediaMak sheet parsing error: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Build query string
     */
    protected function buildQueryString(array $params): string
    {
        $parts = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $parts[] = urlencode($key) . '[]=' . urlencode($item);
                }
            } else {
                $parts[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        return implode('&', $parts);
    }

    /**
     * Transform MediaMak data to standard format
     */
    protected function transformData(array $item, $connection): array
    {
        return [
            'network_id' => $connection->network_id,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_external_id' => $item['campaign_id'] ?? null,
            'coupon_code' => $item['coupon_code'] ?? '',
            'purchase_type' => 'coupon', // MediaMak is typically coupon-based
            'country' => $item['country'] ?? 'N/A',
            'sale_amount' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'clicks' => $item['clicks'] ?? 0,
            'conversions' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'N/A',
            'transaction_id' => $item['transaction_id'] ?? '',
            'date' => $item['date'] ?? now()->format('Y-m-d'),
            'status' => $item['status'] ?? 'approved',
        ];
    }

    /**
     * Test MediaMak connection
     */
    public function testConnection(array $credentials): array
    {
        try {
            $spreadsheetId = $credentials['api_key'];
            if (empty($spreadsheetId)) return ['success' => false, 'message' => 'Sheets ID required', 'data' => null];
            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();
            if (!empty($sheets)) return ['success' => true, 'message' => 'Connection successful', 'data' => ['sheets' => $sheets]];
            return ['success' => false, 'message' => 'No sheets found', 'data' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Sync data from MediaMak
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $startDate = \Carbon\Carbon::parse($config['date_from'] ?? now()->startOfMonth());
            $endDate = \Carbon\Carbon::parse($config['date_to'] ?? now());
            $connection = new \stdClass();
            $connection->id = 0;
            $connection->network_id = 0;
            $connection->api_key = $credentials['api_key'];
            $connection->credentials = [];
            $data = $this->fetchData((object)$connection, $startDate, $endDate);
            return ['success' => true, 'message' => 'Data synced successfully', 'data' => $data, 'count' => count($data)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
    }
    
    /**
     * Validate MediaMak connection (Google Sheets)
     */
    public function validateConnection($connection): bool
    {
        try {
            $spreadsheetId = $connection->api_key;
            
            if (empty($spreadsheetId)) {
                return false;
            }

            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();

            return !empty($sheets);

        } catch (\Exception $e) {
            Log::error("MediaMak connection validation failed: {$e->getMessage()}");
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

