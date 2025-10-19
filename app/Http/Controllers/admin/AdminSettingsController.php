<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class AdminSettingsController extends Controller
{
    public function branding()
    {
        $settings = SiteSetting::whereIn('key', ['site_name','meta_description','meta_author','favicon','logo','logo_light','logo_dark','logo_sm'])->pluck('value','key');
        return view('admin.settings.branding', compact('settings'));
    }

    public function saveBranding(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_author' => 'nullable|string|max:255',
            'favicon' => 'nullable|string|max:500',
            'logo' => 'nullable|string|max:500',
            'logo_light' => 'nullable|string|max:500',
            'logo_dark' => 'nullable|string|max:500',
            'logo_sm' => 'nullable|string|max:500',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'branding',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        return back()->with('success', 'Branding settings saved');
    }

    /**
     * Display SMTP settings.
     */
    public function smtp()
    {
        $settings = SiteSetting::whereIn('key', [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 
            'smtp_encryption', 'smtp_from_name', 'smtp_from_email'
        ])->pluck('value', 'key');

        return view('admin.settings.smtp', compact('settings'));
    }

    /**
     * Save SMTP settings.
     */
    public function saveSmtp(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'smtp_from_name' => 'nullable|string|max:255',
            'smtp_from_email' => 'nullable|email|max:255',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'smtp',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        return back()->with('success', 'SMTP settings saved');
    }

    /**
     * Display SEO settings.
     */
    public function seo()
    {
        $settings = SiteSetting::whereIn('key', [
            'google_analytics_id', 'google_tag_manager_id', 'meta_keywords', 'og_image'
        ])->pluck('value', 'key');

        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * Save SEO settings.
     */
    public function saveSeo(Request $request)
    {
        $validated = $request->validate([
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'meta_keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'seo',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        return back()->with('success', 'SEO settings saved');
    }

    /**
     * Display general settings.
     */
    public function general()
    {
        $settings = SiteSetting::whereIn('key', [
            'timezone', 'date_format', 'time_format', 'currency', 
            'currency_symbol', 'language', 'maintenance_mode'
        ])->pluck('value', 'key');

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Save general settings.
     */
    public function saveGeneral(Request $request)
    {
        $validated = $request->validate([
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
            'currency_symbol' => 'nullable|string|max:5',
            'language' => 'nullable|string|max:5',
            'maintenance_mode' => 'boolean',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'general',
                    'is_active' => true,
                    'last_modified_at' => now(),
                    'updated_by' => $adminId,
                ]
            );
        }

        return back()->with('success', 'General settings saved');
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Get SMTP settings
            $smtpSettings = SiteSetting::whereIn('key', [
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 
                'smtp_encryption', 'smtp_from_name', 'smtp_from_email'
            ])->pluck('value', 'key');

            // Configure mail settings
            Config::set('mail.mailers.smtp.host', $smtpSettings['smtp_host'] ?? '');
            Config::set('mail.mailers.smtp.port', $smtpSettings['smtp_port'] ?? 587);
            Config::set('mail.mailers.smtp.username', $smtpSettings['smtp_username'] ?? '');
            Config::set('mail.mailers.smtp.password', $smtpSettings['smtp_password'] ?? '');
            Config::set('mail.mailers.smtp.encryption', $smtpSettings['smtp_encryption'] ?? 'tls');
            Config::set('mail.from.address', $smtpSettings['smtp_from_email'] ?? '');
            Config::set('mail.from.name', $smtpSettings['smtp_from_name'] ?? '');

            // Send test email
            Mail::raw('This is a test email from AdvancedCouponSystem Admin Panel.', function ($message) use ($request, $smtpSettings) {
                $message->to($request->test_email)
                        ->subject('Test Email from AdvancedCouponSystem')
                        ->from($smtpSettings['smtp_from_email'] ?? 'noreply@advancedcouponsystem.com', 
                               $smtpSettings['smtp_from_name'] ?? 'AdvancedCouponSystem');
            });

            return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function payment()
    {
        $settings = SiteSetting::whereIn('key', [
            'stripe_public_key',
            'stripe_secret_key',
            'stripe_webhook_secret',
            'paypal_client_id',
            'paypal_client_secret',
            'paypal_mode',
            'paypal_webhook_id'
        ])->pluck('value', 'key');
        
        return view('admin.settings.payment', compact('settings'));
    }

    public function savePayment(Request $request)
    {
        $validated = $request->validate([
            'stripe_public_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_client_secret' => 'nullable|string|max:255',
            'paypal_mode' => 'nullable|in:sandbox,live',
            'paypal_webhook_id' => 'nullable|string|max:255',
        ]);

        $adminId = Auth::guard('admin')->id();

        foreach ($validated as $key => $value) {
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

        return back()->with('success', 'Payment settings saved successfully');
    }
}


