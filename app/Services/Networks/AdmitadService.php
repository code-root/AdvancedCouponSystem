<?php

namespace App\Services\Networks;

use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdmitadService extends BaseNetworkService
{
    protected string $networkName = 'Admitad';
    
    protected array $requiredFields = [
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'your.email@example.com',
            'help' => 'Your Admitad account email',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Enter your password',
            'help' => 'Your Admitad account password',
        ],
    ];
    
    protected array $defaultConfig = [
        'base_url' => 'https://login.mitgo.com',
        'login_url' => 'https://login.mitgo.com/auth/realms/users/protocol/openid-connect/auth',
        'store_url' => 'https://store.admitad.com',
        'api_url' => 'https://store.admitad.com/en/api',
        'token_url' => 'https://api.admitad.com/token/',
        'client_id' => 'monolith',
        'recaptcha_key' => '6LfcGc4UAAAAAJUHmqqqR5cybEkn_N7QeS8nk_U9',
    ];
    
    /**
     * Test connection by logging in and getting OAuth code (with retry)
     */
    public function testConnection(array $credentials): array
    {
        $maxRetries = 2;
        $attempt = 0;
        $lastError = '';
        
        // Extend execution time for OAuth flow
        set_time_limit(120);
        
        // Validate credentials once
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials provided',
                'errors' => $validation['errors'],
            ];
        }
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                Log::info("Admitad: Connection attempt #{$attempt}");
                
                // Step 1: Login and get OAuth authorization (fresh attempt)
                $authResult = $this->performLogin($credentials['email'], $credentials['password']);
                
                if (!$authResult['success']) {
                    $lastError = $authResult['message'];
                    Log::warning("Admitad: Login failed on attempt #{$attempt}: {$lastError}");
                    
                    if ($attempt < $maxRetries) {
                        sleep(3); // Wait 3 seconds before retry (OAuth needs more time)
                        continue;
                    }
                    
                    return [
                        'success' => false,
                        'message' => 'Login failed: ' . $lastError,
                    ];
                }
                
                Log::info("Admitad: Successfully connected on attempt #{$attempt}");
                
            return [
                'success' => true,
                    'message' => 'Successfully connected to Admitad!' . ($attempt > 1 ? " (after {$attempt} attempts)" : ''),
                'data' => [
                        'client_id' => $authResult['client_id'] ?? '',
                        'cookies' => $authResult['cookies'] ?? '',
                        'connected_at' => now()->format('Y-m-d H:i:s'),
                    ],
                ];
                
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::error("Admitad connection attempt #{$attempt} failed: {$lastError}");
                
                if ($attempt < $maxRetries) {
                    sleep(3); // Wait before retry
                    continue;
                }
                
                return [
                    'success' => false,
                    'message' => 'Connection failed after ' . $maxRetries . ' attempts: ' . $lastError,
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Connection failed after all retry attempts: ' . $lastError,
        ];
    }
    
    /**
     * Perform complete login flow
     */
    private function performLogin(string $email, string $password): array
    {
        try {
            $cookies = [];
            
            // Step 1: Get login page and extract parameters
            Log::info('Admitad: Step 1 - Loading login page');
            $loginPageResult = $this->getLoginPage($cookies);
            if (!$loginPageResult['success']) {
                return $loginPageResult;
            }
            
            $execution = $loginPageResult['execution'];
            $tabId = $loginPageResult['tab_id'];
            $sessionCode = $loginPageResult['session_code'];
            $cookies = array_merge($cookies, $loginPageResult['cookies']);
            
            // Step 2: POST authenticate
            Log::info('Admitad: Step 2 - Sending login credentials');
            $authResult = $this->postAuthenticate($email, $password, $execution, $tabId, $sessionCode, $cookies);
            if (!$authResult['success']) {
                return $authResult;
            }
            
            $cookies = array_merge($cookies, $authResult['cookies']);
            
            // Step 3-7: Follow redirect chain
            Log::info('Admitad: Steps 3-7 - Following OAuth redirects');
            $finalResult = $this->followRedirectChain($authResult['location'], $cookies);
            if (!$finalResult['success']) {
                return $finalResult;
            }
            
            $cookies = array_merge($cookies, $finalResult['cookies']);
            
            // Step 8: Get client_id
            Log::info('Admitad: Step 8 - Getting client credentials');
            $credentialsResult = $this->getClientCredentials($cookies);
            if (!$credentialsResult['success']) {
                return $credentialsResult;
            }
            
            return [
                'success' => true,
                'client_id' => $credentialsResult['client_id'],
                'cookies' => $this->buildCookieString($cookies),
            ];
            
        } catch (\Exception $e) {
            Log::error('Admitad performLogin exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Step 1: Get login page
     */
    private function getLoginPage(array &$cookies): array
    {
        try {
            $url = $this->defaultConfig['login_url'] . '?' . http_build_query([
                'client_id' => $this->defaultConfig['client_id'],
                'redirect_uri' => 'https://store.admitad.com/en/sso/login-complete/',
                'response_type' => 'code',
                'scope' => 'openid email profile',
                'state' => $this->generateState(),
                'ui_locales' => 'en'
            ]);
            
            $response = Http::timeout(30)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($url);
            
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to load login page'];
            }
            
            $html = $response->body();
            
            // Extract parameters
            preg_match('/"execution"\s*:\s*"([^"]+)"/', $html, $executionMatch);
            preg_match('/tab_id=([^&"]+)/', $html, $tabIdMatch);
            preg_match('/session_code=([^&"]+)/', $html, $sessionCodeMatch);
            
            if (empty($executionMatch[1]) || empty($tabIdMatch[1]) || empty($sessionCodeMatch[1])) {
                return ['success' => false, 'message' => 'Failed to extract login parameters'];
            }
            
            // Extract cookies
            $newCookies = $this->extractCookies($response->cookies()->toArray());
            
            return [
                'success' => true,
                'execution' => $executionMatch[1],
                'tab_id' => $tabIdMatch[1],
                'session_code' => $sessionCodeMatch[1],
                'cookies' => $newCookies,
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error loading login page: ' . $e->getMessage()];
        }
    }
    
    /**
     * Step 2: POST authenticate
     */
    private function postAuthenticate(string $email, string $password, string $execution, string $tabId, string $sessionCode, array $cookies): array
    {
        try {
            $url = $this->defaultConfig['base_url'] . 
                   '/auth/realms/users/login-actions/authenticate?' . 
                   http_build_query([
                       'session_code' => $sessionCode,
                       'execution' => $execution,
                       'client_id' => $this->defaultConfig['client_id'],
                       'tab_id' => $tabId,
                   ]);
            
            $response = Http::timeout(30)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cookie' => $this->buildCookieString($cookies),
                ])
                ->asForm()
                ->post($url, [
                    'username' => $email,
                    'password' => $password,
                    'rememberMe' => 'on',
                    'login' => '',
                    'credentialId' => '',
                ]);
            
            if (!$response->redirect()) {
                return ['success' => false, 'message' => 'Authentication failed - invalid credentials'];
            }
            
            $location = $response->header('Location');
            $newCookies = $this->extractCookies($response->cookies()->toArray());
            
            return [
                'success' => true,
                'location' => $this->makeAbsolute($location, $this->defaultConfig['base_url']),
                'cookies' => $newCookies,
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Authentication error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Step 3-7: Follow redirect chain
     */
    private function followRedirectChain(string $startUrl, array &$cookies): array
    {
        try {
            $url = $startUrl;
            $maxRedirects = 10;
            $redirectCount = 0;
            
            while ($redirectCount < $maxRedirects) {
                $response = Http::timeout(30)
                    ->withOptions(['allow_redirects' => false])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Cookie' => $this->buildCookieString($cookies),
                    ])
                    ->get($url);
                
                // Merge cookies
                $newCookies = $this->extractCookies($response->cookies()->toArray());
                $cookies = array_merge($cookies, $newCookies);
                
                if ($response->redirect()) {
                    $location = $response->header('Location');
                    $url = $this->makeAbsolute($location, $this->getBaseUrl($url));
                    $redirectCount++;
                } else {
                    // Reached final page
                    return [
                        'success' => true,
                        'cookies' => $cookies,
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Too many redirects'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Redirect error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Step 8: Get client credentials
     */
    private function getClientCredentials(array $cookies): array
    {
        try {
            $url = $this->defaultConfig['api_url'] . '/w/credentials/';
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'application/json',
                    'Cookie' => $this->buildCookieString($cookies),
                ])->get($url);
            
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to get credentials'];
            }
            
            $data = $response->json();
            $clientId = $data['client_id'] ?? null;
            
            if (empty($clientId)) {
                return ['success' => false, 'message' => 'Client ID not found'];
        }
        
        return [
                'success' => true,
                'client_id' => $clientId,
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error getting credentials: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get authorization code (with reCAPTCHA)
     */
    public function getAuthorizationCode(string $clientId, string $clientSecret, array $cookies): array
    {
        try {
            // Step 1: Solve reCAPTCHA
            Log::info('Admitad: Solving reCAPTCHA...');
            $recaptchaService = new RecaptchaService();
            $websiteUrl = "https://store.admitad.com/en/api/authorize/?" . http_build_query([
                'scope' => 'statistics advcampaigns banners websites coupons',
                'state' => '200',
                'redirect_uri' => url('/admitad/callback'),
                'response_type' => 'code',
                'client_id' => $clientId,
            ]);
            
            $recaptchaToken = $recaptchaService->solveRecaptchaV3(
                $websiteUrl,
                $this->defaultConfig['recaptcha_key'],
                'myverify',
                0.3
            );
            
            if (empty($recaptchaToken)) {
                return ['success' => false, 'message' => 'Failed to solve reCAPTCHA'];
            }
            
            Log::info('Admitad: reCAPTCHA solved successfully');
            
            // Step 2: Get authorization page
            $authPageResult = $this->getAuthorizationPage($clientId, $cookies);
            if (!$authPageResult['success']) {
                return $authPageResult;
            }
            
            $csrfToken = $authPageResult['csrf_token'];
            
            // Step 3: Submit authorization with reCAPTCHA
            return $this->submitAuthorization($clientId, $csrfToken, $recaptchaToken, $cookies);
            
        } catch (\Exception $e) {
            Log::error('Admitad getAuthorizationCode exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Authorization error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get authorization page to extract CSRF token
     */
    private function getAuthorizationPage(string $clientId, array $cookies): array
    {
        try {
            $url = $this->defaultConfig['api_url'] . '/authorize/?' . http_build_query([
                'scope' => 'statistics advcampaigns banners websites coupons',
                'state' => '200',
                'redirect_uri' => url('/admitad/callback'),
                'response_type' => 'code',
                'client_id' => $clientId,
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Cookie' => $this->buildCookieString($cookies),
                ])->get($url);
            
            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to load authorization page'];
            }
            
            $html = $response->body();
            preg_match("/name='csrfmiddlewaretoken' value='([^']+)'/", $html, $csrfMatch);
            
            if (empty($csrfMatch[1])) {
                return ['success' => false, 'message' => 'CSRF token not found'];
            }
            
            return [
                'success' => true,
                'csrf_token' => $csrfMatch[1],
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error getting authorization page: ' . $e->getMessage()];
        }
    }
    
    /**
     * Submit authorization with reCAPTCHA
     */
    private function submitAuthorization(string $clientId, string $csrfToken, string $recaptchaToken, array $cookies): array
    {
        try {
            $url = $this->defaultConfig['api_url'] . '/authorize/?' . http_build_query([
                'scope' => 'statistics advcampaigns banners websites coupons',
                'state' => '200',
                'redirect_uri' => url('/admitad/callback'),
                'response_type' => 'code',
                'client_id' => $clientId,
            ]);
            
            $response = Http::timeout(30)
                ->withOptions(['allow_redirects' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cookie' => $this->buildCookieString($cookies),
                    'Referer' => $url,
                ])
                ->asForm()
                ->post($url, [
                    'csrfmiddlewaretoken' => $csrfToken,
                    'redirect_uri' => url('/admitad/callback'),
                    'scope' => 'statistics advcampaigns banners websites coupons',
                    'client_id' => $clientId,
                    'state' => '200',
                    'response_type' => 'code',
                    'allow' => 'Allow access',
                    'captcha' => $recaptchaToken,
                    'g-recaptcha-response' => '',
                ]);
            
            if (!$response->redirect()) {
                return ['success' => false, 'message' => 'Authorization failed'];
            }
            
            $location = $response->header('Location');
            
            // Extract code from redirect URL
            if (preg_match('/[?&]code=([^&]+)/', $location, $codeMatch)) {
                return [
                    'success' => true,
                    'code' => $codeMatch[1],
                    'redirect_url' => $location,
                ];
            }
            
            return ['success' => false, 'message' => 'Authorization code not found in redirect'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Submit authorization error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code, string $clientId, string $clientSecret): array
    {
        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post($this->defaultConfig['token_url'], [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => url('/admitad/callback'),
                ]);
            
            if (!$response->successful()) {
                $error = $response->json()['error_description'] ?? 'Unknown error';
                return ['success' => false, 'message' => 'Token exchange failed: ' . $error];
            }
            
            $data = $response->json();
        
        return [
                'success' => true,
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_in' => $data['expires_in'] ?? 3600,
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Token exchange error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Sync data from Admitad (with auto re-authentication)
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            // Extend execution time for sync operations
            set_time_limit(180);
            
            // Extract dates from config
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            // Try with stored access_token first
            $accessToken = $credentials['access_token'] ?? '';
            
            if (!empty($accessToken)) {
                // Try to decrypt if encrypted
                try {
                    $accessToken = decrypt($accessToken);
                } catch (\Exception $e) {
                    // Token was not encrypted, use as is
                }
                
                // Fetch data with current token
                Log::info('Admitad: Attempting sync with stored access token');
                $dataResult = $this->fetchAdmitadData($accessToken, $startDate, $endDate);
                
                // If successful, return data
                if ($dataResult['success']) {
                    $totalRecords = count($dataResult['data'] ?? []);
                    
                    return [
                        'success' => true,
                        'message' => "Successfully synced {$totalRecords} records from Admitad",
                        'data' => [
                            'coupons' => [
                                'campaigns' => $totalRecords,
                                'coupons' => $totalRecords,
                                'purchases' => 0,
                                'total' => $totalRecords,
                                'data' => $dataResult['data'],
                            ],
                        ],
                    ];
                }
                
                Log::warning('Admitad: Access token failed, attempting re-authentication');
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
            Log::info('Admitad: Re-authenticating...');
            $loginResult = $this->performLogin($credentials['email'], $password);
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Session expired and re-login failed. Please reconnect.',
                ];
            }
            
            // Get new client credentials
            $clientId = $loginResult['client_id'];
            $clientSecret = $credentials['client_secret'] ?? '';
            
            if (empty($clientSecret)) {
                try {
                    $clientSecret = decrypt($credentials['client_secret']);
                } catch (\Exception $e) {
                    // Not encrypted
                }
            }
            
            $cookies = $loginResult['cookies'];
            
            // Get authorization code
            Log::info('Admitad: Getting authorization code...');
            $authResult = $this->getAuthorizationCode($clientId, $clientSecret, $this->parseCookieString($cookies));
            
            if (!$authResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Re-authentication failed: ' . $authResult['message'],
                ];
            }
            
            // Exchange code for new access token
            $tokenResult = $this->exchangeCodeForToken($authResult['code'], $clientId, $clientSecret);
            
            if (!$tokenResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to get new access token: ' . $tokenResult['message'],
                ];
            }
            
            $newAccessToken = $tokenResult['access_token'];
            
            // Fetch data with new token
            Log::info('Admitad: Fetching data with new access token');
            $dataResult = $this->fetchAdmitadData($newAccessToken, $startDate, $endDate);
            
            if (!$dataResult['success']) {
                return $dataResult;
            }
            
            $totalRecords = count($dataResult['data'] ?? []);
            
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from Admitad (re-authenticated)",
                'data' => [
                    'coupons' => [
                        'campaigns' => $totalRecords,
                        'coupons' => $totalRecords,
                        'purchases' => 0,
                        'total' => $totalRecords,
                        'data' => $dataResult['data'],
                    ],
                ],
                'new_access_token' => encrypt($newAccessToken), // Return new token to update
                'new_cookies' => $cookies,
            ];
            
        } catch (\Exception $e) {
            Log::error('Admitad sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Fetch data from Admitad API
     */
    private function fetchAdmitadData(string $accessToken, string $startDate, string $endDate): array
    {
        try {
            // Convert date format to dd.mm.yyyy for Admitad API
            $startDateFormatted = Carbon::parse($startDate)->format('d.m.Y');
            $endDateFormatted = Carbon::parse($endDate)->format('d.m.Y');
            
            Log::info("Admitad: Fetching data from {$startDateFormatted} to {$endDateFormatted}");
            
            // Fetch actions (both coupons and links)
            $actionsUrl = 'https://api.admitad.com/statistics/actions/';
            $allResults = [];
            
            // Fetch with pagination (offset)
            $offsets = [0, 500, 1000, 1500];
            
            foreach ($offsets as $offset) {
                $response = Http::timeout(60)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ])->get($actionsUrl, [
                        'start_date' => $startDateFormatted,
                        'date_end' => $endDateFormatted,
                        'limit' => 500,
                        'offset' => $offset,
                        'order_by' => 'action_date',
                    ]);
                
                // Check if token expired (401)
                if ($response->status() === 401) {
                    return [
                        'success' => false,
                        'message' => 'Access token expired',
                        'needs_reauth' => true,
                    ];
                }
                
                if (!$response->successful()) {
                    Log::error("Admitad API error: Status {$response->status()}");
                    break; // Stop pagination on error
                }
                
                $data = $response->json();
                $results = $data['results'] ?? [];
                
                if (empty($results)) {
                    break; // No more data
                }
                
                $allResults = array_merge($allResults, $results);
                
                Log::info("Admitad: Fetched " . count($results) . " records at offset {$offset}");
                
                // If we got less than limit, no need to continue
                if (count($results) < 500) {
                    break;
                }
            }
            
            Log::info("Admitad: Total records fetched: " . count($allResults));
            
            // Transform data to standard format
            $transformedData = $this->transformAdmitadData($allResults, $startDate, $endDate);
            
            return [
                'success' => true,
                'data' => $transformedData,
            ];
            
        } catch (\Exception $e) {
            Log::error('Admitad fetchData error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Transform Admitad data to standard format
     * 
     * Purchase Type Detection Logic:
     * 1. If promocode exists → definitely coupon
     * 2. If subid exists → analyze format:
     *    - If subid looks like coupon code (letters+numbers, not purely numeric, 3+ chars) → coupon
     *    - Otherwise → link
     * 3. If neither exists → default to link
     */
    private function transformAdmitadData(array $items, string $startDate, string $endDate): array
    {
        $transformedData = [];
        
        foreach ($items as $item) {
            // return $item;
            $campaignId = $item['advcampaign_id'] ?? null;
            $campaignName = $item['advcampaign_name'] ?? 'Unknown Campaign';
            $status = $item['status'] ?? 'pending';
            $currency = $item['currency'] ?? 'USD';
            $cart = $item['cart'] ?? 0;
            $payment = $item['payment'] ?? 0;
            $actionDate = $item['action_date'] ?? $startDate;
            $actionId = $item['action_id'] ?? null;
            $promocode = $item['promocode'] ?? null;
            $subid = $item['subid'] ?? null;
            
            // Skip if no campaign ID
            if (empty($campaignId)) {
                continue;
            }
            
            // Convert currency from AED to USD if needed
            if ($currency === 'AED') {
                $saleAmount = $cart / 3.67;
                $revenue = $payment / 3.67;
            } else {
                $saleAmount = $cart;
                $revenue = $payment;
            }
            
            // Determine type and code
            $type = 'link'; // default
            $code = '';
            
            if (!empty($promocode)) {
                // This is definitely a coupon purchase
                $type = 'coupon';
                $code = $promocode;
            }  else {
                $type = 'link';
                $code = 'subid-' . $subid;
            }
            
            $transformedItem = [
                'campaign_id' => $campaignId,
                'campaign_name' => $campaignName,
                'code' => $code,
                'type' => $type, // coupon or link
                'purchase_type' => $type, // Add purchase_type field
                'country' => 'NA',
                'order_id' => null,
                'network_order_id' => $actionId,
                'sales_amount' => round((float) $saleAmount, 2),
                'revenue' => round((float) $revenue, 2),
                'quantity' => 1,
                'customer_type' => 'unknown',
                'status' => $this->normalizeAdmitadStatus($status),
                'order_date' => $actionDate,
                'purchase_date' => $actionDate,
                'currency' => $currency,
                'original_cart' => $cart,
                'original_payment' => $payment,
                'subid' => $subid,
                'promocode' => $promocode, // Keep original promocode for reference
                'type_detection' => [
                    'has_promocode' => !empty($promocode),
                    'has_subid' => !empty($subid),
                    'subid_format' => !empty($subid) ? (preg_match('/^[A-Za-z0-9\-_]{3,}$/', $subid) && !is_numeric($subid) ? 'coupon_like' : 'link_like') : null,
                ],
            ];
            
            $transformedData[] = $transformedItem;
        }
        
        return $transformedData;
    }
    
    /**
     * Normalize Admitad status
     */
    private function normalizeAdmitadStatus(string $status): string
    {
        $statusMap = [
            'approved' => 'approved',
            'confirmed' => 'approved',
            'pending' => 'pending',
            'rejected' => 'rejected',
            'declined' => 'rejected',
            'hold' => 'pending',
        ];
        
        return $statusMap[strtolower($status)] ?? 'pending';
    }
    
    /**
     * Parse cookie string to array
     */
    private function parseCookieString(string $cookieString): array
    {
        $cookies = [];
        $parts = explode('; ', $cookieString);
        
        foreach ($parts as $part) {
            $pair = explode('=', $part, 2);
            if (count($pair) === 2) {
                $cookies[$pair[0]] = $pair[1];
            }
        }
        
        return $cookies;
    }
    
    // Helper methods
    
    private function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }
    
    private function extractCookies(array $responseCookies): array
    {
        $cookies = [];
        foreach ($responseCookies as $cookie) {
            $name = $cookie['Name'] ?? '';
            $value = $cookie['Value'] ?? '';
            if ($name && $value) {
                $cookies[$name] = $value;
            }
        }
        return $cookies;
    }
    
    private function buildCookieString(array $cookies): string
    {
        $parts = [];
        foreach ($cookies as $name => $value) {
            $parts[] = "{$name}={$value}";
        }
        return implode('; ', $parts);
    }
    
    private function makeAbsolute(string $location, string $baseUrl): string
    {
        if (empty($location)) {
            return $baseUrl;
        }
        
        if (str_starts_with($location, 'http://') || str_starts_with($location, 'https://')) {
            return $location;
        }
        
        $parsedBase = parse_url($baseUrl);
        $scheme = $parsedBase['scheme'] ?? 'https';
        $host = $parsedBase['host'] ?? '';
        
        if (str_starts_with($location, '/')) {
            return "{$scheme}://{$host}{$location}";
        }
        
        return "{$baseUrl}/{$location}";
    }
    
    private function getBaseUrl(string $url): string
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        return "{$scheme}://{$host}";
    }
}
