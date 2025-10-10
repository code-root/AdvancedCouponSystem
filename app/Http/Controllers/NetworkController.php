<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\NetworkConnection;
use App\Models\NetworkData;
use App\Services\Networks\NetworkServiceFactory;
use App\Helpers\NetworkDataProcessor;
use Illuminate\Http\Request;
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
            ->get();
        
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
                if (in_array($key, ['client_secret', 'api_secret', 'password', 'token'])) {
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
    public function show(Network $network)
    {
        $userConnection = auth()->user()->networkConnections()
            ->where('network_id', $network->id)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

        // Get network data
        $networkData = $userConnection->network->data()->latest()->paginate(10);

        return view('dashboard.networks.show', compact('network', 'userConnection', 'networkData'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Network $network)
    {
        $userConnection = auth()->user()->networkConnections()
            ->where('network_id', $network->id)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

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
                        if (in_array($key, ['client_secret', 'api_secret', 'password', 'token'])) {
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
    public function destroy(Request $request, $networkId)
    {
        try {
            // Find connection by ID (not network_id)
            $userConnection = auth()->user()->networkConnections()
                ->where('id', $networkId)
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
            
            return response()->json([
                'success' => true,
                'data' => [
                    'required_fields' => $service->getRequiredFields(),
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
                if (in_array($key, ['client_secret', 'api_secret', 'password', 'token'])) {
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
            
            // Sync data based on network (coupons and/or links)
            $syncResult = $service->syncData($decryptedCredentials, $startDate, $endDate);
            
            if (!$syncResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: ' . ($syncResult['message'] ?? 'Unknown error')
                ], 400);
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
}