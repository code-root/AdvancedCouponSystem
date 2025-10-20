@props([
    'type' => 'modal', // modal, card, banner, inline
    'title' => 'Upgrade Required',
    'message' => 'This feature requires a subscription.',
    'feature' => null,
    'benefits' => [],
    'currentPlan' => null,
    'targetPlan' => null,
    'actionUrl' => null,
    'actionText' => 'Upgrade Now',
    'showClose' => true,
    'size' => 'md' // sm, md, lg, xl
])

@php
    $actionUrl = $actionUrl ?? route('subscription.plans');
    
    // Default benefits based on feature
    if (empty($benefits) && $feature) {
        $benefits = match($feature) {
            'add-network' => [
                'Connect unlimited networks',
                'Sync data from all sources', 
                'Advanced network management'
            ],
            'add-campaign' => [
                'Create unlimited campaigns',
                'Advanced campaign analytics',
                'Automated optimization'
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
    
    // Default benefits if still empty
    if (empty($benefits)) {
        $benefits = [
            'Unlock all features',
            'Connect unlimited networks',
            'Advanced analytics',
            'Priority support'
        ];
    }
@endphp

@if($type === 'modal')
    <!-- Upgrade Modal -->
    <div class="modal fade" id="upgradeModal" tabindex="-1" aria-labelledby="upgradeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-{{ $size }}">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="upgradeModalLabel">
                        <i class="ti ti-crown me-2"></i>{{ $title }}
                    </h5>
                    @if($showClose)
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    @endif
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                <i class="ti ti-lock fs-24"></i>
                            </div>
                        </div>
                        <h4 class="mb-2">{{ $title }}</h4>
                        <p class="text-muted">{{ $message }}</p>
                    </div>
                    
                    @if(!empty($benefits))
                        <div class="mb-4">
                            <h6 class="mb-3">What you'll get:</h6>
                            <ul class="list-unstyled">
                                @foreach($benefits as $benefit)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        {{ $benefit }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if($currentPlan && $targetPlan)
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Upgrade from <strong>{{ $currentPlan }}</strong> to <strong>{{ $targetPlan }}</strong></span>
                                <span class="badge bg-primary">Recommended</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <a href="{{ $actionUrl }}" class="btn btn-primary btn-lg w-100">
                        <i class="ti ti-crown me-2"></i>{{ $actionText }}
                    </a>
                </div>
            </div>
        </div>
    </div>

@elseif($type === 'card')
    <!-- Upgrade Card -->
    <div class="card border-warning">
        <div class="card-header bg-warning-subtle">
            <div class="d-flex align-items-center">
                <i class="ti ti-lock text-warning me-2"></i>
                <h6 class="mb-0 text-warning">{{ $title }}</h6>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-3">{{ $message }}</p>
            
            @if(!empty($benefits))
                <ul class="list-unstyled mb-3">
                    @foreach($benefits as $benefit)
                        <li class="mb-1">
                            <i class="ti ti-check text-success me-2"></i>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>
            @endif
            
            <a href="{{ $actionUrl }}" class="btn btn-warning">
                <i class="ti ti-crown me-1"></i>{{ $actionText }}
            </a>
        </div>
    </div>

@elseif($type === 'banner')
    <!-- Upgrade Banner -->
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="ti ti-lock me-3 fs-4"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">{{ $title }}</h6>
                <p class="mb-0">{{ $message }}</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ $actionUrl }}" class="btn btn-warning btn-sm">
                    <i class="ti ti-crown me-1"></i>{{ $actionText }}
                </a>
            </div>
        </div>
        @if($showClose)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>

@elseif($type === 'inline')
    <!-- Inline Upgrade Prompt -->
    <div class="text-center py-4">
        <div class="avatar-lg mx-auto mb-3">
            <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                <i class="ti ti-lock fs-24"></i>
            </div>
        </div>
        <h5 class="mb-2">{{ $title }}</h5>
        <p class="text-muted mb-3">{{ $message }}</p>
        
        @if(!empty($benefits))
            <div class="mb-3">
                <small class="text-muted">Unlock these features:</small>
                <div class="mt-2">
                    @foreach($benefits as $benefit)
                        <span class="badge bg-success-subtle text-success me-1 mb-1">
                            <i class="ti ti-check me-1"></i>{{ $benefit }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
        
        <a href="{{ $actionUrl }}" class="btn btn-warning">
            <i class="ti ti-crown me-1"></i>{{ $actionText }}
        </a>
    </div>
@endif

