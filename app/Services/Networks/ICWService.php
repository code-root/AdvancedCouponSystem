<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Log;
use Revolution\Google\Sheets\Facades\Sheets;

class ICWService extends BaseNetworkService
{
    protected string $networkName = 'ICW';

    protected array $requiredFields = [
        'api_key' => [
            'label' => 'Google Sheets ID',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'Enter Google Sheets Spreadsheet ID',
            'help' => 'Share sheet with: swaqny@swaqnydeveloper.iam.gserviceaccount.com (Editor permission)',
        ],
    ];

    /**
     * Fetch data from Google Sheets for ICW
     */
    public function fetchData($connection, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $spreadsheetId = $connection->api_key;
            
            if (empty($spreadsheetId)) {
                throw new \Exception("Google Sheets ID not configured");
            }

            // Get month string for sheet name (e.g., "January 1" from date)
            $dateString = $this->getDateString($startDate);
            $values = Sheets::spreadsheet($spreadsheetId)->sheet($dateString)->all();

            if (empty($values) || count($values) === 0) {
                return [];
            }

            $results = [];
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

                // Check if row has required data
                if (!empty($row[2]) || !empty($row[3]) || !empty($row[1])) {
                    $campaignName = $row[0] ?? 'Unknown';
                    $couponCode = $row[1] ?? 'NA';
                    $orders = intval($row[2] ?? 1);
                    $revenue = $this->convertToDecimal($row[3] ?? 0);
                    $saleAmount = $this->convertToDecimal($row[4] ?? 0);
                    
                    // Parse date from column 5, fallback to startDate
                    $date = $startDate->format('Y-m-d');
                    if (isset($row[5]) && !empty($row[5])) {
                        try {
                            $date = Carbon::parse($row[5])->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Keep default date
                        }
                    }

                    $results[] = [
                        'campaign_id' => $rowNumber,
                        'campaign_name' => $campaignName,
                        'coupon_code' => $couponCode,
                        'purchase_type' => 'coupon', // ICW is typically coupon-based
                        'country' => 'N/A',
                        'sale_amount' => $saleAmount,
                        'revenue' => $revenue,
                        'clicks' => 0,
                        'conversions' => $orders,
                        'customer_type' => 'N/A',
                        'transaction_id' => '',
                        'date' => $date,
                        'status' => 'approved',
                    ];
                }
            }

            return $results;

        } catch (\Exception $e) {
            Log::error("ICW API Error: {$e->getMessage()}", [
                'connection_id' => $connection->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \Exception("Failed to fetch data from ICW: {$e->getMessage()}");
        }
    }

    /**
     * Get sheet name from date (e.g., "January 1")
     */
    protected function getDateString(Carbon $date): string
    {
        return $date->format('F j');
    }

    /**
     * Transform ICW data to standard format
     */
    protected function transformData(array $item, $connection): array
    {
        return [
            'network_id' => $connection->network_id,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_external_id' => $item['campaign_id'] ?? null,
            'coupon_code' => $item['coupon_code'] ?? 'NA',
            'country' => $item['country'] ?? 'N/A',
            'sale_amount' => $item['sale_amount'] ?? 0,
            'revenue' => $item['revenue'] ?? 0,
            'clicks' => $item['clicks'] ?? 0,
            'conversions' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'N/A',
            'transaction_id' => $item['transaction_id'] ?? '',
            'date' => $item['date'] ?? now()->format('Y-m-d'),
            'status' => $item['status'] ?? 'approved',
        ];
    }

    /**
     * Test ICW connection
     */
    public function testConnection(array $credentials): array
    {
        try {
            $spreadsheetId = $credentials['api_key'];
            
            if (empty($spreadsheetId)) {
                return ['success' => false, 'message' => 'Google Sheets ID required', 'data' => null];
            }

            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();

            if (!empty($sheets)) {
                return ['success' => true, 'message' => 'Connection successful', 'data' => ['sheets' => $sheets]];
            }

            return ['success' => false, 'message' => 'No sheets found', 'data' => null];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Sync data from ICW
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
     * Validate ICW connection (Google Sheets)
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
            Log::error("ICW connection validation failed: {$e->getMessage()}");
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

