<?php

namespace App\Http\Middleware;

use App\Events\AdminSessionStarted;
use App\Models\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Jenssegers\Agent\Agent;

class TrackAdminSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $sessionId = session()->getId();
            
            // Get device info
            $agent = new Agent();
            $deviceName = $agent->device() ?: 'Desktop';
            $platform = $agent->platform();
            $browser = $agent->browser();
            
            // Find or create session
            $adminSession = AdminSession::where('session_id', $sessionId)->first();
            
            if (!$adminSession) {
                // Create new session
                $adminSession = AdminSession::create([
                    'admin_id' => $admin->id,
                    'session_id' => $sessionId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_name' => $deviceName,
                    'platform' => $platform,
                    'browser' => $browser,
                    'login_at' => now(),
                    'last_activity_at' => now(),
                    'is_active' => true,
                    'location' => $this->getLocationFromIp($request->ip()),
                ]);
                
                // Broadcast session started event
                event(new AdminSessionStarted($admin, [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_name' => $deviceName,
                    'platform' => $platform,
                    'browser' => $browser,
                    'location' => $this->getLocationFromIp($request->ip()),
                ]));
            } else {
                // Update last activity
                $adminSession->updateActivity();
            }
        }
        
        return $next($request);
    }
    
    /**
     * Get location from IP address.
     */
    private function getLocationFromIp(string $ip): ?string
    {
        // Skip local IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return 'Local';
        }
        
        // You can integrate with IP geolocation service here
        // For now, return null
        return null;
    }
}

