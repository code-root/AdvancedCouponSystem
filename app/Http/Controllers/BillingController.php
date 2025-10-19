<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function __construct(private SubscriptionService $subs) {}

    public function checkout(Request $request, Plan $plan)
    {
        // If stripe/paypal disabled, return error (admin can activate manually)
        if (!config('services.stripe.enabled') && !config('services.paypal.enabled')) {
            return back()->with('error', 'Payment gateways are disabled. Contact support or admin can activate your plan.');
        }

        // Placeholder: here you'd create Stripe Checkout Session or PayPal Order
        // For now, simulate success and activate subscription directly
        $this->subs->activate(Auth::user(), $plan, $request->input('coupon'));
        Payment::create([
            'user_id' => Auth::id(),
            'subscription_id' => Subscription::where('user_id', Auth::id())->value('id'),
            'gateway' => config('services.stripe.enabled') ? 'stripe' : 'paypal',
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => 'paid',
            'external_id' => null,
            'receipt_url' => null,
            'meta' => null,
            'paid_at' => now(),
        ]);

        return redirect()->route('subscriptions.manage')->with('success', 'Payment successful and subscription activated');
    }

    public function stripeWebhook(Request $request)
    {
        // Handle Stripe webhook events (placeholder)
        return response()->json(['received' => true]);
    }

    public function paypalWebhook(Request $request)
    {
        // Handle PayPal webhook events (placeholder)
        return response()->json(['received' => true]);
    }
}


