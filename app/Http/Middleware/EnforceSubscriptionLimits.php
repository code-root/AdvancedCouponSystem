<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature = null): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->hasActiveSubscription()) {
            // Check if this is a read-only operation
            if ($this->isReadOnlyOperation($request)) {
                // Allow read-only access but mark as read-only
                $request->attributes->set('read_only', true);
                return $next($request);
            }
            
            // Block write operations for non-subscribers
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active subscription required for this action. Please subscribe to continue.',
                    'subscription_required' => true,
                    'redirect_url' => route('subscription.plans')
                ], 403);
            }
            
            return redirect()->route('subscription.plans')
                ->with('error', 'Active subscription required for this action. Please subscribe to continue.');
        }
        
        // Check feature-specific limits if specified
        if ($feature && !$this->hasFeatureAccess($user, $feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Your current plan doesn't support this feature. Please upgrade your subscription.",
                    'upgrade_required' => true,
                    'redirect_url' => route('subscription.plans')
                ], 403);
            }
            
            return redirect()->route('subscription.plans')
                ->with('error', "Your current plan doesn't support this feature. Please upgrade your subscription.");
        }
        
        return $next($request);
    }
    
    /**
     * Check if the request is a read-only operation.
     */
    private function isReadOnlyOperation(Request $request): bool
    {
        $method = $request->method();
        $readOnlyMethods = ['GET', 'HEAD', 'OPTIONS'];
        
        return in_array($method, $readOnlyMethods);
    }
    
    /**
     * Check if user has access to a specific feature.
     */
    private function hasFeatureAccess($user, string $feature): bool
    {
        $subscription = $user->activeSubscription;
        
        if (!$subscription || !$subscription->plan) {
            return false;
        }
        
        $planFeatures = $subscription->plan->features ?? [];
        
        return match ($feature) {
            'add-network' => $this->checkNetworkLimit($user, $planFeatures),
            'add-campaign' => $this->checkCampaignLimit($user, $planFeatures),
            'sync-data' => $this->checkSyncLimit($user, $planFeatures),
            'export-data' => $planFeatures['export_data'] ?? false,
            'api-access' => $planFeatures['api_access'] ?? false,
            'priority-support' => $planFeatures['priority_support'] ?? false,
            'advanced-analytics' => $planFeatures['advanced_analytics'] ?? false,
            default => true,
        };
    }
    
    /**
     * Check network limit.
     */
    private function checkNetworkLimit($user, array $features): bool
    {
        $limit = $features['networks_limit'] ?? 0;
        
        if ($limit === 0) {
            return false; // No networks allowed
        }
        
        if ($limit === -1) {
            return true; // Unlimited
        }
        
        $currentCount = $user->networks()->count();
        return $currentCount < $limit;
    }
    
    /**
     * Check campaign limit.
     */
    private function checkCampaignLimit($user, array $features): bool
    {
        $limit = $features['campaigns_limit'] ?? 0;
        
        if ($limit === 0) {
            return false; // No campaigns allowed
        }
        
        if ($limit === -1) {
            return true; // Unlimited
        }
        
        $currentCount = $user->campaigns()->count();
        return $currentCount < $limit;
    }
    
    /**
     * Check sync limit.
     */
    private function checkSyncLimit($user, array $features): bool
    {
        $limit = $features['syncs_per_month'] ?? 0;
        
        if ($limit === 0) {
            return false; // No syncs allowed
        }
        
        if ($limit === -1) {
            return true; // Unlimited
        }
        
        $currentMonthSyncs = $user->syncLogs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        return $currentMonthSyncs < $limit;
    }
}

