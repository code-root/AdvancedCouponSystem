<?php

namespace App\Services\Networks;

class AdmitadService extends BaseNetworkService
{
    protected string $networkName = 'Admitad';
    
    protected array $requiredFields = [
        'client_id',
        'client_secret',
        'website_id'
    ];
    
    protected array $defaultConfig = [
        'api_url' => 'https://api.admitad.com/v1',
        'auth_url' => 'https://www.admitad.com/oauth/authorize',
        'timeout' => 60,
        'rate_limit' => 2000
    ];
    
    /**
     * Test connection to Admitad API
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
        
        // First, get access token
        $tokenResponse = $this->getAccessToken($credentials);
        if (!$tokenResponse['success']) {
            return $tokenResponse;
        }
        
        $accessToken = $tokenResponse['access_token'];
        $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
        $endpoint = "{$apiUrl}/me";
        
        $response = $this->makeRequest('get', $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json'
            ]
        ]);
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'Successfully connected to Admitad',
                'data' => [
                    'username' => $response['data']['username'] ?? 'Unknown',
                    'email' => $response['data']['email'] ?? '',
                    'balance' => $response['data']['balance'] ?? 0
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to connect to Admitad: ' . ($response['error'] ?? 'Unknown error'),
            'data' => null
        ];
    }
    
    /**
     * Get OAuth access token
     */
    private function getAccessToken(array $credentials): array
    {
        $authUrl = $this->defaultConfig['auth_url'];
        $tokenEndpoint = str_replace('/authorize', '/token', $authUrl);
        
        $response = $this->makeRequest('post', $tokenEndpoint, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'],
                'scope' => 'public_data'
            ]
        ]);
        
        if ($response['success'] && isset($response['data']['access_token'])) {
            return [
                'success' => true,
                'access_token' => $response['data']['access_token']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to get access token: ' . ($response['error'] ?? 'Invalid credentials')
        ];
    }
    
    /**
     * Sync data from Admitad
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            // Get access token first
            $tokenResponse = $this->getAccessToken($credentials);
            if (!$tokenResponse['success']) {
                return [
                    'success' => false,
                    'message' => $tokenResponse['message'],
                    'data' => []
                ];
            }
            
            $accessToken = $tokenResponse['access_token'];
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            $apiUrl = $credentials['api_endpoint'] ?? $this->defaultConfig['api_url'];
            $websiteId = $credentials['website_id'] ?? '';
            
            // Fetch statistics from Admitad
            $endpoint = "{$apiUrl}/statistics/websites/{$websiteId}/";
            
            $response = $this->makeRequest('get', $endpoint, [
                'query' => [
                    'date_start' => $startDate,
                    'date_end' => $endDate,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);
            
            if ($response['success']) {
                $data = $response['data']['results'] ?? [];
                
                return [
                    'success' => true,
                    'message' => "Successfully synced data from Admitad",
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
                'message' => 'Failed to sync data from Admitad',
                'data' => []
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error syncing Admitad data: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}

