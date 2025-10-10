<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of countries
     */
    public function index()
    {
        $countries = Country::withCount('networks')->paginate(15);
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
            'currency' => ['nullable', 'string', 'max:3'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
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
        $country->load('networks');
        
        $stats = [
            'total_networks' => $country->networks()->count(),
            'active_networks' => $country->networks()->where('is_active', true)->count(),
        ];

        return view('dashboard.countries.show', compact('country', 'stats'));
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
            'currency' => ['nullable', 'string', 'max:3'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
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
        $country->delete();
        return redirect()->route('countries.index')->with('success', 'Country deleted successfully');
    }

    /**
     * Get country networks
     */
    public function networks(Country $country)
    {
        $networks = $country->networks()->with('campaigns')->paginate(20);
        return response()->json($networks);
    }
}

