<?php

declare(strict_types=1);

namespace App\Services\Networks\Omolaat;

/**
 * Optimized CLI wrapper for Omolaat data fetching
 * Provides direct function calls instead of shell_exec for better performance
 */
class CLI
{
    private Client $client;
    private array $config;

    public function __construct()
    {
        $this->client = new Client();
        $this->config = [
            'timestamp' => '1760377533394',
            'iv_hex' => '302e36367832353033343936303138313938',
        ];
    }

    /**
     * Execute Omolaat data fetching with optimized performance
     * 
     * @param array $options CLI options array
     * @return array Result with success status and data
     */
    public function execute(array $options): array
    {
        try {
            // Validate required options
            if (empty($options['email']) || empty($options['password'])) {
                return [
                    'success' => false,
                    'message' => 'Missing --email or --password'
                ];
            }

            // Login to Omolaat
            $loginResult = $this->client->login(
                (string) $options['email'],
                (string) $options['password']
            );

            // Build and encrypt payload
            $payload = $this->buildPayload($options);
            $ivBytes = hex2bin($this->config['iv_hex']);
            $encrypted = Crypto::encryptBubblePayload(
                'omolaat',
                $payload,
                $this->config['timestamp'],
                $ivBytes
            );

            $encMinimal = [
                'x' => $encrypted['x'],
                'y' => $encrypted['y'],
                'z' => $encrypted['z'],
            ];

            // Execute paginated search
            $maxPages = (int) ($options['max_pages'] ?? 100);
            $responses = $this->client->paginateSearch('omolaat', $encMinimal, $maxPages);

            // Format output based on requested format
            $outputFormat = strtolower((string) ($options['output'] ?? 'json'));
            
            if ($outputFormat === 'csv') {
                return $this->formatAsCsv($responses, $options['out'] ?? null);
            }

            return [
                'success' => true,
                'responses' => $responses,
                'debug' => [
                    'total_pages' => count($responses),
                    'max_pages_requested' => $maxPages,
                ]
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build search payload with date filters
     */
    private function buildPayload(array $options): array
    {
        $fromMs = $this->parseDateToMs($options['from'] ?? null, false);
        $toMs = $this->parseDateToMs($options['to'] ?? null, true);

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
                    'n' => 1,
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
                    'n' => 9,
                ],
            ],
        ];

        // Apply date filters
        if ($fromMs !== null || $toMs !== null) {
            foreach ($payload['searches'] as &$searchObj) {
                if (!isset($searchObj['constraints'])) {
                    $searchObj['constraints'] = [];
                }
                
                $hasGte = false;
                $hasLt = false;
                
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
     */
    private function parseDateToMs(?string $val, bool $end = false): ?int
    {
        if ($val === null || $val === '') {
            return null;
        }

        if (ctype_digit($val)) {
            $ms = (int) $val;
            return $ms < 1000000000000 ? $ms * 1000 : $ms;
        }

        $timestamp = strtotime($val);
        if ($timestamp === false) {
            return null;
        }

        $timestamp += $end ? 86400 : 0;
        return (int) ($timestamp * 1000);
    }

    /**
     * Format responses as CSV
     */
    private function formatAsCsv(array $responses, ?string $outputFile = null): array
    {
        $rows = [];
        $headers = [
            'Order ID', 'Version', 'Sales ID', 'POS Order ID', 'POS Order ID1',
            'Order Date', 'Created Date', 'Modified Date', 'Pending Date',
            'Confirming Date', 'Canceled Date', 'Order Amount', 'Discount Amount',
            'VAT Amount', 'Affiliate Amount', 'Platform Amount', 'Status',
            'POS Status', 'Last Workflow Update', 'New Month',
            'Referral Order Generated', 'Coupon ID', 'Coupon Name ID',
            'Store ID', 'Affiliate ID', 'Created By'
        ];

        foreach ($responses as $pageResp) {
            $pageResponses = $pageResp['responses'] ?? [];
            foreach ($pageResponses as $resp) {
                $hits = $resp['hits']['hits'] ?? [];
                if (!is_array($hits)) continue;
                
                foreach ($hits as $hit) {
                    $src = $hit['_source'] ?? [];
                    $rows[] = [
                        $hit['_id'] ?? '',
                        $hit['_version'] ?? '',
                        $src['sales_id_text'] ?? '',
                        $src['pos_order_id_text'] ?? '',
                        $src['pos_order_id1_text'] ?? '',
                        isset($src['order_date_date']) ? $this->tsToDate($src['order_date_date']) : '',
                        isset($src['Created Date']) ? $this->tsToDate($src['Created Date']) : '',
                        isset($src['Modified Date']) ? $this->tsToDate($src['Modified Date']) : '',
                        isset($src['pending_date_date']) ? $this->tsToDate($src['pending_date_date']) : '',
                        isset($src['confirming_date_date']) ? $this->tsToDate($src['confirming_date_date']) : '',
                        isset($src['canceled_date_date']) ? $this->tsToDate($src['canceled_date_date']) : '',
                        $src['order_amount_number'] ?? 0,
                        $src['discount_amount_number'] ?? 0,
                        $src['vat_amount_number'] ?? 0,
                        $src['affiliate_amount_number'] ?? 0,
                        $src['platform_amount_number'] ?? 0,
                        $src['status_option_opt__order_status'] ?? '',
                        $src['pos_status_text'] ?? '',
                        $src['last_workflow_update_text'] ?? '',
                        $src['new_month_text'] ?? '',
                        isset($src['referral_order_generated__boolean']) ? 
                            ($src['referral_order_generated__boolean'] ? 'true' : 'false') : 'false',
                        $src['coupon_custom_coupon'] ?? '',
                        $src['coupon_name_custom_coupon_name'] ?? '',
                        $src['store_custom_services_stores'] ?? '',
                        $src['affiliate_id_custom_affiliate'] ?? '',
                        $src['Created By'] ?? '',
                    ];
                }
            }
        }

        if (!$outputFile) {
            $outputFile = 'omolaat_orders_' . date('Ymd_His') . '.csv';
        }

        // Ensure directory exists
        $dir = dirname($outputFile);
        if ($dir !== '.' && !is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                return [
                    'success' => false,
                    'message' => 'Cannot create directory: ' . $dir
                ];
            }
        }

        $fh = fopen($outputFile, 'w');
        if ($fh === false) {
            return [
                'success' => false,
                'message' => 'Cannot write: ' . $outputFile
            ];
        }

        fputcsv($fh, $headers);
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);

        return [
            'success' => true,
            'file' => $outputFile,
            'rows' => count($rows)
        ];
    }

    /**
     * Convert millisecond timestamp to human-readable date
     */
    private function tsToDate($ts): string
    {
        try {
            $sec = (int) floor(((int) $ts) / 1000);
            return date('Y-m-d H:i:s', $sec);
        } catch (\Throwable $e) {
            return (string) $ts;
        }
    }
}