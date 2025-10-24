<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\NetworkDataProcessor;

class LinkArabyService extends BaseNetworkService
{
    protected string $networkName = 'LinkAraby';
    
    protected array $requiredFields = [
        'username' => [
            'label' => 'Username',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'your.username',
            'help' => 'Your LinkAraby username',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Enter your password',
            'help' => 'Your LinkAraby password',
        ],
        'account_type' => [
            'label' => 'Account Type',
            'type' => 'select',
            'required' => true,
            'options' => [
                'affiliate' => 'Affiliate',
                'merchant' => 'Merchant'
            ],
            'default' => 'affiliate',
            'help' => 'Select your account type',
        ],
    ];
    
    protected array $defaultConfig = [
        'base_url' => 'https://portal.linkaraby.com',
        'api_url' => 'https://www.linkaraby.com',
        'login_url' => 'https://www.linkaraby.com/affiliates/login.php',
        'merchant_login_url' => 'https://www.linkaraby.com/merchants/login.php',
        'script_url' => 'https://www.linkaraby.com/scripts/server.php',
        'timeout' => 30,
        'rate_limit' => 100,
        'max_retries' => 3,
        'retry_delay' => 2,
    ];
    
    private $session = null;
    private $csrfToken = null;
    private $isLoggedIn = false;
    private $affiliateSid = null;
    
