<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserSession;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Events\NewSessionCreated;
use App\Notifications\NewLoginNotification;

class TrackUserSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track sessions for regular users, not admins
        if (auth()->check() && !auth()->guard('admin')->check()) {
            $this->updateOrCreateSession($request);
        }

        return $next($request);
    }

    /**
     * Update or create user session record
     * Optimized to reduce database queries and improve performance
     */
    protected function updateOrCreateSession(Request $request): void
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        // First, check if session exists for this user
        $userSession = UserSession::where('session_id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        if (!$userSession) {
            // Check if session exists for any user (to handle session conflicts)
            $existingSession = UserSession::where('session_id', $sessionId)->first();
            
            if ($existingSession) {
                // Delete the existing session and create new one for current user
                $existingSession->delete();
            }
            
            // Create new session record with full details
            $this->createSessionRecord($request, $sessionId, $userId);
        } else {
            // Update existing session with heartbeat (optimized update)
            $this->updateSessionHeartbeat($userSession);
        }
    }

    /**
     * Create new session record with all details
     */
    protected function createSessionRecord(Request $request, string $sessionId, int $userId): void
    {
        try {
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            // Get device information
            $deviceType = $this->getDeviceType($agent);
            $deviceName = $this->getDeviceName($agent);
            $platform = $agent->platform();
            $browser = $agent->browser();
            $browserVersion = $agent->version($browser);

            // Get location from IP
            $ipAddress = $request->ip();
            $location = $this->getLocationFromIP($ipAddress);

            // Get referrer and UTM parameters
            $referrer = $request->headers->get('referer');
            $utmSource = $request->get('utm_source');
            $utmMedium = $request->get('utm_medium');
            $utmCampaign = $request->get('utm_campaign');

            // Create session record safely
            $userSession = UserSession::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            
            // Device info
            'device_type' => $deviceType,
            'device_name' => $deviceName,
            'platform' => $platform,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            
            // Location info
            'country' => $location['country'] ?? null,
            'country_code' => $location['country_code'] ?? null,
            'region' => $location['region'] ?? null,
            'city' => $location['city'] ?? null,
            'timezone' => $location['timezone'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            
            // Referrer info
            'referrer_url' => $referrer,
            'landing_page' => $request->fullUrl(),
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
            
            // Session details
            'last_activity' => now(),
            'last_heartbeat' => now(),
            'login_at' => now(),
            'expires_at' => now()->addMinutes(config('session.lifetime', 120)),
            'is_active' => true,
            'is_online' => true,
        ]);
        
        // Broadcast new session event (for real-time updates)
        try {
            broadcast(new NewSessionCreated($userSession))->toOthers();
        } catch (\Exception $e) {
            Log::error('Failed to broadcast session event: ' . $e->getMessage());
        }
        
            // Send notification to user about new login (only if email is configured)
            if (config('mail.default') !== 'log' && config('mail.mailers.smtp.host')) {
                try {
                    $user = \App\Models\User::find($userId);
                    $user->notify(new NewLoginNotification($userSession));
                } catch (\Exception $e) {
                    Log::error('Failed to send login notification: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to create user session record: ' . $e->getMessage(), [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update session heartbeat efficiently
     * Only updates necessary fields to reduce database load
     */
    protected function updateSessionHeartbeat(UserSession $userSession): void
    {
        $now = now();
        
        // Only update if last heartbeat was more than 30 seconds ago
        // This reduces database writes significantly
        if (!$userSession->last_heartbeat || $userSession->last_heartbeat->diffInSeconds($now) > 30) {
            $userSession->update([
                'last_activity' => $now,
                'last_heartbeat' => $now,
                'is_active' => true,
                'is_online' => true,
            ]);
        }
    }

    /**
     * Get device type
     */
    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isDesktop()) {
            return 'desktop';
        }
        
        return 'unknown';
    }

    /**
     * Get device name
     */
    protected function getDeviceName(Agent $agent): ?string
    {
        // Try to get device name
        $device = $agent->device();
        
        if ($device && $device !== 'WebKit') {
            return $device;
        }
        
        // Fallback to platform + browser
        $platform = $agent->platform();
        $browser = $agent->browser();
        
        return $platform && $browser ? "$platform / $browser" : null;
    }

    /**
     * Get location from IP address using ipapi.co
     * Optimized with better caching and error handling
     */
    protected function getLocationFromIP(string $ip): array
    {
        // Skip for local IPs and private networks
        if ($this->isLocalIP($ip)) {
            return [
                'country' => 'Local',
                'country_code' => 'LO',
                'city' => 'Localhost',
            ];
        }

        // Cache the result for 7 days (longer cache for better performance)
        $cacheKey = "ip_location_{$ip}";
        
        return Cache::remember($cacheKey, 604800, function () use ($ip) {
            try {
                // Using ipapi.co (free, no API key needed, 1000 requests/day)
                $response = Http::timeout(2)->retry(2, 100)->get("https://ipapi.co/{$ip}/json/");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    return [
                        'country' => $data['country_name'] ?? null,
                        'country_code' => $data['country_code'] ?? null,
                        'region' => $data['region'] ?? null,
                        'city' => $data['city'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to get location for IP: ' . $ip, ['error' => $e->getMessage()]);
            }
            
            return [];
        });
    }

    /**
     * Check if IP is local or private
     */
    protected function isLocalIP(string $ip): bool
    {
        return $ip === '127.0.0.1' || 
               $ip === 'localhost' || 
               str_starts_with($ip, '192.168.') ||
               str_starts_with($ip, '10.') ||
               str_starts_with($ip, '172.') ||
               $ip === '::1';
    }
}

