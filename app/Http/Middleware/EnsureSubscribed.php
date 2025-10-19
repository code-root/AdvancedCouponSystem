<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use Closure;
use Illuminate\Http\Request;

class EnsureSubscribed
{
    public function __construct(private PlanLimitService $limitService) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            try {
                $this->limitService->assertSubscribed($user);
            } catch (\Throwable $e) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Subscription required'], 402);
                }
                return redirect()->route('dashboard')->with('error', 'Subscription required');
            }
        }

        return $next($request);
    }
}




