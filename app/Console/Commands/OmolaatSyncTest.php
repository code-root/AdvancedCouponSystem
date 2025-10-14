<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Networks\OmolaatService;

class OmolaatSyncTest extends Command
{
    protected $signature = 'omolaat:sync-test {--email=} {--password=} {--from=} {--to=} {--limit=10} {--debug} {--store}';
    protected $description = 'Test login and fetch Omolaat data from a date range (default: start of month to today).';

    public function handle(OmolaatService $service): int
    {
        $email = (string) ($this->option('email') ?? '');
        $password = (string) ($this->option('password') ?? '');
        $from = (string) ($this->option('from') ?? '');
        $to = (string) ($this->option('to') ?? '');

        if ($email === '' || $password === '') {
            $this->error('Missing --email or --password');
            return 1;
        }

        $config = [];
        if ($from !== '') {
            $config['date_from'] = $from; // YYYY-MM-DD
        }
        if ($to !== '') {
            $config['date_to'] = $to; // YYYY-MM-DD
        }
        if ($this->option('debug')) {
            $config['debug'] = true;
        }
        if ($this->option('store')) {
            $config['store'] = true;
        }

        $credentials = ['email' => $email, 'password' => $password];

        // Step 1: Ensure login works
        $conn = $service->testConnection($credentials);
        if (!($conn['success'] ?? false)) {
            $this->error(($conn['message'] ?? 'Login failed'));
            return 3;
        }
        if ($this->option('debug')) {
            $userId = $conn['data']['user_id'] ?? null;
            $cookiesStr = $conn['data']['cookies'] ?? '';
            $this->line(json_encode([
                'login' => [
                    'success' => true,
                    'user_id' => $userId,
                    'cookies_len' => strlen((string) $cookiesStr),
                ]
            ], JSON_UNESCAPED_UNICODE));
        }

        // Step 2: Fetch data
        $result = $service->syncData($credentials, $config);

        if (!($result['success'] ?? false)) {
            $this->error(($result['message'] ?? 'Failed') . '');
            return 2;
        }

        // Aggregate totals: orders count and revenue
        $hits = $result['data']['coupons']['data'] ?? [];
        $ordersTotal = (int) ($result['data']['coupons']['total'] ?? (is_array($hits) ? count($hits) : 0));
        $revenueAffiliate = 0.0;
        $revenueOrder = 0.0;
        if (is_array($hits)) {
            foreach ($hits as $hit) {
                $src = $hit['_source'] ?? [];
                $revenueAffiliate += (float) ($src['affiliate_amount_number'] ?? 0);
                $revenueOrder += (float) ($src['order_amount_number'] ?? 0);
            }
        }

        $payload = [
            'success' => true,
            'message' => $result['message'] ?? '',
            'orders_total' => $ordersTotal,
            // Revenue as affiliate commission; also include gross order amount for reference
            'revenue_affiliate' => $revenueAffiliate,
            'revenue_order_amount' => $revenueOrder,
        ];

        if ($this->option('debug')) {
            $payload['debug'] = $result['data']['debug'] ?? null;
        }

        $this->info(json_encode($payload, JSON_UNESCAPED_UNICODE));
        return 0;
    }
}


