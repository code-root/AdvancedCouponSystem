@extends('dashboard.layouts.vertical', ['title' => 'Choose a Plan'])

@section('content')
@include('dashboard.layouts.partials.page-title', ['subtitle' => 'Subscription', 'title' => 'Choose a Plan'])

<div class="row">
    @if(session('success'))
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Plan Comparison Link -->
    <div class="col-12 mb-4">
        <div class="text-center">
            <a href="{{ route('subscriptions.compare') }}" class="btn btn-outline-primary">
                <i class="ti ti-table me-1"></i>Compare All Plans
            </a>
        </div>
    </div>

    @foreach($plans as $index => $plan)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card pricing-card h-100 {{ $plan->is_popular ? 'border-primary shadow-lg' : '' }}">
                @if($plan->is_popular)
                    <div class="card-header bg-primary text-white text-center">
                        <span class="badge bg-light text-primary fs-6">Most Popular</span>
                    </div>
                @endif
                
                <div class="card-body d-flex flex-column p-4">
                    <!-- Plan Header -->
                    <div class="text-center mb-4">
                        <h4 class="card-title mb-2">{{ $plan->name }}</h4>
                        <p class="text-muted mb-3">{{ $plan->description }}</p>
                        
                        <!-- Pricing -->
                        <div class="mb-3">
                            <h2 class="fw-bold text-primary mb-0">
                                ${{ number_format($plan->price, 2) }}
                                <small class="fs-6 text-muted">/{{ $plan->billing_cycle }}</small>
                            </h2>
                            @if($plan->trial_days > 0)
                                <p class="text-success mb-0">
                                    <i class="ti ti-gift me-1"></i>{{ $plan->trial_days }} days free trial
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Features List -->
                    <div class="mb-4">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                <strong>{{ $plan->max_networks }}</strong> Networks
                            </li>
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                Sync every <strong>{{ $plan->sync_window_size }} {{ $plan->sync_window_unit }}(s)</strong>
                            </li>
                            @if($plan->daily_sync_limit)
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <strong>{{ number_format($plan->daily_sync_limit) }}</strong> daily syncs
                                </li>
                            @endif
                            @if($plan->monthly_sync_limit)
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    <strong>{{ number_format($plan->monthly_sync_limit) }}</strong> monthly syncs
                                </li>
                            @endif
                            @if($plan->revenue_cap)
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Revenue cap: <strong>${{ number_format($plan->revenue_cap) }}</strong>
                                </li>
                            @endif
                            @if($plan->orders_cap)
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Orders cap: <strong>{{ number_format($plan->orders_cap) }}</strong>
                                </li>
                            @endif
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                Email support
                            </li>
                            @if($plan->is_popular)
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Priority support
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Action Form -->
                    <div class="mt-auto">
                        <form method="POST" action="{{ route('subscriptions.activate', $plan) }}" class="plan-form">
                            @csrf
                            
                            <!-- Coupon Input -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="coupon" class="form-control" placeholder="Coupon code (optional)" id="coupon-{{ $plan->id }}">
                                    <button class="btn btn-outline-secondary" type="button" onclick="applyCoupon({{ $plan->id }})">
                                        <i class="ti ti-tag"></i>
                                    </button>
                                </div>
                                <div id="coupon-result-{{ $plan->id }}" class="mt-2"></div>
                            </div>

                            <!-- Price Display -->
                            <div class="text-center mb-3">
                                <div id="price-display-{{ $plan->id }}">
                                    <span class="fs-5 fw-bold text-primary">${{ number_format($plan->price, 2) }}</span>
                                    <small class="text-muted">/{{ $plan->billing_cycle }}</small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }} btn-lg" type="submit">
                                    <i class="ti ti-credit-card me-1"></i>Subscribe Now
                                </button>
                                
                                @if($plan->trial_days > 0)
                                    <form method="POST" action="{{ route('subscriptions.trial', $plan) }}" class="d-grid">
                                        @csrf
                                        <button class="btn btn-outline-success" type="submit">
                                            <i class="ti ti-gift me-1"></i>Start {{ $plan->trial_days }}-Day Trial
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- FAQ Section -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Frequently Asked Questions</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Can I change my plan later?</h6>
                        <p class="text-muted">Yes, you can upgrade or downgrade your plan at any time. Changes will be prorated.</p>
                        
                        <h6>What happens after the trial?</h6>
                        <p class="text-muted">After your trial period, your subscription will automatically continue unless you cancel.</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Do you offer refunds?</h6>
                        <p class="text-muted">We offer a 30-day money-back guarantee for all paid subscriptions.</p>
                        
                        <h6>Can I use coupons?</h6>
                        <p class="text-muted">Yes, you can apply coupon codes during checkout to get discounts on your subscription.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function applyCoupon(planId) {
    const couponInput = document.getElementById(`coupon-${planId}`);
    const couponResult = document.getElementById(`coupon-result-${planId}`);
    const priceDisplay = document.getElementById(`price-display-${planId}`);
    const coupon = couponInput.value.trim();
    
    if (!coupon) {
        couponResult.innerHTML = '<div class="text-warning"><i class="ti ti-alert-circle me-1"></i>Please enter a coupon code</div>';
        return;
    }
    
    // Show loading
    couponResult.innerHTML = '<div class="text-info"><i class="ti ti-loader me-1"></i>Validating coupon...</div>';
    
    // Simulate API call (replace with actual API call)
    fetch('/api/validate-coupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            coupon: coupon,
            plan_id: planId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            const discount = data.discount;
            const originalPrice = {{ $plans->first()->price ?? 0 }};
            const discountedPrice = originalPrice - (originalPrice * discount / 100);
            
            couponResult.innerHTML = `<div class="text-success"><i class="ti ti-check-circle me-1"></i>Coupon applied! ${discount}% discount</div>`;
            priceDisplay.innerHTML = `
                <span class="text-decoration-line-through text-muted me-2">$${originalPrice.toFixed(2)}</span>
                <span class="fs-5 fw-bold text-success">$${discountedPrice.toFixed(2)}</span>
                <small class="text-muted">/${data.billing_cycle}</small>
            `;
        } else {
            couponResult.innerHTML = `<div class="text-danger"><i class="ti ti-x-circle me-1"></i>${data.message || 'Invalid coupon code'}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        couponResult.innerHTML = '<div class="text-danger"><i class="ti ti-x-circle me-1"></i>Error validating coupon</div>';
    });
}

// Auto-apply coupon on Enter key
document.addEventListener('DOMContentLoaded', function() {
    const couponInputs = document.querySelectorAll('input[name="coupon"]');
    couponInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const planId = this.id.split('-')[1];
                applyCoupon(planId);
            }
        });
    });
});
</script>
@endsection


