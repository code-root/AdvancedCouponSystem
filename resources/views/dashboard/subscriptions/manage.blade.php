@extends('dashboard.layouts.vertical', ['title' => 'My Subscription'])

@section('content')
@include('dashboard.layouts.partials.page-title', ['subtitle' => 'Subscription', 'title' => 'My Subscription'])

@if(isset($subscription) && $subscription)
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted">Plan</div>
                    <div class="fw-semibold">{{ $subscription->plan->name ?? '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Status</div>
                    <span class="badge bg-primary">{{ ucfirst($subscription->status) }}</span>
                </div>
                <div class="col-md-5">
                    <div class="text-muted">Trial ends</div>
                    <div>{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d H:i') : '-' }}</div>
                </div>
            </div>
            <hr>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 bg-light-subtle h-100">
                        <div class="card-body">
                            <div class="text-muted">Daily Usage</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Sync: {{ $dailyUsage->sync_count ?? 0 }} / {{ $subscription->plan->daily_sync_limit ?? '∞' }}</div>
                                    <div class="fs-12 text-muted">Orders: {{ $dailyUsage->orders_count ?? 0 }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-12 text-muted">Revenue: {{ number_format($dailyUsage->revenue_sum ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 bg-light-subtle h-100">
                        <div class="card-body">
                            <div class="text-muted">Monthly Usage</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Sync: {{ $monthlyUsage->sync_count ?? 0 }} / {{ $subscription->plan->monthly_sync_limit ?? '∞' }}</div>
                                    <div class="fs-12 text-muted">Orders: {{ $monthlyUsage->orders_count ?? 0 }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-12 text-muted">Revenue: {{ number_format($monthlyUsage->revenue_sum ?? 0, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <form method="POST" action="{{ route('subscriptions.cancel') }}">
                    @csrf
                    <button class="btn btn-outline-danger">Cancel Subscription</button>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">No active subscription. <a href="{{ route('subscriptions.plans') }}">Choose a plan</a>.</div>
@endif
@endsection


