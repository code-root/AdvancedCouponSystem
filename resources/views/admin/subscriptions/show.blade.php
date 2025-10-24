@extends('admin.layouts.app')

@section('title', 'Subscription Details')
@section('subtitle', 'View Subscription Information')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Subscription #{{ $subscription->id }}</h4>
                <p class="text-muted mb-0">View and manage subscription details</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Subscriptions
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i>Edit Subscription
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Subscription Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Subscription #{{ $subscription->id }}</h5>
                    <div>
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
                        <span class="badge bg-{{ $color }} fs-6">{{ ucfirst($subscription->status) }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">User Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $subscription->user->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $subscription->user->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>User ID:</strong></td>
                                <td>#{{ $subscription->user_id }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Plan Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Plan:</strong></td>
                                <td>{{ $subscription->plan->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Price:</strong></td>
                                <td>${{ number_format($subscription->plan->price ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Billing:</strong></td>
                                <td>{{ ucfirst($subscription->billing_interval ?? 'monthly') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Subscription Dates</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Start Date:</strong></td>
                                <td>{{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>End Date:</strong></td>
                                <td>{{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Trial Ends:</strong></td>
                                <td>{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cancelled At:</strong></td>
                                <td>{{ $subscription->cancelled_at ? $subscription->cancelled_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Payment Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Gateway:</strong></td>
                                <td>{{ ucfirst($subscription->gateway ?? 'Manual') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Customer ID:</strong></td>
                                <td>{{ $subscription->gateway_customer_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Subscription ID:</strong></td>
                                <td>{{ $subscription->gateway_subscription_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Paid Until:</strong></td>
                                <td>{{ $subscription->paid_until ? $subscription->paid_until->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($subscription->meta && count($subscription->meta) > 0)
                    <hr>
                    <h6 class="text-muted mb-3">Additional Information</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            @foreach($subscription->meta as $key => $value)
                                <tr>
                                    <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Actions Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn btn-outline-primary w-100">
                            <i class="ti ti-edit me-1"></i>Edit
                        </a>
                    </div>
                    
                    @if($subscription->status !== 'canceled')
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning w-100" onclick="cancelSubscription({{ $subscription->id }})">
                                <i class="ti ti-x me-1"></i>Cancel
                            </button>
                        </div>
                    @endif
                    
                    @if($subscription->status === 'trialing')
                        <div class="col-md-3">
                            <button class="btn btn-outline-success w-100" onclick="manualActivate({{ $subscription->id }})">
                                <i class="ti ti-hand-click me-1"></i>Manual Activate
                            </button>
                        </div>
                    @endif
                    
                    <div class="col-md-3">
                        <button class="btn btn-outline-info w-100" onclick="upgradeSubscription({{ $subscription->id }})">
                            <i class="ti ti-arrow-up me-1"></i>Upgrade
                        </button>
                    </div>
                    
                    <div class="col-md-3">
                        <button class="btn btn-outline-primary w-100" onclick="extendSubscription({{ $subscription->id }})">
                            <i class="ti ti-calendar-plus me-1"></i>Extend
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Days Active</span>
                    <span class="fw-semibold">
                        {{ $subscription->starts_at ? $subscription->starts_at->diffInDays(now()) : 0 }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Days Remaining</span>
                    <span class="fw-semibold">
                        {{ $subscription->ends_at ? max(0, now()->diffInDays($subscription->ends_at, false)) : 'N/A' }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Revenue</span>
                    <span class="fw-semibold text-success">
                        ${{ number_format($subscription->plan->price ?? 0, 2) }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Created</span>
                    <span class="fw-semibold">
                        {{ $subscription->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Change History -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Change History</h5>
            </div>
            <div class="card-body">
                @forelse($changeHistory ?? [] as $change)
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-shrink-0">
                            <div class="avatar-xs">
                                <span class="avatar-title bg-primary rounded-circle">
                                    <i class="ti ti-edit"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">{{ $change['action'] ?? 'Updated' }}</h6>
                            <p class="text-muted mb-1">{{ $change['description'] ?? 'No description' }}</p>
                            <small class="text-muted">{{ $change['date'] ?? 'Unknown date' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <i class="ti ti-history fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No change history available</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Payment History -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                @forelse($paymentHistory ?? [] as $payment)
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-shrink-0">
                            <div class="avatar-xs">
                                <span class="avatar-title bg-success rounded-circle">
                                    <i class="ti ti-credit-card"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">${{ number_format($payment['amount'] ?? 0, 2) }}</h6>
                            <p class="text-muted mb-1">{{ $payment['status'] ?? 'Unknown status' }}</p>
                            <small class="text-muted">{{ $payment['date'] ?? 'Unknown date' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3">
                        <i class="ti ti-credit-card fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No payment history available</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modals (same as index.blade.php) -->
@include('admin.subscriptions.partials.modals')
@vite(['resources/js/admin-subscriptions.js'])

@endsection

