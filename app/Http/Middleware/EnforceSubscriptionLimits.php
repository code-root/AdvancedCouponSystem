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
        
        if (!$user) {
            return $next($request);
        }
        
        $hasActiveSubscription = $user->hasActiveSubscription();
        $subscription = $user->activeSubscription;
        
        // Always allow read-only operations (GET, HEAD, OPTIONS)
        if ($this->isReadOnlyOperation($request)) {
            // Mark as read-only if no active subscription
            if (!$hasActiveSubscription) {
                $request->attributes->set('read_only', true);
                $request->attributes->set('subscription_status', 'none');
            } else {
                $request->attributes->set('read_only', false);
                $request->attributes->set('subscription_status', $subscription->status);
            }
            return $next($request);
        }
        
        // For write operations, check subscription
        if (!$hasActiveSubscription) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active subscription required for this action. Subscribe now to unlock all features!',
                    'subscription_required' => true,
                    'redirect_url' => route('subscription.plans'),
                    'upgrade_prompt' => [
                        'title' => 'Unlock Full Access',
                        'message' => 'Subscribe to start managing your networks, campaigns, and data.',
                        'benefits' => [
                            'Connect unlimited networks',
                            'Manage campaigns',
                            'Export data',
                            'Priority support'
                        ]
                    ]
                ], 402);
            }
            
            return redirect()->route('subscription.plans')
                ->with('error', 'Active subscription required for this action. Subscribe now to unlock all features!')
                ->with('upgrade_prompt', 'Subscribe to start managing your networks, campaigns, and data.');
        }
        
        // Check feature-specific limits if specified
        if ($feature && !$this->hasFeatureAccess($user, $feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Your current plan doesn't support this feature. Upgrade to unlock more capabilities!",
                    'upgrade_required' => true,
                    'redirect_url' => route('subscription.plans'),
                    'feature' => $feature,
                    'upgrade_prompt' => [
                        'title' => 'Upgrade Required',
                        'message' => "This feature requires a higher plan. Upgrade now to unlock {$feature}.",
                        'current_plan' => $subscription->plan->name ?? 'Free',
                        'benefits' => $this->getFeatureBenefits($feature)
                    ]
                ], 403);
            }
            
            return redirect()->route('subscription.plans')
                ->with('error', "Your current plan doesn't support this feature. Upgrade to unlock more capabilities!")
                ->with('upgrade_prompt', "This feature requires a higher plan. Upgrade now to unlock {$feature}.");
        }
        
        // Mark as full access for active subscribers
        $request->attributes->set('read_only', false);
        $request->attributes->set('subscription_status', $subscription->status);
        
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
    
    /**
     * Get benefits for a specific feature.
     */
    private function getFeatureBenefits(string $feature): array
    {
        return match ($feature) {
            'add-network' => [
                'Connect unlimited networks',
                'Sync data from all sources',
                'Advanced network management'
            ],
            'add-campaign' => [
                'Create unlimited campaigns',
                'Advanced campaign analytics',
                'Automated campaign optimization'
            ],
            'sync-data' => [
                'Unlimited data sync',
                'Real-time updates',
                'Custom sync schedules'
            ],
            'export-data' => [
                'Export all your data',
                'Multiple export formats',
                'Scheduled exports'
            ],
            'api-access' => [
                'Full API access',
                'Webhook integrations',
                'Custom integrations'
            ],
            'advanced-analytics' => [
                'Advanced reporting',
                'Custom dashboards',
                'Data insights'
            ],
            default => [
                'Unlock premium features',
                'Get priority support',
                'Access to all tools'
            ]
        };
    }
}

