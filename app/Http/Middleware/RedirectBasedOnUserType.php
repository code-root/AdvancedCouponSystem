<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (User model)
        if (Auth::check()) {
            $user = Auth::user();
            
            // Regular user - redirect to user dashboard
            return redirect()->route('dashboard');
        }
        
        // Check if admin is authenticated (Admin model)
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            // Check if admin account is active
            if ($admin->active) {
                return redirect()->route('admin.dashboard');
            } else {
                Auth::guard('admin')->logout();
                return redirect()->route('admin.login')->with('error', 'Your account is inactive. Please contact support.');
            }
        }

        return $next($request);
    }
}
