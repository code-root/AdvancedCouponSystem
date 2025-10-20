@extends('admin.layouts.app')

@section('title', 'Subscription Statistics')
@section('subtitle', 'Analytics and Insights')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Subscription Statistics</h4>
                <p class="text-muted mb-0">Analytics and insights for subscription performance</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Subscriptions
                        </a>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="date" class="form-control" id="dateFrom" value="{{ request('date_from', now()->subMonths(6)->format('Y-m-d')) }}">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="dateTo" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" onclick="updateDateRange()">
                            <i class="ti ti-refresh me-1"></i>Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_subscriptions'] ?? 0 }}</h3>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_subscriptions'] ?? 0 }}</h3>
                <small class="text-muted">Currently active</small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Monthly Revenue</h5>
                <h3 class="mb-0 fw-bold text-warning">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</h3>
                <small class="text-muted">This month</small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Churn Rate</h5>
                <h3 class="mb-0 fw-bold text-danger">{{ $churnRate ?? 0 }}%</h3>
                <small class="text-muted">This month</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Monthly Subscriptions Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Monthly Subscriptions Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlySubscriptionsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Plan Distribution Chart -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Plan Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="planDistributionChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Additional Metrics -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Conversion Metrics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-info">{{ $conversionRate ?? 0 }}%</h4>
                        <p class="text-muted mb-0">Trial to Paid</p>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $stats['subscriptions_this_month'] ?? 0 }}</h4>
                        <p class="text-muted mb-0">New This Month</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Status Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <h6 class="text-success">{{ $stats['active_subscriptions'] ?? 0 }}</h6>
                        <small class="text-muted">Active</small>
                    </div>
                    <div class="col-3">
                        <h6 class="text-info">{{ $stats['trial_subscriptions'] ?? 0 }}</h6>
                        <small class="text-muted">Trial</small>
                    </div>
                    <div class="col-3">
                        <h6 class="text-warning">{{ $stats['cancelled_subscriptions'] ?? 0 }}</h6>
                        <small class="text-muted">Cancelled</small>
                    </div>
                    <div class="col-3">
                        <h6 class="text-danger">{{ $stats['expired_subscriptions'] ?? 0 }}</h6>
                        <small class="text-muted">Expired</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Plans Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Top Performing Plans</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Subscribers</th>
                        <th>Revenue</th>
                        <th>Avg. Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planDistribution ?? [] as $plan)
                        <tr>
                            <td>
                                <h6 class="mb-0">{{ $plan->name }}</h6>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $plan->count }}</span>
                            </td>
                            <td>
                                <strong>${{ number_format($plan->revenue, 2) }}</strong>
                            </td>
                            <td>
                                <span class="text-muted">${{ number_format($plan->revenue / max($plan->count, 1), 2) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ti ti-chart-bar fs-48 mb-3"></i>
                                    <h5>No data available</h5>
                                    <p>No plan distribution data found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Update date range function
function updateDateRange() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    if (dateFrom && dateTo) {
        const url = new URL(window.location);
        url.searchParams.set('date_from', dateFrom);
        url.searchParams.set('date_to', dateTo);
        window.location.href = url.toString();
    }
}
// Monthly Subscriptions Chart
const monthlyCtx = document.getElementById('monthlySubscriptionsChart').getContext('2d');
const monthlyData = @json($monthlyData ?? []);
const monthlyLabels = monthlyData.map(item => item.month);
const monthlyCounts = monthlyData.map(item => item.count);
const monthlyRevenue = monthlyData.map(item => item.revenue);

new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Subscriptions',
            data: monthlyCounts,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Revenue ($)',
            data: monthlyRevenue,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Plan Distribution Chart
const planCtx = document.getElementById('planDistributionChart').getContext('2d');
const planData = @json($planDistribution ?? []);
const planLabels = planData.map(item => item.name);
const planCounts = planData.map(item => item.count);

new Chart(planCtx, {
    type: 'doughnut',
    data: {
        labels: planLabels,
        datasets: [{
            data: planCounts,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
@endpush

