<?php

namespace App\Http\Controllers;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SyncUsage;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subs) {}

    public function plans()
    {
        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        return view('dashboard.subscriptions.plans', compact('plans'));
    }

    public function compare()
    {
        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        return view('dashboard.subscriptions.compare', compact('plans'));
    }

    public function manage()
    {
        $subscription = Subscription::where('user_id', Auth::id())->with('plan')->first();

        // Usage (current windows)
        $now = now();
        $dailyStart = $now->copy()->startOfDay();
        $dailyEnd = $now->copy()->endOfDay();
        $monthlyStart = $now->copy()->startOfMonth();
        $monthlyEnd = $now->copy()->endOfMonth();

        $dailyUsage = SyncUsage::where('user_id', Auth::id())
            ->where('period', 'daily')
            ->where('window_start', $dailyStart)
            ->where('window_end', $dailyEnd)
            ->first();

        $monthlyUsage = SyncUsage::where('user_id', Auth::id())
            ->where('period', 'monthly')
            ->where('window_start', $monthlyStart)
            ->where('window_end', $monthlyEnd)
            ->first();

        return view('dashboard.subscriptions.manage', compact('subscription', 'dailyUsage', 'monthlyUsage'));
    }

    public function startTrial(Request $request, Plan $plan)
    {
        $this->subs->startTrial(Auth::user(), $plan);
        return redirect()->route('dashboard')->with('success', 'Trial started');
    }

    public function activate(Request $request, Plan $plan)
    {
        $coupon = $request->input('coupon');
        $this->subs->activate(Auth::user(), $plan, $coupon);
        return redirect()->route('dashboard')->with('success', 'Subscription activated');
    }

    public function cancel(Request $request)
    {
        $subscription = Subscription::where('user_id', Auth::id())->firstOrFail();
        $this->subs->cancel($subscription);
        return back()->with('success', 'Subscription canceled');
    }
}