    /**
     * Test connection to LinkAraby API
     */
    public function testConnection(array $credentials): array
    {
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
            // Step 1: Initialize session
            if (!$this->initializeSession()) {
                return [
                    'success' => false,
                    'message' => 'Failed to initialize session',
                    'data' => null,
                    'error_code' => 'SESSION_INIT_FAILED'
                ];
            }
            
            // Step 2: Perform login
            $loginResult = $this->performLogin($credentials['username'], $credentials['password'], $credentials['account_type'] ?? 'affiliate');
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Login failed: ' . $loginResult['message'],
                    'data' => null,
                    'error_code' => 'LOGIN_FAILED'
                ];
            }
            
            // Step 3: Test data fetching
            $testResult = $this->testDataFetching();
            
            if (!$testResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Data fetching test failed: ' . $testResult['message'],
                    'data' => null,
                    'error_code' => 'DATA_FETCH_FAILED'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Successfully connected to LinkAraby!',
                'data' => [
                    'username' => $credentials['username'],
                    'account_type' => $credentials['account_type'] ?? 'affiliate',
                    'session_active' => $this->isLoggedIn,
                    'test_records' => $testResult['total_records'] ?? 0,
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
     * Initialize session and get CSRF token
     */
    private function initializeSession(): bool
    {
        try {
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'ar-SA,ar;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Connection' => 'keep-alive',
                ])
                ->get($this->defaultConfig['base_url'] . '/member/login');
            
            if (!$response->successful()) {
                return false;
            }
            
            // Extract CSRF token from HTML
            $this->csrfToken = $this->extractCsrfToken($response->body());
            
            if (!$this->csrfToken) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Extract CSRF token from HTML content
     */
    private function extractCsrfToken(string $html): ?string
    {
        // Look for CSRF token in meta tag
        if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }
        
        // Look for CSRF token in hidden input
        if (preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Perform login to LinkAraby
     */
    private function performLogin(string $username, string $password, string $accountType = 'affiliate'): array
    {
        try {
            $loginData = [
                '_token' => $this->csrfToken,
                'username' => $username,
                'password' => $password,
                'rememberMe' => '1',
                'language' => 'ar-SA'
            ];
            
            $loginUrl = $accountType === 'merchant' 
                ? $this->defaultConfig['merchant_login_url']
                : $this->defaultConfig['login_url'];
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'ar-SA,ar;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer' => $this->defaultConfig['base_url'] . '/member/login',
                ])
                ->asForm()
                ->post($loginUrl, $loginData);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Login request failed: HTTP ' . $response->status(),
                ];
            }
            
            // Check for successful login (302 redirect or success message)
            if ($response->status() === 302 || strpos($response->body(), 'dashboard') !== false) {
                $this->isLoggedIn = true;
                
                // Extract session ID from cookies
                $cookies = $response->cookies();
                foreach ($cookies as $cookie) {
                    if (strpos($cookie->getName(), 'affiliates_pap_sid') !== false) {
                        $this->affiliateSid = $cookie->getValue();
                        break;
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Login failed: Invalid credentials or account type',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Test data fetching capability
     */
    private function testDataFetching(): array
    {
        try {
            if (!$this->isLoggedIn) {
                return [
                    'success' => false,
                    'message' => 'Not logged in',
                ];
            }
            
            $startDate = now()->subDays(7)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            
            $requestData = [
                "C" => "Gpf_Rpc_Server",
                "M" => "run",
                "requests" => [[
                    "C" => "Pap_Affiliates_Reports_TransactionsGrid",
                    "M" => "getRows",
                    "sort_col" => "dateinserted",
                    "sort_asc" => false,
                    "offset" => 0,
                    "limit" => 10,
                    "filters" => [
                        ["dateinserted", "D>=", $startDate],
                        ["dateinserted", "D<=", $endDate]
                    ],
                    "columns" => [
                        ["id"], ["id"], ["commission"], ["totalcost"], 
                        ["t_orderid"], ["productid"], ["countrycode"], 
                        ["dateinserted"], ["banner"], ["name"], ["rtype"], 
                        ["tier"], ["commissionTypeName"], ["rstatus"]
                    ]
                ]],
                "S" => $this->affiliateSid
            ];
            
            // Add delay to avoid rate limiting
            sleep(2);
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $this->defaultConfig['base_url'] . '/member/dashboard',
                ])
                ->asForm()
                ->post($this->defaultConfig['script_url'], [
                    'D' => json_encode($requestData)
                ]);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Data fetch request failed: HTTP ' . $response->status(),
                ];
            }
            
            $data = $response->json();
            
            if (!$data || !is_array($data) || empty($data[0]['rows'])) {
                return [
                    'success' => true,
                    'message' => 'Data fetch successful but no records found',
                    'total_records' => 0,
                ];
            }
            
            $totalRecords = count($data[0]['rows']) - 1; // Subtract header row
            
            return [
                'success' => true,
                'message' => 'Data fetch successful',
                'total_records' => $totalRecords,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Data fetch error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sync data from LinkAraby API
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            $limit = $config['limit'] ?? 1000;
            
            // Initialize session if not already done
            if (!$this->isLoggedIn) {
                if (!$this->initializeSession()) {
                    return [
                        'success' => false,
                        'message' => 'Failed to initialize session',
                    ];
                }
                
                $loginResult = $this->performLogin(
                    $credentials['username'], 
                    $credentials['password'], 
                    $credentials['account_type'] ?? 'affiliate'
                );
                
                if (!$loginResult['success']) {
                    return [
                        'success' => false,
                        'message' => 'Login failed: ' . $loginResult['message'],
                    ];
                }
            }
            
            // Fetch orders data
            $ordersData = $this->fetchOrdersData($startDate, $endDate, $limit);
            
            if (!$ordersData['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch orders: ' . $ordersData['message'],
                ];
            }
            
            $orders = $ordersData['data'];
            $totalRecords = count($orders);
            
            // Process data using NetworkDataProcessor
            $processedData = $this->processNetworkData($orders, $config);
            
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} orders from LinkAraby",
                'data' => [
                    'orders' => [
                        'total' => $totalRecords,
                        'processed' => $processedData['processed'] ?? 0,
                        'campaigns' => $processedData['campaigns'] ?? 0,
                        'revenue' => $processedData['revenue'] ?? 0,
                        'data' => $orders,
                    ],
                ],
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Fetch orders data from LinkAraby API
     */
    private function fetchOrdersData(string $startDate, string $endDate, int $limit = 1000): array
    {
        try {
            $requestData = [
                "C" => "Gpf_Rpc_Server",
                "M" => "run",
                "requests" => [[
                    "C" => "Pap_Affiliates_Reports_TransactionsGrid",
                    "M" => "getRows",
                    "sort_col" => "dateinserted",
                    "sort_asc" => false,
                    "offset" => 0,
                    "limit" => $limit,
                    "filters" => [
                        ["dateinserted", "D>=", $startDate],
                        ["dateinserted", "D<=", $endDate]
                    ],
                    "columns" => [
                        ["id"], ["id"], ["commission"], ["totalcost"], 
                        ["t_orderid"], ["productid"], ["countrycode"], 
                        ["dateinserted"], ["banner"], ["name"], ["rtype"], 
                        ["tier"], ["commissionTypeName"], ["rstatus"]
                    ]
                ]],
                "S" => $this->affiliateSid
            ];
            
            // Add delay to avoid rate limiting
            sleep(2);
            
            $response = Http::timeout($this->defaultConfig['timeout'])
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $this->defaultConfig['base_url'] . '/member/dashboard',
                ])
                ->asForm()
                ->post($this->defaultConfig['script_url'], [
                    'D' => json_encode($requestData)
                ]);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch orders: HTTP ' . $response->status(),
                ];
            }
            
            $data = $response->json();
            
            if (!$data || !is_array($data) || empty($data[0]['rows'])) {
                return [
                    'success' => true,
                    'data' => [],
                    'message' => 'No orders found for the specified period',
                ];
            }
            
            $transformedData = $this->transformLinkArabyData($data[0]['rows']);
            
            return [
                'success' => true,
                'data' => $transformedData,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Transform LinkAraby data to standard format
     */
    private function transformLinkArabyData(array $rows): array
    {
        $transformedData = [];
        
        // Skip header row
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if (count($row) < 10) {
                continue; // Skip incomplete rows
            }
            
            $transformedData[] = [
                'order_id' => $row[0] ?? null,
                'commission' => (float) ($row[2] ?? 0),
                'total_cost' => (float) ($row[3] ?? 0),
                'network_order_id' => $row[4] ?? null,
                'product_id' => $row[5] ?? null,
                'country_code' => $row[6] ?? 'N/A',
                'date_inserted' => $row[7] ?? now()->format('Y-m-d H:i:s'),
                'banner' => $row[8] ?? null,
                'name' => $row[9] ?? 'Unknown',
                'rtype' => $row[10] ?? 'Unknown',
                'commission_type' => $row[11] ?? 'Unknown',
                'status' => $row[12] ?? 'Unknown',
                'purchase_type' => 'order',
                'type' => 'order',
                'sale_amount' => (float) ($row[3] ?? 0),
                'revenue' => (float) ($row[2] ?? 0),
                'quantity' => 1,
                'customer_type' => 'N/A',
                'transaction_id' => $row[4] ?? null,
                'order_date' => $row[7] ?? now()->format('Y-m-d'),
                'purchase_date' => $row[7] ?? now()->format('Y-m-d'),
                'currency' => 'SAR',
            ];
        }
        
        return $transformedData;
    }
    
    /**
     * Process network data using NetworkDataProcessor
     */
    private function processNetworkData(array $data, array $config = []): array
    {
        try {
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            $networkId = $config['network_id'] ?? 2; // LinkAraby network ID
            $userId = $config['user_id'] ?? 1;
            
            // Process using NetworkDataProcessor
            $result = NetworkDataProcessor::processCouponData(
                $data,
                $networkId,
                $userId,
                $startDate,
                $endDate,
                'linkaraby'
            );
            
            if ($result['success']) {
                return $result['processed'];
            }
            
            return [
                'processed' => 0,
                'campaigns' => 0,
                'revenue' => 0,
                'errors' => $result['message'] ?? 'Processing failed'
            ];
            
        } catch (\Exception $e) {
            return [
                'processed' => 0,
                'campaigns' => 0,
                'revenue' => 0,
                'errors' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate LinkAraby connection
     */
    public function validateConnection($connection): bool
    {
        try {
            if (empty($connection->credentials['username']) || empty($connection->credentials['password'])) {
                return false;
            }

            $testResult = $this->testConnection([
                'username' => $connection->credentials['username'],
                'password' => $connection->credentials['password'],
                'account_type' => $connection->credentials['account_type'] ?? 'affiliate',
            ]);

            return $testResult['success'];

        } catch (\Exception $e) {
            Log::error("LinkAraby connection validation failed: {$e->getMessage()}");
            return false;
        }
    }
}