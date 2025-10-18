<?php

namespace App\Services\Networks;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OptimiseMediaService extends BaseNetworkService implements NetworkServiceInterface
{
    protected string $networkName = 'OptimiseMedia';
    
    protected array $requiredFields = [
        'token' => [
            'label' => 'API Token',
            'type' => 'text',
            'required' => true,
            'encrypted' => false,
            'placeholder' => 'Enter your API Token'
        ],
        'contact_id' => [
            'label' => 'Contact ID',
            'type' => 'text',
            'required' => true,
            'encrypted' => false,
            'placeholder' => 'Enter your Contact ID'
        ],
        'agency_id' => [
            'label' => 'Agency ID',
            'type' => 'text',
            'required' => true,
            'encrypted' => false,
            'placeholder' => 'Enter your Agency ID'
        ]
    ];
    
    protected array $defaultConfig = [
        'date_from' => null, // Will be set dynamically
        'date_to' => null,   // Will be set dynamically
        'target_currency' => 'USD',
        'date_type' => 'conversionDate'
    ];

    /**
     * Test the connection to OptimiseMedia API
     */
    public function testConnection(array $credentials): array
    {
        try {
            $validation = $this->validateCredentials($credentials);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials provided',
                    'errors' => $validation['errors']
                ];
            }

            $token = $credentials['token'];
            $contactId = $credentials['contact_id'];
            $agencyId = $credentials['agency_id'];

            // Build API URL with query parameters
            $apiUrl = 'https://public.api.optimisemedia.com/v1/reporting/';
            $apiUrl .= '?contactId=' . $contactId . '&agencyId=' . $agencyId;

            $testData = [
                "measures" => [
                    "rejectedCommission",
                    "pendingCommission",
                    "validatedCommission",
                    "clicks",
                    "pendingConversions", 
                    "validatedConversions", 
                    "rejectedConversions", 
                    "clickCommission", 
                    "averageOrderValue", 
                    "originalOrderValue", 
                    "totalCommission", 
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
                "fromDate" => Carbon::now()->startOfMonth()->format('d/m/Y'),
                "toDate" => Carbon::now()->endOfMonth()->format('d/m/Y'),
                "targetCurrency" => "USD",
                "dateGroupBy" => "daily",
                "includeOriginalCurrency" => true,
                "includeTargetCurrency" => true
            ];


            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'APIkey' => $token
                ])
                ->post($apiUrl, $testData);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Successfully connected to OptimiseMedia!',
                    'data' => [
                        'api_url' => $apiUrl,
                        'status_code' => $response->status()
                    ]
                ];
            }

            Log::error('OptimiseMedia Connection Test Failed:', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $apiUrl
            ]);

            return [
                'success' => false,
                'message' => 'Failed to connect to OptimiseMedia API: ' . $response->body(),
                'data' => [
                    'status' => $response->status(),
                    'error' => $response->body()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('OptimiseMedia Connection Test Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Sync data from OptimiseMedia
     */
    public function syncData(array $credentials, array $config = []): array
    {
        try {
            $validation = $this->validateCredentials($credentials);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials provided',
                    'errors' => $validation['errors']
                ];
            }

            $token = $credentials['token'];
            $contactId = $credentials['contact_id'];
            $agencyId = $credentials['agency_id'];

            // Parse dates - default to full month (1st to last day) if not specified
            $startDate = isset($config['date_from']) 
                ? Carbon::parse($config['date_from']) 
                : Carbon::now()->startOfMonth();
            
            $endDate = isset($config['date_to']) 
                ? Carbon::parse($config['date_to']) 
                : Carbon::now()->endOfMonth();

            // Build API URL
            $apiUrl = 'https://public.api.optimisemedia.com/v1/reporting/';
            $apiUrl .= '?contactId=' . $contactId . '&agencyId=' . $agencyId;

            // Build request data according to OptimiseMedia API


            $requestData = [
                "measures" => [
                    "rejectedCommission",
                    "pendingCommission",
                    "validatedCommission",
                    "clicks",
                    "pendingConversions", 
                    "validatedConversions", 
                    "rejectedConversions", 
                    "clickCommission", 
                    "averageOrderValue", 
                    "originalOrderValue", 
                    "totalCommission", 
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
                'fromDate' => $startDate->format('d/m/Y'),
                'toDate' => $endDate->format('d/m/Y'),
                "targetCurrency" => "USD",
                "dateGroupBy" => "daily",
                "includeOriginalCurrency" => true,
                "includeTargetCurrency" => true
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'APIkey' => $token
                ])
                ->post($apiUrl, $requestData);

            if (!$response->successful()) {
                Log::error('OptimiseMedia API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $apiUrl
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to fetch data from OptimiseMedia',
                    'data' => [
                        'status' => $response->status(),
                        'error' => $response->body()
                    ]
                ];
            }

            $responseData = $response->json();

            if (empty($responseData) || !is_array($responseData)) {
                return [
                    'success' => false,
                    'message' => 'No data returned from OptimiseMedia API',
                    'data' => []
                ];
            }

            // Process and return data
            $totalRecords = count($responseData);
            
            // Add purchase_type and ensure required fields for each item
            foreach ($responseData as &$item) {
                // Calculate revenue from all commission types
                $rejectedCommission = (float) ($item["rejectedCommission"] ?? 0);
                $pendingCommission = (float) ($item["pendingCommission"] ?? 0);
                $validatedCommission = (float) ($item["validatedCommission"] ?? 0);
                $revenue = $rejectedCommission + $pendingCommission + $validatedCommission;
                
                $item['purchase_type'] = 'coupon'; // OptimiseMedia is typically coupon-based
                // Ensure required fields exist with default values
                $item['campaign_id'] = $item['advertiserId'] ?? 'UNKNOWN';
                $item['campaign_name'] = $item['advertiserName'] ?? 'Unknown Campaign';
                $item['code'] = $item['voucherCode'] ?? 'NA';
                $item['country'] = $item['countryCode'] ?? 'NA';
                $item['sales_amount'] = (float) ($item['originalOrderValue'] ?? 0);
                $item['revenue'] = $revenue;
                $item['quantity'] = ($item['validatedConversions'] ?? 0) + ($item['pendingConversions'] ?? 0) + ($item['rejectedConversions'] ?? 0);
                $item['customer_type'] = 'unknown';
                $item['status'] = 'approved'; // Default status
                $item['order_date'] = $item['date'] ?? now()->format('Y-m-d');
                $item['purchase_date'] = $item['date'] ?? now()->format('Y-m-d');
            }
            
            return [
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from OptimiseMedia",
                'data' => [
                    'coupons' => [
                        'campaigns' => $totalRecords,
                        'coupons' => $totalRecords,
                        'purchases' => 0,
                        'total' => $totalRecords,
                        'data' => $responseData
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('OptimiseMedia Sync Exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get default configuration for this network
     */
    public function getDefaultConfig(): array
    {
        return [
            'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'date_to' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            'target_currency' => 'USD',
            'date_type' => 'conversionDate'
        ];
    }
}

