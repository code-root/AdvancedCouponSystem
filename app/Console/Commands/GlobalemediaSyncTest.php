<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Networks\GlobalemediaService;

class GlobalemediaSyncTest extends Command
{
    protected $signature = 'globalemedia:sync-test {--email=} {--password=} {--from=} {--to=} {--debug}';
    protected $description = 'Login to Globalemedia and fetch performance data (default: current month).';

    public function handle(GlobalemediaService $service): int
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

        $credentials = ['email' => $email, 'password' => $password];

        // 1) Test login
        $conn = $service->testConnection($credentials);
        $this->line('Login: ' . json_encode($conn));
        if (!($conn['success'] ?? false)) {
            return 2;
        }

        // 2) Fetch data
        $result = $service->syncData($credentials, $config);
        $count = isset($result['data']['coupons']['data']) && is_array($result['data']['coupons']['data']) ? count($result['data']['coupons']['data']) : 0;
        $this->info(json_encode([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? '',
            'count' => $count,
        ], JSON_UNESCAPED_UNICODE));
        return ($result['success'] ?? false) ? 0 : 3;
    }
}


