<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ImpersonationHelper;

class EnsureUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        if ($type === 'user') {
            // Check if user is authenticated (User model)
            if (!Auth::check()) {
                return redirect()->route('login');
            }
            
            // Check if admin is trying to access user pages
            if (Auth::guard('admin')->check()) {
                // Allow if admin is impersonating a user
                if (ImpersonationHelper::isImpersonating()) {
                    // Validate impersonation session
                    if (!ImpersonationHelper::validateImpersonation()) {
                        ImpersonationHelper::stopImpersonation();
                        return redirect()->route('admin.login')->with('error', 'Impersonation session expired or invalid.');
                    }

                    // Check if impersonation session is expired (2 hours timeout)
                    if (ImpersonationHelper::isImpersonationExpired(120)) {
                        ImpersonationHelper::stopImpersonation();
                        return redirect()->route('admin.login')->with('error', 'Impersonation session expired.');
                    }

                    // Add impersonation data to request for use in controllers/views
                    $request->merge([
                        'is_impersonating' => true,
                        'impersonated_user_id' => ImpersonationHelper::getImpersonatedUserId(),
                        'impersonated_user_name' => ImpersonationHelper::getImpersonatedUserName(),
                        'impersonating_admin_id' => ImpersonationHelper::getAdminId(),
                    ]);

                    return $next($request);
                }
                return redirect()->route('admin.dashboard')->with('error', 'Access denied. You are logged in as admin.');
            }
        }
        
        if ($type === 'admin') {
            // Check if admin is authenticated (Admin model)
            if (!Auth::guard('admin')->check()) {
                return redirect()->route('admin.login');
            }
            
            // Check if admin account is active
            $admin = Auth::guard('admin')->user();
            if (!$admin->active) {
                Auth::guard('admin')->logout();
                return redirect()->route('admin.login')->with('error', 'Your account is inactive. Please contact support.');
            }
        }

        return $next($request);
    }
}
