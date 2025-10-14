<?php

declare(strict_types=1);

namespace App\Services\Networks\Omolaat;

final class Client
{
    private string $baseUrl = 'https://my.omolaat.com';
    private array $cookies = [];
    private array $defaultHeaders = [];

    public function __construct()
    {
        $this->defaultHeaders = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
        ];
    }

    // Low-level request with cookie jar
    private function request(string $method, string $url, array $headers = [], ?string $body = null, bool $json = false): array
    {
        $ch = curl_init();
        $fullUrl = str_starts_with($url, 'http') ? $url : $this->baseUrl . $url;
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_ENCODING => 'gzip, deflate, br',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
            CURLOPT_TIMEOUT => 45,
        ]);

        $mergedHeaders = array_merge($this->defaultHeaders, $headers);
        $headerLines = [];
        foreach ($mergedHeaders as $k => $v) {
            $headerLines[] = $k . ': ' . $v;
        }
        if (!empty($this->cookies)) {
            $cookieStr = '';
            foreach ($this->cookies as $ck => $cv) {
                if ($cookieStr !== '') $cookieStr .= '; ';
                $cookieStr .= $ck . '=' . $cv;
            }
            $headerLines[] = 'Cookie: ' . $cookieStr;
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            if ($json) {
                $headerLines[] = 'Content-Type: application/json';
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerLines);

        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('cURL error: ' . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr($response, 0, $headerSize);
        $rawBody = substr($response, $headerSize);

        // Parse Set-Cookie
        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (stripos($line, 'Set-Cookie:') === 0) {
                $cookieKv = trim(substr($line, strlen('Set-Cookie:')));
                $parts = explode(';', $cookieKv);
                if (!empty($parts)) {
                    $kv = explode('=', trim($parts[0]), 2);
                    if (count($kv) === 2) {
                        $this->cookies[$kv[0]] = $kv[1];
                    }
                }
            }
        }

        return [
            'status' => $status,
            'headers' => $rawHeaders,
            'body' => $rawBody,
        ];
    }

    private static function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    private static function makeFiberId(): string
    {
        $t = self::nowMs();
        return $t . 'x' . (int) (microtime(true) * 1000000);
    }

    public function initializeSession(): void
    {
        // Step 1: affiliate page
        $this->request('GET', '/affiliate/My%20Performance', [
            'Upgrade-Insecure-Requests' => '1',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        ]);

        // Step 2: main page
        $this->request('GET', '/', [
            'Upgrade-Insecure-Requests' => '1',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        ]);

        // Step 3: init data
        $this->request('GET', '/api/1.1/init/data?location=https%3A%2F%2Fmy.omolaat.com%2F', [
            'Accept' => '*/*',
            'Referer' => 'https://my.omolaat.com/',
        ]);

        // Step 4: user hi
        $timestamp = self::nowMs();
        $fiber = self::makeFiberId();
        $epoch = self::makeFiberId();
        $headers = [
            'X-Bubble-Fiber-ID' => $fiber,
            'X-Bubble-Platform' => 'web',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Content-Type' => 'application/json',
            'X-Bubble-Client-Version' => 'f3e74823084defdfa3362e8cf532a37cc32be5ed',
            'cache-control' => 'no-cache',
            'X-Bubble-PL' => ($timestamp - 3000) . 'x727',
            'X-Bubble-Epoch-Name' => 'Epoch: Runmode page fully loaded',
            'X-Bubble-Client-Commit-Timestamp' => '1760361376000',
            'X-Bubble-R' => 'https://my.omolaat.com/',
            'X-Bubble-Epoch-ID' => $epoch,
            'X-Bubble-Breaking-Revision' => '5',
            'Origin' => 'https://my.omolaat.com',
            'Referer' => 'https://my.omolaat.com/',
        ];
        $this->request('POST', '/user/hi', $headers, '{}', true);
    }

    public function login(string $email, string $password): array
    {
        $this->initializeSession();

        $timestamp = self::nowMs();
        $fiber = self::makeFiberId();
        $runId = $timestamp . 'x400580689282938100';
        $serverCallId = $timestamp . 'x558775507381657900';

        // Extract user_id from cookie omolaat_live_u2main
        $userId = null;
        if (isset($this->cookies['omolaat_live_u2main'])) {
            $parts = explode('|', $this->cookies['omolaat_live_u2main']);
            if (count($parts) > 1) {
                $userId = $parts[1];
            }
        }
        if ($userId === null) {
            throw new \RuntimeException('Could not get user_id from cookies');
        }

        $headers = [
            'X-Bubble-Fiber-ID' => $fiber,
            'X-Bubble-Platform' => 'web',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Content-Type' => 'application/json',
            'X-Bubble-Client-Version' => 'f3e74823084defdfa3362e8cf532a37cc32be5ed',
            'cache-control' => 'no-cache',
            'X-Bubble-PL' => ($timestamp - 5000) . 'x727',
            'X-Bubble-Client-Commit-Timestamp' => '1760361376000',
            'X-Bubble-R' => 'https://my.omolaat.com/',
            'X-Bubble-Breaking-Revision' => '5',
            'Origin' => 'https://my.omolaat.com',
            'Referer' => 'https://my.omolaat.com/',
        ];

        $payload = [
            'wait_for' => [],
            'app_last_change' => '36087641123',
            'client_breaking_revision' => 5,
            'calls' => [[
                'client_state' => [
                    'element_instances' => [
                        'bTLpn' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTLpn',
                            'parent_element_id' => 'bTLpm',
                        ],
                        'bTLpf' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTLpf',
                            'parent_element_id' => 'bTLpa',
                        ],
                        'bTLpl' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTLpl',
                            'parent_element_id' => 'bTLpg',
                        ],
                        'bTIcR' => 'NOT_FOUND',
                        'bTHUF' => 'NOT_FOUND',
                        'bTYRW' => 'NOT_FOUND',
                        'bTLqK' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTLqK',
                            'parent_element_id' => 'bTLnF',
                        ],
                        'bTLqo' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTLqo',
                            'parent_element_id' => 'bTLnF',
                        ],
                        'bTGYf' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTGYf',
                            'parent_element_id' => null,
                        ],
                        'bTLpm' => [
                            'dehydrated' => '1348695171700984260__LOOKUP__ElementInstance::bTLpm',
                            'parent_element_id' => 'bTLpZ',
                        ],
                    ],
                    'element_state' => [
                        '1348695171700984260__LOOKUP__ElementInstance::bTLpf' => [
                            'is_visible' => true,
                            'value_that_is_valid' => $email,
                            'value' => $email,
                        ],
                        '1348695171700984260__LOOKUP__ElementInstance::bTLpl' => [
                            'is_visible' => true,
                            'value_that_is_valid' => $password,
                            'value' => $password,
                        ],
                        '1348695171700984260__LOOKUP__ElementInstance::bTLpm' => [
                            'group_data' => null,
                        ],
                    ],
                    'other_data' => [
                        'Current Page Scroll Position' => 0,
                        'Current Page Width' => 1536,
                        'secure_list' => array_fill(0, 6, $password),
                    ],
                    'cache' => new \stdClass(),
                    'exists' => new \stdClass(),
                ],
                'run_id' => $runId,
                'server_call_id' => $serverCallId,
                'item_id' => 'bTTZP0',
                'element_id' => 'bTLpn',
                'page_id' => 'bTGYf',
                'uid_generator' => [
                    'timestamp' => $timestamp,
                    'seed' => 382723601125662140,
                ],
                'random_seed' => 0.3458067383425456,
                'current_date_time' => $timestamp,
                'current_wf_params' => new \stdClass(),
            ]],
            'timezone_offset' => -180,
            'timezone_string' => 'Africa/Cairo',
            'user_id' => $userId,
            'should_stream' => false,
        ];

        $resp = $this->request('POST', '/workflow/start', $headers, json_encode($payload), true);
        if ($resp['status'] !== 200) {
            throw new \RuntimeException('Login failed: ' . $resp['status'] . "\n" . $resp['body']);
        }

        return [
            'cookies' => $this->cookies,
            'headers' => $headers,
            'raw' => $resp['body'],
        ];
    }

    public function search(string $appname, array $encrypted, ?string $fiberId = null): array
    {
        $headers = [
            'X-Bubble-R' => 'https://my.omolaat.com/affiliate/My%2520Performance',
            'X-Bubble-Fiber-ID' => $fiberId ?: self::makeFiberId(),
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Content-Type' => 'application/json',
        ];
        $resp = $this->request('POST', '/elasticsearch/msearch', $headers, json_encode($encrypted), true);
        return [
            'status' => $resp['status'],
            'json' => json_decode($resp['body'], true),
        ];
    }

    public function paginateSearch(string $appname, array $encrypted, int $maxPages = 50): array
    {
        $all = [];
        $page = 1;
        $totalHits = 0;
        $totalExpected = null;
        $pageSize = 10; // Each page contains 10 items
        
        while ($page <= $maxPages) {
            // Decrypt, edit from, re-encrypt with same timestamp/IV
            [$timestamp, $ivBytes, $payloadStr] = Crypto::decryptBubblePayload($appname, $encrypted['x'], $encrypted['y'], $encrypted['z']);
            $payload = json_decode($payloadStr, true);
            if (!is_array($payload)) {
                break;
            }
            
            if (isset($payload['searches']) && is_array($payload['searches'])) {
                foreach ($payload['searches'] as &$searchObj) {
                    $searchObj['from'] = ($page - 1) * $pageSize;
                }
                unset($searchObj);
            }
            
            $modified = Crypto::encryptBubblePayload($appname, $payload, $timestamp, $ivBytes);
            $enc = [
                'x' => $modified['x'],
                'y' => $modified['y'],
                'z' => $modified['z'],
            ];
            
            $res = $this->search($appname, $enc);
            if ($res['status'] !== 200) {
                break;
            }
            
            $data = $res['json'];
            $all[] = $data;
            
            // Count hits in current page
            $currentPageHits = 0;
            if (isset($data['responses']) && is_array($data['responses'])) {
                foreach ($data['responses'] as $resp) {
                    $hits = $resp['hits']['hits'] ?? [];
                    $currentPageHits += is_countable($hits) ? count($hits) : 0;
                    
                    // Get total expected hits from first response
                    if ($totalExpected === null && isset($resp['hits']['total'])) {
                        $totalExpected = is_array($resp['hits']['total']) 
                            ? ($resp['hits']['total']['value'] ?? $resp['hits']['total']) 
                            : $resp['hits']['total'];
                    }
                }
            }
            
            $totalHits += $currentPageHits;
         
            
            // Stop conditions:
            // 1. No hits in current page
            // 2. We've reached the total expected hits
            // 3. Current page has fewer hits than page size (last page)
            if ($currentPageHits === 0) {
                break;
            }
            
            if ($totalExpected !== null && $totalHits >= $totalExpected) {
              
                break;
            }
            
            if ($currentPageHits < $pageSize) {
            
                break;
            }
            
            $page++;
            usleep(500000); // 0.5s delay
        }
        
  
        return $all;
    }

    /**
     * Fetch data day by day with proper timestamp ranges
     * يسحب البيانات يوم بيوم مع نطاقات timestamp صحيحة
     */
    public function fetchDataDayByDay(string $appname, array $encrypted, string $fromDate, string $toDate, int $maxPagesPerDay = 50): array
    {
        $allResults = [];
        $totalDays = 0;
        $totalHits = 0;
        
        // Parse dates
        $startDate = new \DateTime($fromDate);
        $endDate = new \DateTime($toDate);
   
        
        // Loop through each day
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dayStart = clone $currentDate;
            $dayStart->setTime(0, 0, 0); // Start of day: 00:00:00
            
            $dayEnd = clone $currentDate;
            $dayEnd->setTime(23, 59, 59); // End of day: 23:59:59
            
            // Convert to milliseconds
            $dayStartMs = (int) ($dayStart->getTimestamp() * 1000);
            $dayEndMs = (int) ($dayEnd->getTimestamp() * 1000);
            

            
            // Create day-specific payload
            $dayResults = $this->fetchDayData($appname, $encrypted, $dayStartMs, $dayEndMs, $maxPagesPerDay);
            
            if (!empty($dayResults)) {
                $allResults[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day_start_ms' => $dayStartMs,
                    'day_end_ms' => $dayEndMs,
                    'data' => $dayResults,
                    'hits_count' => $this->countHitsInResults($dayResults)
                ];
                
                $totalHits += $this->countHitsInResults($dayResults);
            }
            
            $totalDays++;
            $currentDate->add(new \DateInterval('P1D')); // Add 1 day
            
            // Small delay between days
            usleep(250000); // 0.25s delay
        }
        
        return $allResults;
    }
    
    /**
     * Fetch data for a specific day with timestamp range
     * يسحب بيانات يوم محدد مع نطاق timestamp
     */
    private function fetchDayData(string $appname, array $encrypted, int $dayStartMs, int $dayEndMs, int $maxPages): array
    {
        $dayResults = [];
        $page = 1;
        $totalHits = 0;
        $totalExpected = null;
        $pageSize = 10;
        
        while ($page <= $maxPages) {
            // Decrypt, edit from and date constraints, re-encrypt
            [$timestamp, $ivBytes, $payloadStr] = Crypto::decryptBubblePayload($appname, $encrypted['x'], $encrypted['y'], $encrypted['z']);
            $payload = json_decode($payloadStr, true);
            if (!is_array($payload)) {
                break;
            }
            
            // Update pagination and date constraints
            if (isset($payload['searches']) && is_array($payload['searches'])) {
                foreach ($payload['searches'] as &$searchObj) {
                    $searchObj['from'] = ($page - 1) * $pageSize;
                    
                    // Update date constraints for this specific day
                    if (!isset($searchObj['constraints'])) {
                        $searchObj['constraints'] = [];
                    }
                    
                    $hasGte = false;
                    $hasLt = false;
                    
                    // Update existing date constraints
                    foreach ($searchObj['constraints'] as &$constraint) {
                        if (($constraint['key'] ?? '') === 'order_date_date') {
                            if (($constraint['constraint_type'] ?? '') === 'gte') {
                                $constraint['value'] = $dayStartMs;
                                $hasGte = true;
                            }
                            if (($constraint['constraint_type'] ?? '') === 'less than') {
                                $constraint['value'] = $dayEndMs;
                                $hasLt = true;
                            }
                        }
                    }
                    unset($constraint);
                    
                    // Add missing date constraints
                    if (!$hasGte) {
                        $searchObj['constraints'][] = [
                            'key' => 'order_date_date',
                            'value' => $dayStartMs,
                            'constraint_type' => 'gte',
                        ];
                    }
                    if (!$hasLt) {
                        $searchObj['constraints'][] = [
                            'key' => 'order_date_date',
                            'value' => $dayEndMs,
                            'constraint_type' => 'less than',
                        ];
                    }
                }
                unset($searchObj);
            }
            
            $modified = Crypto::encryptBubblePayload($appname, $payload, $timestamp, $ivBytes);
            $enc = [
                'x' => $modified['x'],
                'y' => $modified['y'],
                'z' => $modified['z'],
            ];
            
            $res = $this->search($appname, $enc);
            if ($res['status'] !== 200) {
                break;
            }
            
            $data = $res['json'];
            $dayResults[] = $data;
            
            // Count hits in current page
            $currentPageHits = 0;
            if (isset($data['responses']) && is_array($data['responses'])) {
                foreach ($data['responses'] as $resp) {
                    $hits = $resp['hits']['hits'] ?? [];
                    $currentPageHits += is_countable($hits) ? count($hits) : 0;
                    
                    // Get total expected hits from first response
                    if ($totalExpected === null && isset($resp['hits']['total'])) {
                        $totalExpected = is_array($resp['hits']['total']) 
                            ? ($resp['hits']['total']['value'] ?? $resp['hits']['total']) 
                            : $resp['hits']['total'];
                    }
                }
            }
            
            $totalHits += $currentPageHits;
            
            // Stop conditions for this day
            if ($currentPageHits === 0) {
                \Log::info("Stopping day pagination: No hits in current page", [
                    'page' => $page,
                    'day_start_ms' => $dayStartMs,
                    'day_end_ms' => $dayEndMs
                ]);
                break;
            }
            
            if ($totalExpected !== null && $totalHits >= $totalExpected) {
                \Log::info("Stopping day pagination: Reached total expected hits", [
                    'total_hits' => $totalHits,
                    'total_expected' => $totalExpected,
                    'day_start_ms' => $dayStartMs,
                    'day_end_ms' => $dayEndMs
                ]);
                break;
            }
            
            if ($currentPageHits < $pageSize) {
                \Log::info("Stopping day pagination: Last page detected", [
                    'current_page_hits' => $currentPageHits,
                    'page_size' => $pageSize,
                    'day_start_ms' => $dayStartMs,
                    'day_end_ms' => $dayEndMs
                ]);
                break;
            }
            
            $page++;
            usleep(500000); // 0.5s delay
        }
        
        \Log::info("Day data fetch completed", [
            'day_start_ms' => $dayStartMs,
            'day_end_ms' => $dayEndMs,
            'pages_fetched' => count($dayResults),
            'total_hits' => $totalHits,
            'total_expected' => $totalExpected
        ]);
        
        return $dayResults;
    }
    
    /**
     * Count total hits in results array
     * يحسب العدد الإجمالي للنتائج
     */
    private function countHitsInResults(array $results): int
    {
        $totalHits = 0;
        foreach ($results as $result) {
            if (isset($result['responses']) && is_array($result['responses'])) {
                foreach ($result['responses'] as $resp) {
                    $hits = $resp['hits']['hits'] ?? [];
                    $totalHits += is_countable($hits) ? count($hits) : 0;
                }
            }
        }
        return $totalHits;
    }
}


