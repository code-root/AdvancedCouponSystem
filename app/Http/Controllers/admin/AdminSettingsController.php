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
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 
            'mail_encryption', 'mail_from_address', 'mail_from_name', 'mail_verify_peer'
        ])->pluck('value', 'key');

        return view('admin.settings.smtp', compact('settings'));
    }

    /**
     * Save SMTP settings.
     */
    public function saveSmtp(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'nullable|string|max:255',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'mail_verify_peer' => 'nullable|boolean',
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
            'meta_description', 'meta_keywords', 'meta_author', 'robots_meta',
            'og_title', 'og_description', 'facebook_url', 'twitter_url', 
            'linkedin_url', 'instagram_url', 'google_analytics_id', 
            'google_tag_manager_id', 'facebook_pixel_id'
        ])->pluck('value', 'key');

        return view('admin.settings.seo', compact('settings'));
    }

    /**
     * Save SEO settings.
     */
    public function saveSeo(Request $request)
    {
        $validated = $request->validate([
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_author' => 'nullable|string|max:255',
            'robots_meta' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_tag_manager_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
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
            'site_name', 'site_url', 'timezone', 'locale', 'maintenance_mode',
            'maintenance_message', 'registration_enabled'
        ])->pluck('value', 'key');

        return view('admin.settings.general', compact('settings'));
    }

    /**
     * Save general settings.
     */
    public function saveGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'nullable|url|max:255',
            'timezone' => 'nullable|string|max:255',
            'locale' => 'nullable|string|max:10',
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:500',
            'registration_enabled' => 'nullable|boolean',
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


