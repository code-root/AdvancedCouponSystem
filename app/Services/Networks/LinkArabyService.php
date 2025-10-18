<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Log;
use Revolution\Google\Sheets\Facades\Sheets;

class LinkArabyService extends BaseNetworkService
{
    protected string $networkName = 'LinkAraby';

    protected array $requiredFields = [
        'api_key' => [
            'label' => 'Google Sheets ID',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'Enter Google Sheets Spreadsheet ID',
            'help' => 'Example: 1abc123def456ghi789jkl - Share sheet with: swaqny@swaqnydeveloper.iam.gserviceaccount.com',
        ],
    ];

    /**
     * Fetch data from Google Sheets for LinkAraby
     */
    public function fetchData($connection, Carbon $startDate, Carbon $endDate): array
    {
        try {
            // Google Sheets ID stored in api_key
            $spreadsheetId = $connection->api_key;
            
            if (empty($spreadsheetId)) {
                throw new \Exception("Google Sheets ID not configured");
            }

            // Get sheet name based on year
            $sheetName = $startDate->year;

            // Fetch data from Google Sheets
            $values = Sheets::spreadsheet($spreadsheetId)->sheet($sheetName)->all();

            if (empty($values)) {
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

                // Columns: [0]=Campaign, [1]=SAU (AED), [3]=Revenue (AED), [5]=Date, [7]=Campaign Name, [10]=Customer Type
                $saleAmountAED = $this->convertToDecimal($row[1] ?? 0);
                $revenueAED = $this->convertToDecimal($row[0] ?? 0);

                // Convert from AED to SAR (1 AED = 1 SAR / 3.75)
                $saleAmount = $saleAmountAED / 3.75;
                $revenue = $revenueAED / 3.75;

                $campaignName = $row[7] ?? 'Unknown Campaign';
                $customerType = $row[10] ?? 'N/A';
                $date = isset($row[5]) ? Carbon::parse($row[5])->format('Y-m-d') : $startDate->format('Y-m-d');

                $results[] = [
                    'campaign_id' => 'NA',
                    'campaign_name' => $campaignName,
                    'coupon_code' => '', // No coupon code for link-based campaigns
                    'country' => 'N/A',
                    'sale_amount' => $saleAmount,
                    'revenue' => $revenue,
                    'clicks' => 1,
                    'conversions' => 1,
                    'customer_type' => $customerType,
                    'transaction_id' => $row[2] ?? '', // Transaction ID from column 2
                    'date' => $date,
                    'status' => 'approved',
                    'is_link_campaign' => true, // Mark as link campaign
                ];
            }

            return $results;

        } catch (\Exception $e) {
            Log::error("LinkAraby API Error: {$e->getMessage()}", [
                'connection_id' => $connection->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \Exception("Failed to fetch data from LinkAraby: {$e->getMessage()}");
        }
    }

    /**
     * Transform LinkAraby data to standard format
     */
    protected function transformData(array $item, $connection): array
    {
        return [
            'network_id' => $connection->network_id,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_external_id' => $item['campaign_id'] ?? 'NA',
            'coupon_code' => $item['coupon_code'] ?? '',
            'purchase_type' => 'coupon', // LinkAraby is typically coupon-based
            'country' => $item['country'] ?? 'N/A',
            'sale_amount' => $item['sale_amount'] ?? 0,
            'revenue' => $item['revenue'] ?? 0,
            'clicks' => $item['clicks'] ?? 1,
            'conversions' => $item['conversions'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'N/A',
            'transaction_id' => $item['transaction_id'] ?? '',
            'date' => $item['date'] ?? now()->format('Y-m-d'),
            'status' => $item['status'] ?? 'approved',
        ];
    }

    /**
     * Test LinkAraby connection
     */
    public function testConnection(array $credentials): array
    {
        try {
            $spreadsheetId = $credentials['api_key'];
            
            if (empty($spreadsheetId)) {
                return [
                    'success' => false,
                    'message' => 'Google Sheets ID is required',
                    'data' => null,
                ];
            }

            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();

            if (!empty($sheets)) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => ['sheets' => $sheets],
                ];
            }

            return [
                'success' => false,
                'message' => 'No sheets found',
                'data' => null,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Sync data from LinkAraby
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

            return [
                'success' => true,
                'message' => 'Data synced successfully',
                'data' => $data,
                'count' => count($data),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
    
    /**
     * Validate LinkAraby connection (Google Sheets)
     */
    public function validateConnection($connection): bool
    {
        try {
            $spreadsheetId = $connection->api_key;
            
            if (empty($spreadsheetId)) {
                return false;
            }

            // Try to fetch sheet list
            $sheets = Sheets::spreadsheet($spreadsheetId)->sheetList();

            return !empty($sheets);

        } catch (\Exception $e) {
            Log::error("LinkAraby connection validation failed: {$e->getMessage()}");
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

        // Remove currency symbols and spaces
        $cleaned = preg_replace('/[^\d.,\-]/', '', $value);
        
        // Handle Arabic/English number formats
        $cleaned = str_replace(',', '', $cleaned);

        return is_numeric($cleaned) ? floatval($cleaned) : 0;
    }
}

