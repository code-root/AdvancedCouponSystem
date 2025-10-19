<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $admin = Auth::guard('admin')->user();

        // Check if admin is active
        if (!$admin->isActive()) {
            Auth::guard('admin')->logout();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive'
                ], 403);
            }
            return redirect()->route('admin.login')->with('error', 'Your account is inactive.');
        }

        // Check if admin has the required permission
        if (!$admin->hasPermissionTo($permission, 'admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions',
                    'required_permission' => $permission
                ], 403);
            }
            
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}

