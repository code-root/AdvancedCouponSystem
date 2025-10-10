<?php

namespace App\Services\Networks;

use Carbon\Carbon;

class BoostinyService extends BaseNetworkService
{
    protected string $networkName = 'Boostiny';
    
    protected array $requiredFields = [
        'api_key',
        'api_secret'
    ];
    
    protected array $defaultConfig = [
        'api_url' => 'https://api.boostiny.com',
        'auth_url' => 'https://app.boostiny.com/oauth/authorize',
        'timeout' => 30,
        'rate_limit' => 1000
    ];
    
    /**
     * Test connection to Boostiny API
     */
    public function testConnection(array $credentials): array
    {
        // Validate credentials first
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials provided. Please provide your API Key and API Secret.',
                'errors' => $validation['errors'],
                'data' => null
            ];
        }
        
        try {
            // Use API Key directly as access token (Boostiny format)
            $accessToken = $credentials['api_key'];
            
            // Date range: first day of current month to today
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            
            // Use the actual Boostiny API endpoint
            $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
            $endpoint = "{$apiUrl}/publisher/performance";
            
            $response = $this->makeRequest('get', $endpoint, [
                'query' => [
                    'limit' => 1000,
                    'from' => $startDate,
                    'to' => $endDate,
                ],
                'headers' => [
                    'Authorization' => $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);
            
            // Check for successful response
            if ($response['success'] && $response['status'] === 200) {
                $data = $response['data'];
                
                return [
                    'success' => true,
                    'message' => 'Successfully connected to Boostiny!',
                    'data' => [
                        'publisher_id' => $data['payload']['publisher_id'] ?? 'Unknown',
                        'status' => 'active',
                        'api_version' => 'v2',
                        'total_records' => $data['payload']['total'] ?? 0,
                        'date_range' => [
                            'from' => $startDate,
                            'to' => $endDate
                        ]
                    ]
                ];
            }
            
            // Handle specific error responses
            if (isset($response['status'])) {
                $errorMessage = 'Unknown error';
                
                // Try to extract error message from response
                if (isset($response['data']['payload']['errors']['message'])) {
                    $errorMessage = $response['data']['payload']['errors']['message'];
                } elseif (isset($response['error'])) {
                    $errorMessage = $response['error'];
                }
                
                switch ($response['status']) {
                    case 401:
                        return [
                            'success' => false,
                            'message' => 'Authentication failed: ' . $errorMessage . '. Please verify your API Key is correct and active.',
                            'data' => [
                                'error_code' => 401,
                                'error_details' => $errorMessage,
                                'url' => $apiUrl,
                                'hint' => 'Check if your API Key is correct and has not expired.'
                            ]
                        ];
                        
                    case 403:
                        return [
                            'success' => false,
                            'message' => 'Access forbidden: ' . $errorMessage . '. Your API Key may not have the required permissions.',
                            'data' => [
                                'error_code' => 403,
                                'error_details' => $errorMessage,
                                'hint' => 'Contact Boostiny support to enable API access for your account.'
                            ]
                        ];
                        
                    case 429:
                        return [
                            'success' => false,
                            'message' => 'Rate limit exceeded. Please try again in a few minutes.',
                            'data' => [
                                'error_code' => 429,
                                'error_details' => $errorMessage,
                                'hint' => 'Wait a few minutes before trying again.'
                            ]
                        ];
                        
                    case 404:
                        return [
                            'success' => false,
                            'message' => 'API endpoint not found. Please check the API URL.',
                            'data' => [
                                'error_code' => 404,
                                'error_details' => $errorMessage,
                                'hint' => 'Verify that the API URL is correct: ' . $apiUrl
                            ]
                        ];
                        
                    case 500:
                    case 502:
                    case 503:
                        return [
                            'success' => false,
                            'message' => 'Boostiny server error. Please try again later.',
                            'data' => [
                                'error_code' => $response['status'],
                                'error_details' => $errorMessage,
                                'hint' => 'This is a temporary issue with Boostiny servers.'
                            ]
                        ];
                        
                    default:
                        return [
                            'success' => false,
                            'message' => 'Connection failed: ' . $errorMessage,
                            'data' => [
                                'error_code' => $response['status'],
                                'error_details' => $errorMessage
                            ]
                        ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Failed to connect to Boostiny: Invalid response from API',
                'data' => null
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'data' => [
                    'exception' => get_class($e),
                    'error_details' => $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Get coupon data from Boostiny API
     */
    public function getDataCode(array $credentials, string $startDate, string $endDate): array
    {
        try {
            $accessToken = $credentials['api_key'];
            $apiUrl =  $this->defaultConfig['api_url'];
            $endpoint = "{$apiUrl}/publisher/performance";
            
            $limit = 10000000;
            
            $response = $this->makeRequest('get', $endpoint, [
                'query' => [
                    'limit' => $limit,
                    'from' => $startDate,
                    'to' => $endDate
                ],
                'headers' => [
                    'Authorization' => $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);
            
            if ($response['success'] && $response['status'] === 200) {
                return [
                    'success' => true,
                    'type' => 'coupon',
                    'data' => $response['data']['payload']['data'] ?? [],
                    'pagination' => $response['data']['payload']['pagination'] ?? [],
                    'total' => $response['data']['payload']['pagination']['total'] ?? 0
                ];
            }
            
            // Return detailed error
            $errorMsg = 'Failed to fetch coupon data';
            if (isset($response['data']['payload']['errors']['message'])) {
                $errorMsg .= ': ' . $response['data']['payload']['errors']['message'];
            } elseif (isset($response['error'])) {
                $errorMsg .= ': ' . $response['error'];
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
     * Get link performance data from Boostiny API
     */
    public function getDataLink(array $credentials, string $startDate, string $endDate): array
    {
        try {
            $accessToken = $credentials['api_key'];
            $apiUrl = $this->defaultConfig['api_url'];
            $endpoint = "{$apiUrl}/reports/link-performance/data";
            
            $response = $this->makeRequest('get', $endpoint, [
                'query' => [
                    'limit' => 20,
                    'page' => 1,
                    'dimensions' => ['campaign_networks', 'traffic_source'],
                    'metrics' => ['orders', 'revenue', 'sales_amount_usd'],
                    'from' => $startDate,
                    'to' => $endDate,
                    'type' => 'gross'
                ],
                'headers' => [
                    'Authorization' => $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);
            
            if ($response['success'] && $response['status'] === 200) {
                return [
                    'success' => true,
                    'type' => 'link',
                    'data' => $response['data']['payload']['data'] ?? [],
                    'pagination' => $response['data']['payload']['pagination'] ?? [],
                    'total' => $response['data']['payload']['pagination']['total'] ?? 0
                ];
            }
            
            // Return detailed error
            $errorMsg = 'Failed to fetch link data';
            if (isset($response['data']['payload']['errors']['message'])) {
                $errorMsg .= ': ' . $response['data']['payload']['errors']['message'];
            } elseif (isset($response['error'])) {
                $errorMsg .= ': ' . $response['error'];
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
                'message' => 'Error fetching link data: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Sync all data (coupons and links)
     */
    public function syncData(array $credentials, string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::now()->format('Y-m-d');
        
        $results = [
            'coupons' => $this->getDataCode($credentials, $startDate, $endDate),
            'links' => $this->getDataLink($credentials, $startDate, $endDate)
        ];
        
        $totalRecords = ($results['coupons']['total'] ?? 0) + ($results['links']['total'] ?? 0);
        
        if ($results['coupons']['success'] || $results['links']['success']) {
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from Boostiny",
                'data' => [
                    'coupons' => [
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
        
        // Return detailed error info for debugging
        return [
            'success' => false,
            'message' => 'Failed to sync data from Boostiny. Details: ' . 
                        ($results['coupons']['message'] ?? 'Unknown') . ' | ' . 
                        ($results['links']['message'] ?? 'Unknown'),
            'data' => [
                'coupons_error' => $results['coupons'],
                'links_error' => $results['links']
            ]
        ];
    }
}
