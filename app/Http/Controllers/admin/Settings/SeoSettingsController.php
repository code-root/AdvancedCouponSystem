<?php

namespace App\Http\Controllers\admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeoSettingsController extends Controller
{
    /**
     * Display SEO settings.
     */
    public function index()
    {
        try {
            $settings = SiteSetting::whereIn('key', [
                'meta_description', 'meta_keywords', 'meta_author', 'robots_meta',
                'og_title', 'og_description', 'facebook_url', 'twitter_url', 
                'linkedin_url', 'instagram_url', 'google_analytics_id', 
                'google_tag_manager_id', 'facebook_pixel_id'
            ])->pluck('value', 'key');

            $title = 'SEO Settings';
            $subtitle = 'Search Engine Optimization';

            return view('admin.settings.seo', compact('settings', 'title', 'subtitle'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load SEO settings: ' . $e->getMessage());
        }
    }

    /**
     * Update SEO settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_author' => 'nullable|string|max:255',
            'robots_meta' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'seo',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'SEO settings updated successfully'
            ]);
        }

        return back()->with('success', 'SEO settings updated successfully');
    }

    /**
     * AJAX update SEO settings.
     */
    public function updateAjax(Request $request)
    {
        return $this->update($request);
    }
}
