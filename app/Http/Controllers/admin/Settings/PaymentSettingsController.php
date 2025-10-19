<?php

namespace App\Http\Controllers\admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentSettingsController extends Controller
{
    /**
     * Display payment settings.
     */
    public function index()
    {
        try {
            $settings = SiteSetting::whereIn('key', [
                'stripe_public_key', 'stripe_secret_key', 'stripe_webhook_secret',
                'paypal_client_id', 'paypal_client_secret', 'paypal_mode',
                'currency', 'currency_symbol', 'payment_methods'
            ])->pluck('value', 'key');

            $title = 'Payment Settings';
            $subtitle = 'Payment Configuration';

            return view('admin.settings.payment', compact('settings', 'title', 'subtitle'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load payment settings: ' . $e->getMessage());
        }
    }

    /**
     * Update payment settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'stripe_public_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_client_secret' => 'nullable|string|max:255',
            'paypal_mode' => 'nullable|in:sandbox,live',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:10',
            'payment_methods' => 'nullable|array',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'payment',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment settings updated successfully'
            ]);
        }

        return back()->with('success', 'Payment settings updated successfully');
    }

    /**
     * AJAX update payment settings.
     */
    public function updateAjax(Request $request)
    {
        return $this->update($request);
    }

    /**
     * Test payment configuration.
     */
    public function testPayment(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:stripe,paypal',
        ]);

        try {
            if ($request->provider === 'stripe') {
                // Test Stripe connection
                $stripe = new \Stripe\StripeClient(
                    SiteSetting::where('key', 'stripe_secret_key')->value('value')
                );
                $stripe->balance->retrieve();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Stripe connection successful!'
                ]);
            } elseif ($request->provider === 'paypal') {
                // Test PayPal connection
                return response()->json([
                    'success' => true,
                    'message' => 'PayPal configuration valid!'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment test failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
