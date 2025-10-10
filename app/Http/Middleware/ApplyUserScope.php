<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\UserHelper;

class ApplyUserScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply scope if user is authenticated
        if (auth()->check()) {
            $request->merge([
                'target_user_id' => UserHelper::getTargetUserId(),
                'current_user_id' => UserHelper::getCurrentUserId(),
            ]);
        }
        
        return $next($request);
    }
}
