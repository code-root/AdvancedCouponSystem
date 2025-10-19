<?php

namespace App\Http\Controllers\admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralSettingsController extends Controller
{
    /**
     * Display general settings.
     */
    public function index()
    {
        try {
            $settings = SiteSetting::whereIn('key', [
                'site_name', 'site_url', 'timezone', 'locale', 'maintenance_mode',
                'maintenance_message', 'registration_enabled'
            ])->pluck('value', 'key');

            $title = 'General Settings';
            $subtitle = 'Site Configuration';

            return view('admin.settings.general', compact('settings', 'title', 'subtitle'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load general settings: ' . $e->getMessage());
        }
    }

    /**
     * Update general settings.
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'site_name' => 'required|string|max:255',
                'site_url' => 'nullable|url|max:255',
                'timezone' => 'nullable|string|max:255',
                'locale' => 'nullable|string|max:10',
                'maintenance_mode' => 'nullable|boolean',
                'maintenance_message' => 'nullable|string|max:500',
                'registration_enabled' => 'nullable|boolean',
            ]);

            $adminId = Auth::guard('admin')->id();

            foreach ($validated as $key => $value) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'group' => 'general',
                        'is_active' => true,
                        'last_modified_at' => now(),
                        'updated_by' => $adminId,
                    ]
                );
            }

            // Clear cache after updating settings
            cache()->forget('site_settings');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'General settings updated successfully'
                ]);
            }

            return back()->with('success', 'General settings updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update general settings: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update general settings: ' . $e->getMessage());
        }
    }

    /**
     * AJAX update general settings.
     */
    public function updateAjax(Request $request)
    {
        try {
            return $this->update($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update general settings: ' . $e->getMessage()
            ], 500);
        }
    }
}
