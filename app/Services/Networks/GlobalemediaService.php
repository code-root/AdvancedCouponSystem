<?php

namespace App\Services\Networks;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class GlobalemediaService extends BaseNetworkService
{
    protected string $networkName = 'globalemedia';
    
    protected array $requiredFields = [
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'your.email@example.com',
            'help' => 'Your Globalemedia account email',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Enter your password',
            'help' => 'Your Globalemedia account password',
        ],
    ];
    
    protected array $defaultConfig = [
        'base_url' => 'https://login.globalemedia.net',
        'login_url' => 'https://login.globalemedia.net/login.html',
        'api_url' => 'https://login.globalemedia.net/publisher/performance.html',
    ];

    /**
     * Test connection by logging in and getting cookies
     */
    public function testConnection(array $credentials): array
    {
        try {
            // Step 1: Get token and PHPSESSID
            $loginData = $this->getLoginTokenAndSession();
            
            if (!$loginData['success']) {
                return [
                    'success' => false,
                    'message' => $loginData['message'],
                ];
            }
            
            $token = $loginData['token'];
            $phpsessid = $loginData['phpsessid'];
            
            // Step 2: Login with credentials
            $loginResult = $this->performLogin(
                $credentials['email'],
                $credentials['password'],
                $token,
                $phpsessid
            );
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Login failed: ' . $loginResult['message'],
                ];
            }
            
            // Step 3: Store cookies for future use
            $cookieString = $this->buildCookieString($loginResult['cookies']);
            
            return [
                'success' => true,
                'message' => 'Successfully connected to Globalemedia!',
                'data' => [
                    'token' => $token,
                    'phpsessid' => $phpsessid,
                    'cookies' => $cookieString,
                    'all_cookies' => $loginResult['cookies'],
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('Globalemedia connection test failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Step 1: Get login token and PHPSESSID
     */
    private function getLoginTokenAndSession(): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Connection' => 'keep-alive',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
            ])->get($this->defaultConfig['login_url']);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to load login page',
                ];
            }
            
            $html = $response->body();
            
            // Extract token using regex
            preg_match('/name="token" value="([^"]+)"/', $html, $tokenMatches);
            $token = $tokenMatches[1] ?? null;
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to extract login token',
                ];
            }
            
            // Extract PHPSESSID from cookies
            $cookies = $response->cookies()->toArray();
            $phpsessid = null;
            
            foreach ($cookies as $cookie) {
                if ($cookie['Name'] === 'PHPSESSID') {
                    $phpsessid = $cookie['Value'];
                    break;
                }
            }
            
            if (!$phpsessid) {
                return [
                    'success' => false,
                    'message' => 'Failed to get session cookie',
                ];
            }
            
            return [
                'success' => true,
                'token' => $token,
                'phpsessid' => $phpsessid,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting login token: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Step 2: Perform login with credentials
     */
    private function performLogin(string $email, string $password, string $token, string $phpsessid): array
    {
        try {
            // Encode password to base64
            $encodedPassword = base64_encode($password);
            
            $response = Http::withOptions([
                'allow_redirects' => false, // Don't follow redirects
            ])->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'max-age=0',
                'Connection' => 'keep-alive',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Cookie' => 'PHPSESSID=' . $phpsessid,
                'Origin' => 'https://login.globalemedia.net',
                'Referer' => 'https://login.globalemedia.net/login.html',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'same-origin',
                'Sec-Fetch-User' => '?1',
            ])->asForm()->post($this->defaultConfig['login_url'], [
                'email' => $email,
                'password' => $encodedPassword,
                'is_pass_encoded' => 'true',
                'action' => 'login',
                'token' => $token,
            ]);
            
            // Get all cookies from response
            $allCookies = $response->cookies()->toArray();
            
            // Check if login was successful (should redirect or return 200)
            if ($response->successful() || $response->redirect()) {
                // Merge original PHPSESSID with new cookies
                $finalCookies = [
                    ['Name' => 'PHPSESSID', 'Value' => $phpsessid]
                ];
                
                foreach ($allCookies as $cookie) {
                    if ($cookie['Name'] !== 'PHPSESSID') {
                        $finalCookies[] = $cookie;
                    }
                }
                
                return [
                    'success' => true,
                    'cookies' => $finalCookies,
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Invalid credentials or login failed',
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build cookie string from cookies array
     */
    private function buildCookieString(array $cookies): string
    {
        $cookieParts = [];
        
        foreach ($cookies as $cookie) {
            $name = $cookie['Name'] ?? '';
            $value = $cookie['Value'] ?? '';
            if ($name && $value) {
                $cookieParts[] = "{$name}={$value}";
            }
        }
        
        return implode('; ', $cookieParts);
    }

    /**
     * Sync data from Globalemedia
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            // Extract dates from config
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            // Try with stored cookies first
            $cookieString = $credentials['cookies'] ?? '';
            
            if (!empty($cookieString)) {
                // Fetch performance data
                $performanceData = $this->getPerformanceData($cookieString, $startDate, $endDate);
                
                // If successful, return data
                if ($performanceData['success']) {
                    $totalRecords = count($performanceData['data'] ?? []);
                    
                    return [
                        'success' => true,
                        'message' => "Successfully synced {$totalRecords} records from Globalemedia",
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
            }
            
            // If cookies failed or not available, re-login
            if (empty($credentials['email']) || empty($credentials['password'])) {
                return [
                    'success' => false,
                    'message' => 'Session expired. Please reconnect with your credentials.',
                ];
            }
            
            // Decrypt password if encrypted
            $password = $credentials['password'];
            
            // Re-authenticate
            $loginResult = $this->testConnection([
                'email' => $credentials['email'],
                'password' => $password,
            ]);
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Session expired and re-login failed. Please reconnect.',
                ];
            }
            
            // Get new cookies
            $newCookieString = $loginResult['data']['cookies'];
            
            // Fetch performance data with new cookies
            $performanceData = $this->getPerformanceData($newCookieString, $startDate, $endDate);
            
            if (!$performanceData['success']) {
                return $performanceData;
            }
            
            $totalRecords = count($performanceData['data'] ?? []);
            
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from Globalemedia (re-authenticated)",
                'data' => [
                    'coupons' => [
                        'campaigns' => $totalRecords,
                        'coupons' => $totalRecords,
                        'purchases' => 0,
                        'total' => $totalRecords,
                        'data' => $performanceData['data'],
                    ],
                ],
                'new_cookies' => $newCookieString, // Return new cookies to update
            ];
            
        } catch (\Exception $e) {
            Log::error('Globalemedia sync failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance data from Globalemedia
     */
    private function getPerformanceData(string $cookieString, string $startDate, string $endDate): array
    {
        try {
            // Build query params to match Platformance
            $params = [
                'group' => ['adid', 'coupon'],
                'fields' => [
                    'totalClicks',
                    'totalConversions',
                    'payout',
                    'cr',
                    'saleAmount',
                    'extConv',
                    'pendingTotalConversions',
                    'pendingPayout',
                    'cancelledConversions'
                ],
                'start' => $startDate,
                'end' => $endDate,
                'report_name' => 'performance',
                'zone' => 'Asia/Dubai',
            ];
            
            // Build URL with proper encoding
            $queryString = '';
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $queryString .= $key . '[]=' . urlencode($item) . '&';
                    }
                } else {
                    $queryString .= $key . '=' . urlencode($value) . '&';
                }
            }
            $queryString = rtrim($queryString, '&');
            
            $url = $this->defaultConfig['api_url'] . '?' . $queryString;
            
            $response = Http::withOptions([
                'allow_redirects' => false, // Don't follow redirects
            ])->withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cookie' => $cookieString,
                'Referer' => 'https://login.globalemedia.net/publisher/',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'same-origin',
            ])->get($url);
            
            // Check if redirected (session expired)
            if ($response->status() === 302 || $response->redirect()) {
                return [
                    'success' => false,
                    'message' => 'Session expired',
                    'needs_reauth' => true,
                ];
            }
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch data. Status: ' . $response->status(),
                ];
            }
            
            // Parse HTML response
            $html = $response->body();
            
            $parsedData = $this->parsePerformanceHTML($html);
            
            return [
                'success' => true,
                'data' => $parsedData,
                'raw_html_length' => strlen($html),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse HTML table to extract performance data
     */
    private function parsePerformanceHTML(string $html): array
    {
        $data = [];
        
        try {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            
            // Find all table rows in tbody
            $rows = $xpath->query('//tbody/tr');
            
            foreach ($rows as $rowIndex => $row) {
                $columns = $row->getElementsByTagName('td');
                
                // Skip if not enough columns
                if ($columns->length < 13) {
                    continue;
                }
                
                // Extract data matching Globalemedia structure
                // Based on the Logi5/Globalemedia controller code
                $campaign = trim($columns->item(0)->nodeValue ?? '');
                $couponCode = trim($columns->item(1)->nodeValue ?? '');
                $clicks = trim($columns->item(2)->nodeValue ?? '0');
                $conversions = trim($columns->item(3)->nodeValue ?? '0');
                $payout = trim($columns->item(4)->nodeValue ?? '0');
                $saleAmount = trim($columns->item(12)->nodeValue ?? '0'); // Column 12 for sale amount
                
                // Skip if no campaign name or coupon code
                if (empty($campaign) || empty($couponCode)) {
                    continue;
                }
                
                $data[] = [
                    'campaign_id' => $campaign,
                    'campaign_name' => $campaign,
                    'code' => $couponCode,
                    'country' => 'NA',
                    'order_id' => null,
                    'network_order_id' => null,
                    'order_value' => $this->convertToDecimal($saleAmount),
                    'commission' => $this->convertToDecimal($payout),
                    'revenue' => $this->convertToDecimal($payout),
                    'clicks' => intval($clicks),
                    'quantity' => intval($conversions) > 0 ? intval($conversions) : 1,
                    'customer_type' => 'unknown',
                    'status' => 'approved',
                    'order_date' => now()->format('Y-m-d'),
                    'purchase_date' => now()->format('Y-m-d'),
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Error parsing Globalemedia HTML: ' . $e->getMessage());
        }
        
        return $data;
    }

    /**
     * Convert currency string to decimal
     */
    private function convertToDecimal(string $value): float
    {
        // Remove currency symbols and commas
        $cleaned = preg_replace('/[^0-9.\-]/', '', $value);
        return floatval($cleaned);
    }

    /**
     * Validate connection (placeholder - uses testConnection)
     */
    public function validateConnection($connection): bool
    {
        try {
            if (!isset($connection->credentials['cookies'])) {
                return false;
            }

            // Simple validation - check if cookies exist
            return !empty($connection->credentials['cookies']);

        } catch (\Exception $e) {
            Log::error("Globalemedia connection validation failed: {$e->getMessage()}");
            return false;
        }
    }
}
