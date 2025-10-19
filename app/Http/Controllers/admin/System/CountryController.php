<?php

namespace App\Http\Controllers\admin\System;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Display a listing of countries.
     */
    public function index()
    {
        try {
            $countries = Country::orderBy('name')->paginate(50);
            $stats = $this->getCountryStatistics();
            return view('admin.countries.index', compact('countries', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load countries: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new country.
     */
    public function create()
    {
        try {
            return view('admin.countries.create');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load create form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created country.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:countries',
                'code' => 'required|string|max:3|unique:countries',
                'currency' => 'nullable|string|max:3',
                'is_active' => 'boolean'
            ]);

            Country::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'currency' => $request->currency ? strtoupper($request->currency) : null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Clear cache after creating new country
            cache()->forget('country_statistics');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Country added successfully'
                ]);
            }

            return back()->with('success', 'Country added successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add country: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to add country: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified country.
     */
    public function show($id)
    {
        $country = Country::findOrFail($id);
        return view('admin.countries.show', compact('country'));
    }

    /**
     * Show the form for editing the specified country.
     */
    public function edit($id)
    {
        $country = Country::findOrFail($id);
        return view('admin.countries.edit', compact('country'));
    }

    /**
     * Update the specified country.
     */
    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name,' . $id,
            'code' => 'required|string|max:3|unique:countries,code,' . $id,
            'currency' => 'nullable|string|max:3',
            'is_active' => 'boolean'
        ]);

        $country->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'currency' => $request->currency ? strtoupper($request->currency) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Country updated successfully'
            ]);
        }

        return back()->with('success', 'Country updated successfully.');
    }

    /**
     * Remove the specified country.
     */
    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        
        // Check if country has associated data
        $hasData = $country->users()->count() > 0 || 
                   $country->campaigns()->count() > 0 ||
                   $country->purchases()->count() > 0;

        if ($hasData) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete country with associated data'
            ], 403);
        }
        
        $country->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Country deleted successfully'
        ]);
    }

    /**
     * AJAX store country.
     */
    public function storeAjax(Request $request)
    {
        return $this->store($request);
    }

    /**
     * AJAX update country.
     */
    public function updateAjax(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    /**
     * AJAX destroy country.
     */
    public function destroyAjax($id)
    {
        return $this->destroy($id);
    }

    /**
     * Toggle country status.
     */
    public function toggleStatus(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $country->update(['is_active' => !$country->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Country status updated successfully',
            'is_active' => $country->is_active
        ]);
    }

    /**
     * Bulk import countries.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getPathname()));
        $header = array_shift($data);

        $imported = 0;
        $errors = [];

        foreach ($data as $row) {
            try {
                $rowData = array_combine($header, $row);
                
                Country::updateOrCreate(
                    ['code' => strtoupper($rowData['code'])],
                    [
                        'name' => $rowData['name'],
                        'currency' => isset($rowData['currency']) ? strtoupper($rowData['currency']) : null,
                        'is_active' => isset($rowData['is_active']) ? (bool)$rowData['is_active'] : true,
                    ]
                );
                $imported++;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $row,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} countries successfully",
            'imported' => $imported,
            'errors' => $errors
        ]);
    }

    /**
     * Export countries.
     */
    public function export()
    {
        $countries = Country::orderBy('name')->get();
        
        $data = $countries->map(function ($country) {
            return [
                'name' => $country->name,
                'code' => $country->code,
                'currency' => $country->currency,
                'is_active' => $country->is_active ? 'Yes' : 'No',
                'created_at' => $country->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => 'countries_' . now()->format('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    /**
     * Get country statistics.
     */
    private function getCountryStatistics()
    {
        try {
            return cache()->remember('country_statistics', 300, function () {
                $today = today();
                $thisWeek = now()->startOfWeek();
                
                return [
                    'total_countries' => Country::count(),
                    'active_countries' => Country::where('is_active', true)->count(),
                    'inactive_countries' => Country::where('is_active', false)->count(),
                    'recent_countries' => Country::where('created_at', '>=', $thisWeek)->count(),
                    'countries_this_month' => Country::whereMonth('created_at', now()->month)->count(),
                    'countries_this_year' => Country::whereYear('created_at', now()->year)->count(),
                ];
            });
        } catch (\Exception $e) {
            return [
                'total_countries' => 0,
                'active_countries' => 0,
                'inactive_countries' => 0,
                'recent_countries' => 0,
                'countries_this_month' => 0,
                'countries_this_year' => 0,
            ];
        }
    }
}
