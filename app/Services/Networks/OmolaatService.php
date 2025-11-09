<?php

namespace App\Services\Networks;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use App\Services\Networks\Omolaat\Client as OmClient;
use App\Services\Networks\Omolaat\Crypto as OmCrypto;

class OmolaatService extends BaseNetworkService
{
    protected string $networkName = 'omolaat';

    protected array $requiredFields = [
        'email' => [
            'label' => 'Email',
            'type' => 'email',
            'required' => true,
            'placeholder' => 'your.email@example.com',
            'help' => 'Your Omolaat account email',
        ],
        'password' => [
            'label' => 'Password',
            'type' => 'password',
            'required' => true,
            'placeholder' => 'Enter your password',
            'help' => 'Your Omolaat account password',
        ],
    ];
    
    protected array $defaultConfig = [
        'base_url' => 'https://my.omolaat.com',
        'login_url' => 'https://my.omolaat.com/workflow/start',
        'search_url' => 'https://my.omolaat.com/elasticsearch/msearch',
        'init_url' => 'https://my.omolaat.com/api/1.1/init/data',
        'user_hi_url' => 'https://my.omolaat.com/user/hi',
    ];

    // Fixed IVs used by Bubble.io (same as Python version)
    private const FIXED_IV_Y = 'po9';
    private const FIXED_IV_X = 'fl1';

