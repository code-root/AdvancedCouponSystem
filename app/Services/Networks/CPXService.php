<?php

namespace App\Services\Networks;

use Carbon\Carbon;
use App\Models\NetworkConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CPXService extends BaseNetworkService
{
    protected string $networkName = 'CPX';

    protected array $requiredFields = [
        'api_key' => [
            'label' => 'API Token',
            'type' => 'text',
            'required' => true,
            'placeholder' => 'Enter your CPX API Token',
            'help' => 'Found in CPX Dashboard → Settings → API Token',
        ],
        'affiliate_id' => [
            'label' => 'Affiliate ID',
            'type' => 'text',
            'required' => true,
            'placeholder' => '1634',
            'help' => 'Your CPX Affiliate ID',
        ],
    ];

    /**
     * Fetch data from CPX API
     */
    public function fetchData($connection, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $url = $connection->api_url ?? 'https://api.cpx.ae/api/auth/conversions_report_dashboard';
            
            $headers = [
                'Accept' => 'application/json, text/plain, */*',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Accept-Language' => 'en-US;q=0.9,en;q=0.8,en-GB;q=0.7',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'Content-Type' => 'application/json',
                'Host' => 'api.cpx.ae',
                'Origin' => 'https://dash.cpxaffiliate.com',
                'Pragma' => 'no-cache',
                'token' => $connection->api_key,
            ];

            $allResults = [];
            $currentDate = $startDate->copy();

            // Loop through each day in the date range
            while ($currentDate <= $endDate) {
                $body = [
                    'timeperiod' => 'Daterange',
                    'affiliate_id' => $connection->credentials['affiliate_id'] ?? '1634',
                    'page' => 1,
                    'pagesize' => 100,
                    'startdate' => $currentDate->format('Y-m-d'),
                    'enddate' => $currentDate->format('Y-m-d'),
                ];

                $response = Http::withHeaders($headers)->post($url, $body);

                if ($response->successful()) {
                    $result = $response->json();
                    
                    if (isset($result['result']) && isset($result['data']['All_Conversions'])) {
                        $conversions = $result['data']['All_Conversions'];
                        
                        foreach ($conversions as $conversion) {
                            $allResults[] = [
                                'campaign_id' => $conversion['campaign_id'] ?? null,
                                'campaign_name' => $conversion['advertiser'] ?? 'Unknown',
                                'coupon_code' => $conversion['coupon_code'] ?? '',
                                'country' => $conversion['country'] ?? 'Unknown',
                                'sale_amount' => floatval($conversion['sale_amount'] ?? 0),
                                'commission' => floatval($conversion['payout'] ?? 0),
                                'orders' => 1,
                                'customer_type' => $conversion['customer_type'] ?? 'new',
                                'transaction_id' => $conversion['order_id'] ?? '',
                                'date' => Carbon::parse($conversion['time'] ?? $currentDate)->format('Y-m-d'),
                                'status' => $conversion['status'] ?? 'approved',
                            ];
                        }
                    }
                }

                $currentDate->addDay();
            }

            return $allResults;

        } catch (\Exception $e) {
            Log::error("CPX API Error: {$e->getMessage()}", [
                'connection_id' => $connection->id,
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \Exception("Failed to fetch data from CPX: {$e->getMessage()}");
        }
    }

    /**
     * Transform CPX data to standard format
     */
    protected function transformData(array $item, $connection): array
    {
        return [
            'network_id' => $connection->network_id,
            'campaign_name' => $item['campaign_name'] ?? 'Unknown',
            'campaign_external_id' => $item['campaign_id'] ?? null,
            'coupon_code' => $item['coupon_code'] ?? '',
            'country' => $item['country'] ?? 'Unknown',
            'sale_amount' => $item['sale_amount'] ?? 0,
            'commission' => $item['commission'] ?? 0,
            'clicks' => 0,
            'conversions' => $item['orders'] ?? 1,
            'customer_type' => $item['customer_type'] ?? 'new',
            'transaction_id' => $item['transaction_id'] ?? '',
            'date' => $item['date'] ?? now()->format('Y-m-d'),
            'status' => $item['status'] ?? 'approved',
        ];
    }

    /**
     * Test CPX connection
     */
    public function testConnection(array $credentials): array
    {
        try {
            $url = $credentials['api_url'] ?? 'https://api.cpx.ae/api/auth/conversions_report_dashboard';
            
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'token' => $credentials['api_key'],
            ];

            $body = [
                'timeperiod' => 'Daterange',
                'affiliate_id' => $credentials['affiliate_id'] ?? '1634',
                'page' => 1,
                'pagesize' => 1,
                'startdate' => now()->subDay()->format('Y-m-d'),
                'enddate' => now()->format('Y-m-d'),
            ];

            $response = Http::withHeaders($headers)->timeout(10)->post($url, $body);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $response->status(),
                'data' => null,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Sync data from CPX
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $startDate = \Carbon\Carbon::parse($config['date_from'] ?? now()->startOfMonth());
            $endDate = \Carbon\Carbon::parse($config['date_to'] ?? now());

            // Create temporary connection object
            $connection = new \stdClass();
            $connection->id = 0;
            $connection->network_id = 0;
            $connection->api_url = $credentials['api_url'] ?? 'https://api.cpx.ae/api/auth/conversions_report_dashboard';
            $connection->api_key = $credentials['api_key'];
            $connection->credentials = ['affiliate_id' => $credentials['affiliate_id'] ?? '1634'];

            $data = $this->fetchData((object)$connection, $startDate, $endDate);

            return [
                'success' => true,
                'message' => 'Data synced successfully',
                'data' => $data,
                'count' => count($data),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
    
    /**
     * Validate CPX connection
     */
    public function validateConnection($connection): bool
    {
        try {
            $url = $connection->api_url ?? 'https://api.cpx.ae/api/auth/conversions_report_dashboard';
            
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'token' => $connection->api_key,
            ];

            $body = [
                'timeperiod' => 'Daterange',
                'affiliate_id' => $connection->credentials['affiliate_id'] ?? '1634',
                'page' => 1,
                'pagesize' => 1,
                'startdate' => now()->subDay()->format('Y-m-d'),
                'enddate' => now()->format('Y-m-d'),
            ];

            $response = Http::withHeaders($headers)->timeout(10)->post($url, $body);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error("CPX connection validation failed: {$e->getMessage()}");
            return false;
        }
    }
}

