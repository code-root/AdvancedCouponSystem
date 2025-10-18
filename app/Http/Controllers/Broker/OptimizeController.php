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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OptimizeController extends Controller
{
    protected $network;

    public function __construct()
    {
        $this->network = Network::where('name', 'optimize')->first();
    }

    /**
     * Get Optimize Media reporting data
     */
    public function getReporting(Request $request)
    {
        $user = Auth::user();
        $connection = $this->getConnection($user);
        
        if (!$connection || !$connection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection to Optimize Media'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('d/m/Y'));
            $endDate = $request->input('end_date', now()->format('d/m/Y'));

            $contactId = $connection->getCredential('contact_id');
            $agencyId = $connection->getCredential('agency_id');
            
            if (!$contactId || !$agencyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing contact ID or agency ID'
                ], 400);
            }

            $apiUrl = $this->network->url_api . 'reporting/?contactId=' . $contactId . '&agencyId=' . $agencyId;
            
            $data = [
                "measures" => [
                    "rejectedrevenue",
                    "pendingrevenue", 
                    "validatedrevenue",
                    "clicks",
                    "pendingConversions",
                    "validatedConversions",
                    "rejectedConversions",
                    "clickrevenue",
                    "averageOrderValue",
                    "originalOrderValue",
                    "totalrevenue",
                    "uniqueVisitors"
                ],
                "dimensions" => [
                    "date",
                    "countryCode",
                    "currencyCode", 
                    "campaignName",
                    "voucherCode",
                    "advertiserName",
                    "advertiserId"
                ],
                "conditions" => [
                    [
                        "operator" => ">=",
                        "valueList" => ["0"],
                        "field" => "clicks",
                        "or" => true
                    ]
                ],
                "orderBys" => [
                    [
                        "direction" => "desc",
                        "field" => "voucherCode"
                    ]
                ],
                "dateType" => "conversionDate",
                "fromDate" => $startDate,
                "toDate" => $endDate,
                "targetCurrency" => "USD",
                "dateGroupBy" => "daily",
                "includeOriginalCurrency" => true,
                "includeTargetCurrency" => true
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'APIkey' => $connection->access_token
            ])->post($apiUrl, $data);

            if ($response->successful()) {
                $responseData = $response->json();
                $this->processReportingData($responseData, $user, $startDate, $endDate);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Reporting data synced successfully',
                    'data' => $responseData
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data from Optimize Media API'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Optimize Media API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Optimize Media API'
            ], 500);
        }
    }

    /**
     * Process reporting data and save to database
     */
    private function processReportingData($responseData, $user, $startDate, $endDate)
    {
        if (empty($responseData) || !is_array($responseData)) {
            return;
        }

        DB::transaction(function () use ($responseData, $user, $startDate, $endDate) {
            // Delete existing data for date range
            Purchase::where('user_id', $user->id)
                ->where('network_id', $this->network->id)
                ->whereBetween('order_date', [
                    Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d'),
                    Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d')
                ])
                ->delete();

            foreach ($responseData as $item) {
                try {
                    // Validate required fields
                    if (empty($item["date"]) || empty($item["advertiserName"]) || empty($item["advertiserId"])) {
                        continue;
                    }
                    
                    // Calculate total orders count
                    $countOrders = ($item["pendingConversions"] ?? 0) + 
                                  ($item["validatedConversions"] ?? 0) + 
                                  ($item["rejectedConversions"] ?? 0);
                    
                    // Skip items with no conversions
                    if ($countOrders == 0) {
                        continue;
                    }
                    
                    $date = $item["date"];
                    $campaignLogo = 'https://www.optimisemedia.com/assets/icons/logo-circle.svg';
                    
                    // Handle country code
                    $countryCode = ($item['countryCode'] && $item['countryCode'] !== '-') 
                        ? $item['countryCode'] 
                        : 'US';
                    
                    $rejectedrevenue = $item["rejectedrevenue"] ?? 0;
                    $pendingrevenue = $item["pendingrevenue"] ?? 0;
                    $validatedrevenue = $item["validatedrevenue"] ?? 0;
                    $revenue = $rejectedrevenue + $pendingrevenue + $validatedrevenue;
                    
                    // Get or create country
                    $country = Country::where('code', $countryCode)->first();
                    if (!$country) {
                        $country = Country::where('code', 'US')->first();
                    }
                    
                    // Create or get campaign
                    $campaign = Campaign::firstOrCreate([
                        'network_id' => $this->network->id,
                        'user_id' => $user->id,
                        'network_campaign_id' => $item["advertiserId"],
                    ], [
                        'name' => $item["advertiserName"],
                        'campaign_type' => 'coupon',
                        'status' => 'active',
                        'logo_url' => $campaignLogo,
                        'advertiser_name' => $item["advertiserName"],
                        'advertiser_id' => $item["advertiserId"],
                    ]);

                    // Create or get coupon if voucher code exists
                    $coupon = null;
                    if (!empty($item["voucherCode"])) {
                        $coupon = Coupon::firstOrCreate([
                            'campaign_id' => $campaign->id,
                            'code' => $item["voucherCode"],
                        ], [
                            'status' => 'active',
                            'used_count' => 0,
                        ]);
                    }

                    // Create purchase record
                    Purchase::create([
                        'coupon_id' => $coupon?->id,
                        'campaign_id' => $campaign->id,
                        'network_id' => $this->network->id,
                        'user_id' => $user->id,
                        'sales_amount' => $item["originalOrderValue"] ?? 0,
                        'revenue' => $revenue,
                        'revenue' => $revenue,
                        'quantity' => $countOrders,
                        'currency' => 'USD',
                        'country_code' => $country->code,
                        'customer_type' => strtolower(trim($item["campaignName"] ?? '')),
                        'status' => 'approved',
                        'order_date' => $date,
                        'purchase_date' => $date,
                        'metadata' => [
                            'clicks' => $item["clicks"] ?? 0,
                            'validated_conversions' => $item["validatedConversions"] ?? 0,
                            'pending_conversions' => $item["pendingConversions"] ?? 0,
                            'rejected_conversions' => $item["rejectedConversions"] ?? 0,
                            'average_sales_amount' => $item["averageOrderValue"] ?? 0,
                            'unique_visitors' => $item["uniqueVisitors"] ?? 0,
                        ]
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error processing Optimize Media item: ' . $e->getMessage(), [
                        'item' => $item,
                        'user_id' => $user->id
                    ]);
                }
            }
        });
    }

    /**
     * Get user's connection to Optimize Media
     */
    private function getConnection($user)
    {
        return NetworkConnection::where('user_id', $user->id)
            ->where('network_id', $this->network->id)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Test connection to Optimize Media
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
            $contactId = $connection->getCredential('contact_id');
            $agencyId = $connection->getCredential('agency_id');
            
            if (!$contactId || !$agencyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing contact ID or agency ID'
                ], 400);
            }

            $apiUrl = $this->network->url_api . 'reporting/?contactId=' . $contactId . '&agencyId=' . $agencyId;
            
            $testData = [
                "measures" => ["clicks"],
                "dimensions" => ["date"],
                "fromDate" => now()->format('d/m/Y'),
                "toDate" => now()->format('d/m/Y'),
                "targetCurrency" => "USD",
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'APIkey' => $connection->access_token
            ])->post($apiUrl, $testData);

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
