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
        if (auth()->check()) {
            $this->updateOrCreateSession($request);
        }

        return $next($request);
    }

    /**
     * Update or create user session record
     */
    protected function updateOrCreateSession(Request $request): void
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        // Get or create session record
        $userSession = UserSession::where('session_id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        if (!$userSession) {
            // Create new session record
            $this->createSessionRecord($request, $sessionId, $userId);
        } else {
            // Update existing session with heartbeat
            $userSession->update([
                'last_activity' => now(),
                'last_heartbeat' => now(),
                'is_active' => true,
                'is_online' => true,
            ]);
        }
    }

    /**
     * Create new session record with all details
     */
    protected function createSessionRecord(Request $request, string $sessionId, int $userId): void
    {
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

        // Create session record
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
            \Log::error('Failed to broadcast session event: ' . $e->getMessage());
        }
        
        // Send notification to user about new login (only if email is configured)
        if (config('mail.default') !== 'log' && config('mail.mailers.smtp.host')) {
            try {
                $user = \App\Models\User::find($userId);
                $user->notify(new NewLoginNotification($userSession));
            } catch (\Exception $e) {
                \Log::error('Failed to send login notification: ' . $e->getMessage());
            }
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
     */
    protected function getLocationFromIP(string $ip): array
    {
        // Skip for local IPs
        if ($ip === '127.0.0.1' || $ip === 'localhost' || str_starts_with($ip, '192.168.')) {
            return [
                'country' => 'Local',
                'country_code' => 'LO',
                'city' => 'Localhost',
            ];
        }

        // Cache the result for 24 hours
        $cacheKey = "ip_location_{$ip}";
        
        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            try {
                // Using ipapi.co (free, no API key needed, 1000 requests/day)
                $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");
                
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
}

