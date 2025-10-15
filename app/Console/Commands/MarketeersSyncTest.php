<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Networks\MarketeersService;

class MarketeersSyncTest extends Command
{
    protected $signature = 'marketeers:sync-test {--email=} {--password=} {--from=} {--to=} {--currency=USD} {--page_size=100} {--network-id=} {--user-id=} {--save} {--debug}';
    protected $description = 'Login to Marketeers and fetch coupon conversions (default: current month). Use --save to store data in database.';

    public function handle(MarketeersService $service): int
    {
        $email = (string) ($this->option('email') ?? '');
        $password = (string) ($this->option('password') ?? '');
        $from = (string) ($this->option('from') ?? '');
        $to = (string) ($this->option('to') ?? '');
        $currency = (string) ($this->option('currency') ?? 'USD');
        $pageSize = (int) ($this->option('page_size') ?? 100);
        $networkId = (int) ($this->option('network-id') ?? 0);
        $userId = (int) ($this->option('user-id') ?? 0);
        $save = (bool) $this->option('save');

        if ($email === '' || $password === '') {
            $this->error('Missing --email or --password');
            return 1;
        }

        $config = [
            'currency' => $currency,
            'page_size' => $pageSize,
        ];
        if ($from !== '') {
            $config['date_from'] = $from; // YYYY-MM-DD
        }
        if ($to !== '') {
            $config['date_to'] = $to; // YYYY-MM-DD
        }

        // Add network_id and user_id if saving to database
        if ($save) {
            if ($networkId <= 0 || $userId <= 0) {
                $this->error('Missing --network-id or --user-id for database saving');
                return 1;
            }
            $config['network_id'] = $networkId;
            $config['user_id'] = $userId;
        }

        $credentials = ['email' => $email, 'password' => $password];

        // 1) Test login
        $conn = $service->testConnection($credentials);
        if (!($conn['success'] ?? false)) {
            $this->error($conn['message'] ?? 'Login failed');
            return 2;
        }

        // 2) Fetch data (and save if --save flag is provided)
        $result = $service->syncData($credentials, $config);
        if (!($result['success'] ?? false)) {
            $this->error($result['message'] ?? 'Fetch failed');
            return 3;
        }

        $data = $result['data']['coupons'] ?? [];
        $count = isset($data['data']) && is_array($data['data']) ? count($data['data']) : 0;
        $total = (int) ($data['total'] ?? $count);
        $campaigns = (int) ($data['campaigns'] ?? 0);
        $coupons = (int) ($data['coupons'] ?? 0);
        $purchases = (int) ($data['purchases'] ?? 0);

        $this->info(json_encode([
            'success' => true,
            'message' => $result['message'] ?? '',
            'total' => $total,
            'count' => $count,
            'campaigns' => $campaigns,
            'coupons' => $coupons,
            'purchases' => $purchases,
            'saved' => $save,
        ], JSON_UNESCAPED_UNICODE));

        if ($this->option('debug')) {
            $this->line(json_encode([
                'user_id' => $conn['data']['user_id'] ?? null,
                'publisher_id' => $conn['data']['publisher_id'] ?? null,
            ], JSON_UNESCAPED_UNICODE));
        }

        return 0;
    }
}


