<?php

namespace App\Services\Networks;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use App\Models\NetworkProxy;
use App\Helpers\NetworkDataProcessor;

class MarketeersService extends BaseNetworkService
{
    protected string $networkName = 'marketeers';
    
    protected array $requiredFields = [
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'your.email@example.com',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Enter your password',
        ],
    ];
    
    protected array $defaultConfig = [
        'frontend_url' => 'https://marketeers.ollkom.com',
        'backend_url' => 'https://marketeers-backend-prod-oci.ollkom.com',
        'timeout' => 30,
        'retry_attempts' => 2,
        'request_delay' => 0.1,
        'verify_ssl' => false,
        'page_size' => 100,
    ];
    
    /**
     * Test connection: Full auth flow (CSRF -> login -> session)
     */
    public function testConnection(array $credentials): array
    {
        // Validate
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Invalid credentials provided',
                'errors' => $validation['errors'],
            ];
        }
        
        try {
            $frontend = rtrim($this->defaultConfig['frontend_url'], '/');
            
            $clientConfig = [
                'base_uri' => $frontend . '/',
                'cookies' => true,
                'verify' => ($this->defaultConfig['verify_ssl'] ?? true),
                'timeout' => $this->defaultConfig['timeout'] ?? 15,
                'headers' => $this->buildBaseHeaders(),
            ];

            // Try pick a random active proxy for this network
            // Proxy-only mode: try up to 10 proxies, no direct fallback
            $proxies = NetworkProxy::activeForNetwork($this->networkName)
                ->inRandomOrder()
                ->limit(3)
                ->get();
            $attempts = 0;
            $client = null;
            $pickedProxy = null;
            do {
                $attempts++;
                $pickedProxy = $proxies[$attempts - 1] ?? null;
                if (!$pickedProxy) {
                    break;
                }
                $cfg = $clientConfig;
                $cfg['proxy'] = $pickedProxy->toGuzzleProxyArray();
                $client = new Client($cfg);
                try {
                    // lightweight probe to validate proxy
                    $client->request('GET', 'api/auth/csrf', [ 'headers' => ['accept' => 'application/json'] ]);
                    break; // proxy ok
                } catch (\Exception $e) {
                    $pickedProxy->markFailure();
                    $client = null;
                    continue;
                }
            } while ($attempts < max(1, $proxies->count()));
            if (!$client) {
                return [
                    'success' => false,
                    'message' => 'No working proxies available for marketeers. Please update proxy list.',
                ];
            }
            
            // Step 1: CSRF
            try {
                $csrfResp = $client->request('GET', 'api/auth/csrf', [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept' => 'application/json, text/plain, */*',
                    'sec-fetch-site' => 'same-origin',
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-dest' => 'empty',
                    'referer' => $frontend . '/publisher/login',
                    'origin' => $frontend,
                ],
                ]);
            } catch (\Exception $e) {
                // If proxy failed, mark it and retry once without proxy
                if (isset($proxy)) {
                    $proxy->markFailure();
                }
                $client = new Client([
                    'base_uri' => $frontend . '/',
                    'cookies' => true,
                    'verify' => ($this->defaultConfig['verify_ssl'] ?? true),
                    'timeout' => $this->defaultConfig['timeout'] ?? 30,
                    'headers' => $this->buildBaseHeaders(),
                ]);
                $csrfResp = $client->request('GET', 'api/auth/csrf', [
                    'headers' => [
                        'content-type' => 'application/json',
                        'accept' => 'application/json, text/plain, */*',
                        'sec-fetch-site' => 'same-origin',
                        'sec-fetch-mode' => 'cors',
                        'sec-fetch-dest' => 'empty',
                        'referer' => $frontend . '/publisher/login',
                        'origin' => $frontend,
                    ],
                ]);
            }
            $csrfJson = json_decode((string)$csrfResp->getBody(), true);
            $csrfToken = $csrfJson['csrfToken'] ?? null;
            if (!$csrfToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain CSRF token',
                ];
            }
            
            // Step 2: Login
            $loginResp = $client->request('POST', 'api/auth/callback/credentials', [
                'headers' => [
                    'content-type' => 'application/x-www-form-urlencoded',
                    'accept' => '*/*',
                    'origin' => $frontend,
                    'referer' => $frontend . '/publisher/login',
                    'sec-fetch-site' => 'same-origin',
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-dest' => 'empty',
                ],
                'form_params' => [
                    'redirect' => 'false',
                    'email' => $credentials['email'],
                    'password' => $credentials['password'],
                    'csrfToken' => $csrfToken,
                    'callbackUrl' => $frontend . '/publisher/login',
                    'json' => 'true',
                ],
            ]);
            $status = $loginResp->getStatusCode();
            if ($status < 200 || $status >= 400) {
                return [
                    'success' => false,
                    'message' => 'Login failed with status ' . $status,
                ];
            }
            
            // Step 3: Session info
            $sessionResp = $client->request('GET', 'api/auth/session', [
                'headers' => [
                    'content-type' => 'application/json',
                    'accept' => 'application/json, text/plain, */*',
                    'referer' => $frontend . '/publisher/login',
                    'sec-fetch-site' => 'same-origin',
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-dest' => 'empty',
                ],
            ]);
            $sessionJson = json_decode((string)$sessionResp->getBody(), true);
            $accessToken = $sessionJson['accessToken'] ?? null;
            $userId = $sessionJson['userDetail']['id'] ?? null;
            $publisherId = $sessionJson['userDetail']['publisher'] ?? null;
            
            if (empty($accessToken) || empty($publisherId)) {
                return [
                    'success' => false,
                    'message' => 'Missing access token or publisher id',
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Successfully connected to Marketeers',
                'data' => [
                    'access_token' => $accessToken,
                    'user_id' => $userId,
                    'publisher_id' => $publisherId,
                    // pass proxy info to reuse in fetch
                    'proxy' => isset($pickedProxy) ? $pickedProxy->toGuzzleProxyArray() : null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Marketeers connection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Sync data for current month by default, with pagination
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            // Ensure we have tokens (either from prior testConnection or do a quick auth)
            $accessToken = $credentials['access_token'] ?? null;
            $publisherId = $credentials['publisher_id'] ?? null;
            $proxyConfig = $credentials['proxy'] ?? null;
            
            if (empty($accessToken) || empty($publisherId)) {
                $conn = $this->testConnection($credentials);
                if (!($conn['success'] ?? false)) {
                    return $conn;
                }
                $accessToken = $conn['data']['access_token'] ?? null;
                $publisherId = $conn['data']['publisher_id'] ?? null;
                $proxyConfig = $conn['data']['proxy'] ?? null;
            }
            
            // Date range: current month by default
            $startDate = $config['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = $config['date_to'] ?? Carbon::now()->format('Y-m-d');
            
            $pageSize = min((int)($config['page_size'] ?? $this->defaultConfig['page_size']), 100);
            
            $allResults = [];
            $page = 1;
            
            // Build Guzzle client for backend fetch (reuse proxy)
            $backendBase = rtrim($this->defaultConfig['backend_url'], '/');
            $clientCfg = [
                'base_uri' => $backendBase . '/',
                'cookies' => false,
                'verify' => ($this->defaultConfig['verify_ssl'] ?? true),
                'timeout' => $this->defaultConfig['timeout'] ?? 30,
                'headers' => $this->buildBaseHeaders(),
            ];
            if (!empty($proxyConfig)) {
                $clientCfg['proxy'] = $proxyConfig;
            }
            $guzzle = new \GuzzleHttp\Client($clientCfg);
            
            while (true) {
                $params = [
                    'all' => 'true',
                    'publisher' => (string)$publisherId,
                    'currency__name' => $config['currency'] ?? 'USD',
                    'page' => $page,
                    'page_size' => $pageSize,
                    'order_date_after' => $startDate,
                    'order_date_before' => $endDate,
                ];
                
                try {
                    $resp = $guzzle->request('GET', 'api/v1/coupon_conversion_history/', [
                        'headers' => $this->buildAuthHeaders($accessToken),
                        'query' => $params,
                    ]);
                } catch (\Exception $e) {
                    return [
                        'success' => false,
                        'message' => 'Fetch failed: ' . $e->getMessage(),
                    ];
                }
                $status = $resp->getStatusCode();
                if ($status < 200 || $status >= 300) {
                    return [
                        'success' => false,
                        'message' => 'Fetch failed: HTTP ' . $status,
                    ];
                }
                $data = json_decode((string)$resp->getBody(), true) ?? [];
                $results = $data['results'] ?? [];
                $allResults = array_merge($allResults, $results);
                if (empty($data['next'])) {
                    break;
                }
                $page++;
                usleep((int)round($this->defaultConfig['request_delay'] * 1_000_000));
            }
            
            // Transform Marketeers data to standard format
            $transformedData = $this->transformMarketeersData($allResults);
            
            // Process and save to database if network_id and user_id are provided
            if (isset($config['network_id']) && isset($config['user_id'])) {
                $processResult = NetworkDataProcessor::processCouponData(
                    $transformedData,
                    $config['network_id'],
                    $config['user_id'],
                    $startDate,
                    $endDate,
                    'marketeers'
                );
                
                $processed = $processResult['processed'] ?? ['campaigns' => 0, 'coupons' => 0, 'purchases' => 0, 'errors' => []];
                
                return [
                    'success' => true,
                    'message' => "Successfully synced {$processed['purchases']} records from Marketeers",
                    'data' => [
                        'coupons' => [
                            'campaigns' => $processed['campaigns'],
                            'coupons' => $processed['coupons'],
                            'purchases' => $processed['purchases'],
                            'total' => count($allResults),
                            'data' => $transformedData,
                        ],
                    ],
                ];
            }
            
            // Return raw data if no database saving requested
            return [
                'success' => true,
                'message' => 'Successfully synced ' . count($allResults) . ' records from Marketeers',
                'data' => [
                    'coupons' => [
                        'campaigns' => count($allResults),
                        'coupons' => count($allResults),
                        'purchases' => 0,
                        'total' => count($allResults),
                        'data' => $transformedData,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Marketeers sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Transform Marketeers data to standard format for database storage
     */
    private function transformMarketeersData(array $items): array
    {
        $transformed = [];
        
        foreach ($items as $item) {
            // Extract campaign data
            $campaignId = $item['campaign']['id'] ?? null;
            $campaignName = $item['campaign']['title'] ?? 'Unknown Campaign';
            
            // Extract coupon code
            $code = $item['coupon']['code'] ?? 'MKT-' . $campaignId;
            
            // Extract financial data (use USD values)
            $orderValue = (float)($item['order_amount_usd'] ?? $item['order_amount'] ?? 0);
            $commission =(float)($item['order_amount_usd'] ?? $item['order_amount'] ?? 0);
            $revenue = (float)($item['payout'] ?? $item['payout_usd'] ?? 0);
            
            // Extract country code
            $countryCode = $item['country']['isoalpha2'] ?? 'NA';
            
            // Extract order IDs
            $networkOrderId = $item['markteers_order_id'] ?? null;
            $orderId = $item['advertiser_order_id'] ?? null;
            
            // Extract dates
            $orderDate = isset($item['order_date']) 
                ? Carbon::parse($item['order_date'])->format('Y-m-d') 
                : Carbon::now()->format('Y-m-d');
            
            // Determine status (Marketeers uses status field)
            $status = strtolower($item['status'] ?? 'pending');
            $statusMap = [
                'approved' => 'approved',
                'pending' => 'pending',
                'rejected' => 'rejected',
                'paid' => 'paid',
            ];
            $normalizedStatus = $statusMap[$status] ?? 'pending';
            
            $transformed[] = [
                'campaign_id' => $campaignId,
                'campaign_name' => $campaignName,
                'code' => $code,
                'purchase_type' => 'coupon',
                'country' => $countryCode,
                'order_id' => $orderId,
                'network_order_id' => $networkOrderId,
                'order_value' => $orderValue,
                'commission' => $commission,
                'revenue' => $revenue,
                'quantity' => (int)($item['order_quantity'] ?? 1),
                'customer_type' => 'unknown',
                'status' => $normalizedStatus,
                'order_date' => $orderDate,
                'purchase_date' => $orderDate,
            ];
        }
        
        return $transformed;
    }
    
    private function buildBaseHeaders(array $extras = []): array
    {
        // Align with Python headers
        $base = [
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
            'sec-ch-ua' => '"Google Chrome";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
            // Match Python client encodings to avoid unsupported compressed bodies
            'accept-encoding' => 'gzip, deflate',
            'accept-language' => 'en-US,en;q=0.9',
            'connection' => 'keep-alive',
        ];
        return array_merge($base, $extras);
    }
    
    private function buildAuthHeaders(string $accessToken, array $extras = []): array
    {
        return $this->buildBaseHeaders(array_merge([
            'authorization' => 'Bearer ' . $accessToken,
            'accept' => 'application/json, text/plain, */*',
            'origin' => $this->defaultConfig['frontend_url'],
            'sec-fetch-site' => 'same-site',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-dest' => 'empty',
            'referer' => rtrim($this->defaultConfig['frontend_url'], '/') . '/',
        ], $extras));
    }

    /**
     * Extract cookies from Set-Cookie header(s)
     */
    private function extractCookiesFromSetCookieHeader($setCookieHeader): array
    {
        $cookies = [];
        if (empty($setCookieHeader)) {
            return $cookies;
        }
        $headers = is_array($setCookieHeader) ? $setCookieHeader : [$setCookieHeader];
        foreach ($headers as $line) {
            $segments = explode(';', (string) $line);
            $kv = $segments[0] ?? '';
            if ($kv === '') { continue; }
            $parts = explode('=', $kv, 2);
            if (count($parts) === 2) {
                $name = trim($parts[0]);
                $value = trim($parts[1]);
                if ($name !== '' && $value !== '') {
                    $cookies[$name] = $value;
                }
            }
        }
        return $cookies;
    }

    /**
     * Build Cookie header string from array
     */
    private function buildCookieHeader(array $cookies): string
    {
        $pairs = [];
        foreach ($cookies as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }
        return implode('; ', $pairs);
    }
}
