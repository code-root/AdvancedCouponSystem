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

class DigizagController extends Controller
{
    protected $network;

    public function __construct()
    {
        $this->network = Network::where('name', 'digizag')->first();
    }

    /**
     * Get Digizag campaigns data
     */
    public function getCampaigns(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Digizag'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $apiUrl = 'https://api.digizag.com/v1/campaigns';
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'limit' => 100,
                'page' => 1,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                $this->processCampaignData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Campaigns data synced successfully',
                    'data' => $data['data'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data from Digizag API'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Digizag API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Digizag API'
            ], 500);
        }
    }

    /**
     * Get Digizag performance data
     */
    public function getPerformance(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Digizag'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $apiUrl = 'https://api.digizag.com/v1/performance';
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => ['campaign', 'date'],
                'metrics' => ['clicks', 'conversions', 'revenue', 'revenue'],
                'limit' => 1000,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                $this->processPerformanceData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Performance data synced successfully',
                    'data' => $data['data'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch performance data'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Digizag Performance Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching performance data'
            ], 500);
        }
    }

    /**
     * Get Digizag affiliate links
     */
    public function getAffiliateLinks(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Digizag'
            ], 400);
        }

        try {
            $apiUrl = 'https://api.digizag.com/v1/affiliate-links';
            $params = [
                'status' => 'active',
                'limit' => 100,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Affiliate links fetched successfully',
                    'data' => $data['data'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch affiliate links'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Digizag Affiliate Links Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching affiliate links'
            ], 500);
        }
    }

    /**
     * Process campaign data and save to database
     */
    private function processCampaignData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['data']) || empty($data['data'])) {
            return;
        }

        // Delete existing data for date range
        Purchase::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->delete();

        foreach ($data['data'] as $item) {
            try {
                // Create or get campaign
                $campaign = Campaign::firstOrCreate([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'network_campaign_id' => $item['campaign_id'] ?? uniqid(),
                ], [
                    'name' => $item['campaign_name'] ?? 'Unknown Campaign',
                    'campaign_type' => $item['campaign_type'] ?? 'coupon',
                    'status' => $item['status'] === 'active' ? 'active' : 'inactive',
                    'description' => $item['description'] ?? null,
                ]);

                // Create or get coupon if exists
                if (!empty($item['coupon_code'])) {
                    $coupon = Coupon::firstOrCreate([
                        'campaign_id' => $campaign->id,
                        'code' => $item['coupon_code'],
                    ], [
                        'status' => 'active',
                        'discount_value' => $item['discount_value'] ?? null,
                        'discount_type' => $item['discount_type'] ?? 'percentage',
                        'expires_at' => $item['expires_at'] ? Carbon::parse($item['expires_at']) : null,
                        'usage_limit' => $item['usage_limit'] ?? null,
                        'used_count' => 0,
                    ]);
                }

                // Store campaign metadata
                \App\Models\NetworkData::create([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'data_type' => 'campaign',
                    'data' => $item,
                    'data_date' => $startDate,
                    'synced_at' => now(),
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Digizag campaign data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Process performance data and save to database
     */
    private function processPerformanceData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['data']) || empty($data['data'])) {
            return;
        }

        foreach ($data['data'] as $item) {
            try {
                if (empty($item['campaign_id']) || ($item['conversions'] ?? 0) == 0) {
                    continue;
                }

                // Get or create campaign
                $campaign = Campaign::where('network_id', $this->network->id)
                    ->where('user_id', $user->id)
                    ->where('network_campaign_id', $item['campaign_id'])
                    ->first();

                if (!$campaign) {
                    $campaign = Campaign::create([
                        'network_id' => $this->network->id,
                        'user_id' => $user->id,
                        'network_campaign_id' => $item['campaign_id'],
                        'name' => $item['campaign_name'] ?? 'Unknown Campaign',
                        'campaign_type' => 'performance',
                        'status' => 'active',
                    ]);
                }

                // Get country
                $country = Country::where('code', $item['country'] ?? 'NA')->first();
                if (!$country) {
                    $country = Country::where('code', 'NA')->first();
                }

                // Create purchase record
                Purchase::create([
                    'campaign_id' => $campaign->id,
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'sales_amount' => $item['revenue'] ?? 0,
                    'revenue' => $item['revenue'] ?? 0,
                    'revenue' => $item['revenue'] ?? 0,
                    'quantity' => $item['conversions'] ?? 1,
                    'currency' => $item['currency'] ?? 'USD',
                    'country_code' => $country->code,
                    'status' => 'approved',
                    'order_date' => $item['date'] ?? $startDate,
                    'purchase_date' => $item['date'] ?? $startDate,
                    'metadata' => [
                        'clicks' => $item['clicks'] ?? 0,
                        'conversion_rate' => $item['conversion_rate'] ?? 0,
                        'cpc' => $item['cpc'] ?? 0,
                        'cpm' => $item['cpm'] ?? 0,
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Digizag performance data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get user's connection to Digizag
     */
    private function getConnection($user)
    {
        return NetworkConnection::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Test connection to Digizag
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
            ])->get('https://api.digizag.com/v1/profile');

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