    /**
     * Test connection by logging in and getting session (with retry mechanism)
     */
    public function testConnection(array $credentials): array
    {
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Missing required credentials',
                'errors' => $validation['errors'],
            ];
        }

        try {
            $client = new OmClient();
            $loginRes = $client->login((string) $credentials['email'], (string) $credentials['password']);

            // استخراج user_id من الكوكيز
            $userId = null;
            foreach ($loginRes['cookies'] as $ck => $cv) {
                if ($ck === 'omolaat_live_u2main') {
                    $parts = explode('|', $cv);
                    if (count($parts) > 1) {
                        $userId = $parts[1];
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Successfully connected to Omolaat',
                'data' => [
                    'cookies' => $this->cookiesArrayToHeader($loginRes['cookies'] ?? []),
                    'user_id' => $userId,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Omolaat testConnection error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }



    /**
     * Sync data from Omolaat using encrypted search (with re-authentication)
     */
    public function syncData(array $credentials, array $config = []): array
    {
        $validation = $this->validateCredentials($credentials);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Missing required credentials',
                'errors' => $validation['errors'],
            ];
        }

        try {
            // استدعاء سكربت CLI المرجعي مباشرة وأخذ الرد
            $cli = $this->runOmolaatCli($credentials, $config);
            if (!$cli['success']) {
                return $cli;
            }

            $responses = $cli['responses'];

            // استخراج hits وتفريغها من النتائج الجديدة (يوم بيوم)
            $flat = [];
            $total = 0;
            foreach ($responses as $dayResult) {
                if (!is_array($dayResult) || !isset($dayResult['data'])) continue;
                
                foreach ($dayResult['data'] as $resp) {
                    if (!is_array($resp)) continue;
                    foreach ($resp['responses'] ?? [] as $r) {
                        $hits = $r['hits']['hits'] ?? [];
                        if (is_array($hits)) {
                            foreach ($hits as $h) { 
                                // Add day information to each hit
                                $h['_day_info'] = [
                                    'date' => $dayResult['date'] ?? null,
                                    'day_start_ms' => $dayResult['day_start_ms'] ?? null,
                                    'day_end_ms' => $dayResult['day_end_ms'] ?? null,
                                ];
                                $flat[] = $h; 
                            }
                            $total += count($hits);
                        }
                    }
                }
            }

            // Create detailed message based on completion status
            $debug = $cli['debug'] ?? [];
            $isComplete = $debug['is_complete'] ?? false;
            $completionPercentage = $debug['completion_percentage'] ?? 100;
            $totalExpected = $debug['total_expected'] ?? null;
            
            $daysProcessed = $debug['total_days_processed'] ?? 0;
            $daysWithData = $debug['days_with_data'] ?? 0;
            
            $message = 'Successfully synced from Omolaat (day-by-day)';
            if ($isComplete) {
                $message .= " - Complete data fetch ({$completionPercentage}%)";
            } else {
                $message .= " - Partial data fetch ({$completionPercentage}%)";
                if ($totalExpected) {
                    $message .= " - Expected: {$totalExpected}, Got: {$total}";
                }
            }
            $message .= " - Processed {$daysProcessed} days, {$daysWithData} with data";

            return [
                'success' => true,
                'message' => $message,
                'data' => [
                    'coupons' => [
                        'total' => $total,
                        'data' => $flat,
                    ],
                    'raw' => $responses,
                    'debug' => $cli['debug'] ?? null,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Omolaat syncData error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute Omolaat data fetching directly without CLI
     * Improved performance by eliminating shell_exec overhead
     */
    private function runOmolaatCli(array $credentials, array $config): array
    {
        try {
            $email = (string) ($credentials['email'] ?? '');
            $password = (string) ($credentials['password'] ?? '');
            $from = (string) ($config['date_from'] ?? '');
            $to = (string) ($config['date_to'] ?? '');
            $maxPages = (int) ($config['max_pages'] ?? 50);

            // Initialize client and login
            $client = new OmClient();
            $loginResult = $client->login($email, $password);

            // Build search payload with date filters
            $payload = $this->buildSearchPayload($from, $to);
            
            // Encrypt payload
            $timestamp = (string) (int) (microtime(true) * 1000);
            $ivHex = '302e3233313133323930393539313738';
            
            $ivBytes = hex2bin($ivHex);
            $encrypted = OmCrypto::encryptBubblePayload('omolaat', $payload, $timestamp, $ivBytes);

            $encMinimal = [
                'x' => $encrypted['x'],
                'y' => $encrypted['y'],
                'z' => $encrypted['z'],
            ];

            // Execute day-by-day search instead of bulk search
            $responses = $client->fetchDataDayByDay('omolaat', $encMinimal, $from, $to, $maxPages);

            // Calculate total hits and validate completeness for day-by-day results
            $totalHits = 0;
            $totalExpected = null;
            $pagesWithData = 0;
            $daysWithData = 0;
            Log::info('responses: -' . json_encode($responses));
            foreach ($responses as $dayResult) {
                if (isset($dayResult['data']) && is_array($dayResult['data'])) {
                    $dayHits = $dayResult['hits_count'] ?? 0;
                    $totalHits += $dayHits;
                    
                    if ($dayHits > 0) {
                        $daysWithData++;
                    }
                    
                    // Count pages with data for this day
                    foreach ($dayResult['data'] as $response) {
                        if (isset($response['responses']) && is_array($response['responses'])) {
                            foreach ($response['responses'] as $resp) {
                                $hits = $resp['hits']['hits'] ?? [];
                                $currentHits = is_countable($hits) ? count($hits) : 0;
                                
                                if ($currentHits > 0) {
                                    $pagesWithData++;
                                }
                                
                                // Get total expected from first response
                                if ($totalExpected === null && isset($resp['hits']['total'])) {
                                    $totalExpected = is_array($resp['hits']['total']) 
                                        ? ($resp['hits']['total']['value'] ?? $resp['hits']['total']) 
                                        : $resp['hits']['total'];
                                }
                            }
                        }
                    }
                }
            }
            
            // Check if we got all expected data
            $isComplete = $totalExpected === null || $totalHits >= $totalExpected;
            $completionPercentage = $totalExpected > 0 ? round(($totalHits / $totalExpected) * 100, 2) : 100;
            
            Log::info('Omolaat day-by-day data fetch completed', [
                'total_days_processed' => count($responses),
                'days_with_data' => $daysWithData,
                'total_pages_fetched' => $pagesWithData,
                'total_hits' => $totalHits,
                'total_expected' => $totalExpected,
                'is_complete' => $isComplete,
                'completion_percentage' => $completionPercentage,
                'max_pages_per_day' => $maxPages,
                'date_range' => ['from' => $from, 'to' => $to],
                'fetch_method' => 'day_by_day'
            ]);

            return [
                'success' => true,
                'responses' => $responses,
                'debug' => [
                    'total_days_processed' => count($responses),
                    'days_with_data' => $daysWithData,
                    'total_pages_fetched' => $pagesWithData,
                    'total_hits' => $totalHits,
                    'total_expected' => $totalExpected,
                    'is_complete' => $isComplete,
                    'completion_percentage' => $completionPercentage,
                    'max_pages_per_day' => $maxPages,
                    'date_range' => ['from' => $from, 'to' => $to],
                    'fetch_method' => 'day_by_day'
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Omolaat direct execution error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build search payload with date filters
     * Optimized payload structure for better performance
     */
    private function buildSearchPayload(string $from, string $to): array
    {
        // Parse date filters
        $fromMs = $this->parseDateToMs($from, false);
        $toMs = $this->parseDateToMs($to, true);

        // Default payload structure
        $payload = [
            'appname' => 'omolaat',
            'app_version' => 'live',
            'searches' => [
                [
                    'appname' => 'omolaat',
                    'app_version' => 'live',
                    'type' => 'custom.coupon1',
                    'constraints' => [
                        [
                            'key' => 'affiliate_id_custom_affiliate',
                            'value' => '1348695171700984260__LOOKUP__1738240965542x460877641088237600',
                            'constraint_type' => 'equals',
                        ],
                    ],
                    'sorts_list' => [
                        [
                            'sort_field' => 'order_date_date',
                            'descending' => true,
                        ],
                    ],
                    'from' => 0,
                    'search_path' => '{"constructor_name":"DataSource","args":[{"type":"json","value":"%ed.bTJRt.%el.bTaTB2.%el.bTaTT2.%p.%ds"},{"type":"node","value":{"constructor_name":"Element","args":[{"type":"json","value":"%ed.bTJRt.%el.bTaTB2.%el.bTaTT2"}]}},{"type":"raw","value":"Search"}]}',
                    'columns' => ['_id', '_version', 'order_date_date'],
                    'situation' => 'initial search',
                    'n' => 10,
                ],
                [
                    'appname' => 'omolaat',
                    'app_version' => 'live',
                    'type' => 'custom.coupon1',
                    'constraints' => [
                        [
                            'key' => 'affiliate_id_custom_affiliate',
                            'value' => '1348695171700984260__LOOKUP__1738240965542x460877641088237600',
                            'constraint_type' => 'equals',
                        ],
                    ],
                    'sorts_list' => [
                        [
                            'sort_field' => 'order_date_date',
                            'descending' => true,
                        ],
                    ],
                    'from' => 0,
                    'search_path' => '{"constructor_name":"DataSource","args":[{"type":"json","value":"%ed.bTJRt.%el.bTaTB2.%el.bTaTT2.%p.%ds"},{"type":"node","value":{"constructor_name":"Element","args":[{"type":"json","value":"%ed.bTJRt.%el.bTaTB2.%el.bTaTT2"}]}},{"type":"raw","value":"Search"}]}',
                    'situation' => 'initial search',
                    'n' => 10,
                ],
            ],
        ];

        // Apply date range filters if provided
        if ($fromMs !== null || $toMs !== null) {
            foreach ($payload['searches'] as &$searchObj) {
                if (!isset($searchObj['constraints']) || !is_array($searchObj['constraints'])) {
                    $searchObj['constraints'] = [];
                }
                
                $hasGte = false;
                $hasLt = false;
                
                // Update existing date constraints
                foreach ($searchObj['constraints'] as &$constraint) {
                    if (($constraint['key'] ?? '') === 'order_date_date') {
                        if (($constraint['constraint_type'] ?? '') === 'gte' && $fromMs !== null) {
                            $constraint['value'] = $fromMs;
                            $hasGte = true;
                        }
                        if (($constraint['constraint_type'] ?? '') === 'less than' && $toMs !== null) {
                            $constraint['value'] = $toMs;
                            $hasLt = true;
                        }
                    }
                }
                unset($constraint);
                
                // Add missing date constraints
                if ($fromMs !== null && !$hasGte) {
                    $searchObj['constraints'][] = [
                        'key' => 'order_date_date',
                        'value' => $fromMs,
                        'constraint_type' => 'gte',
                    ];
                }
                if ($toMs !== null && !$hasLt) {
                    $searchObj['constraints'][] = [
                        'key' => 'order_date_date',
                        'value' => $toMs,
                        'constraint_type' => 'less than',
                    ];
                }
            }
            unset($searchObj);
        }

        return $payload;
    }

    /**
     * Parse date value to milliseconds timestamp
     * Optimized for better performance with early returns
     * 
     * @param string|null $val Date value (epoch ms or YYYY-MM-DD)
     * @param bool $end Whether this is an end date (exclusive)
     * @return int|null Milliseconds timestamp or null if invalid
     */
    private function parseDateToMs(?string $val, bool $end = false): ?int
    {
        if ($val === null || $val === '') {
            return null;
        }

        // Fast path for numeric epoch timestamps
        if (ctype_digit($val)) {
            $ms = (int) $val;
            // Convert seconds to milliseconds if needed
            return $ms < 1000000000000 ? $ms * 1000 : $ms;
        }

        // Parse date string YYYY-MM-DD
        $timestamp = strtotime($val);
        if ($timestamp === false) {
            return null;
        }

        if ($end) {
            // For end date, add 23 hours, 59 minutes, 59 seconds to include the entire day
            $timestamp += 86399; // 23:59:59
        }
        
        return (int) ($timestamp * 1000);
    }

    /**
     * Convert cookies array to HTTP header string
     * Optimized for better performance
     * 
     * @param array $cookies Cookies array
     * @return string Cookie header string
     */
    private function cookiesArrayToHeader(array $cookies): string
    {
        if (empty($cookies)) {
            return '';
        }
        
        // Use array_map for better performance than foreach loop
        return implode('; ', array_map(
            fn($k, $v) => $k . '=' . $v,
            array_keys($cookies),
            array_values($cookies)
        ));
    }
    // No persistence here: storage is handled centrally via NetworkDataProcessor in NetworkController
}
 