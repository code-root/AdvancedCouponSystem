<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Campaign;
use App\Models\Network;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports index
     */
    public function index(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getReportsData($request);
        }
        
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        // Get filter options
        $networks = $user->connectedNetworks;
        $campaigns = Campaign::where('user_id', $userId)->get();
        
        // Get overall stats
        $stats = $this->getOverallStats();
        
        return view('dashboard.reports.index', compact('networks', 'campaigns', 'stats'));
    }
    
    /**
     * Get reports data with filters
     */
    private function getReportsData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        // Build base queries
        $purchasesQuery = Purchase::where('user_id', $userId);
        $campaignsQuery = Campaign::where('user_id', $userId);
        $couponsQuery = Coupon::whereHas('campaign', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
        
        // Apply filters
        $this->applyReportFilters($purchasesQuery, $request);
        $this->applyReportFilters($campaignsQuery, $request);
        $this->applyReportFilters($couponsQuery, $request);
        
        // Get filtered statistics
        $stats = [
            // Purchases
            'total_purchases' => $purchasesQuery->count(),
            'approved_purchases' => (clone $purchasesQuery)->where('status', 'approved')->count(),
            'pending_purchases' => (clone $purchasesQuery)->where('status', 'pending')->count(),
            'rejected_purchases' => (clone $purchasesQuery)->where('status', 'rejected')->count(),
            
            // Revenue
            'total_revenue' => $purchasesQuery->sum('revenue'),
            'total_commission' => $purchasesQuery->sum('commission'),
            'total_order_value' => $purchasesQuery->sum('order_value'),
            
            // Campaigns & Coupons
            'total_campaigns' => $campaignsQuery->count(),
            'active_campaigns' => (clone $campaignsQuery)->where('status', 'active')->count(),
            'total_coupons' => $couponsQuery->count(),
            'active_coupons' => (clone $couponsQuery)->where('status', 'active')->count(),
            
            // By Network
            'by_network' => $this->getRevenueByNetwork($request, $userId),
            
            // By Campaign
            'top_campaigns' => $this->getTopCampaigns($request, $userId),
            
            // By Date
            'daily_revenue' => $this->getDailyRevenue($request, $userId),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Apply filters to query
     */
    private function applyReportFilters($query, Request $request)
    {
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
            
            // Clean up
            $networkIds = array_filter($networkIds, function($id) {
                return !empty($id) && $id !== 'null' && $id !== null && $id !== '';
            });
            
            $networkIds = array_values($networkIds);
            
            if (!empty($networkIds)) {
                if (method_exists($query->getModel(), 'network')) {
                    $query->whereIn('network_id', $networkIds);
                } else {
                    $query->whereHas('campaign', function($q) use ($networkIds) {
                        $q->whereIn('network_id', $networkIds);
                    });
                }
            }
        } elseif ($request->network_id) {
            if (method_exists($query->getModel(), 'network')) {
                $query->where('network_id', $request->network_id);
            } else {
                $query->whereHas('campaign', function($q) use ($request) {
                    $q->where('network_id', $request->network_id);
                });
            }
        }
        
        // Filter by campaign (support multiple)
        $campaignIds = $request->input('campaign_ids', []);
        
        // Ensure it's an array
        if (!is_array($campaignIds)) {
            $campaignIds = $campaignIds ? [$campaignIds] : [];
        }
        
        // Clean up
        $campaignIds = array_filter($campaignIds, function($id) {
            return !empty($id) && $id !== 'null' && $id !== null && $id !== '';
        });
        
        $campaignIds = array_values($campaignIds);
        
        if (!empty($campaignIds)) {
            if ($query->getModel()->getTable() === 'purchases') {
                $query->whereIn('campaign_id', $campaignIds);
            } elseif ($query->getModel()->getTable() === 'coupons') {
                $query->whereIn('campaign_id', $campaignIds);
            }
        } elseif ($request->has('campaign_id')) {
            if ($query->getModel()->getTable() === 'purchases') {
                $query->where('campaign_id', $request->campaign_id);
            } elseif ($query->getModel()->getTable() === 'coupons') {
                $query->where('campaign_id', $request->campaign_id);
            }
        }
        
        // Filter by date range
        if ($request->date_from) {
            $dateColumn = $query->getModel()->getTable() === 'purchases' ? 'order_date' : 'created_at';
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $dateColumn = $query->getModel()->getTable() === 'purchases' ? 'order_date' : 'created_at';
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }
        
        return $query;
    }
    
    /**
     * Get revenue by network
     */
    private function getRevenueByNetwork(Request $request, int $userId)
    {
        $query = Purchase::where('user_id', $userId)
            ->select('network_id', 
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('SUM(commission) as total_commission'),
                DB::raw('COUNT(*) as total_purchases'))
            ->with('network:id,display_name')
            ->groupBy('network_id');
        
        // Apply date filters
        if ($request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        
        return $query->orderByDesc('total_revenue')->limit(10)->get();
    }
    
    /**
     * Get top campaigns
     */
    private function getTopCampaigns(Request $request, int $userId)
    {
        $query = Purchase::where('user_id', $userId)
            ->select('campaign_id',
                DB::raw('SUM(revenue) as total_revenue'),
                DB::raw('COUNT(*) as total_purchases'))
            ->with('campaign:id,name,network_id')
            ->groupBy('campaign_id');
        
        // Apply filters
        if ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        if ($request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        
        return $query->orderByDesc('total_revenue')->limit(10)->get();
    }
    
    /**
     * Get daily revenue
     */
    private function getDailyRevenue(Request $request, int $userId)
    {
        $startDate = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->date_to ?? Carbon::now()->format('Y-m-d');
        
        $query = Purchase::where('user_id', $userId)
            ->select(DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(revenue) as revenue'),
                DB::raw('COUNT(*) as purchases'))
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc');
        
        if ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        
        return $query->get();
    }
    
    /**
     * Get overall statistics
     */
    private function getOverallStats()
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        $currentMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_revenue' => Purchase::where('user_id', $userId)->sum('revenue'),
            'total_purchases' => Purchase::where('user_id', $userId)->count(),
            'total_campaigns' => Campaign::where('user_id', $userId)->count(),
            'total_coupons' => Coupon::whereHas('campaign', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            
            // This month
            'month_revenue' => Purchase::where('user_id', $userId)
                ->where('order_date', '>=', $currentMonth)
                ->sum('revenue'),
            'month_purchases' => Purchase::where('user_id', $userId)
                ->where('order_date', '>=', $currentMonth)
                ->count(),
        ];
    }
    
    /**
     * Coupons Report
     */
    public function coupons(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getCouponsReportData($request);
        }
        
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $networks = $user->connectedNetworks;
        $campaigns = Campaign::where('user_id', $userId)->get();
        
        return view('dashboard.reports.coupons', compact('networks', 'campaigns'));
    }
    
    /**
     * Purchases Report
     */
    public function purchases(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getPurchasesReportData($request);
        }
        
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $networks = $user->connectedNetworks;
        $campaigns = Campaign::where('user_id', $userId)->get();
        
        return view('dashboard.reports.purchases', compact('networks', 'campaigns'));
    }
    
    /**
     * Campaigns Report
     */
    public function campaigns(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getCampaignsReportData($request);
        }
        
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $networks = $user->connectedNetworks;
        
        return view('dashboard.reports.campaigns', compact('networks'));
    }
    
    /**
     * Revenue Report
     */
    public function revenue(Request $request)
    {
        // For AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return $this->getRevenueReportData($request);
        }
        
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $networks = $user->connectedNetworks;
        $campaigns = Campaign::where('user_id', $userId)->get();
        
        return view('dashboard.reports.revenue', compact('networks', 'campaigns'));
    }
    
    /**
     * Get Coupons Report Data
     */
    private function getCouponsReportData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $query = Coupon::whereHas('campaign', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['campaign.network']);
        
        // Apply filters
        if ($request->network_id) {
            $query->whereHas('campaign', function($q) use ($request) {
                $q->where('network_id', $request->network_id);
            });
        }
        
        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $statsQuery = clone $query;
        
        $stats = [
            'total_coupons' => $statsQuery->count(),
            'active_coupons' => (clone $statsQuery)->where('status', 'active')->count(),
            'used_coupons' => (clone $statsQuery)->where('used_count', '>', 0)->count(),
            'expired_coupons' => (clone $statsQuery)->where('expires_at', '<', now())->count(),
            
            // By network
            'by_network' => Coupon::whereHas('campaign', function($q) use ($userId, $request) {
                    $q->where('user_id', $userId);
                    if ($request->network_id) {
                        $q->where('network_id', $request->network_id);
                    }
                })
                ->select('campaign_id', DB::raw('COUNT(*) as total'))
                ->with('campaign.network')
                ->groupBy('campaign_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get Purchases Report Data
     */
    private function getPurchasesReportData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $query = Purchase::where('user_id', $userId);
        
        // Apply filters
        if ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        
        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        
        $statsQuery = clone $query;
        
        $stats = [
            'total_purchases' => $statsQuery->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
            'total_revenue' => (clone $statsQuery)->sum('revenue'),
            'total_commission' => (clone $statsQuery)->sum('commission'),
            
            // Daily trend
            'daily_trend' => $this->getDailyRevenue($request, $userId),
            
            // By status
            'by_status' => Purchase::where('user_id', $userId)
                ->when($request->network_id, fn($q) => $q->where('network_id', $request->network_id))
                ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(revenue) as revenue'))
                ->groupBy('status')
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get Campaigns Report Data
     */
    private function getCampaignsReportData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $query = Campaign::where('user_id', $userId);
        
        // Apply filters
        if ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $statsQuery = clone $query;
        
        $stats = [
            'total_campaigns' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('status', 'active')->count(),
            'paused' => (clone $statsQuery)->where('status', 'paused')->count(),
            'inactive' => (clone $statsQuery)->where('status', 'inactive')->count(),
            
            // Performance
            'top_performers' => $this->getTopCampaigns($request, $userId),
            
            // By type
            'by_type' => Campaign::where('user_id', $userId)
                ->when($request->network_id, fn($q) => $q->where('network_id', $request->network_id))
                ->select('campaign_type', DB::raw('COUNT(*) as total'))
                ->groupBy('campaign_type')
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Get Revenue Report Data
     */
    private function getRevenueReportData(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userId = $user->id;
        
        $query = Purchase::where('user_id', $userId);
        
        // Apply filters
        if ($request->network_id) {
            $query->where('network_id', $request->network_id);
        }
        
        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }
        
        $statsQuery = clone $query;
        
        $stats = [
            'total_revenue' => $statsQuery->sum('revenue'),
            'total_commission' => $statsQuery->sum('commission'),
            'total_order_value' => $statsQuery->sum('order_value'),
            'avg_order_value' => $statsQuery->avg('order_value'),
            
            // Daily revenue
            'daily_revenue' => $this->getDailyRevenue($request, $userId),
            
            // By network
            'by_network' => $this->getRevenueByNetwork($request, $userId),
            
            // Monthly comparison
            'monthly_comparison' => Purchase::where('user_id', $userId)
                ->select(
                    DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
                    DB::raw('SUM(revenue) as revenue'),
                    DB::raw('COUNT(*) as orders')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Export report
     */
    public function export(Request $request, $type)
    {
        // Implementation for export functionality
        return response()->json([
            'success' => true,
            'message' => 'Export functionality coming soon'
        ]);
    }
}
