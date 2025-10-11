<?php

namespace App\Services\Networks;

class ClickDealerService extends BaseNetworkService
{
    protected string $networkName = 'ClickDealer';
    
    protected array $requiredFields = [
        'api_token',
        'affiliate_id'
    ];
    
    protected array $defaultConfig = [
        'api_url' => 'https://api.clickdealer.com/v2',
        'auth_url' => 'https://affiliates.clickdealer.com/oauth',
        'timeout' => 45,
        'rate_limit' => 500
    ];
    
    /**
     * Test connection to ClickDealer API
     */
    public function testConnection(array $credentials): array
    {
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials provided',
                'errors' => $validation['errors'],
                'data' => null
            ];
        }
        
        $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
        $endpoint = "{$apiUrl}/affiliate/validate";
        
        $response = $this->makeRequest('post', $endpoint, [
            'json' => [
                'api_token' => $credentials['api_token'],
                'affiliate_id' => $credentials['affiliate_id']
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'Successfully connected to ClickDealer',
                'data' => [
                    'affiliate_name' => $response['data']['affiliate']['name'] ?? 'Unknown',
                    'status' => $response['data']['status'] ?? 'active',
                    'balance' => $response['data']['balance'] ?? 0
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to connect to ClickDealer: ' . ($response['error'] ?? 'Invalid credentials'),
            'data' => null
        ];
    }
    
    /**
     * Sync data from ClickDealer
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
            $endpoint = "{$apiUrl}/statistics/conversions";
            
            $response = $this->makeRequest('get', $endpoint, [
                'query' => [
                    'api_token' => $credentials['api_token'],
                    'affiliate_id' => $credentials['affiliate_id'],
                    'date_start' => $startDate,
                    'date_end' => $endDate,
                ],
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);
            
            if ($response['success']) {
                $data = $response['data']['conversions'] ?? [];
                
                return [
                    'success' => true,
                    'message' => "Successfully synced data from ClickDealer",
                    'data' => [
                        'coupons' => [
                            'total' => count($data),
                            'campaigns' => count($data),
                            'coupons' => count($data),
                            'purchases' => 0,
                            'data' => $data
                        ]
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to sync data from ClickDealer',
                'data' => []
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error syncing ClickDealer data: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}

