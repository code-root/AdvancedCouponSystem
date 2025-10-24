<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Admin;
use App\Services\SubscriptionService;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\SubscriptionCancelledNotification;
use App\Notifications\SubscriptionUpgradedNotification;
use App\Notifications\ManualSubscriptionActivatedNotification;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionUpgraded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminSubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subs) {}

    public function index(Request $request)
    {
        try {
            $query = Subscription::with(['user:id,name,email', 'plan:id,name,price']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('plan_id')) {
                $query->where('plan_id', $request->plan_id);
            }
            if ($request->filled('gateway')) {
                $query->where('gateway', $request->gateway);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $subscriptions = $query->orderByDesc('created_at')->paginate(20);
            $stats = $this->getSubscriptionStatistics();
            $plans = Cache::remember('active_plans_for_subscriptions', 300, function () {
                return Plan::where('is_active', true)->select('id', 'name', 'price')->orderBy('price')->get();
            });

            return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'plans'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load subscriptions: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(Request $request, $id)
    {
        $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|string|in:active,trialing,canceled,past_due,expired',
        ]);

        $subscription->update([
            'plan_id' => (int) $validated['plan_id'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription updated');
    }

    public function activateTrial($userId, $planId)
    {
        $user = User::findOrFail($userId);
        $plan = Plan::findOrFail($planId);
        $this->subs->startTrial($user, $plan);
        return redirect()->back()->with('success', 'Trial started');
    }

    public function activatePlan($userId, $planId)
    {
        $user = User::findOrFail($userId);
        $plan = Plan::findOrFail($planId);
        $this->subs->activate($user, $plan);
        return redirect()->back()->with('success', 'Plan activated');
    }

    /**
     * Display subscription statistics.
     */
    public function statistics()
    {
        try {
            $stats = $this->getSubscriptionStatistics();
            $monthlyData = $this->getMonthlySubscriptionData();
            $planDistribution = $this->getPlanDistribution();
            $churnRate = $this->getChurnRate();
            $conversionRate = $this->getTrialToPaidConversionRate();

            return view('admin.subscriptions.statistics', compact(
                'stats', 
                'monthlyData', 
                'planDistribution', 
                'churnRate', 
                'conversionRate'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load statistics: ' . $e->getMessage());
        }
    }

    /**
     * Display subscription details.
     */
    public function show($id)
    {
        try {
            $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
            $changeHistory = $this->getSubscriptionChangeHistory($id);
            $paymentHistory = $this->getPaymentHistory($id);

            return view('admin.subscriptions.show', compact('subscription', 'changeHistory', 'paymentHistory'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load subscription details: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
            $oldStatus = $subscription->status;

            $subscription->update([
                'status' => 'canceled',
                'cancelled_at' => now(),
                'meta' => array_merge($subscription->meta ?? [], [
                    'cancellation_reason' => $request->reason,
                    'cancelled_by' => Auth::guard('admin')->id(),
                ]),
            ]);

            // Notify all admins
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new SubscriptionCancelledNotification($subscription, $request->reason));
            }

            // Fire event
            event(new SubscriptionCancelled($subscription, $request->reason));

            // Clear cache
            Cache::forget('subscription_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upgrade a subscription.
     */
    public function upgrade(Request $request, $id)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:plans,id',
            ]);

            $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
            $oldPlan = $subscription->plan;
            $newPlan = Plan::findOrFail($request->plan_id);

            $subscription->update([
                'plan_id' => $newPlan->id,
                'meta' => array_merge($subscription->meta ?? [], [
                    'upgraded_by' => Auth::guard('admin')->id(),
                    'upgrade_date' => now(),
                ]),
            ]);

            // Notify all admins
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new SubscriptionUpgradedNotification($subscription, $oldPlan, $newPlan));
            }

            // Fire event
            event(new SubscriptionUpgraded($subscription, $oldPlan, $newPlan));

            // Clear cache
            Cache::forget('subscription_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Subscription upgraded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upgrade subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually activate a subscription.
     */
    public function manualActivate($id)
    {
        try {
            $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);

            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'meta' => array_merge($subscription->meta ?? [], [
                    'manually_activated_by' => Auth::guard('admin')->id(),
                    'manual_activation_date' => now(),
                ]),
            ]);

            // Notify all admins
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new ManualSubscriptionActivatedNotification($subscription, Auth::guard('admin')->user()->name));
            }

            // Clear cache
            Cache::forget('subscription_statistics');

            return response()->json([
                'success' => true,
                'message' => 'Subscription manually activated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extend a subscription.
     */
    public function extend(Request $request, $id)
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:1|max:365',
            ]);

            $subscription = Subscription::findOrFail($id);
            $newEndDate = $subscription->ends_at->addDays((int) $request->days);

            $subscription->update([
                'ends_at' => $newEndDate,
                'meta' => array_merge($subscription->meta ?? [], [
                    'extended_by' => Auth::guard('admin')->id(),
                    'extension_days' => $request->days,
                    'extension_date' => now(),
                ]),
            ]);

            // Clear cache
            Cache::forget('subscription_statistics');

            return response()->json([
                'success' => true,
                'message' => "Subscription extended by {$request->days} days"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extend subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export subscriptions.
     */
    public function export(Request $request)
    {
        try {
            $query = Subscription::with(['user', 'plan']);

            // Apply same filters as index
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('plan_id')) {
                $query->where('plan_id', $request->plan_id);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $subscriptions = $query->orderByDesc('created_at')->get();

            $filename = 'subscriptions_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($subscriptions) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'ID', 'User Name', 'User Email', 'Plan Name', 'Status', 
                    'Price', 'Gateway', 'Start Date', 'End Date', 'Created At'
                ]);

                // Data
                foreach ($subscriptions as $subscription) {
                    fputcsv($file, [
                        $subscription->id,
                        $subscription->user->name,
                        $subscription->user->email,
                        $subscription->plan->name,
                        $subscription->status,
                        $subscription->plan->price,
                        $subscription->gateway,
                        $subscription->starts_at?->format('Y-m-d'),
                        $subscription->ends_at?->format('Y-m-d'),
                        $subscription->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export subscriptions: ' . $e->getMessage());
        }
    }

    /**
     * Get subscription statistics.
     */
    private function getSubscriptionStatistics()
    {
        return Cache::remember('subscription_statistics', 300, function () {
            $today = today();
            $thisMonth = now()->month;
            $thisYear = now()->year;

            return [
                'total_subscriptions' => Subscription::count(),
                'active_subscriptions' => Subscription::where('status', 'active')->count(),
                'trial_subscriptions' => Subscription::where('status', 'trialing')->count(),
                'cancelled_subscriptions' => Subscription::where('status', 'canceled')->count(),
                'expired_subscriptions' => Subscription::where('status', 'expired')->count(),
                'subscriptions_this_month' => Subscription::whereMonth('created_at', $thisMonth)
                    ->whereYear('created_at', $thisYear)->count(),
                'subscriptions_today' => Subscription::whereDate('created_at', $today)->count(),
                'monthly_revenue' => Subscription::where('status', 'active')
                    ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                    ->sum('plans.price'),
                'total_revenue' => Subscription::join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                    ->sum('plans.price'),
            ];
        });
    }

    /**
     * Get monthly subscription data for charts.
     */
    private function getMonthlySubscriptionData()
    {
        return Cache::remember('monthly_subscription_data', 300, function () {
            return Subscription::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as count,
                SUM(plans.price) as revenue
            ')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        });
    }

    /**
     * Get plan distribution.
     */
    private function getPlanDistribution()
    {
        return Cache::remember('plan_distribution', 300, function () {
            return Subscription::selectRaw('
                plans.name,
                COUNT(*) as count,
                SUM(plans.price) as revenue
            ')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.status', 'active')
            ->groupBy('plans.id', 'plans.name')
            ->orderByDesc('count')
            ->get();
        });
    }

    /**
     * Get churn rate.
     */
    private function getChurnRate()
    {
        return Cache::remember('churn_rate', 300, function () {
            $totalActive = Subscription::where('status', 'active')->count();
            $cancelledThisMonth = Subscription::where('status', 'canceled')
                ->whereMonth('cancelled_at', now()->month)
                ->whereYear('cancelled_at', now()->year)
                ->count();

            return $totalActive > 0 ? round(($cancelledThisMonth / $totalActive) * 100, 2) : 0;
        });
    }

    /**
     * Get trial to paid conversion rate.
     */
    private function getTrialToPaidConversionRate()
    {
        return Cache::remember('trial_conversion_rate', 300, function () {
            $totalTrials = Subscription::where('status', 'trialing')->count();
            $convertedTrials = Subscription::where('status', 'active')
                ->whereNotNull('trial_ends_at')
                ->count();

            return $totalTrials > 0 ? round(($convertedTrials / $totalTrials) * 100, 2) : 0;
        });
    }

    /**
     * Get subscription change history.
     */
    private function getSubscriptionChangeHistory($subscriptionId)
    {
        // This would typically come from an audit log table
        // For now, return empty array
        return [];
    }

    /**
     * Get payment history.
     */
    private function getPaymentHistory($subscriptionId)
    {
        // This would typically come from a payments table
        // For now, return empty array
        return [];
    }
}


