@props([
    'type' => 'info', // info, warning, success, danger
    'title' => null,
    'message' => null,
    'action' => null,
    'actionUrl' => null,
    'actionText' => 'Upgrade Now',
    'dismissible' => true,
    'subscription' => null
])

@php
    $user = auth()->user();
    $subscription = $subscription ?? $user->activeSubscription;
    $hasSubscription = $subscription !== null;
    $isActive = $subscription && $subscription->status === 'active';
    $isTrialing = $subscription && $subscription->status === 'trialing';
    $trialEndsIn = $isTrialing && $subscription->trial_ends_at ? now()->diffInDays($subscription->trial_ends_at, false) : 0;
    
    // Determine banner type and content based on subscription status
    if (!$hasSubscription) {
        $type = 'warning';
        $title = $title ?? 'Unlock Full Access';
        $message = $message ?? 'Subscribe now to connect networks, manage campaigns, and access all features.';
        $actionUrl = $actionUrl ?? route('subscription.plans');
    } elseif ($isTrialing && $trialEndsIn <= 3) {
        $type = 'danger';
        $title = $title ?? 'Trial Ending Soon';
        $message = $message ?? "Your trial ends in {$trialEndsIn} days. Subscribe now to continue using all features.";
        $actionUrl = $actionUrl ?? route('subscription.plans');
    } elseif ($isTrialing) {
        $type = 'info';
        $title = $title ?? 'Trial Active';
        $message = $message ?? "You're enjoying a free trial. {$trialEndsIn} days remaining.";
        $actionUrl = $actionUrl ?? route('subscription.plans');
        $actionText = 'Subscribe Now';
    } elseif ($isActive) {
        // Don't show banner for active subscribers unless explicitly requested
        return;
    } else {
        $type = 'warning';
        $title = $title ?? 'Subscription Required';
        $message = $message ?? 'Your subscription is inactive. Subscribe to continue using all features.';
        $actionUrl = $actionUrl ?? route('subscription.plans');
    }
    
    $alertClasses = [
        'info' => 'alert-info',
        'warning' => 'alert-warning', 
        'success' => 'alert-success',
        'danger' => 'alert-danger'
    ];
    
    $iconClasses = [
        'info' => 'ti ti-info-circle',
        'warning' => 'ti ti-alert-triangle',
        'success' => 'ti ti-check-circle',
        'danger' => 'ti ti-alert-circle'
    ];
@endphp

<div class="alert {{ $alertClasses[$type] }} {{ $dismissible ? 'alert-dismissible fade show' : '' }} mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="{{ $iconClasses[$type] }} me-3 fs-4"></i>
        <div class="flex-grow-1">
            @if($title)
                <h5 class="alert-heading mb-1">{{ $title }}</h5>
            @endif
            <p class="mb-0">{{ $message }}</p>
        </div>
        @if($actionUrl)
            <div class="flex-shrink-0">
                <a href="{{ $actionUrl }}" class="btn btn-{{ $type === 'danger' ? 'danger' : 'primary' }} btn-sm">
                    <i class="ti ti-crown me-1"></i>{{ $actionText }}
                </a>
            </div>
        @endif
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>

