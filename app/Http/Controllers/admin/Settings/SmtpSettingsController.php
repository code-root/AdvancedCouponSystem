<?php

namespace App\Http\Controllers\admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SmtpSettingsController extends Controller
{
    /**
     * Display SMTP settings.
     */
    public function index()
    {
        try {
            $settings = SiteSetting::whereIn('key', [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 
                'mail_encryption', 'mail_from_address', 'mail_from_name', 'mail_verify_peer'
            ])->pluck('value', 'key');

            $title = 'SMTP Settings';
            $subtitle = 'Email Configuration';

            return view('admin.settings.smtp', compact('settings', 'title', 'subtitle'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load SMTP settings: ' . $e->getMessage());
        }
    }

    /**
     * Update SMTP settings.
     */
    public function update(Request $request)
    {
        try {
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

            // Clear cache after updating settings
            cache()->forget('site_settings');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMTP settings updated successfully'
                ]);
            }

            return back()->with('success', 'SMTP settings updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update SMTP settings: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update SMTP settings: ' . $e->getMessage());
        }
    }

    /**
     * AJAX update SMTP settings.
     */
    public function updateAjax(Request $request)
    {
        try {
            return $this->update($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update SMTP settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request)
    {
        try {
            $request->validate([
                'test_email' => 'required|email',
                'test_subject' => 'required|string|max:255',
                'test_message' => 'required|string|max:1000',
            ]);

            // Get current SMTP settings
            $smtpSettings = SiteSetting::whereIn('key', [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 
                'mail_encryption', 'mail_from_address', 'mail_from_name'
            ])->pluck('value', 'key');

            // Configure mail settings dynamically
            config([
                'mail.mailers.smtp.host' => $smtpSettings['mail_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => $smtpSettings['mail_port'] ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => $smtpSettings['mail_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $smtpSettings['mail_password'] ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $smtpSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
                'mail.from.address' => $smtpSettings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $smtpSettings['mail_from_name'] ?? config('mail.from.name'),
            ]);

            Mail::raw($request->test_message, function ($message) use ($request, $smtpSettings) {
                $message->to($request->test_email)
                        ->subject($request->test_subject)
                        ->from(
                            $smtpSettings['mail_from_address'] ?? config('mail.from.address'),
                            $smtpSettings['mail_from_name'] ?? config('mail.from.name')
                        );
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }
}
