@extends('dashboard.layouts.vertical', ['title' => 'Subscription Plans'])

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Subscription Plans</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscription</a></li>
                        <li class="breadcrumb-item active">Plans</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Subscription Alert -->
    @if($currentSubscription)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-info-circle me-3 fs-4"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">Current Plan: {{ $currentSubscription->plan->name }}</h5>
                            <p class="mb-0">
                                Status: <span class="badge bg-{{ $currentSubscription->status == 'active' ? 'success' : ($currentSubscription->status == 'trialing' ? 'info' : 'warning') }}">{{ ucfirst($currentSubscription->status) }}</span>
                                @if($currentSubscription->ends_at)
                                    | Expires: {{ $currentSubscription->ends_at->format('M d, Y') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('subscription.index') }}" class="btn btn-outline-primary">
                                <i class="ti ti-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Plans Grid -->
    <div class="row">
        @foreach($plans as $plan)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 pricing-card {{ $plan->is_popular ? 'border-primary shadow-lg' : '' }}">
                    @if($plan->is_popular)
                        <div class="card-header bg-primary text-white text-center position-relative">
                            <span class="badge bg-light text-primary position-absolute top-0 start-50 translate-middle px-3 py-2">Most Popular</span>
                            <h5 class="text-white mb-0">{{ $plan->name }}</h5>
                        </div>
                    @else
                        <div class="card-header text-center">
                            <h5 class="mb-0">{{ $plan->name }}</h5>
                        </div>
                    @endif
                    
                    <div class="card-body text-center">
                        <div class="pricing-price mb-4">
                            <h2 class="text-primary mb-0">${{ number_format($plan->price, 2) }}</h2>
                            <small class="text-muted">/ {{ $plan->billing_cycle }}</small>
                        </div>
                        
                        <p class="text-muted mb-4">{{ $plan->description }}</p>
                        
                        <!-- Plan Features -->
                        @if($plan->features)
                            <ul class="list-unstyled mb-4">
                                @foreach($plan->features as $feature => $value)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">
                                            @if(is_bool($value))
                                                {{ $value ? 'Unlimited ' . ucfirst(str_replace('_', ' ', $feature)) : 'No ' . ucfirst(str_replace('_', ' ', $feature)) }}
                                            @else
                                                {{ $value }} {{ ucfirst(str_replace('_', ' ', $feature)) }}
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    
                    <div class="card-footer text-center">
                        @if($currentSubscription && $currentSubscription->plan_id == $plan->id)
                            <button class="btn btn-success" disabled>
                                <i class="ti ti-check me-1"></i>Current Plan
                            </button>
                        @elseif($currentSubscription && $currentSubscription->status == 'active')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePlanModal" data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}">
                                <i class="ti ti-refresh me-1"></i>Change Plan
                            </button>
                        @else
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subscribeModal" data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}" data-plan-price="{{ $plan->price }}">
                                <i class="ti ti-crown me-1"></i>Subscribe Now
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Plan Comparison -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Plan Comparison</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Features</th>
                                    @foreach($plans as $plan)
                                        <th class="text-center">{{ $plan->name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Price</strong></td>
                                    @foreach($plans as $plan)
                                        <td class="text-center">${{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }}</td>
                                    @endforeach
                                </tr>
                                @if($plans->first() && $plans->first()->features)
                                    @foreach($plans->first()->features as $feature => $value)
                                        <tr>
                                            <td><strong>{{ ucfirst(str_replace('_', ' ', $feature)) }}</strong></td>
                                            @foreach($plans as $plan)
                                                <td class="text-center">
                                                    @if(isset($plan->features[$feature]))
                                                        @if(is_bool($plan->features[$feature]))
                                                            <i class="ti ti-{{ $plan->features[$feature] ? 'check text-success' : 'x text-danger' }}"></i>
                                                        @else
                                                            {{ $plan->features[$feature] }}
                                                        @endif
                                                    @else
                                                        <i class="ti ti-x text-danger"></i>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscribe Modal -->
<div class="modal fade" id="subscribeModal" tabindex="-1" aria-labelledby="subscribeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subscribeModalLabel">Subscribe to Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="subscribeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Selected Plan</label>
                        <input type="text" class="form-control" id="selectedPlanName" readonly>
                        <input type="hidden" id="selectedPlanId" name="plan_id">
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="stripe">Credit Card (Stripe)</option>
                            <option value="paypal">PayPal</option>
                            <option value="manual">Manual Payment</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="couponCode" class="form-label">Coupon Code (Optional)</label>
                        <input type="text" class="form-control" id="couponCode" name="coupon_code" placeholder="Enter coupon code">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Note:</strong> Your subscription will be activated immediately after successful payment.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Subscribe Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Plan Modal -->
<div class="modal fade" id="changePlanModal" tabindex="-1" aria-labelledby="changePlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePlanModalLabel">Change Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePlanForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Plan</label>
                        <input type="text" class="form-control" value="{{ $currentSubscription->plan->name ?? 'N/A' }}" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Plan</label>
                        <input type="text" class="form-control" id="newPlanName" readonly>
                        <input type="hidden" id="newPlanId" name="plan_id">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="immediateChange" name="immediate" value="1">
                            <label class="form-check-label" for="immediateChange">
                                Change immediately (prorated billing)
                            </label>
                        </div>
                        <small class="text-muted">If unchecked, the plan will change at your next billing cycle.</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>Warning:</strong> Changing your plan may affect your billing cycle and features.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Subscribe Modal
    $('#subscribeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var planId = button.data('plan-id');
        var planName = button.data('plan-name');
        var planPrice = button.data('plan-price');
        
        $('#selectedPlanId').val(planId);
        $('#selectedPlanName').val(planName);
        $('#subscribeModalLabel').text('Subscribe to ' + planName);
    });

    // Change Plan Modal
    $('#changePlanModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var planId = button.data('plan-id');
        var planName = button.data('plan-name');
        
        $('#newPlanId').val(planId);
        $('#newPlanName').val(planName);
        $('#changePlanModalLabel').text('Change to ' + planName);
    });

    // Subscribe Form
    $('#subscribeForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        window.ajaxHelper.post('{{ route("subscription.subscribe") }}', formData, {
            loadingElement: this
        })
        .then(response => {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#subscribeModal').modal('hide');
                location.reload();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error!', error.message || 'An error occurred.', 'error');
        });
    });

    // Change Plan Form
    $('#changePlanForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        window.ajaxHelper.post('{{ route("subscription.change-plan") }}', formData, {
            loadingElement: this
        })
        .then(response => {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#changePlanModal').modal('hide');
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

@push('styles')
<style>
    .pricing-card {
        transition: transform 0.3s ease;
    }
    
    .pricing-card:hover {
        transform: translateY(-5px);
    }
    
    .pricing-card.border-primary {
        border-width: 2px !important;
    }
    
    .pricing-price h2 {
        font-size: 2.5rem;
        font-weight: 700;
    }
</style>
@endpush

<script>
// Subscription Context for JavaScript
@if(isset($subscriptionContext))
window.subscriptionContext = @json($subscriptionContext);
@endif
</script>

