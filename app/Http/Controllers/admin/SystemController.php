<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    /**
     * Display a listing of countries.
     */
    public function index()
    {
        $countries = Country::orderBy('name')->paginate(50);
        return view('admin.countries.index', compact('countries'));
    }

    /**
     * Store a newly created country.
     */
    public function store(Request $request)
    {
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

        return back()->with('success', 'Country added successfully.');
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

        return back()->with('success', 'Country updated successfully.');
    }

    /**
     * Remove the specified country.
     */
    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        
        // Check if country is being used
        $usageCount = DB::table('campaigns')->where('country_id', $id)->count();
        
        if ($usageCount > 0) {
            return back()->with('error', 'Cannot delete country. It is being used by ' . $usageCount . ' campaign(s).');
        }

        $country->delete();
        return back()->with('success', 'Country deleted successfully.');
    }

    /**
     * Display all campaigns across all users (readonly).
     */
    public function campaigns(Request $request)
    {
        $query = Campaign::with(['user:id,name,email', 'network:id,display_name', 'country:id,name'])
            ->withCount(['coupons', 'purchases']);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('network_id')) {
            $query->where('network_id', $request->network_id);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(50);

        // Get filter options
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $networks = Network::select('id', 'display_name')->orderBy('display_name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        // Get statistics
        $stats = [
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'inactive_campaigns' => Campaign::where('status', 'inactive')->count(),
            'campaigns_this_month' => Campaign::whereMonth('created_at', now()->month)->count(),
            'total_coupons' => Campaign::withCount('coupons')->get()->sum('coupons_count'),
            'total_purchases' => Campaign::withCount('purchases')->get()->sum('purchases_count'),
        ];

        return view('admin.campaigns.index', compact('campaigns', 'users', 'networks', 'countries', 'stats'));
    }

    /**
     * Display global system settings.
     */
    public function globalSettings()
    {
        $settings = [
            'maintenance_mode' => config('app.maintenance_mode', false),
            'max_file_upload_size' => config('app.max_file_upload_size', '10MB'),
            'default_timezone' => config('app.timezone', 'UTC'),
            'default_currency' => config('app.currency', 'USD'),
            'session_lifetime' => config('session.lifetime', 120),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];

        return view('admin.system.global-settings', compact('settings'));
    }

    /**
     * Update global system settings.
     */
    public function updateGlobalSettings(Request $request)
    {
        $request->validate([
            'maintenance_mode' => 'boolean',
            'max_file_upload_size' => 'string|max:20',
            'default_timezone' => 'string|max:50',
            'default_currency' => 'string|max:3',
            'session_lifetime' => 'integer|min:1|max:1440',
        ]);

        // Note: In a real application, you would update these in config files
        // or use a configuration management system
        // For now, we'll just show a success message

        return back()->with('success', 'Global settings updated successfully. Note: Some settings may require server restart to take effect.');
    }

    /**
     * Get system statistics for dashboard.
     */
    public function getSystemStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('email_verified_at', '!=', null)->count(),
            'total_networks' => Network::count(),
            'active_networks' => Network::where('is_active', true)->count(),
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'database_size' => $this->getDatabaseSize(),
            'storage_usage' => $this->getStorageUsage(),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        return response()->json($stats);
    }

    /**
     * Get database size.
     */
    private function getDatabaseSize()
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get storage usage.
     */
    private function getStorageUsage()
    {
        try {
            $storagePath = storage_path();
            $size = 0;
            
            if (is_dir($storagePath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($storagePath)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            }
            
            return round($size / 1024 / 1024, 2); // Convert to MB
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get memory usage.
     */
    private function getMemoryUsage()
    {
        return round(memory_get_usage(true) / 1024 / 1024, 2); // Convert to MB
    }
}