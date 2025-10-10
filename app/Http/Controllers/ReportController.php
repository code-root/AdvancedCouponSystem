<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Campaign;
use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports index
     */
    public function index()
    {
        return view('dashboard.reports.index');
    }

    /**
     * Generate coupons report
     */
    public function coupons(Request $request)
    {
        $query = Coupon::with('campaign');

        // Apply filters
        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $coupons = $query->paginate(20);

        $stats = [
            'total_coupons' => $query->count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'used_coupons' => Coupon::where('times_used', '>', 0)->count(),
            'total_uses' => Coupon::sum('times_used'),
        ];

        return view('dashboard.reports.coupons', compact('coupons', 'stats'));
    }

    /**
     * Generate purchases report
     */
    public function purchases(Request $request)
    {
        $query = Purchase::with(['user', 'coupon', 'campaign']);

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $purchases = $query->latest()->paginate(20);

        $stats = [
            'total_purchases' => $query->count(),
            'completed_purchases' => Purchase::where('status', 'completed')->count(),
            'total_revenue' => $query->where('status', 'completed')->sum('final_amount'),
            'total_discounts' => $query->where('status', 'completed')->sum('discount_amount'),
            'average_purchase' => $query->where('status', 'completed')->avg('final_amount'),
        ];

        return view('dashboard.reports.purchases', compact('purchases', 'stats'));
    }

    /**
     * Generate campaigns report
     */
    public function campaigns(Request $request)
    {
        $query = Campaign::with('broker')
            ->withCount(['coupons', 'purchases'])
            ->withSum(['purchases' => function($q) {
                $q->where('status', 'completed');
            }], 'final_amount');

        // Apply filters
        if ($request->broker_id) {
            $query->where('broker_id', $request->broker_id);
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->from_date) {
            $query->whereDate('start_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('end_date', '<=', $request->to_date);
        }

        $campaigns = $query->paginate(20);

        $stats = [
            'total_campaigns' => $query->count(),
            'active_campaigns' => Campaign::where('is_active', true)->count(),
            'total_coupons' => Coupon::count(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('final_amount'),
        ];

        return view('dashboard.reports.campaigns', compact('campaigns', 'stats'));
    }

    /**
     * Generate brokers report
     */
    public function brokers(Request $request)
    {
        $brokers = Broker::with('country')
            ->withCount(['campaigns', 'connections'])
            ->get();

        foreach ($brokers as $broker) {
            $broker->total_revenue = Purchase::whereHas('campaign', function($q) use ($broker) {
                $q->where('broker_id', $broker->id);
            })->where('status', 'completed')->sum('final_amount');

            $broker->total_purchases = Purchase::whereHas('campaign', function($q) use ($broker) {
                $q->where('broker_id', $broker->id);
            })->count();
        }

        $stats = [
            'total_brokers' => $brokers->count(),
            'active_brokers' => $brokers->where('is_active', true)->count(),
            'total_campaigns' => Campaign::count(),
            'total_revenue' => Purchase::where('status', 'completed')->sum('final_amount'),
        ];

        return view('dashboard.reports.brokers', compact('brokers', 'stats'));
    }

    /**
     * Generate revenue report
     */
    public function revenue(Request $request)
    {
        $period = $request->period ?? 'daily'; // daily, weekly, monthly, yearly

        $dateFormat = match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m-%d',
        };

        $revenue = Purchase::selectRaw("
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                COUNT(*) as total_purchases,
                SUM(amount) as gross_revenue,
                SUM(discount_amount) as total_discounts,
                SUM(final_amount) as net_revenue,
                AVG(final_amount) as average_purchase
            ")
            ->where('status', 'completed')
            ->when($request->from_date, function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->to_date);
            })
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->paginate(30);

        $stats = [
            'total_revenue' => Purchase::where('status', 'completed')->sum('final_amount'),
            'total_discounts' => Purchase::where('status', 'completed')->sum('discount_amount'),
            'total_purchases' => Purchase::where('status', 'completed')->count(),
            'average_purchase' => Purchase::where('status', 'completed')->avg('final_amount'),
        ];

        return view('dashboard.reports.revenue', compact('revenue', 'stats', 'period'));
    }

    /**
     * Export report
     */
    public function export(Request $request, $type)
    {
        $data = match($type) {
            'coupons' => $this->getCouponsExportData($request),
            'purchases' => $this->getPurchasesExportData($request),
            'campaigns' => $this->getCampaignsExportData($request),
            'revenue' => $this->getRevenueExportData($request),
            default => [],
        };

        // Implementation depends on export format (CSV, Excel, PDF)
        // For now, returning JSON
        return response()->json([
            'type' => $type,
            'data' => $data,
            'exported_at' => now(),
        ]);
    }

    /**
     * Download exported report
     */
    public function download($file)
    {
        // Implementation for downloading previously exported files
        $path = storage_path('app/exports/' . $file);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    /**
     * Get coupons export data
     */
    protected function getCouponsExportData($request)
    {
        $query = Coupon::with('campaign');

        if ($request->campaign_id) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query->get();
    }

    /**
     * Get purchases export data
     */
    protected function getPurchasesExportData($request)
    {
        $query = Purchase::with(['user', 'coupon', 'campaign']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query->get();
    }

    /**
     * Get campaigns export data
     */
    protected function getCampaignsExportData($request)
    {
        $query = Campaign::with('broker')
            ->withCount(['coupons', 'purchases']);

        if ($request->broker_id) {
            $query->where('broker_id', $request->broker_id);
        }

        return $query->get();
    }

    /**
     * Get revenue export data
     */
    protected function getRevenueExportData($request)
    {
        return Purchase::with(['user', 'coupon', 'campaign'])
            ->where('status', 'completed')
            ->when($request->from_date, function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->to_date);
            })
            ->get();
    }
}

