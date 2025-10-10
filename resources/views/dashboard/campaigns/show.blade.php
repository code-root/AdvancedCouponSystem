@extends('layouts.vertical', ['title' => 'Campaign Details'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Campaigns', 'title' => $campaign->name])

    <!-- Campaign Header -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-2">{{ $campaign->name }}</h3>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="ti ti-affiliate me-1"></i>{{ $campaign->network->display_name }}
                                </span>
                                <span class="badge bg-info-subtle text-info">
                                    <i class="ti ti-tag me-1"></i>{{ ucfirst($campaign->campaign_type) }}
                                </span>
                                @if($campaign->status === 'active')
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ti ti-check me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ti ti-pause me-1"></i>{{ ucfirst($campaign->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i> Edit Campaign
                            </a>
                            <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                    <h3 class="mb-0 fw-bold text-success">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Commission</h5>
                    <h3 class="mb-0 fw-bold text-primary">${{ number_format($stats['total_commission'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Order Value</h5>
                    <h3 class="mb-0 fw-bold text-info">${{ number_format($stats['total_order_value'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Purchases</h5>
                    <h3 class="mb-0 fw-bold">{{ $stats['total_purchases'] ?? 0 }}</h3>
                    <small class="text-success">{{ $stats['approved_purchases'] ?? 0 }} Approved</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Coupons</h5>
                    <h3 class="mb-0 fw-bold">{{ $stats['total_coupons'] ?? 0 }}</h3>
                    <small class="text-warning">{{ $stats['used_coupons'] ?? 0 }} Used</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Top Performing Coupons</h4>
                </div>
                <div class="card-body">
                    <div id="topCouponsChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Sales Distribution</h4>
                </div>
                <div class="card-body">
                    <div id="salesPieChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Coupons Performance Table -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Coupon Performance ({{ $campaign->coupons->count() }})</h4>
                </div>
                <div class="card-body">
                    @if($campaign->coupons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th class="text-end">Orders</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Avg Order</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody id="couponsTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-3">
                                            <div class="spinner-border text-primary spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-ticket-off fs-48 text-muted"></i>
                            <p class="text-muted mt-3">No coupons available for this campaign</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Campaign Info -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Campaign Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Campaign ID</label>
                        <p class="mb-0 fw-semibold">{{ $campaign->network_campaign_id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Network</label>
                        <p class="mb-0">{{ $campaign->network->display_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Type</label>
                        <p class="mb-0">{{ ucfirst($campaign->campaign_type) }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0">
                            @if($campaign->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($campaign->status === 'paused')
                                <span class="badge bg-warning">Paused</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($campaign->status) }}</span>
                            @endif
                        </p>
                    </div>
                    @if($campaign->description)
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <p class="mb-0">{{ $campaign->description }}</p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="text-muted small">Created</label>
                        <p class="mb-0">{{ $campaign->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $campaign->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('coupons.create') }}?campaign_id={{ $campaign->id }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Coupon
                        </a>
                        <a href="{{ route('purchases.index') }}?campaign_id={{ $campaign->id }}" class="btn btn-info">
                            <i class="ti ti-shopping-cart me-1"></i> View Purchases
                        </a>
                        @if($campaign->status === 'active')
                            <form action="{{ route('campaigns.update', $campaign->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="paused">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="ti ti-pause me-1"></i> Pause Campaign
                                </button>
                            </form>
                        @else
                            <form action="{{ route('campaigns.update', $campaign->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ti ti-player-play me-1"></i> Activate Campaign
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('campaigns.destroy', $campaign->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this campaign?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="ti ti-trash me-1"></i> Delete Campaign
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    
<script>
let topCouponsChart = null;
let salesPieChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadCouponPerformance();
});

function loadCouponPerformance() {
    fetch(`{{ route('campaigns.coupon-stats', $campaign->id) }}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCouponsTable(data.coupons);
            renderTopCouponsChart(data.coupons);
            renderSalesPieChart(data.coupons);
        }
    })
    .catch(error => {
        console.error('Error loading coupon performance:', error);
        document.getElementById('couponsTableBody').innerHTML = 
            '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
    });
}

function renderCouponsTable(coupons) {
    const tbody = document.getElementById('couponsTableBody');
    
    if (!coupons || coupons.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No coupon data available</td></tr>';
        return;
    }
    
    let html = '';
    coupons.forEach(coupon => {
        const revenue = parseFloat(coupon.total_revenue || 0);
        const orders = coupon.total_orders || 0;
        const avgOrder = orders > 0 ? (revenue / orders) : 0;
        
        const statusBadge = coupon.status === 'active' 
            ? '<span class="badge bg-success-subtle text-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
        
        html += `
            <tr>
                <td><code class="text-primary">${coupon.code}</code></td>
                <td>${statusBadge}</td>
                <td class="text-end fw-semibold">${orders}</td>
                <td class="text-end text-success fw-semibold">$${revenue.toFixed(2)}</td>
                <td class="text-end text-info">$${avgOrder.toFixed(2)}</td>
                <td><small class="text-muted">${new Date(coupon.created_at).toLocaleDateString()}</small></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function renderTopCouponsChart(coupons) {
    if (!coupons || coupons.length === 0) {
        document.getElementById('topCouponsChart').innerHTML = 
            '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    // Sort by revenue and get top 10
    const topCoupons = coupons
        .sort((a, b) => (b.total_revenue || 0) - (a.total_revenue || 0))
        .slice(0, 10);
    
    const codes = topCoupons.map(c => c.code);
    const revenues = topCoupons.map(c => parseFloat(c.total_revenue || 0));
    const orders = topCoupons.map(c => c.total_orders || 0);
    
    const options = {
        series: [{
            name: 'Revenue',
            data: revenues
        }, {
            name: 'Orders',
            data: orders
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        colors: ['#6ac75a', '#465dff'],
        plotOptions: {
            bar: {
                horizontal: true,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        dataLabels: {
            enabled: true,
            offsetX: -6,
            style: {
                fontSize: '12px',
                colors: ['#fff']
            }
        },
        xaxis: {
            categories: codes
        },
        yaxis: [{
            title: {
                text: 'Revenue ($)'
            }
        }],
        legend: {
            position: 'top'
        }
    };

    if (topCouponsChart) {
        topCouponsChart.destroy();
    }
    
    const container = document.querySelector("#topCouponsChart");
    if (container) {
        container.innerHTML = '';
        topCouponsChart = new ApexCharts(container, options);
        topCouponsChart.render();
    }
}

function renderSalesPieChart(coupons) {
    if (!coupons || coupons.length === 0) {
        document.getElementById('salesPieChart').innerHTML = 
            '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    // Get top 5 by revenue for pie chart
    const topCoupons = coupons
        .sort((a, b) => (b.total_revenue || 0) - (a.total_revenue || 0))
        .slice(0, 5);
    
    const codes = topCoupons.map(c => c.code);
    const revenues = topCoupons.map(c => parseFloat(c.total_revenue || 0));
    
    const options = {
        series: revenues,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: codes,
        colors: ['#6ac75a', '#465dff', '#f7b84b', '#f1556c', '#17a2b8'],
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            formatter: function(val, opts) {
                return "$" + opts.w.config.series[opts.seriesIndex].toFixed(2);
            }
        }
    };

    if (salesPieChart) {
        salesPieChart.destroy();
    }
    
    const container = document.querySelector("#salesPieChart");
    if (container) {
        container.innerHTML = '';
        salesPieChart = new ApexCharts(container, options);
        salesPieChart.render();
    }
}
</script>
@endsection

