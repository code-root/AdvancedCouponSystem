@props([
    'feature' => null,
    'plan' => null,
    'showPreview' => true,
    'upgradeMessage' => null,
    'subscription' => null,
    'user' => null
])

@php
    $user = $user ?? auth()->user();
    $subscription = $subscription ?? $user->activeSubscription;
    $hasAccess = true;
    $isReadOnly = false;
    
    // Check if user has access to the feature
    if ($feature) {
        if (!$subscription || $subscription->status !== 'active') {
            $hasAccess = false;
            $isReadOnly = true;
        } else {
            // Check feature-specific access
            $planFeatures = $subscription->plan->features ?? [];
            $hasAccess = match($feature) {
                'add-network' => $this->checkNetworkAccess($user, $planFeatures),
                'add-campaign' => $this->checkCampaignAccess($user, $planFeatures),
                'sync-data' => $this->checkSyncAccess($user, $planFeatures),
                'export-data' => $planFeatures['export_data'] ?? false,
                'api-access' => $planFeatures['api_access'] ?? false,
                'priority-support' => $planFeatures['priority_support'] ?? false,
                'advanced-analytics' => $planFeatures['advanced_analytics'] ?? false,
                default => true
            };
        }
    }
    
    // Check plan requirement
    if ($plan && $subscription && $subscription->plan->name !== $plan) {
        $hasAccess = false;
    }
    
    $upgradeMessage = $upgradeMessage ?? "This feature requires a subscription. Subscribe now to unlock all features.";
@endphp

@if($hasAccess)
    {{ $slot }}
@else
    @if($showPreview)
        <div class="position-relative">
            <!-- Blurred/Disabled Content -->
            <div class="opacity-50" style="filter: blur(2px); pointer-events: none;">
                {{ $slot }}
            </div>
            
            <!-- Overlay with Upgrade Prompt -->
            <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 d-flex align-items-center justify-content-center">
                <div class="bg-white rounded shadow-lg p-4 text-center" style="max-width: 400px;">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                            <i class="ti ti-lock fs-24"></i>
                        </div>
                    </div>
                    <h5 class="mb-2">Feature Locked</h5>
                    <p class="text-muted mb-3">{{ $upgradeMessage }}</p>
                    
                    @if($feature)
                        <div class="mb-3">
                            <small class="text-muted">Unlock {{ ucfirst(str_replace('-', ' ', $feature)) }}:</small>
                            <div class="mt-2">
                                @php
                                    $benefits = match($feature) {
                                        'add-network' => ['Connect unlimited networks', 'Advanced management'],
                                        'add-campaign' => ['Create unlimited campaigns', 'Advanced analytics'],
                                        'sync-data' => ['Unlimited sync', 'Real-time updates'],
                                        'export-data' => ['Export all data', 'Multiple formats'],
                                        'advanced-analytics' => ['Advanced reports', 'Custom dashboards'],
                                        default => ['Premium features', 'Priority support']
                                    };
                                @endphp
                                @foreach($benefits as $benefit)
                                    <span class="badge bg-success-subtle text-success me-1 mb-1">
                                        <i class="ti ti-check me-1"></i>{{ $benefit }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <a href="{{ route('subscription.plans') }}" class="btn btn-warning">
                        <i class="ti ti-crown me-1"></i>Upgrade Now
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Hidden Content with Upgrade Prompt -->
        <div class="text-center py-5">
            <div class="avatar-lg mx-auto mb-3">
                <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                    <i class="ti ti-lock fs-24"></i>
                </div>
            </div>
            <h5 class="mb-2">Feature Locked</h5>
            <p class="text-muted mb-3">{{ $upgradeMessage }}</p>
            
            @if($feature)
                <div class="mb-3">
                    <small class="text-muted">Unlock {{ ucfirst(str_replace('-', ' ', $feature)) }}:</small>
                    <div class="mt-2">
                        @php
                            $benefits = match($feature) {
                                'add-network' => ['Connect unlimited networks', 'Advanced management'],
                                'add-campaign' => ['Create unlimited campaigns', 'Advanced analytics'],
                                'sync-data' => ['Unlimited sync', 'Real-time updates'],
                                'export-data' => ['Export all data', 'Multiple formats'],
                                'advanced-analytics' => ['Advanced reports', 'Custom dashboards'],
                                default => ['Premium features', 'Priority support']
                            };
                        @endphp
                        @foreach($benefits as $benefit)
                            <span class="badge bg-success-subtle text-success me-1 mb-1">
                                <i class="ti ti-check me-1"></i>{{ $benefit }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <a href="{{ route('subscription.plans') }}" class="btn btn-warning">
                <i class="ti ti-crown me-1"></i>Upgrade Now
            </a>
        </div>
    @endif
@endif

@php
    // Helper methods for feature access checking
    function checkNetworkAccess($user, $planFeatures) {
        $limit = $planFeatures['networks_limit'] ?? 0;
        if ($limit === 0) return false;
        if ($limit === -1) return true;
        return $user->networks()->count() < $limit;
    }
    
    function checkCampaignAccess($user, $planFeatures) {
        $limit = $planFeatures['campaigns_limit'] ?? 0;
        if ($limit === 0) return false;
        if ($limit === -1) return true;
        return $user->campaigns()->count() < $limit;
    }
    
    function checkSyncAccess($user, $planFeatures) {
        $limit = $planFeatures['syncs_per_month'] ?? 0;
        if ($limit === 0) return false;
        if ($limit === -1) return true;
        $currentMonthSyncs = $user->syncLogs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        return $currentMonthSyncs < $limit;
    }
@endphp

