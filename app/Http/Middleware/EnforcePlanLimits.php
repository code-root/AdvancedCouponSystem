<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use Closure;
use Illuminate\Http\Request;

class EnforcePlanLimits
{
    public function __construct(private PlanLimitService $limitService) {}

    public function handle(Request $request, Closure $next, string $action)
    {
        $user = $request->user();
        if ($user) {
            try {
                if ($action === 'add-network') {
                    $this->limitService->assertCanAddNetwork($user);
                } elseif ($action === 'sync') {
                    $this->limitService->assertCanSync($user);
                }
            } catch (\Throwable $e) {
                $message = $e->getMessage();
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 429);
                }
                return redirect()->back()->with('error', $message);
            }
        }
        return $next($request);
    }
}




