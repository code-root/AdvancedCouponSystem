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

class AdmitadController extends Controller
{
    protected $network;

    public function __construct()
    {
        $this->network = Network::where('name', 'admitad')->first();
    }

    /**
     * Get Admitad campaigns data
     */
    public function getCampaigns(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Admitad'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('d.m.Y'));
            $endDate = $request->input('end_date', now()->format('d.m.Y'));

            $apiUrl = 'https://api.admitad.com/statistics/actions/';
            $params = [
                'limit' => 500,
                'start_date' => $startDate,
                'date_end' => $endDate,
                'order_by' => 'action_date',
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
                    'data' => $data['results'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data from Admitad API'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Admitad API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Admitad API'
            ], 500);
        }
    }

    /**
     * Get Admitad statistics data
     */
    public function getStatistics(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Admitad'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('d.m.Y'));
            $endDate = $request->input('end_date', now()->format('d.m.Y'));

            $apiUrl = 'https://api.admitad.com/statistics/campaigns/';
            $params = [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $connection->access_token,
                'Accept' => 'application/json',
            ])->get($apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                $this->processStatisticsData($data, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Statistics data synced successfully',
                    'data' => $data['results'] ?? []
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics data'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Admitad Statistics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics data'
            ], 500);
        }
    }

    /**
     * Process campaign data and save to database
     */
    private function processCampaignData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['results']) || empty($data['results'])) {
            return;
        }

        // Delete existing data for date range
        Purchase::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->whereBetween('order_date', [Carbon::createFromFormat('d.m.Y', $startDate)->format('Y-m-d'), Carbon::createFromFormat('d.m.Y', $endDate)->format('Y-m-d')])
            ->delete();

        foreach ($data['results'] as $item) {
            try {
                // Convert AED to USD if needed
                $sau = $item['cart'] ?? 0;
                $revenue = $item['payment'] ?? 0;
                
                if (($item['currency'] ?? 'USD') == 'AED') {
                    $sau = $item['cart'] / 3.67;
                    $revenue = $item['payment'] / 3.67;
                }

                // Create or get campaign
                $campaign = Campaign::firstOrCreate([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'network_campaign_id' => $item['advcampaign_id'],
                ], [
                    'name' => $item['advcampaign_name'],
                    'campaign_type' => $item['promocode'] ? 'coupon' : 'link',
                    'status' => 'active',
                ]);

                if ($item['promocode']) {
                    // Create coupon
                    $coupon = Coupon::firstOrCreate([
                        'campaign_id' => $campaign->id,
                        'code' => $item['promocode'],
                    ], [
                        'status' => 'active',
                        'used_count' => 0,
                    ]);

                    // Create purchase record
                    Purchase::create([
                        'coupon_id' => $coupon->id,
                        'campaign_id' => $campaign->id,
                        'network_id' => $this->network->id,
                        'user_id' => $user->id,
                        'order_id' => $item['action_id'],
                        'network_order_id' => $item['action_id'],
                        'sales_amount' => $sau,
                        'revenue' => $revenue,
                        'revenue' => $revenue,
                        'quantity' => 1,
                        'currency' => 'USD',
                        'country_code' => 'NA',
                        'status' => $item['status'] ?? 'approved',
                        'order_date' => Carbon::parse($item['action_date'])->format('Y-m-d'),
                        'purchase_date' => Carbon::parse($item['action_date'])->format('Y-m-d'),
                    ]);
                } else {
                    // Create purchase record without coupon
                    Purchase::create([
                        'campaign_id' => $campaign->id,
                        'network_id' => $this->network->id,
                        'user_id' => $user->id,
                        'order_id' => $item['action_id'],
                        'network_order_id' => $item['action_id'],
                        'sales_amount' => $sau,
                        'revenue' => $revenue,
                        'revenue' => $revenue,
                        'quantity' => 1,
                        'currency' => 'USD',
                        'country_code' => 'NA',
                        'status' => $item['status'] ?? 'approved',
                        'order_date' => Carbon::parse($item['action_date'])->format('Y-m-d'),
                        'purchase_date' => Carbon::parse($item['action_date'])->format('Y-m-d'),
                        'metadata' => [
                            'sub_id' => $item['subid'] ?? null,
                        ]
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Error processing Admitad campaign data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Process statistics data
     */
    private function processStatisticsData($data, $user, $startDate, $endDate)
    {
        if (!isset($data['results']) || empty($data['results'])) {
            return;
        }

        foreach ($data['results'] as $item) {
            try {
                // Create or get campaign
                $campaign = Campaign::firstOrCreate([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'network_campaign_id' => $item['campaign_id'],
                ], [
                    'name' => $item['campaign_name'],
                    'campaign_type' => 'campaign',
                    'status' => 'active',
                ]);

                // Store statistics data
                \App\Models\NetworkData::create([
                    'network_id' => $this->network->id,
                    'user_id' => $user->id,
                    'data_type' => 'statistics',
                    'data' => $item,
                    'data_date' => Carbon::createFromFormat('d.m.Y', $startDate)->format('Y-m-d'),
                    'synced_at' => now(),
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Admitad statistics data: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get user's connection to Admitad
     */
    private function getConnection($user)
    {
        return NetworkConnection::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Test connection to Admitad
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
            ])->get('https://api.admitad.com/statistics/actions/?limit=1');

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
