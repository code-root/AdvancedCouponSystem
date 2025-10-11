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
                "fromDate" => Carbon::today()->format('d/m/Y'),
                "toDate" => Carbon::today()->format('d/m/Y'),
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

            return [
                'success' => false,
                'message' => 'Failed to connect to OptimiseMedia API',
                'data' => [
                    'status' => $response->status(),
                    'error' => $response->body()
                ]
            ];

        } catch (\Exception $e) {
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

            // Parse dates
            $startDate = isset($config['date_from']) 
                ? Carbon::parse($config['date_from']) 
                : Carbon::now()->startOfMonth();
            
            $endDate = isset($config['date_to']) 
                ? Carbon::parse($config['date_to']) 
                : Carbon::now();

            // Build API URL
            $apiUrl = 'https://public.api.optimisemedia.com/v1/reporting/';
            $apiUrl .= '?contactId=' . $contactId . '&agencyId=' . $agencyId;

            // Build request data according to OptimiseMedia API
            $requestData = [
                'measures' => [
                    'rejectedCommission',
                    'pendingCommission',
                    'validatedCommission',
                    'clicks',
                    'pendingConversions',
                    'validatedConversions',
                    'rejectedConversions',
                    'clickCommission',
                    'averageOrderValue',
                    'originalOrderValue',
                    'totalCommission',
                    'uniqueVisitors'
                ],
                'dimensions' => [
                    'date',
                    'countryCode',
                    'currencyCode',
                    'campaignName',
                    'voucherCode',
                    'advertiserName',
                    'advertiserId'
                ],
                'conditions' => [
                    [
                        'operator' => '>=',
                        'valueList' => ['0'],
                        'field' => 'clicks',
                        'or' => true
                    ]
                ],
                'orderBys' => [
                    [
                        'direction' => 'desc',
                        'field' => 'voucherCode'
                    ]
                ],
                'dateType' => $config['date_type'] ?? 'conversionDate',
                'fromDate' => $startDate->format('d/m/Y'),
                'toDate' => $endDate->format('d/m/Y'),
                'targetCurrency' => $config['target_currency'] ?? 'USD',
                'dateGroupBy' => 'daily',
                'includeOriginalCurrency' => true,
                'includeTargetCurrency' => true
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'APIkey' => $token
                ])
                ->post($apiUrl, $requestData);

            if (!$response->successful()) {
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
            'date_to' => Carbon::now()->format('Y-m-d'),
            'target_currency' => 'USD',
            'date_type' => 'conversionDate'
        ];
    }
}

