<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property\Property;
use App\Models\Developer;
use App\Helpers\SiteSettingsHelper;

/**
 * HomeController - Main controller for homepage
 * Optimized for performance with caching and efficient queries
 */
class HomeController extends Controller
{
    /**
     * Display the main homepage
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Cache expensive queries for better performance
        $featuredProperties = cache()->remember('featured_properties', 300, function () {
            return Property::with(['developer', 'propertyType', 'city'])
                ->where('is_featured', true)
                ->where('is_active', true)
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        });

        $latestProperties = cache()->remember('latest_properties', 300, function () {
            return Property::with(['developer', 'propertyType', 'city'])
                ->where('is_active', true)
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        });

        $topDevelopers = cache()->remember('top_developers', 600, function () {
            return Developer::where('is_active', true)
                ->withCount(['properties' => function ($query) {
                    $query->where('is_active', true)
                          ->where('is_published', true);
                }])
                ->orderBy('properties_count', 'desc')
                ->limit(6)
                ->get();
        });

        // Cache filter options for better performance
        $filterOptions = cache()->remember('property_filter_options', 600, function () {
            return [
                'types' => \App\Models\Property\PropertyType::where('is_active', true)->orderBy('name')->get(),
                'cities' => \App\Models\City::orderBy('name')->get(),
                'amenities' => \App\Models\Amenity::orderBy('name')->get(),
            ];
        });

        // Get dynamic site settings
        $siteSettings = [
            'general' => SiteSettingsHelper::getGeneralSettings(),
            'social' => SiteSettingsHelper::getSocialLinks(),
            'footer' => SiteSettingsHelper::getFooterSettings(),
            'seo' => SiteSettingsHelper::getSeoSettings(),
            'meta' => SiteSettingsHelper::getMetaSettings(),
            'logo' => SiteSettingsHelper::getLogoSettings(),
        ];

        return view('home.index', compact(
            'featuredProperties',
            'latestProperties', 
            'topDevelopers',
            'filterOptions',
            'siteSettings'
        ));
    }
}
