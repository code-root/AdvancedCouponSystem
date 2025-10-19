<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdminRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limit = '60,1'): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) explode(',', $limit)[0];
        $decayMinutes = (int) explode(',', $limit)[1];

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again in ' . $seconds . ' seconds.',
                    'retry_after' => $seconds
                ], 429);
            }

            return response()->view('errors.429', [
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $maxAttempts));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds(RateLimiter::availableIn($key))->timestamp);

        return $response;
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $adminId = auth()->guard('admin')->id();
        $route = $request->route()?->getName() ?? $request->path();
        
        return 'admin_rate_limit:' . $adminId . ':' . $route;
    }
}

