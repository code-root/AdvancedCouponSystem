<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GlobalNetworkService extends BaseNetworkService
{
    protected string $networkName = 'GlobalNetwork';
    
    protected array $requiredFields = [
        'api_key'
    ];
    
    protected array $defaultConfig = [
        'api_url' => 'https://globalnetwork1.api.hasoffers.com/Apiv3/json?Target=Affiliate_Report&Method=getConversions&page=1&limit=2000000&fields[]=Stat.offer_id&fields[]=Stat.datetime&fields[]=Offer.name&fields[]=Stat.conversion_status&fields[]=Stat.payout&fields[]=Stat.ad_id&fields[]=Stat.affiliate_info1&fields[]=Stat.affiliate_info5&sort[Stat.datetime]=desc&filters[Stat.date][conditional]=BETWEEN&hour_offset=5&fields[]=Stat.conversion_sale_amount',
        'timeout' => 60,
        'rate_limit' => 1000
    ];
    
    /**
     * Step 1: Test connection to GlobalNetwork API
     */
    public function testConnection(array $credentials): array
    {
        try {
            // Validate credentials first
            $validation = $this->validateCredentials($credentials);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials provided. Please provide your API Key.',
                    'errors' => $validation['errors'],
                ];
            }
            
            // Test API connection with date range
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            
            $testResult = $this->testApiConnection(
                $credentials['api_key'],
                $startDate,
                $endDate
            );
            
            if (!$testResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Connection failed: ' . $testResult['message'],
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Successfully connected to GlobalNetwork!',
                'data' => [
                    'status' => 'active',
                    'api_version' => 'v3',
                    'total_records' => $testResult['total_records'] ?? 0,
                    'date_range' => [
                        'from' => $startDate,
                        'to' => $endDate
                    ]
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('GlobalNetwork connection test failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Step 2: Test API connection with real request
     */
    private function testApiConnection(string $apiKey, string $startDate, string $endDate): array
    {
        try {
            $endpoint = $this->buildApiUrl($apiKey, $startDate, $endDate);
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($endpoint);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API request failed with status: ' . $response->status(),
                ];
            }
            
            $data = $response->json();
            
            // Check for API errors
            if (isset($data['response']['errors']) && !empty($data['response']['errors'])) {
                $errorMsg = $data['response']['errors']['publicMessage'] ?? 
                           $data['response']['errors']['message'] ?? 
                           'Unknown API error';
                
                return [
                    'success' => false,
                    'message' => $errorMsg,
                ];
            }
            
            // Check if response has expected data structure
            if (isset($data['response']['data']['data'])) {
                return [
                    'success' => true,
                    'total_records' => count($data['response']['data']['data']),
                ];
            }
            
            // Alternative structure
            if (isset($data['response']['data']) && is_array($data['response']['data'])) {
                $dataCount = is_array($data['response']['data']) ? count($data['response']['data']) : 0;
                return [
                    'success' => true,
                    'total_records' => $dataCount,
                ];
            }
            
            return [
                'success' => true,
                'total_records' => 0,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Step 3: Build API URL with parameters
     */
    private function buildApiUrl(string $apiKey, string $startDate, string $endDate): string
    {
        $baseUrl = $this->defaultConfig['api_url'];
        
        return $baseUrl . 
               '&api_key=' . $apiKey . 
               '&filters[Stat.date][values][]=' . $startDate . 
               '&filters[Stat.date][values][]=' . $endDate . 
               '&data_start=' . $startDate . 
               '&data_end=' . $endDate;
    }
    
    /**
     * Sync data from GlobalNetwork
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            // Extract dates from config
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            // Check if API key exists
            if (empty($credentials['api_key'])) {
                return [
                    'success' => false,
                    'message' => 'API Key is required. Please reconnect.',
                ];
            }
            
            // Fetch performance data
            $performanceData = $this->getPerformanceData(
                $credentials['api_key'],
                $startDate,
                $endDate
            );
            
            // If successful, return formatted data
            if ($performanceData['success']) {
                $totalRecords = count($performanceData['data'] ?? []);
                
                return [
                    'success' => true,
                    'message' => "Successfully synced {$totalRecords} records from GlobalNetwork",
                    'data' => [
                        'coupons' => [
                            'campaigns' => $totalRecords,
                            'coupons' => $totalRecords,
                            'purchases' => 0,
                            'total' => $totalRecords,
                            'data' => $performanceData['data'],
                        ],
                    ],
                ];
            }
            
            // If failed, return error
            return [
                'success' => false,
                'message' => $performanceData['message'] ?? 'Failed to sync data from GlobalNetwork',
            ];
            
        } catch (\Exception $e) {
            Log::error('GlobalNetwork sync failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get performance data from GlobalNetwork API
     */
    private function getPerformanceData(string $apiKey, string $startDate, string $endDate): array
    {
        try {
            $endpoint = $this->buildApiUrl($apiKey, $startDate, $endDate);
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($endpoint);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch data. Status: ' . $response->status(),
                ];
            }
            
            $data = $response->json();
            
            // Check for API errors
            if (isset($data['response']['errors']) && !empty($data['response']['errors'])) {
                $errorMsg = $data['response']['errors']['publicMessage'] ?? 
                           $data['response']['errors']['message'] ?? 
                           'API returned error';
                
                return [
                    'success' => false,
                    'message' => $errorMsg,
                ];
            }
            
            // Parse data based on response structure
            $parsedData = $this->parseApiResponse($data);
            
            return [
                'success' => true,
                'data' => $parsedData,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Parse API response and extract data
     */
    private function parseApiResponse(array $data): array
    {
        $items = [];
        
        try {
            // Check for nested response structure (response.response.data.data)
            if (isset($data['response']['response']['data']['data'])) {
                $items = $data['response']['response']['data']['data'];
            }
            // Standard structure (response.data.data)
            elseif (isset($data['response']['data']['data'])) {
                $items = $data['response']['data']['data'];
            }
            // Alternative structure (response.data)
            elseif (isset($data['response']['data']) && is_array($data['response']['data'])) {
                $items = $data['response']['data'];
            }
            
            // Transform data to standard format
            return $this->transformData($items);
            
        } catch (\Exception $e) {
            Log::error('Error parsing GlobalNetwork response: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Transform API data to standard format
     */
    private function transformData(array $items): array
    {
        $transformedData = [];
        
        foreach ($items as $item) {
            // Extract data from API response structure
            $campaignId = $item['Stat']['offer_id'] ?? $item['offer_id'] ?? null;
            $campaignName = $item['Offer']['name'] ?? $item['name'] ?? 'Unknown Campaign';
            $code = $item['Stat']['affiliate_info1'] ?? $item['affiliate_info1'] ?? '';
            $status = $item['Stat']['conversion_status'] ?? $item['conversion_status'] ?? 'pending';
            $payout = $item['Stat']['payout'] ?? $item['payout'] ?? 0;
            $saleAmount = $item['Stat']['conversion_sale_amount'] ?? $item['conversion_sale_amount'] ?? 0;
            $orderDate = $item['Stat']['datetime'] ?? $item['datetime'] ?? now()->format('Y-m-d H:i:s');
            
            // Skip if no campaign ID
            if (empty($campaignId)) {
                continue;
            }
            
            // Generate code if empty
            if (empty($code)) {
                $code = 'GN-' . $campaignId;
            }
            
            $transformedData[] = [
                'campaign_id' => $campaignId,
                'campaign_name' => $campaignName,
                'code' => $code,
                'purchase_type' => 'coupon', // GlobalNetwork is typically coupon-based
                'country' => 'NA',
                'order_id' => null,
                'network_order_id' => $item['Stat']['id'] ?? null,
                'order_value' => (float) $saleAmount,
                'commission' => (float) $payout,
                'revenue' => (float) $payout,
                'quantity' => 1,
                'customer_type' => 'unknown',
                'status' => $this->normalizeStatus($status),
                'order_date' => $orderDate,
                'purchase_date' => $orderDate,
            ];
        }
        
        return $transformedData;
    }
    
    /**
     * Normalize conversion status
     */
    private function normalizeStatus(string $status): string
    {
        $statusMap = [
            'approved' => 'approved',
            'pending' => 'pending',
            'rejected' => 'rejected',
            'cancelled' => 'rejected',
        ];
        
        return $statusMap[strtolower($status)] ?? 'pending';
    }
}
