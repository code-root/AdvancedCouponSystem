<?php

namespace App\Services\Networks;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\NetworkProxy;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class GlobalemediaService extends BaseNetworkService
{
    protected string $networkName = 'globalemedia';
    
    protected array $requiredFields = [
        'email',
        'password',
    ];
    
    protected array $defaultConfig = [
        'base_url' => 'https://login.globalemedia.net',
        'login_url' => 'https://login.globalemedia.net/login.html',
        'api_url' => 'https://login.globalemedia.net/publisher/performance.html',
        'timeout' => 30,
        'retry_attempts' => 2,
        'retry_delay' => 2,
    ];
    
    private $selectedProxy = null;

    /**
     * Get random proxy for this network
     */
    private function getRandomProxy(): ?NetworkProxy
    {
        return NetworkProxy::activeForNetwork($this->networkName)
            ->inRandomOrder()
            ->first();
    }
    
    /**
     * Create Guzzle client with proxy support
     */
    private function createClient(array $config = []): Client
    {
        $defaultConfig = [
            'timeout' => $this->defaultConfig['timeout'],
            'verify' => false,
            'allow_redirects' => false,
        ];
        
        if ($this->selectedProxy) {
            $defaultConfig['proxy'] = $this->selectedProxy->toGuzzleProxyArray();
        }
        
        return new Client(array_merge($defaultConfig, $config));
    }

    /**
     * Test connection with proxy support
     */
    public function testConnection(array $credentials): array
    {
        $maxRetries = $this->defaultConfig['retry_attempts'];
        $retryDelay = $this->defaultConfig['retry_delay'];
        $attempt = 0;
        $lastError = '';
        
        // Get available proxies
        $proxies = NetworkProxy::activeForNetwork($this->networkName)
            ->inRandomOrder()
            ->limit(3)
            ->get();
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                // Select proxy for this attempt
                $this->selectedProxy = $proxies[$attempt - 1] ?? null;
                
                // Create shared cookie jar for the entire connection process
                $sharedCookieJar = new CookieJar();
                
                // Step 1: Get fresh token and PHPSESSID
                $loginData = $this->getLoginTokenAndSession($sharedCookieJar);
                
                if (!$loginData['success']) {
                    $lastError = $loginData['message'];
                    if ($this->selectedProxy) {
                        $this->selectedProxy->markFailure();
                    }
                    
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                    
                    return [
                        'success' => false,
                        'message' => $lastError,
                    ];
                }
                
                $token = $loginData['token'];
                $phpsessid = $loginData['phpsessid'];
                
                // Step 2: Login with credentials using the same cookie jar
                $loginResult = $this->performLogin(
                    $credentials['email'],
                    $credentials['password'],
                    $token,
                    $phpsessid,
                    $sharedCookieJar
                );
                
                if (!$loginResult['success']) {
                    $lastError = $loginResult['message'];
                    if ($this->selectedProxy) {
                        $this->selectedProxy->markFailure();
                    }
                    
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        continue;
                    }
                    
                    return [
                        'success' => false,
                        'message' => 'Login failed: ' . $lastError,
                    ];
                }
                
                $cookieString = $this->buildCookieString($loginResult['cookies']);
                
