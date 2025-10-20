<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;

class SubscriptionPromptService
{
    /**
     * Get prompt for a specific feature.
     */
    public function getPromptForFeature(string $feature, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $subscription = $user->activeSubscription;
        
        $prompts = [
            'add-network' => [
                'title' => 'Connect Networks',
                'message' => 'Subscribe to connect and manage unlimited networks.',
                'benefits' => [
                    'Connect unlimited networks',
                    'Sync data from all sources',
                    'Advanced network management',
                    'Real-time data updates'
                ],
                'cta' => 'Start Connecting',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'add-campaign' => [
                'title' => 'Create Campaigns',
                'message' => 'Subscribe to create and manage unlimited campaigns.',
                'benefits' => [
                    'Create unlimited campaigns',
                    'Advanced campaign analytics',
                    'Automated optimization',
                    'A/B testing tools'
                ],
                'cta' => 'Start Creating',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'sync-data' => [
                'title' => 'Sync Data',
                'message' => 'Subscribe to sync data in real-time.',
                'benefits' => [
                    'Unlimited data sync',
                    'Real-time updates',
                    'Custom sync schedules',
                    'Automated backups'
                ],
                'cta' => 'Start Syncing',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'export-data' => [
                'title' => 'Export Data',
                'message' => 'Subscribe to export all your data.',
                'benefits' => [
                    'Export all your data',
                    'Multiple export formats',
                    'Scheduled exports',
                    'Data backup & recovery'
                ],
                'cta' => 'Start Exporting',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'advanced-analytics' => [
                'title' => 'Advanced Analytics',
                'message' => 'Subscribe to unlock advanced analytics and insights.',
                'benefits' => [
                    'Advanced reporting',
                    'Custom dashboards',
                    'Data insights',
                    'Predictive analytics'
                ],
                'cta' => 'View Analytics',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'api-access' => [
                'title' => 'API Access',
                'message' => 'Subscribe to access our powerful API.',
                'benefits' => [
                    'Full API access',
                    'Webhook integrations',
                    'Custom integrations',
                    'Developer tools'
                ],
                'cta' => 'Get API Access',
                'urgency' => $this->getUrgencyMessage($subscription)
            ]
        ];
        
        return $prompts[$feature] ?? [
            'title' => 'Premium Feature',
            'message' => 'Subscribe to unlock this premium feature.',
            'benefits' => [
                'Unlock premium features',
                'Get priority support',
                'Access to all tools',
                'Advanced customization'
            ],
            'cta' => 'Upgrade Now',
            'urgency' => $this->getUrgencyMessage($subscription)
        ];
    }
    
    /**
     * Get prompt for a specific page.
     */
    public function getPromptForPage(string $page, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $subscription = $user->activeSubscription;
        
        $prompts = [
            'networks' => [
                'title' => 'Network Management',
                'message' => 'Subscribe to connect and manage unlimited networks with advanced features.',
                'benefits' => [
                    'Connect unlimited networks',
                    'Advanced network management',
                    'Real-time data sync',
                    'Priority support'
                ],
                'cta' => 'Start Managing Networks',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'campaigns' => [
                'title' => 'Campaign Management',
                'message' => 'Subscribe to create and manage unlimited campaigns with advanced analytics.',
                'benefits' => [
                    'Create unlimited campaigns',
                    'Advanced campaign analytics',
                    'Automated optimization',
                    'A/B testing tools'
                ],
                'cta' => 'Start Creating Campaigns',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'reports' => [
                'title' => 'Advanced Reports',
                'message' => 'Subscribe to access advanced reporting and analytics.',
                'benefits' => [
                    'Advanced reporting',
                    'Custom dashboards',
                    'Data insights',
                    'Export capabilities'
                ],
                'cta' => 'View Advanced Reports',
                'urgency' => $this->getUrgencyMessage($subscription)
            ],
            'analytics' => [
                'title' => 'Advanced Analytics',
                'message' => 'Subscribe to unlock advanced analytics and insights.',
                'benefits' => [
                    'Advanced analytics',
                    'Custom dashboards',
                    'Data insights',
                    'Predictive analytics'
                ],
                'cta' => 'View Analytics',
                'urgency' => $this->getUrgencyMessage($subscription)
            ]
        ];
        
        return $prompts[$page] ?? [
            'title' => 'Premium Access',
            'message' => 'Subscribe to unlock full access to this page.',
            'benefits' => [
                'Unlock all features',
                'Get priority support',
                'Access to all tools',
                'Advanced customization'
            ],
            'cta' => 'Upgrade Now',
            'urgency' => $this->getUrgencyMessage($subscription)
        ];
    }
    
    /**
     * Get benefits for upgrading from current plan to target plan.
     */
    public function getBenefitsForUpgrade(?Plan $currentPlan, Plan $targetPlan): array
    {
        $benefits = [];
        
        // Compare features
        $currentFeatures = $currentPlan?->features ?? [];
        $targetFeatures = $targetPlan->features ?? [];
        
        foreach ($targetFeatures as $feature => $value) {
            $currentValue = $currentFeatures[$feature] ?? 0;
            
            if ($value > $currentValue) {
                $benefits[] = $this->getFeatureBenefit($feature, $currentValue, $value);
            }
        }
        
        // Add plan-specific benefits
        $planBenefits = [
            'basic' => [
                'Basic network management',
                'Standard support',
                'Basic analytics'
            ],
            'pro' => [
                'Advanced network management',
                'Priority support',
                'Advanced analytics',
                'API access'
            ],
            'enterprise' => [
                'Unlimited everything',
                'Dedicated support',
                'Custom integrations',
                'White-label options'
            ]
        ];
        
        $planName = strtolower($targetPlan->name);
        foreach ($planBenefits as $plan => $planBenefitList) {
            if (str_contains($planName, $plan)) {
                $benefits = array_merge($benefits, $planBenefitList);
                break;
            }
        }
        
        return array_unique($benefits);
    }
    
    /**
     * Get urgency message based on subscription status.
     */
    public function getUrgencyMessage(?Subscription $subscription): ?string
    {
        if (!$subscription) {
            return 'Join thousands of users who are already growing their business!';
        }
        
        if ($subscription->status === 'trialing') {
            $daysLeft = $subscription->trial_ends_at?->diffInDays(now(), false) ?? 0;
            
            if ($daysLeft <= 1) {
                return 'Your trial ends tomorrow! Subscribe now to continue.';
            } elseif ($daysLeft <= 3) {
                return "Only {$daysLeft} days left in your trial! Subscribe now.";
            } else {
                return "You have {$daysLeft} days left in your trial.";
            }
        }
        
        if ($subscription->status === 'active') {
            $daysLeft = $subscription->ends_at?->diffInDays(now(), false) ?? 0;
            
            if ($daysLeft <= 7) {
                return 'Your subscription expires soon! Renew now to continue.';
            }
        }
        
        return null;
    }
    
    /**
     * Get social proof message.
     */
    public function getSocialProofMessage(): string
    {
        $messages = [
            'Join over 10,000+ users who trust our platform',
            'Trusted by businesses worldwide',
            'Join the community of successful entrepreneurs',
            'Over 1M+ data points processed daily',
            'Rated 4.9/5 by our users'
        ];
        
        return $messages[array_rand($messages)];
    }
    
    /**
     * Get money-back guarantee message.
     */
    public function getMoneyBackGuaranteeMessage(): string
    {
        return '30-day money-back guarantee. No questions asked.';
    }
    
    /**
     * Get feature benefit description.
     */
    private function getFeatureBenefit(string $feature, $currentValue, $targetValue): string
    {
        $descriptions = [
            'networks_limit' => function($current, $target) {
                if ($target === -1) return 'Unlimited networks';
                return "Up to {$target} networks" . ($current > 0 ? " (from {$current})" : '');
            },
            'campaigns_limit' => function($current, $target) {
                if ($target === -1) return 'Unlimited campaigns';
                return "Up to {$target} campaigns" . ($current > 0 ? " (from {$current})" : '');
            },
            'syncs_per_month' => function($current, $target) {
                if ($target === -1) return 'Unlimited syncs';
                return "{$target} syncs per month" . ($current > 0 ? " (from {$current})" : '');
            },
            'export_data' => function($current, $target) {
                return $target ? 'Data export capabilities' : 'No data export';
            },
            'api_access' => function($current, $target) {
                return $target ? 'Full API access' : 'No API access';
            },
            'priority_support' => function($current, $target) {
                return $target ? 'Priority support' : 'Standard support';
            },
            'advanced_analytics' => function($current, $target) {
                return $target ? 'Advanced analytics' : 'Basic analytics';
            }
        ];
        
        if (isset($descriptions[$feature])) {
            return $descriptions[$feature]($currentValue, $targetValue);
        }
        
        return ucfirst(str_replace('_', ' ', $feature));
    }
    
    /**
     * Generate contextual upgrade message.
     */
    public function generateUpgradeMessage(string $context, ?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $subscription = $user->activeSubscription;
        
        $baseMessage = $this->getPromptForFeature($context, $user);
        
        return [
            'title' => $baseMessage['title'],
            'message' => $baseMessage['message'],
            'benefits' => $baseMessage['benefits'],
            'cta' => $baseMessage['cta'],
            'urgency' => $baseMessage['urgency'],
            'social_proof' => $this->getSocialProofMessage(),
            'guarantee' => $this->getMoneyBackGuaranteeMessage(),
            'current_plan' => $subscription?->plan?->name ?? 'Free',
            'recommended_plan' => $this->getRecommendedPlan($context),
            'upgrade_url' => route('subscription.plans')
        ];
    }
    
    /**
     * Get recommended plan for a specific context.
     */
    private function getRecommendedPlan(string $context): string
    {
        $recommendations = [
            'add-network' => 'Pro',
            'add-campaign' => 'Pro',
            'sync-data' => 'Pro',
            'export-data' => 'Pro',
            'advanced-analytics' => 'Enterprise',
            'api-access' => 'Enterprise'
        ];
        
        return $recommendations[$context] ?? 'Pro';
    }
}

