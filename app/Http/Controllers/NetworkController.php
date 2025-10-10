<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\NetworkConnection;
use App\Models\NetworkData;
use Illuminate\Http\Request;

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
        $connectedNetworkIds = auth()->user()->networkConnections()->pluck('network_id');
        $networks = Network::where('is_active', true)
            ->whereNotIn('id', $connectedNetworkIds)
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
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'api_endpoint' => 'nullable|url|max:255',
            'contact_id' => 'nullable|string|max:255',
        ]);

        // Create network connection
        $connection = NetworkConnection::create([
            'user_id' => auth()->id(),
            'network_id' => $validated['network_id'],
            'connection_name' => $validated['connection_name'],
            'client_id' => $validated['client_id'],
            'client_secret' => $validated['client_secret'],
            'api_endpoint' => $validated['api_endpoint'],
            'contact_id' => $validated['contact_id'],
            'status' => 'pending',
            'is_connected' => false,
        ]);

        return redirect()->route('networks.index')
            ->with('success', 'Network connection created successfully!');
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
    public function update(Request $request, Network $network)
    {
        $userConnection = auth()->user()->networkConnections()
            ->where('network_id', $network->id)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

        $validated = $request->validate([
            'connection_name' => 'required|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'api_endpoint' => 'nullable|url|max:255',
            'contact_id' => 'nullable|string|max:255',
        ]);

        $userConnection->update($validated);

        return redirect()->route('networks.index')
            ->with('success', 'Network connection updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Network $network)
    {
        $userConnection = auth()->user()->networkConnections()
            ->where('network_id', $network->id)
            ->first();

        if (!$userConnection) {
            return redirect()->route('networks.index')
                ->with('error', 'Network connection not found.');
        }

        $userConnection->delete();

        return redirect()->route('networks.index')
            ->with('success', 'Network connection deleted successfully!');
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
            'api_endpoint' => 'nullable|url|max:255',
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
}