                return [
                    'success' => true,
                    'message' => 'Successfully connected to Globalemedia!' . ($attempt > 1 ? " (after {$attempt} attempts)" : ''),
                    'data' => [
                        'token' => $token,
                        'phpsessid' => $phpsessid,
                        'cookies' => $cookieString,
                        'all_cookies' => $loginResult['cookies'],
                        'cookie_jar' => $sharedCookieJar,
                        'proxy' => $this->selectedProxy ? $this->selectedProxy->toGuzzleProxyArray() : null,
                    ],
                ];
                
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                if ($this->selectedProxy) {
                    $this->selectedProxy->markFailure();
                }
                
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
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
     * Get login token and PHPSESSID with proxy support
     */
    private function getLoginTokenAndSession($cookieJar = null): array
    {
        try {
            // Use provided cookie jar or create new one
            if (!$cookieJar) {
                $cookieJar = new CookieJar();
            }
            
            $client = $this->createClient(['cookies' => $cookieJar]);
            
            $headers = [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Connection' => 'keep-alive',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            ];
            
            $response = $client->get($this->defaultConfig['login_url'], [
                'headers' => $headers,
            ]);
            
            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'message' => 'Failed to load login page: HTTP ' . $response->getStatusCode(),
                ];
            }
            
            $html = $response->getBody()->getContents();
            
            if (!preg_match('/name="token" value="([^"]+)"/', $html, $tokenMatches)) {
                return [
                    'success' => false,
                    'message' => 'Failed to extract login token from HTML',
                ];
            }
            
            $token = $tokenMatches[1];
            
            // Extract PHPSESSID from cookies
            $phpsessid = null;
            foreach ($cookieJar->getIterator() as $cookie) {
                if ($cookie->getName() === 'PHPSESSID') {
                    $phpsessid = $cookie->getValue();
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
     * Perform login with credentials and proxy support
     */
    private function performLogin(string $email, string $password, string $token, string $phpsessid, $cookieJar = null): array
    {
        try {
            // Use provided cookie jar or create new one
            if (!$cookieJar) {
                $cookieJar = new CookieJar();
            }
            
            $client = $this->createClient(['cookies' => $cookieJar]);
            
            $encodedPassword = base64_encode($password);
            $postData = http_build_query([
                'email' => $email,
                'password' => $encodedPassword,
                'is_pass_encoded' => 'true',
                'action' => 'login',
                'token' => $token,
            ]);
            
            $headers = [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Host' => 'login.globalemedia.net',
                'Origin' => 'https://login.globalemedia.net',
                'Referer' => 'https://login.globalemedia.net/login.html',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            ];
            
            $response = $client->post($this->defaultConfig['login_url'], [
                'headers' => $headers,
                'body' => $postData,
            ]);
            
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 302) {
                $allCookies = [];
                foreach ($cookieJar->getIterator() as $cookie) {
                    $allCookies[] = [
                        'Name' => $cookie->getName(),
                        'Value' => $cookie->getValue(),
                        'Domain' => $cookie->getDomain(),
                        'Path' => $cookie->getPath(),
                    ];
                }
                
                return [
                    'success' => true,
                    'cookies' => $allCookies,
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Invalid credentials or login failed (HTTP ' . $response->getStatusCode() . ')',
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
     * Sync data from Globalemedia with proxy support
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $startDate = $config['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? now()->format('Y-m-d');
            
            if (empty($credentials['email']) || empty($credentials['password'])) {
                return [
                    'success' => false,
                    'message' => 'Email and password are required for authentication.',
                ];
            }
            
            // Try with stored cookies first if available
            $cookieString = $credentials['cookies'] ?? '';
            $proxyConfig = $credentials['proxy'] ?? null;
            $cookieJar = $credentials['cookie_jar'] ?? null;
            
            if (!empty($cookieString)) {
                $performanceData = $this->getPerformanceData($cookieString, $startDate, $endDate, $proxyConfig, $cookieJar);
                
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
                        'new_cookies' => $cookieString,
                        'new_proxy' => $proxyConfig,
                        'new_cookie_jar' => $performanceData['cookie_jar'] ?? $cookieJar,
                    ];
                }
            }
            
            // Re-authenticate with fresh login
            $loginResult = $this->testConnection([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ]);
            
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Session expired and re-login failed. Please reconnect.',
                ];
            }
            
            $newCookieString = $loginResult['data']['cookies'];
            $newProxyConfig = $loginResult['data']['proxy'] ?? null;
            $newCookieJar = $loginResult['data']['cookie_jar'] ?? null;
            
            // Fetch performance data with new cookies
            $performanceData = $this->getPerformanceData($newCookieString, $startDate, $endDate, $newProxyConfig, $newCookieJar);
            
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
                'new_cookies' => $newCookieString,
                'new_proxy' => $newProxyConfig,
                'new_cookie_jar' => $performanceData['cookie_jar'] ?? $newCookieJar,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance data with proxy support
     */
    private function getPerformanceData(string $cookieString, string $startDate, string $endDate, $proxyConfig = null, $cookieJar = null): array
    {
        try {
            // Use provided cookie jar or create new one from cookie string
            if (!$cookieJar) {
                $cookieJar = new CookieJar();
                
                // Parse cookie string and add to jar
                $cookiePairs = explode(';', $cookieString);
                foreach ($cookiePairs as $pair) {
                    $pair = trim($pair);
                    if (strpos($pair, '=') !== false) {
                        list($name, $value) = explode('=', $pair, 2);
                        $cookieJar->setCookie(new \GuzzleHttp\Cookie\SetCookie([
                            'Name' => trim($name),
                            'Value' => trim($value),
                            'Domain' => 'login.globalemedia.net',
                            'Path' => '/',
                            'Secure' => true,
                            'HttpOnly' => false,
                        ]));
                    }
                }
            }
            
            $clientConfig = [
                'timeout' => $this->defaultConfig['timeout'],
                'cookies' => $cookieJar,
                'verify' => false,
                'allow_redirects' => false,
            ];
            
            if ($proxyConfig) {
                $clientConfig['proxy'] = $proxyConfig;
            }
            
            $client = new Client($clientConfig);
            
            // Build query parameters
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
            
            $headers = [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'Host' => 'login.globalemedia.net',
                'Referer' => 'https://login.globalemedia.net/publisher/',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
            ];
            
            $response = $client->get($url, [
                'headers' => $headers,
            ]);
            
            // Check if redirected (session expired)
            if ($response->getStatusCode() === 302) {
                $location = $response->getHeader('Location')[0] ?? 'unknown';
                
                // Check if redirected to login page
                if (strpos($location, 'login') !== false) {
                    return [
                        'success' => false,
                        'message' => 'Session expired - redirected to login',
                        'needs_reauth' => true,
                    ];
                }
                
                // If redirected to a different page, try to follow the redirect
                $redirectUrl = $location;
                if (strpos($redirectUrl, 'http') !== 0) {
                    $redirectUrl = 'https://login.globalemedia.net' . $redirectUrl;
                }
                
                $redirectResponse = $client->get($redirectUrl, [
                    'headers' => $headers,
                ]);
                
                if ($redirectResponse->getStatusCode() === 200) {
                    $html = $redirectResponse->getBody()->getContents();
                    $parsedData = $this->parsePerformanceHTML($html);
                    
                    return [
                        'success' => true,
                        'data' => $parsedData,
                        'cookie_jar' => $cookieJar,
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Failed to follow redirect',
                    'needs_reauth' => true,
                ];
            }
            
            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch data. Status: ' . $response->getStatusCode(),
                ];
            }
            
            $html = $response->getBody()->getContents();
            $parsedData = $this->parsePerformanceHTML($html);
            
            return [
                'success' => true,
                'data' => $parsedData,
                'cookie_jar' => $cookieJar,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse HTML table to extract performance data (optimized)
     */
    private function parsePerformanceHTML(string $html): array
    {
        $data = [];
        
        try {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            
            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//tbody/tr');
            $currentDate = now()->format('Y-m-d');
            
            foreach ($rows as $row) {
                if (!$row instanceof \DOMElement) {
                    continue;
                }
                $columns = $row->getElementsByTagName('td');
                
                if ($columns->length < 13) {
                    continue;
                }
                
                $campaign = trim($columns->item(0)->nodeValue ?? '');
                $couponCode = trim($columns->item(1)->nodeValue ?? '');
                $clicks = trim($columns->item(2)->nodeValue ?? '0');
                $conversions = trim($columns->item(3)->nodeValue ?? '0');
                $payout = trim($columns->item(4)->nodeValue ?? '0');
                $saleAmount = trim($columns->item(12)->nodeValue ?? '0');
                
                if (empty($campaign) || empty($couponCode)) {
                    continue;
                }
                
                $data[] = [
                    'campaign_id' => $campaign,
                    'campaign_name' => $campaign,
                    'code' => $couponCode,
                    'purchase_type' => 'coupon',
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
                    'order_date' => $currentDate,
                    'purchase_date' => $currentDate,
                ];
            }
            
        } catch (\Exception $e) {
            // Error parsing HTML
        }
        
        return $data;
    }

    /**
     * Convert currency string to decimal (optimized)
     */
    private function convertToDecimal(string $value): float
    {
        $cleaned = preg_replace('/[^\d.]/', '', $value);
        return (float)$cleaned;
    }
}
