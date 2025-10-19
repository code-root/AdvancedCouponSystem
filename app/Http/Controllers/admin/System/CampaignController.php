<?php

namespace App\Http\Controllers\admin\System;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Network;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of campaigns.
     */
    public function index(Request $request)
    {
        try {
            $query = Campaign::with('user');

            // Apply filters
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
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

            // Get statistics
            $stats = $this->getCampaignStatistics();

            $users = cache()->remember('users_for_campaigns_filter', 300, function () {
                return User::select('id', 'name', 'email')->orderBy('name')->get();
            });

            return view('admin.campaigns.index', compact('campaigns', 'stats', 'users'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load campaigns: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified campaign.
     */
    public function show($id)
    {
        try {
            $campaign = Campaign::with(['user', 'purchases'])->findOrFail($id);
            
            // Get campaign statistics
            $stats = [
                'total_purchases' => $campaign->purchases()->count(),
                'total_revenue' => $campaign->purchases()->sum('revenue'),
                'avg_order_value' => $campaign->purchases()->avg('sales_amount'),
                'conversion_rate' => $this->calculateConversionRate($campaign),
            ];

            return view('admin.campaigns.show', compact('campaign', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load campaign: ' . $e->getMessage());
        }
    }

    /**
     * Update campaign status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:active,paused,completed,cancelled'
            ]);

            $campaign->update(['status' => $request->status]);

            // Clear cache after updating campaign status
            cache()->forget('campaign_statistics');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Campaign status updated successfully',
                    'status' => $campaign->status
                ]);
            }

            return back()->with('success', 'Campaign status updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update campaign status: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update campaign status: ' . $e->getMessage());
        }
    }

    /**
     * Get campaign statistics.
     */
    public function getStatsAjax($id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            
            $stats = [
                'total_purchases' => $campaign->purchases()->count(),
                'total_revenue' => $campaign->purchases()->sum('revenue'),
                'avg_order_value' => round($campaign->purchases()->avg('sales_amount') ?? 0, 2),
                'conversion_rate' => $this->calculateConversionRate($campaign),
                'purchases_this_month' => $campaign->purchases()
                    ->whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->count(),
                'revenue_this_month' => $campaign->purchases()
                    ->whereMonth('order_date', now()->month)
                    ->whereYear('order_date', now()->year)
                    ->sum('revenue'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get campaign statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign chart data.
     */
    public function getChartDataAjax(Request $request, $id)
    {
        try {
            $campaign = Campaign::findOrFail($id);
            $days = $request->get('days', 30);
            $startDate = now()->subDays($days);

            $data = $campaign->purchases()
                ->where('order_date', '>=', $startDate)
                ->selectRaw('DATE(order_date) as date, COUNT(*) as count, SUM(revenue) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $chartData = [];
            $dates = [];
            
            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->copy()->addDays($i)->format('Y-m-d');
                $dates[] = $date;
                $chartData[$date] = [
                    'count' => 0,
                    'revenue' => 0,
                ];
            }

            foreach ($data as $item) {
                if (isset($chartData[$item->date])) {
                    $chartData[$item->date]['count'] = $item->count;
                    $chartData[$item->date]['revenue'] = $item->revenue;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $dates,
                    'datasets' => [
                        [
                            'label' => 'Purchases',
                            'data' => array_values(array_column($chartData, 'count')),
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'borderColor' => 'rgba(59, 130, 246, 1)',
                            'yAxisID' => 'y',
                        ],
                        [
                            'label' => 'Revenue',
                            'data' => array_values(array_column($chartData, 'revenue')),
                            'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                            'borderColor' => 'rgba(34, 197, 94, 1)',
                            'yAxisID' => 'y1',
                        ],
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get chart data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export campaigns.
     */
    public function export(Request $request)
    {
        try {
            $query = Campaign::with('user');

            // Apply same filters as index
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
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

            $campaigns = $query->orderBy('created_at', 'desc')->get();

            $data = $campaigns->map(function ($campaign) {
                return [
                    'Name' => $campaign->name,
                    'User' => $campaign->user->name ?? 'N/A',
                    'Status' => ucfirst($campaign->status),
                    'Total Purchases' => $campaign->purchases()->count(),
                    'Total Revenue' => $campaign->purchases()->sum('revenue'),
                    'Created At' => $campaign->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'filename' => 'campaigns_' . now()->format('Y-m-d_H-i-s') . '.csv'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export campaigns: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate conversion rate for campaign.
     */
    private function calculateConversionRate($campaign)
    {
        // This would need to be implemented based on your business logic
        // For example, if you track clicks/visits vs purchases
        $totalClicks = 1000; // This should come from your tracking system
        $totalPurchases = $campaign->purchases()->count();
        
        if ($totalClicks === 0) return 0;
        
        return round(($totalPurchases / $totalClicks) * 100, 2);
    }

    /**
     * Get campaign statistics.
     */
    private function getCampaignStatistics()
    {
        try {
            return cache()->remember('campaign_statistics', 300, function () {
                $today = today();
                $thisMonth = now()->month;
                $thisYear = now()->year;
                
                return [
                    'total_campaigns' => Campaign::count(),
                    'active_campaigns' => Campaign::where('status', 'active')->count(),
                    'paused_campaigns' => Campaign::where('status', 'paused')->count(),
                    'completed_campaigns' => Campaign::where('status', 'completed')->count(),
                    'cancelled_campaigns' => Campaign::where('status', 'cancelled')->count(),
                    'campaigns_this_month' => Campaign::whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count(),
                    'campaigns_this_year' => Campaign::whereYear('created_at', $thisYear)->count(),
                    'total_networks' => Network::count(),
                    'active_networks' => Network::where('is_active', true)->count(),
                    'total_revenue' => Campaign::with('purchases')->get()->sum(function ($campaign) {
                        return $campaign->purchases->sum('revenue');
                    }),
                    'revenue_this_month' => Campaign::with('purchases')->get()->sum(function ($campaign) {
                        return $campaign->purchases->where('order_date', '>=', now()->startOfMonth())->sum('revenue');
                    }),
                    'revenue_this_year' => Campaign::with('purchases')->get()->sum(function ($campaign) {
                        return $campaign->purchases->where('order_date', '>=', now()->startOfYear())->sum('revenue');
                    }),
                ];
            });
        } catch (\Exception $e) {
            return [
                'total_campaigns' => 0,
                'active_campaigns' => 0,
                'paused_campaigns' => 0,
                'completed_campaigns' => 0,
                'cancelled_campaigns' => 0,
                'campaigns_this_month' => 0,
                'campaigns_this_year' => 0,
                'total_networks' => 0,
                'active_networks' => 0,
                'total_revenue' => 0,
                'revenue_this_month' => 0,
                'revenue_this_year' => 0,
            ];
        }
    }
}
