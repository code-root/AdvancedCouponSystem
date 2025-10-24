<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\NetworkDataProcessor;

class ICWService extends BaseNetworkService
{
    protected string $networkName = 'ICW';
    
    protected array $requiredFields = [
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'your.email@example.com',
            'help' => 'Your ICW account email',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Enter your password',
            'help' => 'Your ICW account password',
        ],
    ];
    
    protected array $defaultConfig = [
        'base_url' => 'https://api.icubeswire.co',
        'login_url' => 'https://api.icubeswire.co/partner/login',
        'reports_url' => 'https://api.icubeswire.co/reports/offer',
        'api_key' => 'xyz', // Fixed API key like in Python
        'timeout' => 30,
        'rate_limit' => 100,
        'max_retries' => 3,
        'retry_delay' => 2,
    ];
    

    /**
     * Test connection to ICW API with enhanced error handling
     */
    public function testConnection(array $credentials): array
    {
        // Enhanced validation with detailed error messages
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials provided',
                'errors' => $validation['errors'],
                'data' => null,
                'error_code' => 'INVALID_CREDENTIALS'
            ];
        }
        
        try {
            // Step 1: Login to get access token with retry logic
            $loginResult = $this->performLoginWithRetry($credentials['email'], $credentials['password']);
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Login failed: ' . $loginResult['message'],
                    'data' => null,
                    'error_code' => 'LOGIN_FAILED'
                ];
            }
            
            $accessToken = $loginResult['access_token'];
            $userData = $loginResult['user_data'];
            
            // Step 2: Test reports API with enhanced error handling
            $testResult = $this->testReportsAPIWithRetry($accessToken, $userData['userId'] ?? null);
            
            if (!$testResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Reports API test failed: ' . $testResult['message'],
                    'data' => null,
                    'error_code' => 'REPORTS_API_FAILED'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Successfully connected to ICW!',
                'data' => [
                    'user_name' => $userData['userFullName'] ?? 'Unknown',
                    'user_email' => $userData['userEmail'] ?? $credentials['email'],
                    'user_country' => $userData['userCountry'] ?? 'Unknown',
                    'user_status' => $userData['userStatus'] ?? 'Unknown',
                    'user_id' => $userData['userId'] ?? null,
                    'access_token' => substr($accessToken, 0, 10) . '...',
                    'total_records' => $testResult['total_records'] ?? 0,
                    'connection_time' => now()->toISOString(),
                ]
            ];
            
        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'data' => null,
                'error_code' => 'CONNECTION_EXCEPTION',
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }
    
    /**
     * Perform login with retry logic
     */
    private function performLoginWithRetry(string $email, string $password): array
    {
        $maxRetries = $this->defaultConfig['max_retries'] ?? 3;
        $retryDelay = $this->defaultConfig['retry_delay'] ?? 2;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $result = $this->performLogin($email, $password, $this->defaultConfig['api_key']);
            
            if ($result['success']) {
                return $result;
            }
            
            // Don't wait after the last attempt
            if ($attempt < $maxRetries) {
                sleep($retryDelay);
            }
        }
        
        return [
            'success' => false,
            'message' => "Login failed after {$maxRetries} attempts",
            'attempts' => $maxRetries
        ];
    }
    
    /**
     * Test reports API with retry logic
     */
    private function testReportsAPIWithRetry(string $accessToken, ?string $affiliateId = null): array
    {
        $maxRetries = $this->defaultConfig['max_retries'] ?? 3;
        $retryDelay = $this->defaultConfig['retry_delay'] ?? 2;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $result = $this->testReportsAPI($accessToken, $affiliateId);
            
            if ($result['success']) {
                return $result;
            }
            
            // Don't wait after the last attempt
            if ($attempt < $maxRetries) {
                sleep($retryDelay);
            }
        }
        
        return [
            'success' => false,
            'message' => "Reports API test failed after {$maxRetries} attempts",
            'attempts' => $maxRetries
        ];
    }
    
    /**
     * Perform login to ICW API
     */
    private function performLogin(string $email, string $password, string $apiKey): array
    {
        try {
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Connection' => 'keep-alive',
                    'Origin' => 'https://partners.icubeswire.co',
                    'Referer' => 'https://partners.icubeswire.co/',
                    'apiKey' => $apiKey,
                ])
                ->asForm()
                ->post($this->defaultConfig['login_url'], [
                    'userEmail' => $email,
                    'requestFor' => 'login',
                    'userPassword' => $password
                ]);
            
            if (!$response->successful()) {
                $errorMessage = 'Login request failed: HTTP ' . $response->status();
                
                // Enhanced error messages based on status code
                switch ($response->status()) {
                    case 401:
                        $errorMessage = 'Unauthorized: Invalid credentials or API key';
                        break;
                    case 403:
                        $errorMessage = 'Forbidden: Access denied';
                        break;
                    case 404:
                        $errorMessage = 'Not Found: Login endpoint not found';
                        break;
                    case 429:
                        $errorMessage = 'Rate Limited: Too many requests';
                        break;
                    case 500:
                        $errorMessage = 'Server Error: ICW server error';
                        break;
                    case 502:
                    case 503:
                    case 504:
                        $errorMessage = 'Service Unavailable: ICW service temporarily down';
                        break;
                }
                
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ];
            }
            
            $data = $response->json();
            
            if (isset($data['status']) && $data['status'] === 'success') {
                $accessToken = $data['accessTokenInfo']['accessToken'] ?? null;
                $userData = $data['data'] ?? [];
                
                if (empty($accessToken)) {
                    
                    return [
                        'success' => false,
                        'message' => 'Login successful but no access token received',
                        'response_data' => $data
                    ];
                }
                
                return [
                    'success' => true,
                    'access_token' => $accessToken,
                    'user_data' => $userData,
                ];
            }
            
            $errorMessage = $data['msg'] ?? 'Login failed with unknown error';
            
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'response_data' => $data
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }
    
    /**
     * Test reports API
     */
    private function testReportsAPI(string $accessToken, ?string $affiliateId = null): array
    {
        try {
            $endDate = now()->format('Y-m-d');
            $startDate = now()->subDays(7)->format('Y-m-d');
            
            $formData = [
                'currentRole' => 'affiliate',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reportOptions[]' => [
                    'grossClicks', 'pendingPayout', 'pendingSaleAmount', 
                    'cr', 'pendingConversions', 'currency'
                ],
                'affiliate' => '1',
                'goal' => '1',
                'date' => '1',
                'url' => '1',
                'file' => '0',
                'code' => '1',
                'geo' => '1',
                'os' => '1',
                'validations' => '0',
                'sort[field]' => 'conversions',
                'sort[sort]' => 'desc',
                'selectedCurrency' => '',
                'pagination[page]' => '1',
                'pagination[perpage]' => '10',
                'query' => '',
                'affiliateId[]' => $affiliateId ?? '1'
            ];
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'accessToken' => $accessToken,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->asForm()
                ->post($this->defaultConfig['reports_url'], $formData);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Reports API request failed: HTTP ' . $response->status(),
                ];
            }
            
            $data = $response->json();
            
            if ($data['status'] === 'success') {
                return [
                    'success' => true,
                    'total_records' => count($data['data'] ?? []),
                ];
            }
            
            return [
                'success' => false,
                'message' => $data['msg'] ?? 'Reports API failed',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Reports API error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sync data from ICW API
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            // Try with stored access token first
            $accessToken = $credentials['access_token'] ?? '';
            
            if (!empty($accessToken)) {
                // Try to decrypt if encrypted
                try {
                    $accessToken = decrypt($accessToken);
                } catch (\Exception $e) {
                    // Token was not encrypted, use as is
                }
                
                // Fetch data with current token
                $dataResult = $this->fetchReportsData($accessToken, $startDate, $endDate, $credentials['affiliate_id'] ?? null);
                
                // If successful, return data
                if ($dataResult['success']) {
                    $totalRecords = count($dataResult['data'] ?? []);
                    
                    // Process data using NetworkDataProcessor
                    $processedData = $this->processNetworkData($dataResult['data'], $config);
                    
                    return [
                        'success' => true,
                        'message' => "Successfully synced {$totalRecords} records from ICW",
                        'data' => [
                            'coupons' => [
                                'campaigns' => $processedData['campaigns'] ?? $totalRecords,
                                'coupons' => $processedData['coupons'] ?? $totalRecords,
                                'purchases' => $processedData['purchases'] ?? 0,
                                'total' => $totalRecords,
                                'data' => $dataResult['data'],
                            ],
                        ],
                    ];
                }
                
            }
            
            // If token failed or not available, re-authenticate
            if (empty($credentials['email']) || empty($credentials['password'])) {
                return [
                    'success' => false,
                    'message' => 'Session expired. Please reconnect with your credentials.',
                ];
            }
            
            // Decrypt password if encrypted
            $password = $credentials['password'];
            try {
                $password = decrypt($password);
            } catch (\Exception $e) {
                // Password was not encrypted, use as is
            }
            
            // Re-authenticate
            $loginResult = $this->performLogin($credentials['email'], $password, $this->defaultConfig['api_key']);
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Session expired and re-login failed. Please reconnect.',
                ];
            }
            
            $newAccessToken = $loginResult['access_token'];
            $userData = $loginResult['user_data'];
            
            // Fetch data with new token
            $dataResult = $this->fetchReportsData($newAccessToken, $startDate, $endDate, $userData['userId'] ?? null);
            
            if (!$dataResult['success']) {
                return $dataResult;
            }
            
            $totalRecords = count($dataResult['data'] ?? []);
            
            // Process data using NetworkDataProcessor
            $processedData = $this->processNetworkData($dataResult['data'], $config);
            
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from ICW (re-authenticated)",
                'data' => [
                    'coupons' => [
                        'campaigns' => $processedData['campaigns'] ?? $totalRecords,
                        'coupons' => $processedData['coupons'] ?? $totalRecords,
                        'purchases' => $processedData['purchases'] ?? 0,
                        'total' => $totalRecords,
                        'data' => $dataResult['data'],
                    ],
                ],
                'new_access_token' => encrypt($newAccessToken), // Return new token to update
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Fetch reports data from ICW API
     */
    private function fetchReportsData(string $accessToken, string $startDate, string $endDate, ?string $affiliateId = null): array
    {
        try {
            $formData = [
                'currentRole' => 'affiliate',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reportOptions[]' => [
                    'grossClicks', 'pendingPayout', 'pendingSaleAmount', 
                    'cr', 'pendingConversions', 'currency'
                ],
                'affiliate' => '1',
                'goal' => '1',
                'date' => '1',
                'url' => '1',
                'file' => '0',
                'code' => '1',
                'geo' => '1',
                'os' => '1',
                'validations' => '0',
                'sort[field]' => 'conversions',
                'sort[sort]' => 'desc',
                'selectedCurrency' => '',
                'pagination[page]' => '1',
                'pagination[perpage]' => '1000', // Get more records
                'query' => '',
                'affiliateId[]' => $affiliateId ?? '1'
            ];
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'accessToken' => $accessToken,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->asForm()
                ->post($this->defaultConfig['reports_url'], $formData);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch reports: HTTP ' . $response->status()
                ];
            }
            
            $data = $response->json();
            
            if ($data['status'] === 'success') {
                $transformedData = $this->transformICWData($data['data'] ?? []);
                
                return [
                    'success' => true,
                    'data' => $transformedData,
                ];
            }
            
            return [
                'success' => false,
                'message' => $data['msg'] ?? 'Failed to fetch reports',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching reports: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Transform ICW API data to standard format
     */
    private function transformICWData(array $items): array
    {
        $transformedData = [];
        
        foreach ($items as $item) {
            $campaignName = $item['campName'] ?? 'Unknown Campaign';
            $goalName = $item['goalName'] ?? 'Unknown Goal';
            $date = $item['date'] ?? now()->format('Y-m-d');
            $grossClicks = (int) ($item['grossClicks'] ?? 0);
            $conversions = (int) ($item['pendingConversions'] ?? 0);
            $payout = (float) ($item['pendingPayout'] ?? 0);
            $saleAmount = (float) ($item['pendingSaleAmount'] ?? 0);
            $cr = (float) ($item['cr'] ?? 0);
            $currency = $item['currency'] ?? 'USD';
            
            $transformedData[] = [
                'campaign_id' => $item['campId'] ?? null,
                'campaign_name' => $campaignName,
                'code' => $goalName,
                'coupon_code' => $goalName,
                'purchase_type' => 'coupon',
                'type' => 'coupon',
                'country' => $item['geo'] ?? 'N/A',
                'sale_amount' => round($saleAmount, 2),
                'revenue' => round($payout, 2),
                'clicks' => $grossClicks,
                'conversions' => $conversions,
                'quantity' => $conversions,
                'customer_type' => 'N/A',
                'transaction_id' => $item['url'] ?? '',
                'order_id' => null,
                'network_order_id' => null,
                'date' => $date,
                'order_date' => $date,
                'purchase_date' => $date,
                'status' => 'approved',
                'conversion_rate' => round($cr * 100, 2),
                'currency' => $currency,
                'os' => $item['os'] ?? 'N/A',
            ];
        }
        
        return $transformedData;
    }

    /**
     * Generate unique boundary for multipart requests
     */
    private function generateBoundary(): string
    {
        return '----WebKitFormBoundary' . str_replace('-', '', substr(uniqid(), 0, 16));
    }
    
    /**
     * Validate ICW connection
     */
    public function validateConnection($connection): bool
    {
        try {
            if (empty($connection->credentials['email']) || empty($connection->credentials['password'])) {
                return false;
            }

            $testResult = $this->testConnection([
                'email' => $connection->credentials['email'],
                'password' => $connection->credentials['password'],
            ]);

            return $testResult['success'];

        } catch (\Exception $e) {
            Log::error("ICW connection validation failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Process network data using NetworkDataProcessor
     */
    private function processNetworkData(array $data, array $config = []): array
    {
        try {
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            $networkId = $config['network_id'] ?? 1;
            $userId = $config['user_id'] ?? 1;
            
            // Transform ICW data to standard format
            $transformedData = $this->transformICWData($data);
            
            // Process using NetworkDataProcessor
            $result = NetworkDataProcessor::processCouponData(
                $transformedData,
                $networkId,
                $userId,
                $startDate,
                $endDate,
                'icw'
            );
            
            if ($result['success']) {
                return $result['processed'];
            }
            
            return [
                'campaigns' => 0,
                'coupons' => 0,
                'purchases' => 0,
                'errors' => $result['message'] ?? 'Processing failed'
            ];
            
        } catch (\Exception $e) {
            Log::error('ICW data processing failed: ' . $e->getMessage());
            return [
                'campaigns' => 0,
                'coupons' => 0,
                'purchases' => 0,
                'errors' => $e->getMessage()
            ];
        }
    }
}

