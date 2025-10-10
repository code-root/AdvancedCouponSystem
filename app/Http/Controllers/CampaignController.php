<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Network;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns
     */
    public function index()
    {
        $campaigns = Campaign::with('network')->paginate(15);
        return view('dashboard.campaigns.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new campaign
     */
    public function create()
    {
        $networks = Network::where('is_active', true)->get();
        return view('dashboard.campaigns.create', compact('networks'));
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:campaigns'],
            'description' => ['nullable', 'string'],
            'network_id' => ['required', 'exists:networks,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $campaign = Campaign::create($validated);

        return redirect()->route('campaigns.index')->with('success', 'Campaign created successfully');
    }

    /**
     * Display the specified campaign
     */
    public function show(Campaign $campaign)
    {
        $campaign->load(['network', 'coupons']);
        
        $stats = [
            'total_coupons' => $campaign->coupons()->count(),
            'used_coupons' => $campaign->coupons()->where('times_used', '>', 0)->count(),
            'total_revenue' => $campaign->purchases()->where('status', 'completed')->sum('amount'),
            'total_purchases' => $campaign->purchases()->count(),
        ];

        return view('dashboard.campaigns.show', compact('campaign', 'stats'));
    }

    /**
     * Show the form for editing the campaign
     */
    public function edit(Campaign $campaign)
    {
        $networks = Network::where('is_active', true)->get();
        return view('dashboard.campaigns.edit', compact('campaign', 'networks'));
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:campaigns,slug,' . $campaign->id],
            'description' => ['nullable', 'string'],
            'network_id' => ['required', 'exists:networks,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user' => ['nullable', 'integer', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $campaign->update($validated);

        return redirect()->route('campaigns.index')->with('success', 'Campaign updated successfully');
    }

    /**
     * Remove the specified campaign
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully');
    }

    /**
     * Activate campaign
     */
    public function activate(Campaign $campaign)
    {
        $campaign->update(['is_active' => true]);
        return back()->with('success', 'Campaign activated successfully');
    }

    /**
     * Deactivate campaign
     */
    public function deactivate(Campaign $campaign)
    {
        $campaign->update(['is_active' => false]);
        return back()->with('success', 'Campaign deactivated successfully');
    }

    /**
     * Get campaign statistics
     */
    public function statistics(Campaign $campaign)
    {
        $stats = [
            'total_coupons' => $campaign->coupons()->count(),
            'active_coupons' => $campaign->coupons()->where('is_active', true)->count(),
            'used_coupons' => $campaign->coupons()->where('times_used', '>', 0)->count(),
            'total_uses' => $campaign->coupons()->sum('times_used'),
            'total_purchases' => $campaign->purchases()->count(),
            'completed_purchases' => $campaign->purchases()->where('status', 'completed')->count(),
            'total_revenue' => $campaign->purchases()->where('status', 'completed')->sum('amount'),
            'average_purchase' => $campaign->purchases()->where('status', 'completed')->avg('amount'),
            'daily_stats' => $campaign->purchases()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as revenue')
                ->groupBy('date')
                ->latest('date')
                ->limit(30)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get campaign coupons
     */
    public function coupons(Campaign $campaign)
    {
        $coupons = $campaign->coupons()->with('purchases')->paginate(20);
        return view('dashboard.campaigns.coupons', compact('campaign', 'coupons'));
    }

    /**
     * Get active campaigns (public)
     */
    public function active()
    {
        $campaigns = Campaign::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with('network')
            ->get();

        return response()->json($campaigns);
    }
}

