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

class BoostinyController extends Controller
{
    protected $network;
    protected $connection;

    public function __construct()
    {
        $this->network = Network::where('name', 'boostiny')->first();
    }

    /**
     * Get Boostiny campaigns data
     */
    public function getCampaigns(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Boostiny'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $apiUrl = 'https://api.boostiny.com/v2/publisher/performance';
            $params = [
                'limit' => 1000,
                'from' => $startDate,
                'to' => $endDate,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
            ])->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                $this->processCampaignData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Campaigns data synced successfully',
                    'data' => $data['payload']['data'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data from Boostiny API'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Boostiny API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Boostiny API'
            ], 500);
        }
    }

    /**
     * Get Boostiny link performance data
     */
    public function getLinkPerformance(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Boostiny'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $apiUrl = 'https://api.boostiny.com/v2/reports/link-performance/data';
            $params = [
                'limit' => 20,
                'page' => 1,
                'dimensions' => ['campaign_networks', 'traffic_source'],
                'metrics' => ['orders', 'revenue', 'sales_amount_usd'],
                'from' => $startDate,
                'to' => $endDate,
                'type' => 'gross'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
            ])->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                $this->processLinkPerformanceData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Link performance data synced successfully',
                    'data' => $data['payload']['data'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch link performance data'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Boostiny Link Performance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching link performance data'
            ], 500);
        }
    }

    /**
     * Process campaign data and save to database
     */
    private function processCampaignData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['payload']['data'])) {
            return;
        }

        // Delete existing data for date range
        Purchase::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->delete();

        foreach ($data['payload']['data'] as $item) {
            try {
                // Create or get campaign
                $campaign = Campaign::firstOrCreate([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'network_campaign_id' => $item['campaign_id'],
                ], [
                    'name' => $item['campaign_name'],
                    'campaign_type' => 'coupon',
                    'status' => 'active',
                ]);

                // Create or get coupon
                $coupon = Coupon::firstOrCreate([
                    'campaign_id' => $campaign->id,
                    'code' => $item['code'] ?? 'NA-' . $item['campaign_name'],
                ], [
                    'status' => 'active',
                    'used_count' => 0,
                ]);

                // Get country
                $country = Country::where('code', $item['country'] ?? 'NA')->first();
                if (!$country) {
                    $country = Country::where('code', 'NA')->first();
                }

                // Create purchase record
                Purchase::create([
                    'coupon_id' => $coupon->id,
                    'campaign_id' => $campaign->id,
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'order_id' => $item['order_id'] ?? null,
                    'network_order_id' => $item['network_order_id'] ?? null,
                    'order_value' => $item['sales_amount_usd'] ?? 0,
                    'commission' => $item['revenue'] ?? 0,
                    'revenue' => $item['revenue'] ?? 0,
                    'quantity' => $item['orders'] ?? 1,
                    'currency' => 'USD',
                    'country_code' => $country->code,
                    'customer_type' => $item['customer_type'] ?? 'unknown',
                    'status' => 'approved',
                    'order_date' => $item['date'] ?? now()->format('Y-m-d'),
                    'purchase_date' => $item['last_updated_at'] ?? now()->format('Y-m-d'),
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Boostiny campaign data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Process link performance data
     */
    private function processLinkPerformanceData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['payload']['data'])) {
            return;
        }

        foreach ($data['payload']['data'] as $item) {
            try {
                // Create campaign for link performance
                $campaign = Campaign::firstOrCreate([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'network_campaign_id' => $item['campaign_id'],
                ], [
                    'name' => $item['campaign_networks'],
                    'campaign_type' => 'link',
                    'status' => 'active',
                ]);

                // Create purchase record for link performance
                Purchase::create([
                    'campaign_id' => $campaign->id,
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'order_value' => $item['sales_amount_usd'] ?? 0,
                    'commission' => $item['revenue'] ?? 0,
                    'revenue' => $item['revenue'] ?? 0,
                    'quantity' => $item['orders'] ?? 1,
                    'currency' => 'USD',
                    'country_code' => 'NA',
                    'status' => 'approved',
                    'order_date' => $startDate,
                    'purchase_date' => $startDate,
                    'metadata' => [
                        'traffic_source' => $item['traffic_source'] ?? null,
                        'sub_id' => $item['traffic_source'] ?? null,
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Boostiny link performance data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get user's connection to Boostiny
     */
    private function getConnection($user)
    {
        return NetworkConnection::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Test connection to Boostiny
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
            ])->get('https://api.boostiny.com/v2/publisher/performance?limit=1');

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
