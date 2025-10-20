<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    /**
     * Display the current user's subscription.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $subscription = $user->subscriptions()->with('plan')->latest()->first();
            $plans = Cache::remember('active_plans_for_user', 300, function () {
                return Plan::where('is_active', true)
                    ->orderBy('price')
                    ->get();
            });

            // Get subscription statistics
            $stats = $this->getUserSubscriptionStats($user);

            return view('dashboard.subscription.index', compact('subscription', 'plans', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load subscription: ' . $e->getMessage());
        }
    }

    /**
     * Display plan comparison.
     */
    public function compare()
    {
        try {
            $user = Auth::user();
            $plans = Plan::where('is_active', true)
                ->orderBy('price')
                ->get();
            
            $subscription = $user->activeSubscription;
            
            return view('dashboard.subscription.compare', compact('plans', 'subscription'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load plan comparison: ' . $e->getMessage());
        }
    }

    /**
     * Display available plans.
     */
    public function plans()
    {
        try {
            $user = Auth::user();
            $plans = Plan::where('is_active', true)
                ->orderBy('price')
                ->get();

            $currentSubscription = $user->subscriptions()->with('plan')->latest()->first();
            
            // Get subscription context for the view
            $subscriptionContext = $this->getSubscriptionContext($user, $currentSubscription);

            return view('dashboard.subscription.plans', compact('plans', 'currentSubscription', 'subscriptionContext'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load plans: ' . $e->getMessage());
        }
    }

    /**
     * Get subscription context for views
     */
    protected function getSubscriptionContext($user, $subscription = null)
    {
        $subscription = $subscription ?? $user->activeSubscription;
        
        return [
            'hasSubscription' => $subscription !== null,
            'isReadOnly' => !$subscription || $subscription->status !== 'active',
            'subscription' => $subscription,
            'plan' => $subscription?->plan,
            'features' => $subscription?->plan?->features ?? [],
            'daysRemaining' => $subscription?->ends_at?->diffInDays(now(), false) ?? 0,
            'isTrialing' => $subscription?->status === 'trialing',
            'trialEndsIn' => $subscription?->trial_ends_at?->diffInDays(now(), false) ?? 0,
            'status' => $subscription?->status ?? 'none',
            'canAddNetwork' => $this->canAddNetwork($user, $subscription),
            'canAddCampaign' => $this->canAddCampaign($user, $subscription),
            'canSyncData' => $this->canSyncData($user, $subscription),
            'canExportData' => $this->canExportData($subscription),
            'canAccessAPI' => $this->canAccessAPI($subscription),
            'canAdvancedAnalytics' => $this->canAdvancedAnalytics($subscription),
        ];
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:plans,id',
                'payment_method' => 'required|string',
                'coupon_code' => 'nullable|string',
            ]);

            $user = Auth::user();
            $plan = Plan::findOrFail($request->plan_id);

            // Check if user already has an active subscription
            $activeSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->first();

            if ($activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription. Please cancel it first or upgrade.',
                ], 400);
            }

            // Create subscription
            $subscription = $this->subscriptionService->createSubscription($user, $plan, [
                'gateway' => $request->payment_method,
                'coupon_code' => $request->coupon_code,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully!',
                'subscription' => $subscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel current subscription.
     */
    public function cancel(Request $request)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            $subscription = $user->subscriptions()
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.',
                ], 404);
            }

            $this->subscriptionService->cancelSubscription($subscription, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request)
    {
        try {
            $user = Auth::user();
            $subscription = $user->subscriptions()
                ->where('status', 'canceled')
                ->latest()
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No cancelled subscription found.',
                ], 404);
            }

            $this->subscriptionService->resumeSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change subscription plan.
     */
    public function changePlan(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:plans,id',
                'immediate' => 'boolean',
            ]);

            $user = Auth::user();
            $newPlan = Plan::findOrFail($request->plan_id);
            $currentSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$currentSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.',
                ], 404);
            }

            $this->subscriptionService->changePlan($currentSubscription, $newPlan, $request->immediate);

            return response()->json([
                'success' => true,
                'message' => 'Plan changed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display subscription invoices.
     */
    public function invoices()
    {
        try {
            $user = Auth::user();
            $invoices = Payment::where('user_id', $user->id)
                ->with('subscription.plan')
                ->orderByDesc('created_at')
                ->paginate(10);

            return view('dashboard.subscription.invoices', compact('invoices'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load invoices: ' . $e->getMessage());
        }
    }

    /**
     * Download invoice PDF.
     */
    public function downloadInvoice($id)
    {
        try {
            $user = Auth::user();
            $payment = Payment::where('user_id', $user->id)
                ->with('subscription.plan')
                ->findOrFail($id);

            // Generate PDF invoice
            $pdf = app('dompdf.wrapper')->loadView('dashboard.subscription.invoice-pdf', compact('payment'));

            return $pdf->download("invoice-{$payment->id}.pdf");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get subscription usage statistics.
     */
    public function usage()
    {
        try {
            $user = Auth::user();
            $subscription = $user->subscriptions()->with('plan')->latest()->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No subscription found.',
                ], 404);
            }

            $usage = $this->getSubscriptionUsage($subscription);

            return response()->json([
                'success' => true,
                'usage' => $usage,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get usage data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user subscription statistics.
     */
    private function getUserSubscriptionStats($user)
    {
        return Cache::remember("user_subscription_stats_{$user->id}", 300, function () use ($user) {
            $subscriptions = $user->subscriptions();
            
            return [
                'total_subscriptions' => $subscriptions->count(),
                'active_subscription' => $subscriptions->where('status', 'active')->first(),
                'total_paid' => $subscriptions->where('status', 'active')->sum('plan.price'),
                'days_remaining' => $this->getDaysRemaining($user),
                'next_billing_date' => $this->getNextBillingDate($user),
            ];
        });
    }

    /**
     * Get days remaining for current subscription.
     */
    private function getDaysRemaining($user)
    {
        $subscription = $user->subscriptions()->where('status', 'active')->latest()->first();
        
        if (!$subscription || !$subscription->ends_at) {
            return 0;
        }

        return now()->diffInDays($subscription->ends_at, false);
    }

    /**
     * Get next billing date.
     */
    private function getNextBillingDate($user)
    {
        $subscription = $user->subscriptions()->where('status', 'active')->latest()->first();
        
        if (!$subscription || !$subscription->ends_at) {
            return null;
        }

        return $subscription->ends_at;
    }

    /**
     * Get subscription usage data.
     */
    private function getSubscriptionUsage($subscription)
    {
        $plan = $subscription->plan;
        $user = $subscription->user;

        // Get current usage based on plan features
        $usage = [];

        if (isset($plan->features['networks_limit'])) {
            $networksCount = $user->networks()->count();
            $usage['networks'] = [
                'used' => $networksCount,
                'limit' => $plan->features['networks_limit'],
                'percentage' => $plan->features['networks_limit'] > 0 
                    ? round(($networksCount / $plan->features['networks_limit']) * 100, 2)
                    : 0,
            ];
        }

        if (isset($plan->features['campaigns_limit'])) {
            $campaignsCount = $user->campaigns()->count();
            $usage['campaigns'] = [
                'used' => $campaignsCount,
                'limit' => $plan->features['campaigns_limit'],
                'percentage' => $plan->features['campaigns_limit'] > 0 
                    ? round(($campaignsCount / $plan->features['campaigns_limit']) * 100, 2)
                    : 0,
            ];
        }

        if (isset($plan->features['syncs_per_month'])) {
            $currentMonthSyncs = $user->syncLogs()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            $usage['syncs'] = [
                'used' => $currentMonthSyncs,
                'limit' => $plan->features['syncs_per_month'],
                'percentage' => $plan->features['syncs_per_month'] > 0 
                    ? round(($currentMonthSyncs / $plan->features['syncs_per_month']) * 100, 2)
                    : 0,
            ];
        }

        return $usage;
    }

    /**
     * Check if user can add networks
     */
    protected function canAddNetwork($user, $subscription)
    {
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $networksLimit = $plan->features['networks_limit'] ?? 0;
        
        if ($networksLimit === -1) {
            return true; // Unlimited
        }

        $currentNetworks = $user->networks()->count();
        return $currentNetworks < $networksLimit;
    }

    /**
     * Check if user can add campaigns
     */
    protected function canAddCampaign($user, $subscription)
    {
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $campaignsLimit = $plan->features['campaigns_limit'] ?? 0;
        
        if ($campaignsLimit === -1) {
            return true; // Unlimited
        }

        $currentCampaigns = $user->campaigns()->count();
        return $currentCampaigns < $campaignsLimit;
    }

    /**
     * Check if user can sync data
     */
    protected function canSyncData($user, $subscription)
    {
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        $syncsLimit = $plan->features['syncs_per_month'] ?? 0;
        
        if ($syncsLimit === -1) {
            return true; // Unlimited
        }

        // Check current month syncs
        $currentMonthSyncs = $user->syncLogs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return $currentMonthSyncs < $syncsLimit;
    }

    /**
     * Check if user can export data
     */
    protected function canExportData($subscription)
    {
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        return $plan->features['export_data'] ?? false;
    }

    /**
     * Check if user can access API
     */
    protected function canAccessAPI($subscription)
    {
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        return $plan->features['api_access'] ?? false;
    }

    /**
     * Check if user can access advanced analytics
     */
    protected function canAdvancedAnalytics($subscription)
    {
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;
        return $plan->features['advanced_analytics'] ?? false;
    }
}

