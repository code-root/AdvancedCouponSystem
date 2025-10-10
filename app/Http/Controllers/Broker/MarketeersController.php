<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerConnection;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MarketeersController extends Controller
{
    protected $broker;

    public function __construct()
    {
        $this->broker = Broker::where('name', 'marketeers')->first();
    }

    /**
     * Get session token from Marketeers
     */
    public function getSessionToken(Request $request)
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
                'accept' => '*/*',
                'accept-language' => 'en-US,en;q=0.9',
                'cache-control' => 'no-cache',
                'content-type' => 'application/json',
                'pragma' => 'no-cache',
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36',
                'cookie' => $request->input('cookies'),
            ];

            $response = Http::withHeaders($headers)->get('https://marketeers.ollkom.com/api/auth/session');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['accessToken'])) {
                    $connection->update([
                        'access_token' => $data['accessToken'],
                        'is_connected' => true,
                        'connected_at' => now(),
                        'expires_at' => now()->addHours(24),
                    ]);

                    Cache::put('marketeers_access_token_' . $user->id, $data['accessToken'], 3600);

                    return response()->json([
                        'success' => true,
                        'message' => 'Session token obtained successfully',
                        'access_token' => $data['accessToken'],
                        'expires' => $data['expires'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get session token'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Marketeers session token error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting session token'
            ], 500);
        }
    }

    /**
     * Get coupon conversion history from Marketeers
     */
    public function getCouponConversionHistory(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Marketeers'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));
            $page = $request->input('page', 1);
            $pageSize = $request->input('page_size', 100);

            $url = 'https://marketeers-backend-prod-oci.ollkom.com/api/v1/coupon_conversion_history';
            $params = [
                'all' => true,
                'order_date_after' => $startDate,
                'order_date_before' => $endDate,
                'search' => '',
                'page' => $page,
                'page_size' => $pageSize,
                'currency__name' => 'USD',
                'publisher' => '1512'
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Host' => 'marketeers-backend-prod-oci.ollkom.com',
                'Origin' => 'https://marketeers.ollkom.com',
                'Referer' => 'https://marketeers.ollkom.com/publisher/reports/couponConversions',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache'
            ];

            $response = Http::withHeaders($headers)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                $this->processCouponConversionData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon conversion data synced successfully',
                    'data' => $data['results'] ?? [],
                    'count' => $data['count'] ?? 0,
                    'total_order_amount' => $data['total_order_amount'] ?? 0,
                    'total_payout' => $data['total_payout'] ?? 0,
                    'total_revenue' => $data['total_revenue'] ?? 0,
                    'total_orders' => $data['total_orders'] ?? 0,
                ]);
            }

            // If token expired, try to refresh
            if ($response->status() === 401 || $response->status() === 403) {
                Cache::forget('marketeers_access_token_' . $user->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Token expired. Please reconnect to Marketeers.'
                ], 401);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch coupon conversion data'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Marketeers API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Marketeers API'
            ], 500);
        }
    }

    /**
     * Sync all Marketeers data
     */
    public function syncData(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Marketeers'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            // Delete existing data for date range
            Purchase::where('user_id', $user->id)
                ->where('broker_id', $this->broker->id)
                ->whereBetween('order_date', [$startDate, $endDate])
                ->delete();

            $allResults = [];
            $page = 1;
            $pageSize = 100;

            do {
                $result = $this->getCouponConversionHistory($request->merge(['page' => $page, 'page_size' => $pageSize]));
                
                if (!$result->getData()->success) {
                    break;
                }

                $results = $result->getData()->data ?? [];
                // Use array_push with spread operator for better memory efficiency
                if (!empty($results)) {
                    array_push($allResults, ...$results);
                }

                $page++;

                // Add delay to avoid rate limiting
                sleep(1);

            } while (count($results) === $pageSize);

            $connection->update(['last_sync' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All data synced successfully',
                'total_records' => count($allResults),
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Marketeers sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing data'
            ], 500);
        }
    }

    /**
     * Process coupon conversion data
     */
    private function processCouponConversionData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['results']) || empty($data['results'])) {
            return;
        }

        foreach ($data['results'] as $conversion) {
            try {
                $campaignName = $conversion['campaign']['name'] ?? $conversion['campaign']['title'] ?? '';
                $couponCode = $conversion['coupon']['code'] ?? '';
                $orderAmount = $conversion['order_amount'] ?? 0;
                $orderAmountUsd = $conversion['order_amount_usd'] ?? 0;
                $revenue = $conversion['revenue'] ?? 0;
                $revenueUsd = $conversion['revenue_usd'] ?? 0;
                $payout = $conversion['payout'] ?? 0;
                $payoutUsd = $conversion['payout_usd'] ?? 0;
                $orderQuantity = $conversion['order_quantity'] ?? 1;
                $orderDate = $conversion['order_date'] ?? null;
                $status = $conversion['status'] ?? 'Tracked';
                $countryName = $conversion['country']['name'] ?? 'Unknown';

                // Create or get campaign
                $campaign = Campaign::firstOrCreate([
                    'broker_id' => $this->broker->id,
                    'user_id' => $user->id,
                    'broker_campaign_id' => $conversion['campaign']['id'] ?? uniqid(),
                ], [
                    'name' => $campaignName,
                    'campaign_type' => 'coupon',
                    'status' => 'active',
                ]);

                // Create or get coupon
                $coupon = Coupon::firstOrCreate([
                    'campaign_id' => $campaign->id,
                    'code' => $couponCode ?: 'NA-' . $campaignName,
                ], [
                    'status' => 'active',
                    'used_count' => 0,
                ]);

                // Get country
                $country = Country::where('name', $countryName)->first();
                if (!$country) {
                    $country = Country::where('code', 'NA')->first();
                }

                // Create purchase record
                Purchase::create([
                    'coupon_id' => $coupon->id,
                    'campaign_id' => $campaign->id,
                    'broker_id' => $this->broker->id,
                    'user_id' => $user->id,
                    'order_id' => $conversion['markteers_order_id'] ?? null,
                    'broker_order_id' => $conversion['advertiser_order_id'] ?? null,
                    'order_value' => $orderAmountUsd,
                    'commission' => $payoutUsd,
                    'revenue' => $revenueUsd,
                    'quantity' => $orderQuantity,
                    'currency' => 'USD',
                    'country_code' => $country->code,
                    'status' => $status === 'Tracked' ? 'approved' : 'pending',
                    'order_date' => $orderDate ? Carbon::parse($orderDate)->format('Y-m-d') : $startDate,
                    'purchase_date' => $orderDate ? Carbon::parse($orderDate)->format('Y-m-d') : $startDate,
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Marketeers conversion data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get user's connection to Marketeers
     */
    private function getConnection($user)
    {
        return BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $this->broker->id)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Test connection to Marketeers
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
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
            ];

            $response = Http::withHeaders($headers)->get('https://marketeers-backend-prod-oci.ollkom.com/api/v1/coupon_conversion_history?limit=1');

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
