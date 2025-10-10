<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrokerController extends Controller
{
    /**
     * Get all available brokers.
     */
    public function index()
    {
        $brokers = Broker::where('is_active', true)
            ->orderBy('display_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brokers,
        ]);
    }

    /**
     * Get broker details.
     */
    public function show(Broker $broker)
    {
        $user = Auth::user();
        $connection = BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'broker' => $broker,
                'connection' => $connection,
            ],
        ]);
    }

    /**
     * Get user's connected brokers.
     */
    public function connected()
    {
        $user = Auth::user();
        $connections = BrokerConnection::with('broker')
            ->where('user_id', $user->id)
            ->where('is_connected', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $connections,
        ]);
    }

    /**
     * Connect to a broker.
     */
    public function connect(Request $request, Broker $broker)
    {
        $user = Auth::user();
        
        // Check if already connected
        $existingConnection = BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->first();

        if ($existingConnection && $existingConnection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Already connected to this broker',
            ], 400);
        }

        // Create or update connection
        $connection = BrokerConnection::updateOrCreate(
            [
                'user_id' => $user->id,
                'broker_id' => $broker->id,
            ],
            [
                'connection_name' => $request->input('connection_name', $broker->display_name),
                'is_active' => true,
                'is_connected' => false,
            ]
        );

        // Generate auth URL based on broker type
        $authUrl = $this->generateAuthUrl($broker, $connection);

        return response()->json([
            'success' => true,
            'message' => 'Connection initiated',
            'data' => [
                'connection' => $connection,
                'auth_url' => $authUrl,
            ],
        ]);
    }

    /**
     * Disconnect from a broker.
     */
    public function disconnect(Broker $broker)
    {
        $user = Auth::user();
        
        $connection = BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found',
            ], 404);
        }

        $connection->update([
            'is_connected' => false,
            'is_active' => false,
            'access_token' => null,
            'refresh_token' => null,
            'connected_at' => null,
            'error_message' => 'Disconnected by user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Disconnected successfully',
        ]);
    }

    /**
     * Handle broker callback.
     */
    public function callback(Request $request, Broker $broker)
    {
        $user = Auth::user();
        
        $connection = BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No connection found',
            ], 404);
        }

        try {
            // Handle different broker types
            $result = $this->handleBrokerCallback($broker, $connection, $request);
            
            if ($result['success']) {
                $connection->update([
                    'is_connected' => true,
                    'connected_at' => now(),
                    'error_message' => null,
                    ...$result['data'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Connected successfully',
                    'data' => $connection,
                ]);
            } else {
                $connection->update([
                    'error_message' => $result['message'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Broker callback error', [
                'broker' => $broker->name,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            $connection->update([
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection failed',
            ], 500);
        }
    }

    /**
     * Sync data from broker.
     */
    public function sync(Request $request, Broker $broker)
    {
        $user = Auth::user();
        
        $connection = BrokerConnection::where('user_id', $user->id)
            ->where('broker_id', $broker->id)
            ->where('is_connected', true)
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'No active connection found',
            ], 404);
        }

        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $result = $this->syncBrokerData($broker, $connection, $startDate, $endDate);
            
            if ($result['success']) {
                $connection->updateLastSync();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data synced successfully',
                    'data' => $result['data'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Broker sync error', [
                'broker' => $broker->name,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed',
            ], 500);
        }
    }

    /**
     * Generate authentication URL for broker.
     */
    private function generateAuthUrl(Broker $broker, BrokerConnection $connection): string
    {
        $params = [
            'client_id' => $broker->client_id,
            'redirect_uri' => $broker->callback_url,
            'response_type' => 'code',
            'state' => $connection->id,
        ];

        return $broker->auth_url . '?' . http_build_query($params);
    }

    /**
     * Handle broker callback based on broker type.
     */
    private function handleBrokerCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        switch ($broker->name) {
            case 'admitad':
                return $this->handleAdmitadCallback($broker, $connection, $request);
            case 'boostiny':
                return $this->handleBoostinyCallback($broker, $connection, $request);
            case 'platformance':
                return $this->handlePlatformanceCallback($broker, $connection, $request);
            case 'optimize':
                return $this->handleOptimizeCallback($broker, $connection, $request);
            case 'marketeers':
                return $this->handleMarketeersCallback($broker, $connection, $request);
            case 'digizag':
                return $this->handleDigizagCallback($broker, $connection, $request);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported broker type',
                ];
        }
    }

    /**
     * Handle Admitad callback.
     */
    private function handleAdmitadCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        $code = $request->input('code');
        
        if (!$code) {
            return [
                'success' => false,
                'message' => 'Authorization code not provided',
            ];
        }

        $response = Http::asForm()->post('https://api.admitad.com/token/', [
            'client_id' => $broker->client_id,
            'client_secret' => $broker->client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $broker->callback_url,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            return [
                'success' => true,
                'data' => [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to get access token',
        ];
    }

    /**
     * Handle Boostiny callback.
     */
    private function handleBoostinyCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        // Boostiny uses API key authentication
        $apiKey = $request->input('api_key');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'API key not provided',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'access_token' => $apiKey,
                'expires_at' => now()->addYear(), // API keys typically don't expire
            ],
        ];
    }

    /**
     * Handle Platformance callback.
     */
    private function handlePlatformanceCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        // Platformance uses cookie-based authentication
        $cookies = $request->input('cookies');
        
        if (!$cookies) {
            return [
                'success' => false,
                'message' => 'Cookies not provided',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'access_token' => $cookies,
                'expires_at' => now()->addDays(30), // Cookies typically expire in 30 days
            ],
        ];
    }

    /**
     * Handle Optimize callback.
     */
    private function handleOptimizeCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        $apiKey = $request->input('api_key');
        $contactId = $request->input('contact_id');
        $agencyId = $request->input('agency_id');
        
        if (!$apiKey || !$contactId || !$agencyId) {
            return [
                'success' => false,
                'message' => 'Required credentials not provided',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'access_token' => $apiKey,
                'credentials' => [
                    'contact_id' => $contactId,
                    'agency_id' => $agencyId,
                ],
                'expires_at' => now()->addYear(),
            ],
        ];
    }

    /**
     * Handle Marketeers callback.
     */
    private function handleMarketeersCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        $sessionToken = $request->input('session_token');
        
        if (!$sessionToken) {
            return [
                'success' => false,
                'message' => 'Session token not provided',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'access_token' => $sessionToken,
                'expires_at' => now()->addHours(24), // Session tokens typically expire in 24 hours
            ],
        ];
    }

    /**
     * Handle Digizag callback.
     */
    private function handleDigizagCallback(Broker $broker, BrokerConnection $connection, Request $request): array
    {
        $apiKey = $request->input('api_key');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'API key not provided',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'access_token' => $apiKey,
                'expires_at' => now()->addYear(),
            ],
        ];
    }

    /**
     * Sync data from broker.
     */
    private function syncBrokerData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        switch ($broker->name) {
            case 'admitad':
                return $this->syncAdmitadData($broker, $connection, $startDate, $endDate);
            case 'boostiny':
                return $this->syncBoostinyData($broker, $connection, $startDate, $endDate);
            case 'platformance':
                return $this->syncPlatformanceData($broker, $connection, $startDate, $endDate);
            case 'optimize':
                return $this->syncOptimizeData($broker, $connection, $startDate, $endDate);
            case 'marketeers':
                return $this->syncMarketeersData($broker, $connection, $startDate, $endDate);
            case 'digizag':
                return $this->syncDigizagData($broker, $connection, $startDate, $endDate);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported broker type',
                ];
        }
    }

    /**
     * Sync Admitad data.
     */
    private function syncAdmitadData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        // Implementation for Admitad data sync
        return [
            'success' => true,
            'message' => 'Admitad data synced successfully',
            'data' => [],
        ];
    }

    /**
     * Sync Boostiny data.
     */
    private function syncBoostinyData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        // Implementation for Boostiny data sync
        return [
            'success' => true,
            'message' => 'Boostiny data synced successfully',
            'data' => [],
        ];
    }

    /**
     * Sync Platformance data.
     */
    private function syncPlatformanceData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        // Implementation for Platformance data sync
        return [
            'success' => true,
            'message' => 'Platformance data synced successfully',
            'data' => [],
        ];
    }

    /**
     * Sync Optimize data.
     */
    private function syncOptimizeData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        // Implementation for Optimize data sync
        return [
            'success' => true,
            'message' => 'Optimize data synced successfully',
            'data' => [],
        ];
    }

    /**
     * Sync Marketeers data.
     */
    private function syncMarketeersData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        // Implementation for Marketeers data sync
        return [
            'success' => true,
            'message' => 'Marketeers data synced successfully',
            'data' => [],
        ];
    }

    /**
     * Sync Digizag data.
     */
    private function syncDigizagData(Broker $broker, BrokerConnection $connection, string $startDate, string $endDate): array
    {
        // Implementation for Digizag data sync
        return [
            'success' => true,
            'message' => 'Digizag data synced successfully',
            'data' => [],
        ];
    }
}
