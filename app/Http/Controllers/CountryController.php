<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    /**
     * Display a listing of countries
     */
    public function index()
    {
        $countries = Country::withCount('purchases')
            ->paginate(15);
        
        return view('dashboard.countries.index', compact('countries'));
    }

    /**
     * Show the form for creating a new country
     */
    public function create()
    {
        return view('dashboard.countries.create');
    }

    /**
     * Store a newly created country
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:3', 'unique:countries'],
            'code_3' => ['nullable', 'string', 'max:3'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $country = Country::create($validated);

        return redirect()->route('countries.index')->with('success', 'Country created successfully');
    }

    /**
     * Display the specified country
     */
    public function show(Country $country)
    {
        $stats = [
            'total_orders' => $country->purchases()->count(),
            'total_revenue' => $country->purchases()->sum('revenue'),
            'total_commission' => $country->purchases()->sum('order_value'),
            'approved_orders' => $country->purchases()->where('status', 'approved')->count(),
        ];

        $recent_orders = $country->purchases()
            ->with(['campaign', 'network', 'coupon'])
            ->latest('order_date')
            ->limit(10)
            ->get();

        return view('dashboard.countries.show', compact('country', 'stats', 'recent_orders'));
    }

    /**
     * Show the form for editing the country
     */
    public function edit(Country $country)
    {
        return view('dashboard.countries.edit', compact('country'));
    }

    /**
     * Update the specified country
     */
    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:3', 'unique:countries,code,' . $country->id],
            'code_3' => ['nullable', 'string', 'max:3'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $country->update($validated);

        return redirect()->route('countries.index')->with('success', 'Country updated successfully');
    }

    /**
     * Remove the specified country
     */
    public function destroy(Country $country)
    {
        // Check if country has purchases
        if ($country->purchases()->count() > 0) {
            return back()->with('error', 'Cannot delete country with existing purchases');
        }
        
        $country->delete();
        return redirect()->route('countries.index')->with('success', 'Country deleted successfully');
    }

    /**
     * Get country statistics (for old routes compatibility)
     */
    public function brokers(Country $country)
    {
        // Return country statistics instead
        $stats = [
            'total_orders' => $country->purchases()->count(),
            'total_revenue' => $country->purchases()->sum('revenue'),
            'total_commission' => $country->purchases()->sum('order_value'),
        ];
        
        return response()->json($stats);
    }
}
