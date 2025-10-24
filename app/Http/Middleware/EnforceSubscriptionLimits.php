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
                    'message' => '🚫 Active subscription required for this action. Subscribe now to unlock all features!',
                    'subscription_required' => true,
                    'redirect_url' => route('subscription.plans'),
                    'upgrade_prompt' => [
                        'title' => '🔒 Subscription Required',
                        'message' => 'You need an active subscription to perform this action. Choose a plan that fits your needs.',
                        'benefits' => [
                            'Connect unlimited networks',
                            'Manage campaigns and data',
                            'Export your data',
                            'Get priority support',
                            'Access advanced features'
                        ],
                        'action_text' => 'View Plans',
                        'icon' => 'ti ti-crown'
                    ]
                ], 402);
            }
            
            return redirect()->route('subscription.plans')
                ->with('error', '🚫 Active subscription required for this action. Subscribe now to unlock all features!')
                ->with('upgrade_prompt', 'You need an active subscription to perform this action. Choose a plan that fits your needs.');
        }
        
        // Check feature-specific limits if specified
        if ($feature && !$this->hasFeatureAccess($user, $feature)) {
            if ($request->expectsJson()) {
                $featureName = $this->getFeatureDisplayName($feature);
                $currentPlan = $subscription->plan->name ?? 'Free';
                
                return response()->json([
                    'success' => false,
                    'message' => "🚫 {$featureName} is not available in your current plan ({$currentPlan}). Upgrade now to unlock this feature!",
                    'upgrade_required' => true,
                    'redirect_url' => route('subscription.plans'),
                    'feature' => $feature,
                    'upgrade_prompt' => [
                        'title' => '🔒 Feature Not Available',
                        'message' => "To access {$featureName}, you need to upgrade from your current {$currentPlan} plan to a higher tier.",
                        'current_plan' => $currentPlan,
                        'benefits' => $this->getFeatureBenefits($feature),
                        'action_text' => 'Upgrade Now',
                        'icon' => 'ti ti-lock'
                    ]
                ], 403);
            }
            
            $featureName = $this->getFeatureDisplayName($feature);
            $currentPlan = $subscription->plan->name ?? 'Free';
            
            return redirect()->route('subscription.plans')
                ->with('error', "🚫 {$featureName} is not available in your current plan ({$currentPlan}). Upgrade now to unlock this feature!")
                ->with('upgrade_prompt', "To access {$featureName}, you need to upgrade from your current {$currentPlan} plan to a higher tier.");
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
     * Get display name for a specific feature.
     */
    private function getFeatureDisplayName(string $feature): string
    {
        return match ($feature) {
            'add-network' => 'Add New Networks',
            'add-campaign' => 'Add New Campaigns',
            'sync-data' => 'Data Synchronization',
            'export-data' => 'Data Export',
            'api-access' => 'API Access',
            'advanced-analytics' => 'Advanced Analytics',
            'priority-support' => 'Priority Support',
            default => 'This Feature'
        };
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
                'Advanced network management',
                'Real-time network monitoring'
            ],
            'add-campaign' => [
                'Create unlimited campaigns',
                'Advanced campaign analytics',
                'Automated campaign optimization',
                'Multi-channel campaign management'
            ],
            'sync-data' => [
                'Unlimited data synchronization',
                'Real-time data updates',
                'Custom sync schedules',
                'Automated data processing'
            ],
            'export-data' => [
                'Export all your data',
                'Multiple export formats (CSV, Excel, JSON)',
                'Scheduled exports',
                'Custom data filtering'
            ],
            'api-access' => [
                'Full API access',
                'Webhook integrations',
                'Custom integrations',
                'Rate limit increases'
            ],
            'advanced-analytics' => [
                'Advanced reporting dashboard',
                'Custom analytics views',
                'Data insights and trends',
                'Performance optimization tools'
            ],
            default => [
                'Unlock premium features',
                'Get priority support',
                'Access to all tools',
                'Enhanced user experience'
            ]
        };
    }
}

