<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Network;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    /**
     * Get target user ID (parent if sub-user, self otherwise)
     */
    private function getTargetUserId(): int
    {
        /** @var User $user */
        $user = Auth::user();
        return $user->isSubUser() ? $user->parent_user_id : $user->id;
    }
    /**
     * Display a listing of campaigns for authenticated user
     */
    public function index(Request $request)
    {
        // For AJAX requests, return JSON
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getCampaignsData($request);
        }
        
        // For web requests, return view
        $targetUserId = $this->getTargetUserId();
        $networks = User::find($targetUserId)->connectedNetworks;
        $stats = $this->getCampaignStats();
        
        return view('dashboard.campaigns.index', compact('networks', 'stats'));
    }
    
    /**
     * Get campaigns data with filters (AJAX)
     */
    private function getCampaignsData(Request $request)
    {
        $targetUserId = $this->getTargetUserId();
        $query = Campaign::where('user_id', $this->getTargetUserId())
            ->with(['network', 'coupons'])
            ->withCount('purchases')
            ->withSum('purchases as total_revenue', 'revenue');
        
        // Filter by network (support multiple)
        if ($request->has('network_ids')) {
            $networkIds = $request->input('network_ids');
            
            // Handle different formats
            if (is_string($networkIds)) {
                $networkIds = str_contains($networkIds, ',') 
                    ? explode(',', $networkIds) 
                    : [$networkIds];
            } elseif (!is_array($networkIds)) {
                $networkIds = [$networkIds];
            }
            
            // Clean up: remove empty values
            $networkIds = array_filter($networkIds, function($id) {
                return !empty($id) && $id !== 'null' && $id !== null && $id !== '';
            });
            
            // Reset array keys
            $networkIds = array_values($networkIds);
            
            if (!empty($networkIds)) {
                $query->whereIn('network_id', $networkIds);
            }
        } elseif ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by campaign type
        if ($request->campaign_type) {
            $query->where('campaign_type', $request->campaign_type);
        }
        
        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('network_campaign_id', 'like', '%' . $request->search . '%');
            });
        }
        
        // Clone for stats
        $statsQuery = clone $query;
        
        // Calculate filtered stats
        $filteredStats = [
            'total' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('status', 'active')->count(),
            'paused' => (clone $statsQuery)->where('status', 'paused')->count(),
            'inactive' => (clone $statsQuery)->where('status', 'inactive')->count(),
            'coupon_type' => (clone $statsQuery)->where('campaign_type', 'coupon')->count(),
            'link_type' => (clone $statsQuery)->where('campaign_type', 'link')->count(),
        ];
        
        $campaigns = $query->latest()->paginate($request->per_page ?? 15);
        
        return response()->json([
            'success' => true,
            'data' => $campaigns,
            'stats' => $filteredStats
        ]);
    }
    
    /**
     * Get campaign statistics
     */
    private function getCampaignStats()
    {
        $userId = $this->getTargetUserId();
        
        return [
            'total' => Campaign::where('user_id', $userId)->count(),
            'active' => Campaign::where('user_id', $userId)->where('status', 'active')->count(),
            'paused' => Campaign::where('user_id', $userId)->where('status', 'paused')->count(),
            'inactive' => Campaign::where('user_id', $userId)->where('status', 'inactive')->count(),
            'coupon_type' => Campaign::where('user_id', $userId)->where('campaign_type', 'coupon')->count(),
            'link_type' => Campaign::where('user_id', $userId)->where('campaign_type', 'link')->count(),
        ];
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
            'used_coupons' => $campaign->coupons()->where('used_count', '>', 0)->count(),
            'total_revenue' => $campaign->purchases()->sum('revenue'),
            'total_commission' => $campaign->purchases()->sum('order_value'),
            'total_order_value' => $campaign->purchases()->sum('order_value'),
            'total_purchases' => $campaign->purchases()->count(),
            'approved_purchases' => $campaign->purchases()->where('status', 'approved')->count(),
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
     * Get coupon statistics for a campaign
     */
    public function getCouponStats(Campaign $campaign)
    {
        $coupons = $campaign->coupons()
            ->withCount('purchases as total_orders')
            ->withSum('purchases as total_revenue', 'revenue')
            ->get();
        
        return response()->json([
            'success' => true,
            'coupons' => $coupons
        ]);
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

