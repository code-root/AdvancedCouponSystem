@extends('dashboard.layouts.vertical', ['title' => 'Usage Statistics'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Subscription', 'title' => 'Usage Statistics'])

    <!-- Usage Overview -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Usage Overview</h4>
                    <p class="text-muted mb-0">Monitor your subscription usage and limits</p>
                </div>
                <div class="card-body">
                    @if($subscription)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                            <i class="ti ti-credit-card"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $subscription->plan->name }}</h6>
                                        <p class="text-muted mb-0 small">
                                            @if($subscription->status === 'active')
                                                <span class="text-success">Active</span>
                                            @elseif($subscription->status === 'trialing')
                                                <span class="text-info">Trial</span>
                                            @else
                                                <span class="text-warning">{{ ucfirst($subscription->status) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-end">
                                    @if($subscription->ends_at)
                                        <p class="text-muted mb-1">Expires</p>
                                        <h6 class="mb-0">{{ $subscription->ends_at->format('M d, Y') }}</h6>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-circle me-2"></i>
                            No active subscription found. <a href="{{ route('subscription.plans') }}" class="alert-link">Choose a plan</a> to start using our services.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    @if($subscription && isset($stats['usage_stats']) && $stats['usage_stats'])
    <div class="row">
        <!-- Networks Usage -->
        <div class="col-lg-6 col-md-12 mb-4" id="networks">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-affiliate me-2 text-primary"></i>Networks
                        </h6>
                        <span class="badge bg-primary">
                            {{ $stats['usage_stats']['networks']['used'] ?? 0 }}/{{ ($stats['usage_stats']['networks']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['networks']['limit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ min($stats['usage_stats']['networks']['percentage'] ?? 0, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            {{ ($stats['usage_stats']['networks']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['networks']['remaining'] ?? 0) . ' remaining' }}
                        </small>
                        <small class="text-muted">
                            {{ number_format($stats['usage_stats']['networks']['percentage'] ?? 0, 1) }}% used
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaigns Usage -->
        <div class="col-lg-6 col-md-12 mb-4" id="campaigns">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-target me-2 text-info"></i>Campaigns
                        </h6>
                        <span class="badge bg-info">
                            {{ $stats['usage_stats']['campaigns']['used'] ?? 0 }}/{{ ($stats['usage_stats']['campaigns']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['campaigns']['limit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: {{ min($stats['usage_stats']['campaigns']['percentage'] ?? 0, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            {{ ($stats['usage_stats']['campaigns']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['campaigns']['remaining'] ?? 0) . ' remaining' }}
                        </small>
                        <small class="text-muted">
                            {{ number_format($stats['usage_stats']['campaigns']['percentage'] ?? 0, 1) }}% used
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sync Usage -->
        <div class="col-lg-6 col-md-12 mb-4" id="syncs">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-refresh me-2 text-warning"></i>Monthly Syncs
                        </h6>
                        <span class="badge bg-warning">
                            {{ $stats['usage_stats']['syncs']['used'] ?? 0 }}/{{ ($stats['usage_stats']['syncs']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['syncs']['limit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" 
                             style="width: {{ min($stats['usage_stats']['syncs']['percentage'] ?? 0, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            {{ ($stats['usage_stats']['syncs']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['syncs']['remaining'] ?? 0) . ' remaining' }}
                        </small>
                        <small class="text-muted">
                            {{ number_format($stats['usage_stats']['syncs']['percentage'] ?? 0, 1) }}% used
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Usage -->
        <div class="col-lg-6 col-md-12 mb-4" id="orders">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-shopping-cart me-2 text-success"></i>Monthly Orders
                        </h6>
                        <span class="badge bg-success">
                            {{ $stats['usage_stats']['orders']['used'] ?? 0 }}/{{ ($stats['usage_stats']['orders']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['orders']['limit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ min($stats['usage_stats']['orders']['percentage'] ?? 0, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            {{ ($stats['usage_stats']['orders']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['orders']['remaining'] ?? 0) . ' remaining' }}
                        </small>
                        <small class="text-muted">
                            {{ number_format($stats['usage_stats']['orders']['percentage'] ?? 0, 1) }}% used
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Usage -->
        <div class="col-12 mb-4" id="revenue">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-currency-dollar me-2 text-danger"></i>Monthly Revenue
                        </h6>
                        <span class="badge bg-danger">
                            ${{ number_format($stats['usage_stats']['revenue']['used'] ?? 0) }}/{{ ($stats['usage_stats']['revenue']['limit'] ?? 0) === -1 ? '∞' : '$' . number_format($stats['usage_stats']['revenue']['limit'] ?? 0) }}
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: {{ min($stats['usage_stats']['revenue']['percentage'] ?? 0, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">
                            {{ ($stats['usage_stats']['revenue']['remaining'] ?? 0) === -1 ? 'Unlimited' : '$' . number_format($stats['usage_stats']['revenue']['remaining'] ?? 0) . ' remaining' }}
                        </small>
                        <small class="text-muted">
                            {{ number_format($stats['usage_stats']['revenue']['percentage'] ?? 0, 1) }}% used
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Usage History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Usage History</h4>
                    <p class="text-muted mb-0">Track your usage over time</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-primary mb-1">Today</h5>
                                <p class="text-muted mb-0">Sync: 0/100</p>
                                <p class="text-muted mb-0">Orders: 0</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-info mb-1">This Week</h5>
                                <p class="text-muted mb-0">Sync: 0/700</p>
                                <p class="text-muted mb-0">Orders: 0</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-warning mb-1">This Month</h5>
                                <p class="text-muted mb-0">Sync: 0/2000</p>
                                <p class="text-muted mb-0">Orders: 0</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h5 class="text-success mb-1">Revenue</h5>
                                <p class="text-muted mb-0">$0.00</p>
                                <p class="text-muted mb-0">This month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('subscription.index') }}" class="btn btn-outline-primary">
                            <i class="ti ti-credit-card me-1"></i>Subscription Details
                        </a>
                        <a href="{{ route('subscription.plans') }}" class="btn btn-outline-info">
                            <i class="ti ti-crown me-1"></i>Upgrade Plan
                        </a>
                        <a href="{{ route('subscription.invoices') }}" class="btn btn-outline-success">
                            <i class="ti ti-receipt me-1"></i>View Invoices
                        </a>
                        <button class="btn btn-outline-warning" onclick="refreshUsage()">
                            <i class="ti ti-refresh me-1"></i>Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function refreshUsage() {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Refreshing...';
    button.disabled = true;
    
    // Simulate refresh
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        location.reload();
    }, 1000);
}

// Auto-refresh usage data every 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        fetch('{{ route("subscription.usage") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateUsageDisplay(data.usage);
            }
        })
        .catch(error => {
            console.warn('Failed to refresh usage data:', error);
        });
    }
}, 300000); // 5 minutes

function updateUsageDisplay(usage) {
    // Update progress bars and percentages
    Object.keys(usage).forEach(key => {
        const element = document.querySelector(`[data-usage-type="${key}"]`);
        if (element) {
            const percentage = usage[key].percentage || 0;
            element.style.width = `${Math.min(percentage, 100)}%`;
        }
    });
}
</script>
@endpush

