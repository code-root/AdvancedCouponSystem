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
    @if(isset($currentSubscription) && $currentSubscription)
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
        @foreach(($plans ?? collect([])) as $plan)
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
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <span class="text-muted">
                                        <strong>{{ $plan->features['networks_limit'] === -1 ? 'Unlimited' : $plan->features['networks_limit'] }}</strong> Networks
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <span class="text-muted">
                                        <strong>{{ $plan->features['campaigns_limit'] === -1 ? 'Unlimited' : $plan->features['campaigns_limit'] }}</strong> Campaigns
                                    </span>
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <span class="text-muted">
                                        Sync every <strong>{{ $plan->features['sync_frequency'] ?? '1 day' }}</strong>
                                    </span>
                                </li>
                                @if($plan->features['syncs_per_day'] && $plan->features['syncs_per_day'] !== -1)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">
                                            <strong>{{ number_format($plan->features['syncs_per_day']) }}</strong> daily syncs
                                        </span>
                                    </li>
                                @endif
                                @if($plan->features['syncs_per_month'] && $plan->features['syncs_per_month'] !== -1)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">
                                            <strong>{{ number_format($plan->features['syncs_per_month']) }}</strong> monthly syncs
                                        </span>
                                    </li>
                                @endif
                                @if($plan->features['orders_limit'] && $plan->features['orders_limit'] !== -1)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">
                                            <strong>{{ number_format($plan->features['orders_limit']) }}</strong> monthly orders
                                        </span>
                                    </li>
                                @endif
                                @if($plan->features['revenue_limit'] && $plan->features['revenue_limit'] !== -1)
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">
                                            Revenue cap: <strong>${{ number_format($plan->features['revenue_limit']) }}</strong>
                                        </span>
                                    </li>
                                @endif
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <span class="text-muted">Data export</span>
                                </li>
                                @if($plan->features['api_access'])
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">API access</span>
                                    </li>
                                @endif
                                @if($plan->features['priority_support'])
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">Priority support</span>
                                    </li>
                                @endif
                                @if($plan->features['advanced_analytics'])
                                    <li class="mb-2">
                                        <i class="ti ti-check text-success me-2"></i>
                                        <span class="text-muted">Advanced analytics</span>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                    
                    <div class="card-footer text-center">
                        @if(isset($currentSubscription) && $currentSubscription && $currentSubscription->plan_id == $plan->id)
                            <button class="btn btn-success" disabled>
                                <i class="ti ti-check me-1"></i>Current Plan
                            </button>
                        @elseif(isset($currentSubscription) && $currentSubscription && $currentSubscription->status == 'active')
                            <button type="button" class="btn btn-primary change-plan-btn" data-bs-toggle="modal" data-bs-target="#changePlanModal" data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}">
                                <i class="ti ti-refresh me-1"></i>Change Plan
                            </button>
                        @else
                            <button type="button" class="btn btn-primary subscribe-btn" data-bs-toggle="modal" data-bs-target="#subscribeModal" data-plan-id="{{ $plan->id }}" data-plan-name="{{ $plan->name }}" data-plan-price="{{ $plan->price }}">
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
                                    @foreach(($plans ?? collect([])) as $plan)
                                        <td class="text-center">${{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }}</td>
                                    @endforeach
                                </tr>
                                @if(($plans ?? collect([]))->first() && ($plans ?? collect([]))->first()->features)
                                    @foreach(($plans ?? collect([]))->first()->features as $feature => $value)
                                        <tr>
                                            <td><strong>{{ ucfirst(str_replace('_', ' ', $feature)) }}</strong></td>
                                            @foreach(($plans ?? collect([])) as $plan)
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
                        <input type="text" class="form-control" value="{{ isset($currentSubscription) && $currentSubscription ? ($currentSubscription->plan->name ?? 'N/A') : 'N/A' }}" readonly>
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

<script>
console.log('Subscription plans page loaded');

    // Subscription Context for JavaScript
    @if(isset($subscriptionContext))
    window.subscriptionContext = @json($subscriptionContext);
    @endif
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Subscription plans page loaded');
        
        // Subscribe Modal
        const subscribeModal = document.getElementById('subscribeModal');
        if (subscribeModal) {
            subscribeModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const planId = button.getAttribute('data-plan-id');
                const planName = button.getAttribute('data-plan-name');
                
                console.log('Opening modal for plan:', planId, planName);
                
                document.getElementById('selectedPlanId').value = planId;
                document.getElementById('selectedPlanName').value = planName;
                document.getElementById('subscribeModalLabel').textContent = 'Subscribe to: ' + planName;
            });
        }

        // Change Plan Modal
        const changePlanModal = document.getElementById('changePlanModal');
        if (changePlanModal) {
            changePlanModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const planId = button.getAttribute('data-plan-id');
                const planName = button.getAttribute('data-plan-name');
                
                console.log('Opening change plan modal for:', planId, planName);
                
                document.getElementById('newPlanId').value = planId;
                document.getElementById('newPlanName').value = planName;
                document.getElementById('changePlanModalLabel').textContent = 'Change to: ' + planName;
            });
        }
        
        // Subscribe Form Handler
        const subscribeForm = document.getElementById('subscribeForm');
        if (subscribeForm) {
            subscribeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');
                
                const planId = document.getElementById('selectedPlanId').value;
                const paymentMethod = document.getElementById('paymentMethod').value;
                
                if (!planId || !paymentMethod) {
                    alert('Please fill all required fields');
                    return;
                }
                
                const submitBtn = subscribeForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Processing...';
                
                const formData = new FormData(subscribeForm);
                
                fetch('{{ route("subscription.subscribe") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Success: ' + data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Subscribe Now';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Subscribe Now';
                });
            });
        }
        
        // Change Plan Form Handler
        const changePlanForm = document.getElementById('changePlanForm');
        if (changePlanForm) {
            changePlanForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Change plan form submitted');
                
                const planId = document.getElementById('newPlanId').value;
                
                if (!planId) {
                    alert('Please select a plan');
                    return;
                }
                
                const submitBtn = changePlanForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Processing...';
                
                const formData = new FormData(changePlanForm);
                
                fetch('{{ route("subscription.change-plan") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Success: ' + data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Change Plan';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Change Plan';
                });
            });
        }
    });
</script>

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
    
    /* Modal improvements */
    .modal {
        z-index: 1055;
    }
    
    .modal-backdrop {
        z-index: 1050;
    }
    
    /* Button improvements */
    .subscribe-btn, .change-plan-btn {
        transition: all 0.3s ease;
    }
    
    .subscribe-btn:hover, .change-plan-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Loading state */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* Form validation */
    .form-control.is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    /* Ensure modal is visible */
    .modal.show {
        display: block !important;
    }
    
    /* Modal content styling */
    .modal-content {
        border: none;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .modal-header {
        border-bottom: 1px solid #e9ecef;
        border-radius: 10px 10px 0 0;
    }
    
    .modal-footer {
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 10px 10px;
    }
</style>
@endsection

