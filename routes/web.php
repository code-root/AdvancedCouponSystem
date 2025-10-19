<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Public root
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Admin impersonation stop route
Route::post('/admin/stop-impersonating', [App\Http\Controllers\admin\AdminUserManagementController::class, 'stopImpersonating'])
    ->name('admin.stop-impersonating');

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
