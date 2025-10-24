@extends('dashboard.layouts.vertical', ['title' => 'My Subscription'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Billing', 'title' => 'My Subscription'])

    <!-- Subscription Status Card -->
    @if($subscription)
        <div class="row">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="text-white mb-1">{{ $subscription->plan->name }}</h5>
                                <p class="mb-0 opacity-75">
                                    @if($subscription->status == 'active')
                                        <i class="ti ti-check-circle me-1"></i>Active
                                    @elseif($subscription->status == 'trialing')
                                        <i class="ti ti-clock me-1"></i>Trial Period
                                    @elseif($subscription->status == 'canceled')
                                        <i class="ti ti-x-circle me-1"></i>Cancelled
                                    @else
                                        <i class="ti ti-alert-circle me-1"></i>{{ ucfirst($subscription->status) }}
                                    @endif
                                </p>
                                <p class="mb-0 opacity-75">
                                    @if($subscription->ends_at)
                                        @if($subscription->status == 'active')
                                            Expires: {{ $subscription->ends_at->format('M d, Y') }}
                                            @if(isset($stats['days_remaining']) && $stats['days_remaining'] > 0)
                                                ({{ $stats['days_remaining'] }} days remaining)
                                            @else
                                                (Expired)
                                            @endif
                                        @elseif($subscription->status == 'trialing' && $subscription->trial_ends_at)
                                            Trial ends: {{ $subscription->trial_ends_at->format('M d, Y') }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                            <div class="col-auto">
                                <div class="text-end">
                                    <h3 class="text-white mb-0">${{ number_format($subscription->plan->price, 2) }}</h3>
                                    <small class="opacity-75">/ {{ $subscription->billing_interval }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-lock me-3 fs-4"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">No Active Subscription</h5>
                            <p class="mb-0">You don't have an active subscription. Subscribe now to unlock all features.</p>
                        </div>
                        <div>
                            <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                                <i class="ti ti-crown me-1"></i>View Plans
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    @if($subscription)
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Total Paid</p>
                                <h4 class="mb-0">${{ number_format($stats['total_paid'] ?? 0, 2) }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-primary-subtle">
                                    <span class="avatar-title rounded-circle bg-primary text-primary font-size-18">
                                        <i class="ti ti-currency-dollar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Days Remaining</p>
                                <h4 class="mb-0">{{ $stats['days_remaining'] ?? 0 }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-success-subtle">
                                    <span class="avatar-title rounded-circle bg-success text-success font-size-18">
                                        <i class="ti ti-calendar"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Next Billing</p>
                                <h4 class="mb-0">
                                    @if(isset($stats['next_billing_date']) && $stats['next_billing_date'])
                                        {{ $stats['next_billing_date']->format('M d') }}
                                    @else
                                        N/A
                                    @endif
                                </h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-info-subtle">
                                    <span class="avatar-title rounded-circle bg-info text-info font-size-18">
                                        <i class="ti ti-credit-card"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-1 overflow-hidden">
                                <p class="text-truncate font-size-14 mb-2">Total Subscriptions</p>
                                <h4 class="mb-0">{{ $stats['total_subscriptions'] ?? 0 }}</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded-circle bg-warning-subtle">
                                    <span class="avatar-title rounded-circle bg-warning text-warning font-size-18">
                                        <i class="ti ti-receipt"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        @if(isset($stats['usage_stats']) && $stats['usage_stats'])
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Usage Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Networks Usage -->
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="ti ti-affiliate me-2 text-primary"></i>Networks
                                            </h6>
                                            <span class="badge bg-primary">
                                                {{ $stats['usage_stats']['networks']['used'] ?? 0 }}/{{ ($stats['usage_stats']['networks']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['networks']['limit'] ?? 0) }}
                                            </span>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: {{ min($stats['usage_stats']['networks']['percentage'] ?? 0, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ ($stats['usage_stats']['networks']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['networks']['remaining'] ?? 0) . ' remaining' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Campaigns Usage -->
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="ti ti-target me-2 text-info"></i>Campaigns
                                            </h6>
                                            <span class="badge bg-info">
                                                {{ $stats['usage_stats']['campaigns']['used'] ?? 0 }}/{{ ($stats['usage_stats']['campaigns']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['campaigns']['limit'] ?? 0) }}
                                            </span>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: {{ min($stats['usage_stats']['campaigns']['percentage'] ?? 0, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ ($stats['usage_stats']['campaigns']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['campaigns']['remaining'] ?? 0) . ' remaining' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Syncs Usage -->
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="ti ti-refresh me-2 text-warning"></i>Monthly Syncs
                                            </h6>
                                            <span class="badge bg-warning">
                                                {{ $stats['usage_stats']['syncs']['used'] ?? 0 }}/{{ ($stats['usage_stats']['syncs']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['syncs']['limit'] ?? 0) }}
                                            </span>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                 style="width: {{ min($stats['usage_stats']['syncs']['percentage'] ?? 0, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ ($stats['usage_stats']['syncs']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['syncs']['remaining'] ?? 0) . ' remaining' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Orders Usage -->
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="ti ti-shopping-cart me-2 text-success"></i>Monthly Orders
                                            </h6>
                                            <span class="badge bg-success">
                                                {{ $stats['usage_stats']['orders']['used'] ?? 0 }}/{{ ($stats['usage_stats']['orders']['limit'] ?? 0) === -1 ? '∞' : ($stats['usage_stats']['orders']['limit'] ?? 0) }}
                                            </span>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ min($stats['usage_stats']['orders']['percentage'] ?? 0, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ ($stats['usage_stats']['orders']['remaining'] ?? 0) === -1 ? 'Unlimited' : ($stats['usage_stats']['orders']['remaining'] ?? 0) . ' remaining' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Revenue Usage -->
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="ti ti-currency-dollar me-2 text-danger"></i>Monthly Revenue
                                            </h6>
                                            <span class="badge bg-danger">
                                                ${{ number_format($stats['usage_stats']['revenue']['used'] ?? 0) }}/{{ ($stats['usage_stats']['revenue']['limit'] ?? 0) === -1 ? '∞' : '$' . number_format($stats['usage_stats']['revenue']['limit'] ?? 0) }}
                                            </span>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                 style="width: {{ min($stats['usage_stats']['revenue']['percentage'] ?? 0, 100) }}%"></div>
                                        </div>
                                        <small class="text-muted">
                                            {{ ($stats['usage_stats']['revenue']['remaining'] ?? 0) === -1 ? 'Unlimited' : '$' . number_format($stats['usage_stats']['revenue']['remaining'] ?? 0) . ' remaining' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    <!-- Subscription Details -->
    @if($subscription)
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Subscription Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="text-muted">Plan Name</th>
                                        <td>{{ $subscription->plan->name }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted">Description</th>
                                        <td>{{ $subscription->plan->description ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted">Price</th>
                                        <td>${{ number_format($subscription->plan->price, 2) }} / {{ $subscription->billing_interval }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted">Status</th>
                                        <td>
                                            <span class="badge bg-{{ $subscription->status == 'active' ? 'success' : ($subscription->status == 'trialing' ? 'info' : ($subscription->status == 'canceled' ? 'danger' : 'warning')) }}">
                                                {{ ucfirst($subscription->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted">Started At</th>
                                        <td>{{ $subscription->starts_at?->format('M d, Y H:i:s') ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted">Ends At</th>
                                        <td>{{ $subscription->ends_at?->format('M d, Y H:i:s') ?? 'N/A' }}</td>
                                    </tr>
                                    @if($subscription->trial_ends_at)
                                        <tr>
                                            <th scope="row" class="text-muted">Trial Ends At</th>
                                            <td>{{ $subscription->trial_ends_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                    @endif
                                    @if($subscription->cancelled_at)
                                        <tr>
                                            <th scope="row" class="text-muted">Cancelled At</th>
                                            <td>{{ $subscription->cancelled_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th scope="row" class="text-muted">Payment Gateway</th>
                                        <td>{{ $subscription->gateway ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($subscription->status == 'active')
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal">
                                    <i class="ti ti-x me-1"></i>Cancel Subscription
                                </button>
                            @elseif($subscription->status == 'canceled')
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resumeSubscriptionModal">
                                    <i class="ti ti-refresh me-1"></i>Resume Subscription
                                </button>
                            @endif
                            
                            <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                                <i class="ti ti-crown me-1"></i>Change Plan
                            </a>
                            
                            <a href="{{ route('subscription.invoices') }}" class="btn btn-info">
                                <i class="ti ti-receipt me-1"></i>View Invoices
                            </a>
                            
                            <a href="{{ route('subscription.usage') }}" class="btn btn-warning">
                                <i class="ti ti-chart-bar me-1"></i>Usage Statistics
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Plan Features -->
                @if($subscription->plan->features)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Plan Features</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @foreach($subscription->plan->features as $feature => $value)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $feature)) }}:</strong>
                                        @if(is_bool($value))
                                            {{ $value ? 'Unlimited' : 'Not Available' }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Available Plans -->
    @if(!$subscription || $subscription->status != 'active')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Available Plans</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($plans as $plan)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 {{ $plan->is_popular ? 'border-primary' : '' }}">
                                        @if($plan->is_popular)
                                            <div class="card-header bg-primary text-white text-center">
                                                <span class="badge bg-light text-primary">Most Popular</span>
                                            </div>
                                        @endif
                                        <div class="card-body text-center">
                                            <h5 class="card-title">{{ $plan->name }}</h5>
                                            <p class="text-muted">{{ $plan->description }}</p>
                                            <h3 class="text-primary">${{ number_format($plan->price, 2) }}</h3>
                                            <small class="text-muted">/ {{ $plan->billing_cycle }}</small>
                                        </div>
                                        <div class="card-footer text-center">
                                            <a href="{{ route('subscription.plans') }}" class="btn btn-primary">
                                                Choose Plan
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Cancel Subscription Modal -->
@if($subscription && $subscription->status == 'active')
    <div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelSubscriptionModalLabel">Cancel Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cancelSubscriptionForm">
                    <div class="modal-body">
                        <p>Are you sure you want to cancel your subscription?</p>
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Reason for cancellation (optional)</label>
                            <textarea class="form-control" id="cancelReason" name="reason" rows="3" placeholder="Please let us know why you're cancelling..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Resume Subscription Modal -->
@if($subscription && $subscription->status == 'canceled')
    <div class="modal fade" id="resumeSubscriptionModal" tabindex="-1" aria-labelledby="resumeSubscriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resumeSubscriptionModalLabel">Resume Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="resumeSubscriptionForm">
                    <div class="modal-body">
                        <p>Are you sure you want to resume your subscription?</p>
                        <p class="text-muted">Your subscription will be reactivated and billing will resume.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Resume Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    // Cancel Subscription
    $('#cancelSubscriptionForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        window.ajaxHelper.post('{{ route("subscription.cancel") }}', formData, {
            loadingElement: this
        })
        .then(response => {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#cancelSubscriptionModal').modal('hide');
                location.reload();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error!', error.message || 'An error occurred.', 'error');
        });
    });

    // Resume Subscription
    $('#resumeSubscriptionForm').on('submit', function (e) {
        e.preventDefault();
        
        window.ajaxHelper.post('{{ route("subscription.resume") }}', {}, {
            loadingElement: this
        })
        .then(response => {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#resumeSubscriptionModal').modal('hide');
                location.reload();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error!', error.message || 'An error occurred.', 'error');
        });
    });
</script>
@endpush

<!-- Subscription Context for JavaScript -->
@if(isset($subscriptionContext))
<script>
window.subscriptionContext = @json($subscriptionContext);
</script>
@endif
