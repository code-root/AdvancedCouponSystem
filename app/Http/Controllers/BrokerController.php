<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerConnection;
use App\Models\BrokerData;
use Illuminate\Http\Request;

class BrokerController extends Controller
{
    /**
     * Display a listing of user broker connections
     */
    public function index()
    {
        // Get user's broker connections
        $userConnections = auth()->user()->brokerConnections()
            ->with('broker')
            ->latest()
            ->paginate(15);
        
        // Get available brokers (not yet connected)
        $connectedBrokerIds = auth()->user()->brokerConnections()->pluck('broker_id');
        $availableBrokers = Broker::where('is_active', true)
            ->whereNotIn('id', $connectedBrokerIds)
            ->get();
        
        return view('dashboard.brokers.index', compact('userConnections', 'availableBrokers'));
    }

    /**
     * Show the form for creating a new broker connection
     */
    public function create()
    {
        $brokers = Broker::where('is_active', true)->get();
        return view('dashboard.brokers.create', compact('brokers'));
    }

    /**
     * Store a newly created broker connection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'broker_id' => ['required', 'exists:brokers,id'],
            'connection_name' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'token' => ['nullable', 'string'],
            'contact_id' => ['nullable', 'string'],
            'api_endpoint' => ['nullable', 'url'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        // Create broker connection
        $connection = BrokerConnection::create([
            'user_id' => auth()->id(),
            'broker_id' => $validated['broker_id'],
            'connection_name' => $validated['connection_name'],
            'client_id' => $validated['client_id'],
            'client_secret' => $validated['client_secret'],
            'token' => $validated['token'] ?? null,
            'contact_id' => $validated['contact_id'] ?? null,
            'api_endpoint' => $validated['api_endpoint'] ?? null,
            'status' => 'connected',
            'is_connected' => true,
            'connected_at' => now(),
            'credentials' => [
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
                'notes' => $validated['notes'] ?? null,
            ],
        ]);

        return redirect()->route('brokers.index')->with('success', 'Broker connected successfully!');
    }

    /**
     * Display the specified broker
     */
    public function show(Broker $broker)
    {
        $broker->load(['country', 'connections', 'campaigns', 'data']);
        
        $stats = [
            'total_campaigns' => $broker->campaigns()->count(),
            'active_campaigns' => $broker->campaigns()->where('is_active', true)->count(),
            'total_connections' => $broker->connections()->count(),
            'active_connections' => $broker->connections()->where('is_active', true)->count(),
        ];

        return view('dashboard.brokers.show', compact('broker', 'stats'));
    }

    /**
     * Show the form for editing the broker
     */
    public function edit(Broker $broker)
    {
        return view('dashboard.brokers.edit', compact('broker'));
    }

    /**
     * Update the specified broker
     */
    public function update(Request $request, Broker $broker)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:brokers,code,' . $broker->id],
            'country_id' => ['nullable', 'exists:countries,id'],
            'api_url' => ['nullable', 'url'],
            'api_key' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $broker->update($validated);

        return redirect()->route('brokers.index')->with('success', 'Broker updated successfully');
    }

    /**
     * Remove the specified broker
     */
    public function destroy(Broker $broker)
    {
        $broker->delete();
        return redirect()->route('brokers.index')->with('success', 'Broker deleted successfully');
    }

    /**
     * Create a new broker connection
     */
    public function createConnection(Request $request, Broker $broker)
    {
        $validated = $request->validate([
            'connection_type' => ['required', 'string'],
            'host' => ['nullable', 'string'],
            'port' => ['nullable', 'integer'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'database' => ['nullable', 'string'],
            'api_endpoint' => ['nullable', 'url'],
            'api_token' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $connection = $broker->connections()->create($validated);

        return redirect()->route('brokers.show', $broker)->with('success', 'Connection created successfully');
    }

    /**
     * Get broker data
     */
    public function getData(Broker $broker)
    {
        $data = $broker->data()
            ->latest()
            ->paginate(20);

        return response()->json($data);
    }
}

