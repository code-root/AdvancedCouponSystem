@extends('dashboard.layouts.vertical', ['title' => 'Compare Plans'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Billing', 'title' => 'Compare Plans'])

    <!-- Subscription Banner -->
    <x-subscription-banner />

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-scale me-2"></i>Plan Comparison
                    </h4>
                    <p class="text-muted mb-0">Compare all available plans and choose the one that fits your needs.</p>
                </div>
                <div class="card-body">
                    @if(isset($plans) && $plans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Features</th>
                                        @foreach($plans as $plan)
                                            <th class="text-center">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h5 class="mb-1">{{ $plan->name }}</h5>
                                                    <div class="mb-2">
                                                        <span class="h3 text-primary mb-0">${{ number_format($plan->price, 2) }}</span>
                                                        <small class="text-muted">/{{ $plan->billing_cycle }}</small>
                                                    </div>
                                                    @if($plan->is_popular)
                                                        <span class="badge bg-primary">Most Popular</span>
                                                    @endif
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Networks -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-affiliate me-2 text-primary"></i>Networks
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(($plan->features['networks_limit'] ?? 0) === -1)
                                                    <span class="text-success fw-bold">Unlimited</span>
                                                @elseif(($plan->features['networks_limit'] ?? 0) === 0)
                                                    <span class="text-muted">None</span>
                                                @else
                                                    <span class="fw-medium">{{ $plan->features['networks_limit'] }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Campaigns -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-target me-2 text-info"></i>Campaigns
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(($plan->features['campaigns_limit'] ?? 0) === -1)
                                                    <span class="text-success fw-bold">Unlimited</span>
                                                @elseif(($plan->features['campaigns_limit'] ?? 0) === 0)
                                                    <span class="text-muted">None</span>
                                                @else
                                                    <span class="fw-medium">{{ $plan->features['campaigns_limit'] }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Sync Frequency -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-clock me-2 text-warning"></i>Sync Frequency
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                <span class="fw-medium">{{ $plan->features['sync_frequency'] ?? '1 day' }}</span>
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Daily Sync Limit -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-refresh me-2 text-info"></i>Daily Sync Limit
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(($plan->features['syncs_per_day'] ?? 0) === -1)
                                                    <span class="text-success fw-bold">Unlimited</span>
                                                @elseif(($plan->features['syncs_per_day'] ?? 0) === 0)
                                                    <span class="text-muted">None</span>
                                                @else
                                                    <span class="fw-medium">{{ number_format($plan->features['syncs_per_day']) }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Monthly Sync Limit -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-calendar me-2 text-warning"></i>Monthly Sync Limit
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(($plan->features['syncs_per_month'] ?? 0) === -1)
                                                    <span class="text-success fw-bold">Unlimited</span>
                                                @elseif(($plan->features['syncs_per_month'] ?? 0) === 0)
                                                    <span class="text-muted">None</span>
                                                @else
                                                    <span class="fw-medium">{{ number_format($plan->features['syncs_per_month']) }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Orders Limit -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-shopping-cart me-2 text-success"></i>Monthly Orders Limit
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(($plan->features['orders_limit'] ?? 0) === -1)
                                                    <span class="text-success fw-bold">Unlimited</span>
                                                @elseif(($plan->features['orders_limit'] ?? 0) === 0)
                                                    <span class="text-muted">None</span>
                                                @else
                                                    <span class="fw-medium">{{ number_format($plan->features['orders_limit']) }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Revenue Limit -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-currency-dollar me-2 text-success"></i>Monthly Revenue Limit
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if(($plan->features['revenue_limit'] ?? 0) === -1)
                                                    <span class="text-success fw-bold">Unlimited</span>
                                                @elseif(($plan->features['revenue_limit'] ?? 0) === 0)
                                                    <span class="text-muted">None</span>
                                                @else
                                                    <span class="fw-medium">${{ number_format($plan->features['revenue_limit']) }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Data Export -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-file-export me-2 text-success"></i>Data Export
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if($plan->features['export_data'] ?? false)
                                                    <i class="ti ti-check text-success fs-5"></i>
                                                @else
                                                    <i class="ti ti-x text-muted fs-5"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- API Access -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-code me-2 text-danger"></i>API Access
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if($plan->features['api_access'] ?? false)
                                                    <i class="ti ti-check text-success fs-5"></i>
                                                @else
                                                    <i class="ti ti-x text-muted fs-5"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Advanced Analytics -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-chart-line me-2 text-purple"></i>Advanced Analytics
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if($plan->features['advanced_analytics'] ?? false)
                                                    <i class="ti ti-check text-success fs-5"></i>
                                                @else
                                                    <i class="ti ti-x text-muted fs-5"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Priority Support -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-headset me-2 text-info"></i>Priority Support
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if($plan->features['priority_support'] ?? false)
                                                    <i class="ti ti-check text-success fs-5"></i>
                                                @else
                                                    <i class="ti ti-x text-muted fs-5"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Trial Days -->
                                    <tr>
                                        <td class="fw-medium">
                                            <i class="ti ti-gift me-2 text-warning"></i>Free Trial
                                        </td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                @if($plan->trial_days > 0)
                                                    <span class="text-success fw-bold">{{ $plan->trial_days }} days</span>
                                                @else
                                                    <span class="text-muted">No trial</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>

                                    <!-- Action Buttons -->
                                    <tr class="table-light">
                                        <td class="fw-medium">Choose Plan</td>
                                        @foreach($plans as $plan)
                                            <td class="text-center">
                                                <div class="d-grid gap-2">
                                                    @if(isset($subscription) && $subscription->plan_id === $plan->id)
                                                        <button class="btn btn-success btn-sm" disabled>
                                                            <i class="ti ti-check me-1"></i>Current Plan
                                                        </button>
                                                    @else
                                                        <a href="{{ route('subscription.plans') }}" class="btn btn-{{ $plan->is_popular ? 'primary' : 'outline-primary' }} btn-sm">
                                                            <i class="ti ti-crown me-1"></i>Choose Plan
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h5 class="card-title text-info">
                                            <i class="ti ti-shield-check me-2"></i>Money-Back Guarantee
                                        </h5>
                                        <p class="card-text">Not satisfied? Get a full refund within 30 days, no questions asked.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">
                                            <i class="ti ti-headset me-2"></i>24/7 Support
                                        </h5>
                                        <p class="card-text">Get help whenever you need it with our dedicated support team.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h4 class="mb-3">Frequently Asked Questions</h4>
                                <div class="accordion" id="faqAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq1">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                                Can I change my plan anytime?
                                            </button>
                                        </h2>
                                        <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we'll prorate any billing differences.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq2">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                                What happens to my data if I cancel?
                                            </button>
                                        </h2>
                                        <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Your data is safe! You can export all your data before canceling, and we keep it for 30 days in case you want to reactivate your subscription.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq3">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                                Do you offer custom plans for large teams?
                                            </button>
                                        </h2>
                                        <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Absolutely! Contact our sales team for custom enterprise plans with dedicated support, custom integrations, and volume discounts.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                    <i class="ti ti-alert-triangle fs-24"></i>
                                </div>
                            </div>
                            <h5 class="mb-2">No Plans Available</h5>
                            <p class="text-muted">There are currently no subscription plans available. Please check back later.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Context for JavaScript -->
    @if(isset($subscriptionContext))
    <script>
    window.subscriptionContext = @json($subscriptionContext);
    </script>
    @endif
@endsection

