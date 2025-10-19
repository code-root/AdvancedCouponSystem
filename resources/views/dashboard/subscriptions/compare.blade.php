@extends('dashboard.layouts.vertical', ['title' => 'Compare Plans'])

@section('content')
@include('dashboard.layouts.partials.page-title', ['subtitle' => 'Subscription', 'title' => 'Compare Plans'])

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

    <!-- Back to Plans -->
    <div class="col-12 mb-4">
        <a href="{{ route('subscriptions.plans') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Plans
        </a>
    </div>

    <!-- Comparison Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Features</th>
                                @foreach($plans as $plan)
                                    <th class="text-center {{ $plan->is_popular ? 'bg-primary text-white' : '' }}">
                                        <div class="p-3">
                                            <h5 class="mb-1">{{ $plan->name }}</h5>
                                            <p class="mb-2 text-muted">{{ $plan->description }}</p>
                                            <h4 class="mb-0">
                                                ${{ number_format($plan->price, 2) }}
                                                <small class="fs-6">/{{ $plan->billing_cycle }}</small>
                                            </h4>
                                            @if($plan->is_popular)
                                                <span class="badge bg-light text-primary mt-2">Most Popular</span>
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Price -->
                            <tr>
                                <td class="fw-semibold">Price</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        <strong>${{ number_format($plan->price, 2) }}</strong>
                                        <br><small class="text-muted">/{{ $plan->billing_cycle }}</small>
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Trial -->
                            <tr>
                                <td class="fw-semibold">Free Trial</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        @if($plan->trial_days > 0)
                                            <span class="text-success">
                                                <i class="ti ti-gift me-1"></i>{{ $plan->trial_days }} days
                                            </span>
                                        @else
                                            <span class="text-muted">No trial</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Max Networks -->
                            <tr>
                                <td class="fw-semibold">Max Networks</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        <strong>{{ $plan->max_networks }}</strong>
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Sync Window -->
                            <tr>
                                <td class="fw-semibold">Sync Frequency</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        Every <strong>{{ $plan->sync_window_size }} {{ $plan->sync_window_unit }}(s)</strong>
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Daily Sync Limit -->
                            <tr>
                                <td class="fw-semibold">Daily Sync Limit</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        @if($plan->daily_sync_limit)
                                            <strong>{{ number_format($plan->daily_sync_limit) }}</strong>
                                        @else
                                            <span class="text-success">Unlimited</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Monthly Sync Limit -->
                            <tr>
                                <td class="fw-semibold">Monthly Sync Limit</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        @if($plan->monthly_sync_limit)
                                            <strong>{{ number_format($plan->monthly_sync_limit) }}</strong>
                                        @else
                                            <span class="text-success">Unlimited</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Revenue Cap -->
                            <tr>
                                <td class="fw-semibold">Revenue Cap</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        @if($plan->revenue_cap)
                                            <strong>${{ number_format($plan->revenue_cap) }}</strong>
                                        @else
                                            <span class="text-success">Unlimited</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Orders Cap -->
                            <tr>
                                <td class="fw-semibold">Orders Cap</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        @if($plan->orders_cap)
                                            <strong>{{ number_format($plan->orders_cap) }}</strong>
                                        @else
                                            <span class="text-success">Unlimited</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Support -->
                            <tr>
                                <td class="fw-semibold">Support</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        @if($plan->is_popular)
                                            <span class="text-primary">
                                                <i class="ti ti-headset me-1"></i>Priority Support
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                <i class="ti ti-mail me-1"></i>Email Support
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Action Buttons -->
                            <tr>
                                <td class="fw-semibold">Action</td>
                                @foreach($plans as $plan)
                                    <td class="text-center">
                                        <div class="d-grid gap-2">
                                            <form method="POST" action="{{ route('subscriptions.activate', $plan) }}" class="d-grid">
                                                @csrf
                                                <button class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }}" type="submit">
                                                    <i class="ti ti-credit-card me-1"></i>Subscribe
                                                </button>
                                            </form>
                                            
                                            @if($plan->trial_days > 0)
                                                <form method="POST" action="{{ route('subscriptions.trial', $plan) }}" class="d-grid">
                                                    @csrf
                                                    <button class="btn btn-outline-success btn-sm" type="submit">
                                                        <i class="ti ti-gift me-1"></i>Start Trial
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feature Highlights -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Why Choose Our Plans?</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="text-center">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    <i class="ti ti-rocket fs-24"></i>
                                </div>
                            </div>
                            <h5>Fast & Reliable</h5>
                            <p class="text-muted">Our sync technology ensures your data is always up-to-date with minimal delays.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="text-center">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                    <i class="ti ti-shield-check fs-24"></i>
                                </div>
                            </div>
                            <h5>Secure & Private</h5>
                            <p class="text-muted">Your data is protected with enterprise-grade security and privacy measures.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="text-center">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-info-subtle text-info rounded-circle">
                                    <i class="ti ti-headset fs-24"></i>
                                </div>
                            </div>
                            <h5>24/7 Support</h5>
                            <p class="text-muted">Get help whenever you need it with our dedicated support team.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Money Back Guarantee -->
<div class="row">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="avatar-lg mx-auto mb-3">
                    <div class="avatar-title bg-success-subtle text-success rounded-circle">
                        <i class="ti ti-shield-check fs-24"></i>
                    </div>
                </div>
                <h4 class="text-success">30-Day Money Back Guarantee</h4>
                <p class="text-muted mb-0">Not satisfied with your subscription? Get a full refund within 30 days, no questions asked.</p>
            </div>
        </div>
    </div>
</div>
@endsection



