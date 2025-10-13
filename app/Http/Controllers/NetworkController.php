<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\NetworkConnection;
use App\Models\NetworkData;
use App\Services\Networks\NetworkServiceFactory;
use App\Helpers\NetworkDataProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NetworkController extends Controller
{
    /**
     * Display a listing of user network connections
     */
    public function index()
    {
        // Get user's network connections
        $userConnections = auth()->user()->networkConnections()
            ->with('network')
            ->latest()
            ->paginate(15);
        
        // Get available networks (not yet connected)
        $connectedNetworkIds = auth()->user()->networkConnections()->pluck('network_id');
        $availableNetworks = Network::where('is_active', true)
            ->whereNotIn('id', $connectedNetworkIds)
            ->withCount(['connections' => function($query) {
                $query->where('is_connected', true);
            }])
            ->get();
        
        // Add statistics for available networks
        $availableNetworks->each(function($network) {
            // Get total coupons count for this network
            $network->total_coupons = DB::table('coupons')
                ->join('campaigns', 'coupons.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.network_id', $network->id)
                ->count();
            
            // Get average revenue per user for this network
            $revenueStats = DB::table('purchases')
                ->select(DB::raw('COUNT(DISTINCT user_id) as user_count, COALESCE(SUM(revenue), 0) as total_revenue'))
                ->where('network_id', $network->id)
                ->first();
            
            $network->avg_revenue_per_user = $revenueStats && $revenueStats->user_count > 0 
                ? round($revenueStats->total_revenue / $revenueStats->user_count, 2) 
                : 0;
        });
        
        // Add connected users count for user's connections as well
        $userConnections->getCollection()->transform(function($connection) {
            $connection->network->loadCount(['connections' => function($query) {
                $query->where('is_connected', true);
            }]);
            return $connection;
        });
        
        return view('dashboard.networks.index', compact('userConnections', 'availableNetworks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get available networks that user hasn't connected to
        // $connectedNetworkIds = auth()->user()->networkConnections()->pluck('network_id');
        $networks = Network::where('is_active', true)
            // ->whereNotIn('id', $connectedNetworkIds)
            ->get();

        return view('dashboard.networks.create', compact('networks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'network_id' => 'required|exists:networks,id',
            'connection_name' => 'required|string|max:255',
            'api_endpoint' => 'required|string|max:2000',
            'credentials' => 'required|array',
            'status' => 'nullable|string|in:active,pending,inactive',
            'is_connected' => 'nullable|boolean',
        ]);

        try {
            // Get network
            $network = Network::findOrFail($validated['network_id']);
            
            // Check if user already has a connection to this network
            $existingConnection = auth()->user()->networkConnections()
                ->where('network_id', $network->id)
                ->first();
                
            if ($existingConnection) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You already have a connection to this network.');
            }
            
            // Prepare credentials (encrypt sensitive data)
            $credentials = $validated['credentials'];
            $encryptedCredentials = [];
            
            foreach ($credentials as $key => $value) {
                // Encrypt sensitive fields
                if (in_array($key, ['client_secret'])) {
                    $encryptedCredentials[$key] = encrypt($value);
                } else {
                    $encryptedCredentials[$key] = $value;
                }
            }
            
            // Create network connection
            $connection = NetworkConnection::create([
            'user_id' => auth()->id(),
                'network_id' => $validated['network_id'],
            'connection_name' => $validated['connection_name'],
                'api_endpoint' => $validated['api_endpoint'],
                'credentials' => $encryptedCredentials,
                'status' => $validated['status'] ?? 'pending',
                'is_connected' => $validated['is_connected'] ?? false,
                'connected_at' => ($validated['is_connected'] ?? false) ? now() : null,
            ]);

            // Return JSON response if AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Network connection created successfully!',
                    'connection' => $connection
                ]);
            }

            return redirect()->route('networks.index')
                ->with('success', 'Network connection created successfully!');
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create connection: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create connection: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($connectionId)
    {
        $userConnection = auth()->user()->networkConnections()
            ->with('network')
            ->where('id', $connectionId)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

        $network = $userConnection->network;

        // Get network data
        $networkData = $network->data()->latest()->paginate(10);

        return view('dashboard.networks.show', compact('network', 'userConnection', 'networkData'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($connectionId)
    {
        $userConnection = auth()->user()->networkConnections()
            ->with('network')
            ->where('id', $connectionId)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

        $network = $userConnection->network;

        return view('dashboard.networks.edit', compact('network', 'userConnection'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $connectionId)
    {
        $userConnection = auth()->user()->networkConnections()
            ->where('id', $connectionId)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

        $validated = $request->validate([
            'connection_name' => 'required|string|max:255',
            'api_endpoint' => 'required|string|max:2000',
            'credentials' => 'nullable|array',
            'status' => 'required|string|in:active,pending,inactive',
            'is_connected' => 'nullable|boolean',
        ]);

        try {
            // Update basic fields
            $updateData = [
                'connection_name' => $validated['connection_name'],
                'api_endpoint' => $validated['api_endpoint'],
                'status' => $validated['status'],
                'is_connected' => $validated['is_connected'] ?? false,
            ];
            
            // Update credentials if provided
            if (!empty($validated['credentials'])) {
                // Get existing credentials
                $existingCredentials = $userConnection->credentials ?? [];
                $newCredentials = $validated['credentials'];
                
                // Merge with existing (only update provided fields)
                foreach ($newCredentials as $key => $value) {
                    if (!empty($value)) {
                        // Encrypt sensitive fields
                        if (in_array($key, ['client_secret'])) {
                            $existingCredentials[$key] = encrypt($value);
                        } else {
                            $existingCredentials[$key] = $value;
                        }
                    }
                }
                
                $updateData['credentials'] = $existingCredentials;
            }
            
            // Update connection
            $userConnection->update($updateData);

            return redirect()->route('networks.index')
                ->with('success', 'Network connection updated successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update connection: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $connectionId)
    {
        try {
            // Find connection by ID
            $userConnection = auth()->user()->networkConnections()
                ->where('id', $connectionId)
                ->first();

            if (!$userConnection) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Network connection not found.'
                    ], 404);
                }
                
                return redirect()->route('networks.index')
                    ->with('error', 'Network connection not found.');
            }

            $networkName = $userConnection->network->display_name;
            $userConnection->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully disconnected from {$networkName}!"
                ]);
            }

            return redirect()->route('networks.index')
                ->with('success', "Successfully disconnected from {$networkName}!");
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('networks.index')
                ->with('error', 'Failed to disconnect network: ' . $e->getMessage());
        }
    }

    /**
     * Create a new network connection
     */
    public function createConnection(Request $request, Network $network)
    {
        $validated = $request->validate([
            'connection_name' => 'required|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'api_endpoint' => 'required|string|max:2000',
            'contact_id' => 'nullable|string|max:255',
        ]);

        // Check if user already has a connection to this network
        $existingConnection = auth()->user()->networkConnections()
            ->where('network_id', $network->id)
            ->first();

        if ($existingConnection) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a connection to this network.'
            ], 400);
        }

        // Create new connection
        $connection = NetworkConnection::create([
            'user_id' => auth()->id(),
            'network_id' => $network->id,
            'connection_name' => $validated['connection_name'],
            'client_id' => $validated['client_id'],
            'client_secret' => $validated['client_secret'],
            'api_endpoint' => $validated['api_endpoint'],
            'contact_id' => $validated['contact_id'],
            'status' => 'pending',
            'is_connected' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Network connection created successfully!',
            'connection' => $connection
        ]);
    }

    /**
     * Get network data
     */
    public function getData(Network $network)
    {
        $userConnection = auth()->user()->networkConnections()
            ->where('network_id', $network->id)
            ->first();

        if (!$userConnection || !$userConnection->is_connected) {
            return response()->json([
                'success' => false,
                'message' => 'Network not connected.'
            ], 400);
        }

        // Get network data
        $data = $userConnection->network->data()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * Get network configuration (required fields and default config)
     */
    public function getNetworkConfig($networkId)
    {
        try {
            $network = Network::findOrFail($networkId);
            
            // Check if service exists for this network
            if (!NetworkServiceFactory::exists($network->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not available for this network',
                    'data' => [
                        'required_fields' => [],
                        'default_config' => []
                    ]
                ]);
            }
            
            $service = NetworkServiceFactory::create($network->name);
            $requiredFields = $service->getRequiredFields();
            
            // Convert required fields to simple array of field names
            // Support both old format ['email', 'password'] and new format ['email' => [...], 'password' => [...]]
            $fieldNames = [];
            foreach ($requiredFields as $key => $value) {
                if (is_array($value)) {
                    // New format: key is field name
                    $fieldNames[] = $key;
                } else {
                    // Old format: value is field name
                    $fieldNames[] = $value;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'required_fields' => $fieldNames,
                    'required_fields_config' => $requiredFields, // Full config for advanced use
                    'default_config' => $service->getDefaultConfig(),
                    'network_info' => [
                        'name' => $network->name,
                        'display_name' => $network->display_name,
                        'description' => $network->description,
                        'api_url' => $network->api_url
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Test network connection before saving
     */
    public function testConnection(Request $request)
    {
        try {
            $validated = $request->validate([
                'network_id' => 'required|exists:networks,id',
                'credentials' => 'required|array'
            ]);
            
            $network = Network::findOrFail($validated['network_id']);
            
            // Check if service exists
            if (!NetworkServiceFactory::exists($network->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not available for this network. Please contact support.'
                ], 400);
            }
            
            $service = NetworkServiceFactory::create($network->name);
            $result = $service->testConnection($validated['credentials']);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync data from network API
     */
    public function syncConnection(Request $request, $connectionId)
    {
        try {
            // Find connection
            $connection = NetworkConnection::where('id', $connectionId)
                ->where('user_id', auth()->id())
                ->first();
                
            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection not found'
                ], 404);
            }
            
            if (!$connection->is_connected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Network is not connected. Please reconnect first.'
                ], 400);
            }
            
            $network = $connection->network;
            
            // Check if service exists
            if (!NetworkServiceFactory::exists($network->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync service not available for this network.'
                ], 400);
            }
            
            // Get date range from request or use current month
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            
            // Get credentials (decrypt if needed)
            $credentials = $connection->credentials ?? [];
            $decryptedCredentials = [];
            
            foreach ($credentials as $key => $value) {
                // Try to decrypt sensitive fields
                if (in_array($key, ['client_secret'])) {
                    try {
                        $decryptedCredentials[$key] = decrypt($value);
                    } catch (\Exception $e) {
                        $decryptedCredentials[$key] = $value;
                    }
                } else {
                    $decryptedCredentials[$key] = $value;
                }
            }
            
            $decryptedCredentials['api_endpoint'] = $connection->api_endpoint;
            
            // Get service instance
            $service = NetworkServiceFactory::create($network->name);
            
            // Prepare sync config
            $syncConfig = [
                'date_from' => $startDate,
                'date_to' => $endDate
            ];
            
            // Sync data based on network (coupons and/or links)
            $syncResult = $service->syncData($decryptedCredentials, $syncConfig);
            
            if (!$syncResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: ' . ($syncResult['message'] ?? 'Unknown error')
                ], 400);
            }
            
            // Update credentials if new ones were returned (e.g., after re-authentication)
            $credentialsUpdated = false;
            
            if (isset($syncResult['new_cookies'])) {
                $credentials['cookies'] = $syncResult['new_cookies'];
                $credentialsUpdated = true;
            }
            
            if (isset($syncResult['new_access_token'])) {
                $credentials['access_token'] = $syncResult['new_access_token'];
                $credentialsUpdated = true;
            }
            
            if ($credentialsUpdated) {
                $connection->update(['credentials' => $credentials]);
                Log::info("Updated credentials for connection {$connection->id} after re-authentication");
            }
            
            // Process coupon data
            $couponStats = ['campaigns' => 0, 'coupons' => 0, 'purchases' => 0];
            if (!empty($syncResult['data']['coupons']['data'])) {
                $couponResult = NetworkDataProcessor::processCouponData(
                    $syncResult['data']['coupons']['data'],
                    $network->id,
                    auth()->id(),
                    $startDate,
                    $endDate,
                    $network->name // Pass network name for format detection
                );
                $couponStats = $couponResult['processed'] ?? $couponStats;
            }
            
            // Process link data
            $linkStats = ['campaigns' => 0, 'purchases' => 0];
            if (!empty($syncResult['data']['links']['data'])) {
                $linkResult = NetworkDataProcessor::processLinkData(
                    $syncResult['data']['links']['data'],
                    $network->id,
                    auth()->id(),
                    $startDate,
                    $endDate
                );
                $linkStats = $linkResult['processed'] ?? $linkStats;
            }
            
            // Update last sync time
            $connection->update([
                'last_sync' => now()
            ]);
            
            $totalRecords = ($couponStats['purchases'] ?? 0) + ($linkStats['purchases'] ?? 0);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$totalRecords} records from {$network->display_name}!",
                'data' => [
                    'coupons' => $couponStats,
                    'links' => $linkStats,
                    'total_records' => $totalRecords,
                    'date_range' => [
                        'from' => $startDate,
                        'to' => $endDate
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verify user password to view credentials
     */
    public function verifyPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'password' => 'required|string'
            ]);
            
            // Verify password using Hash::check
            if (!Hash::check($validated['password'], auth()->user()->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please try again.'
                ], 401);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Password verified successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying password: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reconnect to network and update credentials
     */
    public function reconnect(Request $request)
    {
        try {
            $validated = $request->validate([
                'connection_id' => 'required|exists:network_connections,id',
                'network_id' => 'required|exists:networks,id',
                'credentials' => 'required|array'
            ]);
            
            // Find connection
            $connection = NetworkConnection::where('id', $validated['connection_id'])
                ->where('user_id', auth()->id())
                ->first();
                
            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection not found'
                ], 404);
            }
            
            $network = $connection->network;
            
            // Check if service exists
            if (!NetworkServiceFactory::exists($network->name)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not available for this network'
                ], 400);
            }
            
            $service = NetworkServiceFactory::create($network->name);
            
            // Test connection with new credentials
            $testResult = $service->testConnection($validated['credentials']);
            
            if (!$testResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reconnection failed: ' . ($testResult['message'] ?? 'Unknown error'),
                    'data' => $testResult['data'] ?? null
                ], 400);
            }
            
            // Encrypt sensitive credentials
            $encryptedCredentials = [];
            foreach ($validated['credentials'] as $key => $value) {
                if (in_array($key, ['client_secret'])) {
                    $encryptedCredentials[$key] = encrypt($value);
                } else {
                    $encryptedCredentials[$key] = $value;
                }
            }
            
            // Update credentials from test result (for services that return cookies, tokens, etc.)
            if (isset($testResult['data'])) {
                foreach ($testResult['data'] as $key => $value) {
                    if (!isset($encryptedCredentials[$key])) {
                        $encryptedCredentials[$key] = $value;
                    }
                }
            }
            
            // Update connection
            $connection->update([
                'credentials' => $encryptedCredentials,
                'is_connected' => true,
                'connected_at' => now(),
                'status' => 'active'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully reconnected and updated credentials!',
                'updated_credentials' => $encryptedCredentials,
                'data' => [
                    'connection_id' => $connection->id,
                    'connected_at' => $connection->connected_at->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Reconnection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Reconnection error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle Admitad OAuth callback
     */
    public function admitadCallback(Request $request)
    {
        try {
            $code = $request->input('code');
            $state = $request->input('state');
            
            if (empty($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization code not received',
                    'error' => $request->input('error'),
                    'error_description' => $request->input('error_description'),
                ], 400);
            }
            
            // Find the pending Admitad connection for this user
            // You might want to store the connection_id in the state parameter
            $connection = NetworkConnection::where('user_id', auth()->id())
                ->whereHas('network', function($query) {
                    $query->where('name', 'admitad');
                })
                ->where('status', 'pending')
                ->latest()
                ->first();
            
            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending Admitad connection found',
                ], 404);
            }
            
            // Get client credentials from connection
            $credentials = $connection->credentials ?? [];
            $clientId = $credentials['client_id'] ?? '';
            $clientSecret = $credentials['client_secret'] ?? '';
            
            if (empty($clientId) || empty($clientSecret)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client credentials not found',
                ], 400);
            }
            
            // Exchange code for access token
            $service = NetworkServiceFactory::create('admitad');
            $tokenResult = $service->exchangeCodeForToken($code, $clientId, $clientSecret);
            
            if (!$tokenResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $tokenResult['message'],
                ], 400);
            }
            
            // Update connection with access token
            $credentials['access_token'] = encrypt($tokenResult['access_token']);
            if (isset($tokenResult['refresh_token'])) {
                $credentials['refresh_token'] = encrypt($tokenResult['refresh_token']);
            }
            $credentials['token_expires_at'] = now()->addSeconds($tokenResult['expires_in'] ?? 3600);
            
            $connection->update([
                'credentials' => $credentials,
                'is_connected' => true,
                'connected_at' => now(),
                'status' => 'active',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully connected to Admitad!',
                'data' => [
                    'connection_id' => $connection->id,
                    'access_token' => $tokenResult['access_token'],
                    'state' => $state,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Admitad callback error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Callback error: ' . $e->getMessage()
            ], 500);
        }
    }
}