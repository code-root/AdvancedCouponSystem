<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerConnection;
use App\Models\BrokerData;
use Illuminate\Http\Request;

class BrokerController extends Controller
{
    /**
     * Display a listing of brokers
     */
    public function index()
    {
        $brokers = Broker::with('country')->paginate(15);
        return view('dashboard.brokers.index', compact('brokers'));
    }

    /**
     * Show the form for creating a new broker
     */
    public function create()
    {
        return view('dashboard.brokers.create');
    }

    /**
     * Store a newly created broker
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:brokers'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'api_url' => ['nullable', 'url'],
            'api_key' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $broker = Broker::create($validated);

        return redirect()->route('brokers.index')->with('success', 'Broker created successfully');
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

