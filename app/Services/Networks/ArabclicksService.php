<?php

namespace App\Services\Networks;

use Carbon\Carbon;

class ArabclicksService extends BaseNetworkService
{
    protected string $networkName = 'Arabclicks';
    
    protected array $requiredFields = [
        'api_key'
    ];
    
    protected array $defaultConfig = [
        'api_url' => 'https://arabclicks.api.hasoffers.com/Apiv3/json?Target=Affiliate_Report&Method=getConversions&page=1&limit=2000000&fields[]=Stat.offer_id&fields[]=Stat.datetime&fields[]=Offer.name&fields[]=Stat.conversion_status&fields[]=Stat.payout&fields[]=Stat.ad_id&fields[]=Stat.affiliate_info1&fields[]=Stat.affiliate_info5&sort[Stat.datetime]=desc&filters[Stat.date][conditional]=BETWEEN&hour_offset=-2&NetworkId=arabclicks&fields[]=Stat.conversion_sale_amount&',
        'timeout' => 60,
        'rate_limit' => 1000
    ];
    
    /**
     * Test connection to Arabclicks API
     */
    public function testConnection(array $credentials): array
    {
        // Validate credentials first
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials provided. Please provide your API Key.',
                'errors' => $validation['errors'],
                'data' => null
            ];
        }
        
        try {
            $apiKey = $credentials['api_key'];
            $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
            
            // Date range for test
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            
            // Build API URL with parameters (Arabclicks format - same as Digizag)
            $endpoint = $apiUrl . '&api_key=' . $apiKey . 
                        '&filters[Stat.date][values][]=' . $startDate . 
                        '&filters[Stat.date][values][]=' . $endDate . 
                        '&data_start=' . $startDate . 
                        '&data_end=' . $endDate;
            
            $response = $this->makeRequest('get', $endpoint, [
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);
            
            if ($response['success'] && $response['status'] === 200) {
                $data = $response['data'];
                
                // Check if response has expected structure
                if (isset($data['response']['data']['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Successfully connected to Arabclicks!',
                        'data' => [
                            'status' => 'active',
                            'api_version' => 'v3',
                            'total_records' => count($data['response']['data']['data']),
                            'date_range' => [
                                'from' => $startDate,
                                'to' => $endDate
                            ]
                        ]
                    ];
                }
            }
            
            // Handle errors
            if (isset($response['status'])) {
                $errorMessage = $response['data']['response']['errors']['message'] ?? 
                               $response['error'] ?? 'Unknown error';
                
                switch ($response['status']) {
                    case 401:
                    case 403:
                        return [
                            'success' => false,
                            'message' => 'Authentication failed: ' . $errorMessage . '. Please verify your API Key.',
                            'data' => [
                                'error_code' => $response['status'],
                                'hint' => 'Check if your API Key is correct and active.'
                            ]
                        ];
                        
                    case 429:
                        return [
                            'success' => false,
                            'message' => 'Rate limit exceeded. Please try again later.',
                            'data' => ['error_code' => 429]
                        ];
                        
                    default:
                        return [
                            'success' => false,
                            'message' => 'Connection failed: ' . $errorMessage,
                            'data' => ['error_code' => $response['status']]
                        ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Failed to connect to Arabclicks: Invalid response',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'data' => ['exception' => get_class($e)]
            ];
        }
    }
    
    /**
     * Get coupon data from Arabclicks API
     */
    public function getDataCode(array $credentials, string $startDate, string $endDate): array
    {
        try {
            $apiKey = $credentials['api_key'];
            $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
            
            // Build API URL (Arabclicks format - same as Digizag)
            $endpoint = $apiUrl . '&api_key=' . $apiKey . 
                        '&filters[Stat.date][values][]=' . $startDate . 
                        '&filters[Stat.date][values][]=' . $endDate . 
                        '&data_start=' . $startDate . 
                        '&data_end=' . $endDate;
            
            $response = $this->makeRequest('get', $endpoint, [
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);
            
            if ($response['success'] && $response['status'] === 200) {
                $data = $response['data'];
                
                
                // Check if response has error (and errors is not empty array)
                if (isset($data['response']['errors']) && !empty($data['response']['errors'])) {
                    $errorMsg = $data['response']['errors']['publicMessage'] ?? 
                               $data['response']['errors']['message'] ?? 
                               'API returned error';
                    
                    return [
                        'success' => false,
                        'message' => $errorMsg,
                        'data' => [],
                        'error_details' => $data['response']['errors']
                    ];
                }
                
                // Check for nested response structure (response.response.data.data)
                if (isset($data['response']['response']['data']['data'])) {
                    $items = $data['response']['response']['data']['data'];
                    return [
                        'success' => true,
                        'type' => 'coupon',
                        'data' => $items,
                        'total' => count($items)
                    ];
                }
                
                // Standard structure (response.data.data)
                if (isset($data['response']['data']['data'])) {
                    $items = $data['response']['data']['data'];
                    return [
                        'success' => true,
                        'type' => 'coupon',
                        'data' => $items,
                        'total' => count($items)
                    ];
                }
                
                // Alternative structure
                if (isset($data['response']['data']) && is_array($data['response']['data'])) {
                    return [
                        'success' => true,
                        'type' => 'coupon',
                        'data' => $data['response']['data'],
                        'total' => count($data['response']['data'])
                    ];
                }
                
                // Log unexpected structure
                \Log::warning('Arabclicks unexpected response structure', [
                    'response_keys' => array_keys($data['response'] ?? []),
                    'has_nested_response' => isset($data['response']['response']),
                    'has_data' => isset($data['response']['data'])
                ]);
            }
            
            $errorMsg = 'Failed to fetch coupon data';
            if (isset($response['data']['response']['errors']['publicMessage'])) {
                $errorMsg .= ': ' . $response['data']['response']['errors']['publicMessage'];
            } elseif (isset($response['data']['response']['errors']['message'])) {
                $errorMsg .= ': ' . $response['data']['response']['errors']['message'];
            } elseif ($response['status'] === 401) {
                $errorMsg .= ': Unauthorized (check API key)';
            }
            
            return [
                'success' => false,
                'message' => $errorMsg,
                'status' => $response['status'] ?? 0,
                'data' => []
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching coupon data: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Get link performance data (if Arabclicks supports it)
     */
    public function getDataLink(array $credentials, string $startDate, string $endDate): array
    {
        // Arabclicks might not have separate link endpoint
        // Return empty for now
        return [
            'success' => true,
            'type' => 'link',
            'data' => [],
            'total' => 0
        ];
    }
    
    /**
     * Sync all data
     */
    public function syncData(array $credentials, array $config = []): array
    {
        $startDate = $config['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $config['date_to'] ?? Carbon::now()->format('Y-m-d');
        
        
        $results = [
            'coupons' => $this->getDataCode($credentials, $startDate, $endDate),
            'links' => $this->getDataLink($credentials, $startDate, $endDate)
        ];
        
        $totalRecords = ($results['coupons']['total'] ?? 0) + ($results['links']['total'] ?? 0);
        
        if ($results['coupons']['success'] || $results['links']['success']) {
            $couponTotal = $results['coupons']['total'] ?? 0;
            $linkTotal = $results['links']['total'] ?? 0;
            
            // Log sample data for debugging
            if (!empty($results['coupons']['data'])) {
                
            }
            
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from Arabclicks",
                'data' => [
                    'coupons' => [
                        'campaigns' => $couponTotal + $linkTotal,
                        'coupons' => $couponTotal + $linkTotal,
                        'purchases' => 0,
                        'total' => $results['coupons']['total'] ?? 0,
                        'data' => $results['coupons']['data'] ?? []
                    ],
                    'links' => [
                        'total' => $results['links']['total'] ?? 0,
                        'data' => $results['links']['data'] ?? []
                    ],
                    'date_range' => [
                        'from' => $startDate,
                        'to' => $endDate
                    ]
                ]
            ];
        }
        
        \Log::error('Arabclicks sync failed', [
            'coupons_error' => $results['coupons']['message'] ?? 'Unknown',
            'coupons_result' => $results['coupons']
        ]);
        
        return [
            'success' => false,
            'message' => 'Failed to sync data from Arabclicks. Details: ' . 
                        ($results['coupons']['message'] ?? 'Unknown'),
            'data' => [
                'coupons_error' => $results['coupons']
            ]
        ];
    }
}
