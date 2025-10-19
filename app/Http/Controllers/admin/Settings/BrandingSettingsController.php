<?php

namespace App\Http\Controllers\admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandingSettingsController extends Controller
{
    /**
     * Display branding settings.
     */
    public function index()
    {
        try {
            $settings = SiteSetting::whereIn('key', [
                'site_logo', 'site_favicon', 'primary_color', 'secondary_color',
                'accent_color', 'font_family', 'custom_css', 'custom_js'
            ])->pluck('value', 'key');

            $title = 'Branding Settings';
            $subtitle = 'Site Appearance';

            return view('admin.settings.branding', compact('settings', 'title', 'subtitle'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load branding settings: ' . $e->getMessage());
        }
    }

    /**
     * Update branding settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_logo' => 'nullable|string|max:500',
            'site_favicon' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:255',
            'custom_css' => 'nullable|string|max:10000',
            'custom_js' => 'nullable|string|max:10000',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'branding',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Branding settings updated successfully'
            ]);
        }

        return back()->with('success', 'Branding settings updated successfully');
    }

    /**
     * AJAX update branding settings.
     */
    public function updateAjax(Request $request)
    {
        return $this->update($request);
    }

    /**
     * Upload logo.
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $adminId = Auth::guard('admin')->id();
        
        // Delete old logo if exists
        $oldLogo = SiteSetting::where('key', 'site_logo')->first();
        if ($oldLogo && $oldLogo->value) {
            \Storage::disk('public')->delete($oldLogo->value);
        }

        // Store new logo
        $path = $request->file('logo')->store('branding', 'public');
        
        SiteSetting::updateOrCreate(
            ['key' => 'site_logo'],
            [
                'value' => $path,
                'group' => 'branding',
                'is_active' => true,
                'last_modified_at' => now(),
                'updated_by' => $adminId,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logo uploaded successfully',
                'logo_url' => asset('storage/' . $path)
            ]);
        }

        return back()->with('success', 'Logo uploaded successfully');
    }
}
