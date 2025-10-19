<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $subs) {}

    public function index()
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('admin.subscriptions.index', compact('subscriptions'));
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
}


