<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Network;
use App\Models\NetworkConnection;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class PlatformanceController extends Controller
{
    protected $network;

    public function __construct()
    {
        $this->network = Network::where('name', 'platformance')->first();
    }

    /**
     * Get Platformance performance data
     */
    public function getPerformance(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Platformance'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $url = 'https://login.platformance.co/publisher/performance.html';
            $params = [
                'group' => ['adid', 'coupon', 'created'],
                'fields' => ['grossSaleAmount', 'grossPayout', 'grossConversions', 'cr', 'saleAmount', 'extConv', 'pendingTotalConversions', 'pendingPayout'],
                'start' => $startDate,
                'end' => $endDate,
                'report_name' => 'performance',
                'zone' => 'Asia/Dubai',
            ];

            $headers = [
                'Cookie' => $connection->access_token,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9,ar;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br',
            ];

            $response = Http::withHeaders($headers)->get($url, $params);
            $html = $response->body();

            if ($response->successful()) {
                $data = $this->parsePerformanceData($html);
                $this->processPerformanceData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Performance data synced successfully',
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data from Platformance'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Platformance API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Platformance'
            ], 500);
        }
    }

    /**
     * Parse HTML performance data
     */
    private function parsePerformanceData($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $rows = $xpath->query('//table//tbody/tr');

        $data = [];
        foreach ($rows as $row) {
            $columns = $row->getElementsByTagName('td');
            if ($columns->length >= 8) {
                $data[] = [
                    'campaign' => trim($columns->item(0)->nodeValue) ?? null,
                    'campaign_id' => trim($columns->item(1)->nodeValue) ?? null,
                    'coupon' => trim($columns->item(2)->nodeValue) ?? null,
                    'created' => trim($columns->item(3)->nodeValue) ?? null,
                    'conversions' => trim($columns->item(4)->nodeValue) ?? null,
                    'payout' => trim($columns->item(5)->nodeValue) ?? null,
                    'sale_amount' => trim($columns->item(6)->nodeValue) ?? null,
                    'status' => trim($columns->item(7)->nodeValue) ?? null,
                ];
            }
        }

        return $data;
    }

    /**
     * Process performance data and save to database
     */
    private function processPerformanceData($data, $user, $startDate, $endDate)
    {
        // Delete existing data for date range
        Purchase::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->delete();

        foreach ($data as $item) {
            try {
                if (empty($item['campaign_id'])) {
                    continue;
                }

                $code = $item['coupon'] ?: 'NA-' . $item['campaign'];
                $payout = $this->convertToDecimal($item['payout']);
                $saleAmount = $this->convertToDecimal($item['sale_amount']);

                // Create or get campaign
                $campaign = Campaign::firstOrCreate([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'network_campaign_id' => $item['campaign_id'],
                ], [
                    'name' => $item['campaign'],
                    'campaign_type' => 'coupon',
                    'status' => 'active',
                ]);

                // Create or get coupon
                $coupon = Coupon::firstOrCreate([
                    'campaign_id' => $campaign->id,
                    'code' => $code,
                ], [
                    'status' => 'active',
                    'used_count' => 0,
                ]);

                // Get country
                $country = Country::where('code', 'NA')->first();

                // Create purchase record
                Purchase::create([
                    'coupon_id' => $coupon->id,
                    'campaign_id' => $campaign->id,
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'order_value' => $saleAmount,
                    'commission' => $payout,
                    'revenue' => $payout,
                    'quantity' => $item['conversions'] ?? 1,
                    'currency' => 'USD',
                    'country_code' => $country->code,
                    'status' => 'approved',
                    'order_date' => $item['created'] ? Carbon::parse($item['created'])->format('Y-m-d') : $startDate,
                    'purchase_date' => $item['created'] ? Carbon::parse($item['created'])->format('Y-m-d') : $startDate,
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Platformance performance data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Convert string to decimal
     */
    private function convertToDecimal($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove any non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.-]/', '', $value);
        return (float) $value;
    }

    /**
     * Update token by cookies
     */
    public function updateToken(Request $request)
    {
        $user = Auth::user();
        
        $connection = NetworkConnection::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found'
            ], 404);
        }

        $cookies = $request->input('cookies');
        if (!$cookies) {
            return response()->json([
                'success' => false,
                'message' => 'Cookies are required'
            ], 400);
        }

        $connection->update([
            'access_token' => $cookies,
            'is_connected' => true,
            'connected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token updated successfully'
        ]);
    }

    /**
     * Get user's connection to Platformance
     */
    private function getConnection($user)
    {
        return NetworkConnection::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Test connection to Platformance
     */
    public function testConnection(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found'
            ], 404);
        }

        try {
            $headers = [
                'Cookie' => $connection->access_token,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
            ];

            $response = Http::withHeaders($headers)->get('https://login.platformance.co/publisher/performance.html?limit=1');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }
}
