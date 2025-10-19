@extends('admin.layouts.app')

@section('title', 'Subscriptions Management')
@section('subtitle', 'Manage User Subscriptions')

@section('admin-content')
<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_subscriptions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_subscriptions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Trial Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['trial_subscriptions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Monthly Revenue</h5>
                <h3 class="mb-0 fw-bold text-warning">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="User name or email">
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="trialing" {{ request('status') == 'trialing' ? 'selected' : '' }}>Trialing</option>
                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="plan_id" class="form-label">Plan</label>
                <select class="form-select" id="plan_id" name="plan_id">
                    <option value="">All Plans</option>
                    @foreach($plans ?? [] as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="gateway" class="form-label">Gateway</label>
                <select class="form-select" id="gateway" name="gateway">
                    <option value="">All Gateways</option>
                    <option value="stripe" {{ request('gateway') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                    <option value="paypal" {{ request('gateway') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                    <option value="manual" {{ request('gateway') == 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-refresh me-1"></i>Clear
                </a>
                <a href="{{ route('admin.subscriptions.statistics') }}" class="btn btn-outline-info">
                    <i class="ti ti-chart-bar me-1"></i>Statistics
                </a>
                <button type="button" class="btn btn-outline-success" onclick="exportSubscriptions()">
                    <i class="ti ti-download me-1"></i>Export
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Subscriptions</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Gateway</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions ?? [] as $subscription)
                        <tr>
                            <td>
                                <span class="fw-semibold">#{{ $subscription->id }}</span>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-0">{{ $subscription->user->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">{{ $subscription->user->email ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-0">{{ $subscription->plan->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">${{ number_format($subscription->plan->price ?? 0, 2) }}</small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'trialing' => 'info',
                                        'canceled' => 'warning',
                                        'expired' => 'danger',
                                        'past_due' => 'secondary'
                                    ];
                                    $color = $statusColors[$subscription->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($subscription->status) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-outline-primary">{{ ucfirst($subscription->gateway ?? 'Manual') }}</span>
                            </td>
                            <td>
                                {{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                <small class="text-muted">{{ $subscription->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                            data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.subscriptions.show', $subscription->id) }}">
                                                <i class="ti ti-eye me-2"></i>View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.subscriptions.edit', $subscription->id) }}">
                                                <i class="ti ti-edit me-2"></i>Edit
                                            </a>
                                        </li>
                                        @if($subscription->status !== 'canceled')
                                            <li>
                                                <button class="dropdown-item text-warning" onclick="cancelSubscription({{ $subscription->id }})">
                                                    <i class="ti ti-x me-2"></i>Cancel
                                                </button>
                                            </li>
                                        @endif
                                        @if($subscription->status === 'trialing')
                                            <li>
                                                <button class="dropdown-item text-success" onclick="manualActivate({{ $subscription->id }})">
                                                    <i class="ti ti-hand-click me-2"></i>Manual Activate
                                                </button>
                                            </li>
                                        @endif
                                        <li>
                                            <button class="dropdown-item text-info" onclick="upgradeSubscription({{ $subscription->id }})">
                                                <i class="ti ti-arrow-up me-2"></i>Upgrade
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item text-primary" onclick="extendSubscription({{ $subscription->id }})">
                                                <i class="ti ti-calendar-plus me-2"></i>Extend
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ti ti-inbox fs-48 mb-3"></i>
                                    <h5>No subscriptions found</h5>
                                    <p>No subscriptions match your current filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(($subscriptions ?? null) && $subscriptions->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $subscriptions->firstItem() }} to {{ $subscriptions->lastItem() }} 
                    of {{ $subscriptions->total() }} results
                </div>
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>

@include('admin.subscriptions.partials.modals')
@endsection

@push('scripts')
<script>
@include('admin.subscriptions.partials.scripts')
</script>
@endpush