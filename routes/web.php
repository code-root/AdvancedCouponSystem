<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Public root
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Emergency stop impersonation route - works without middleware conflicts
Route::post('/emergency-stop-impersonating', function() {
    try {
        $adminId = \App\Helpers\ImpersonationHelper::getAdminId();
        $impersonatedUserName = \App\Helpers\ImpersonationHelper::getImpersonatedUserName();
        
        if (!$adminId) {
            return redirect()->route('admin.login')->with('error', 'No active impersonation session.');
        }
        
        // Log impersonation stop
        \Illuminate\Support\Facades\Log::info('Admin stopped impersonation (emergency route)', [
            'admin_id' => $adminId,
            'impersonated_user_name' => $impersonatedUserName,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        // Logout from user session
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        
        // Clear impersonation session data
        \App\Helpers\ImpersonationHelper::stopImpersonation();
        session()->forget(['original_admin_session_id']);
        
        // Restore admin session
        $admin = \App\Models\Admin::find($adminId);
        if ($admin && $admin->active) {
            \Illuminate\Support\Facades\Auth::guard('admin')->login($admin);
            session()->regenerate();
            return redirect()->route('admin.dashboard')->with('success', "Stopped impersonating {$impersonatedUserName}.");
        } else {
            return redirect()->route('admin.login')->with('error', 'Admin account not found or inactive.');
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Emergency stop impersonation error: ' . $e->getMessage());
        return redirect()->route('admin.login')->with('error', 'Failed to stop impersonation: ' . $e->getMessage());
    }
})->name('emergency.stop-impersonating');

// Maintenance helper
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return "Cleared cach , config , view , optimize !";
});

// Include separated route files
require __DIR__.'/dashboard.php';
require __DIR__.'/admin.php';

// Payment webhooks (public endpoints expected by gateways)
Route::post('/stripe/webhook', [\App\Http\Controllers\BillingController::class, 'stripeWebhook'])->name('stripe.webhook');
Route::post('/paypal/webhook', [\App\Http\Controllers\BillingController::class, 'paypalWebhook'])->name('paypal.webhook');
