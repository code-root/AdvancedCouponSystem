@extends('layouts.vertical',['title' => 'Dashboard'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Welcome, {{ auth()->user()->name }} 👋</h4>
                <p class="text-muted mb-0">This is an overview of your performance</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <select class="select2 form-control select2-multiple" id="networkFilter" multiple="multiple" style="min-width: 200px;" data-toggle="select2" data-placeholder="Choose Networks...">
                            @foreach($networks as $network)
                                <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="text" class="form-control" id="dateRange"
                                data-provider="flatpickr" 
                                data-date-format="Y-m-d"
                                data-range-date="true"
                                placeholder="Select date range">
                            <button class="btn btn-primary" onclick="loadDashboard()">
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('networks.create') }}" class="btn btn-success">
                            <i class="ti ti-plug-connected me-1"></i> Connect Network
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Statistics -->
<div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center">

    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Orders</h5>
                <h3 class="mb-0 fw-bold text-info" id="stat-orders">{{ number_format($stats['total_orders'] ?? 0, 0, '.', ',') }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-success" id="purchases-growth"><i class="ti ti-trending-up"></i> 0%</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                <h3 class="mb-0 fw-bold text-primary" id="stat-revenue">${{ number_format($stats['total_revenue'] ?? 0, 2, '.', ',') }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-success" id="revenue-growth"><i class="ti ti-trending-up"></i> 0%</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Sales Amount</h5>
                <h3 class="mb-0 fw-bold text-warning" id="stat-commission">${{ number_format($stats['total_commission'] ?? 0, 2, '.', ',') }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-nowrap">Your earnings</span>
                </p>
            </div>
        </div>
    </div>
    

    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Campaigns</h5>
                <h3 class="mb-0 fw-bold text-warning" id="stat-campaigns">{{ $stats['total_campaigns'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-nowrap">Active campaigns</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Coupons</h5>
                <h3 class="mb-0 fw-bold text-secondary" id="stat-coupons">{{ $stats['total_coupons'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-nowrap">Total coupons</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Networks</h5>
                <h3 class="mb-0 fw-bold" id="stat-networks">{{ $stats['active_networks'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-success"><i class="ti ti-check"></i> Connected</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Trend Chart -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Daily Revenue Trend</h4>
            </div>
            <div class="card-body">
                <div id="revenueChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Purchase Status</h4>
            </div>
            <div class="card-body">
                <div id="statusChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Network Comparison -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Network Performance Comparison</h4>
            </div>
            <div class="card-body">
                <div id="networkComparisonChart" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title mb-0">Top Campaigns</h4>
            </div>
            <div class="card-body">
                <div id="topCampaignsTable">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title mb-0">Top Networks</h4>
            </div>
            <div class="card-body">
                <div id="topNetworksTable">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title mb-0">Recent Purchases</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Campaign</th>
                                <th>Network</th>
                                <th>Amount</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="recentPurchasesBody">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    
<script>
let filters = {};
let revenueChart = null;
let statusChart = null;
let networkComparisonChart = null;

window.addEventListener('load', function() {
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }
    
    // Add change event listener after initialization
    $('#networkFilter').on('change', function() {
        loadDashboard();
    });
    
    loadDashboard();
});

function loadDashboard() {
    const networkIds = $('#networkFilter').val() || [];
    
    filters = {
        network_ids: networkIds.length > 0 ? networkIds : null,
    };
    
    const dateRange = document.getElementById('dateRange').value;
    if (dateRange) {
        const dates = dateRange.split(' to ');
        filters.date_from = dates[0];
        filters.date_to = dates[1] || dates[0];
    }
    
    // Prepare params with proper array handling
    const params = new URLSearchParams();
    
    Object.keys(filters).forEach(key => {
        const value = filters[key];
        
        if (value === null || value === undefined || value === '') {
            return;
        }
        
        // Handle arrays
        if (Array.isArray(value)) {
            value.forEach(item => {
                if (item) {
                    params.append(key + '[]', item);
                }
            });
        } else {
            params.append(key, value);
        }
    });
    
    $.ajax({
        url: '{{ route("dashboard") }}?' + params.toString(),
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            if (data.success) {
                updateStats(data.stats);
                renderCharts(data.stats);
                renderTables(data.stats);
                renderRecentPurchases(data.stats.recent_orders);
            }
        },
        error: function(error) {
            console.error('Error:', error);
        }
    });
}

function updateStats(stats) {
    document.getElementById('stat-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('stat-commission').textContent = '$' + parseFloat(stats.total_commission || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('stat-orders').textContent = (stats.total_orders || 0).toLocaleString('en-US');
    document.getElementById('stat-campaigns').textContent = stats.total_campaigns || 0;
    document.getElementById('stat-coupons').textContent = stats.total_coupons || 0;
    document.getElementById('stat-networks').textContent = stats.active_networks || 0;
    
    // Growth indicators
    const revenueGrowth = stats.revenue_growth || 0;
    const purchasesGrowth = stats.orders_growth || 0;
    
    updateGrowthIndicator('revenue-growth', revenueGrowth);
    updateGrowthIndicator('purchases-growth', purchasesGrowth);
}

function updateGrowthIndicator(elementId, growth) {
    const element = document.getElementById(elementId);
    const isPositive = growth >= 0;
    const icon = isPositive ? 'ti-trending-up' : 'ti-trending-down';
    const color = isPositive ? 'text-success' : 'text-danger';
    
    element.className = color;
    element.innerHTML = `<i class="ti ${icon}"></i> ${Math.abs(growth).toFixed(1)}%`;
}

function renderCharts(stats) {
    try {
        // Revenue Trend Chart
        renderRevenueChart(stats.daily_revenue || []);
        
        // Status Chart
        renderStatusChart(stats.purchase_status || []);
        
        // Network Comparison Chart
        renderNetworkComparisonChart(stats.network_comparison || []);
    } catch (error) {
        console.error('Error rendering charts:', error);
    }
}

function renderRevenueChart(dailyData) {
    const container = document.querySelector("#revenueChart");
    if (!container) return;
    
    if (revenueChart) {
        revenueChart.destroy();
        revenueChart = null;
    }
    
    container.innerHTML = '';
    
    if (!dailyData || dailyData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    const dates = dailyData.map(d => d.date);
    const revenues = dailyData.map(d => parseFloat(d.revenue || 0));
    
    const options = {
        series: [{
            name: 'Revenue',
            data: revenues
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: false }
        },
        colors: ['#6ac75a'],
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: dates
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3
            }
        }
    };
    
    revenueChart = new ApexCharts(container, options);
    revenueChart.render();
}

function renderStatusChart(statusData) {
    const container = document.querySelector("#statusChart");
    if (!container) return;
    
    if (statusChart) {
        statusChart.destroy();
        statusChart = null;
    }
    
    container.innerHTML = '';
    
    if (!statusData || statusData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    const labels = statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1));
    const counts = statusData.map(s => s.count);
    
    const options = {
        series: counts,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: labels,
        colors: ['#6ac75a', '#f7b84b', '#f1556c'],
        legend: {
            position: 'bottom'
        }
    };
    
    statusChart = new ApexCharts(container, options);
    statusChart.render();
}

function renderNetworkComparisonChart(networkData) {
    try {
        const container = document.querySelector("#networkComparisonChart");
        if (!container) return;
        
        if (networkComparisonChart) {
            networkComparisonChart.destroy();
            networkComparisonChart = null;
        }
        
        container.innerHTML = '';
        
        if (!networkData || networkData.length === 0) {
            container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
            return;
        }
        
        const networks = networkData.map(n => n.network?.display_name || 'Unknown');
        const revenues = networkData.map(n => parseFloat(n.total_revenue || 0));
        const orders = networkData.map(n => n.total_orders || 0);
    
    const options = {
        series: [{
            name: 'Revenue',
            data: revenues
        }, {
            name: 'Purchases',
            data: orders
        }],
        chart: {
            type: 'bar',
            height: 400
        },
        colors: ['#6ac75a', '#465dff'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%'
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: networks
        },
        yaxis: [{
            title: {
                text: 'Revenue ($)'
            },
            labels: {
                formatter: function(val) {
                    return '$' + val.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Purchases'
            }
        }],
        legend: {
            position: 'top'
        }
    };
    
        networkComparisonChart = new ApexCharts(container, options);
        networkComparisonChart.render();
    } catch (error) {
        console.error('Error rendering network comparison chart:', error);
        const container = document.querySelector("#networkComparisonChart");
        if (container) {
            container.innerHTML = '<p class="text-center text-danger py-5">Error loading chart data</p>';
        }
    }
}

function renderTables(stats) {
    renderTopCampaigns(stats.top_campaigns || []);
    renderTopNetworks(stats.top_networks || []);
}

function renderTopCampaigns(campaigns) {
    const container = document.getElementById('topCampaignsTable');
    
    if (!campaigns || campaigns.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No data available</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
    html += '<thead><tr><th>Campaign</th><th class="text-end">Revenue</th><th class="text-end">Orders</th></tr></thead><tbody>';
    
    campaigns.forEach(c => {
        html += `
            <tr>
                <td><i class="ti ti-speakerphone text-primary me-2"></i>${c.campaign?.name || 'Unknown'}</td>
                <td class="text-end fw-semibold text-success">$${parseFloat(c.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end">${(c.total_orders || 0).toLocaleString('en-US')}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function renderTopNetworks(networks) {
    const container = document.getElementById('topNetworksTable');
    
    if (!networks || networks.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No data available</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
    html += '<thead><tr><th>Network</th><th class="text-end">Revenue</th><th class="text-end">Commission</th></tr></thead><tbody>';
    
    networks.forEach(n => {
        html += `
            <tr>
                <td><i class="ti ti-affiliate text-info me-2"></i>${n.network?.display_name || 'Unknown'}</td>
                <td class="text-end fw-semibold text-success">$${parseFloat(n.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-end text-primary">$${parseFloat(n.total_commission || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function renderRecentPurchases(purchases) {
    const tbody = document.getElementById('recentPurchasesBody');
    
    if (!purchases || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No recent purchases</td></tr>';
        return;
    }
    
    let html = '';
    orders.forEach(p => {
        const statusBadge = getStatusBadge(p.status);
        html += `
            <tr>
                <td><code>${p.order_id || 'N/A'}</code></td>
                <td>${p.campaign?.name || 'Unknown'}</td>
                <td>${p.network?.display_name || 'Unknown'}</td>
                <td>$${parseFloat(p.order_value || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td class="text-success fw-semibold">$${parseFloat(p.revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                <td>${statusBadge}</td>
                <td>${p.order_date ? new Date(p.order_date).toLocaleDateString() : 'N/A'}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getStatusBadge(status) {
    const badges = {
        'approved': '<span class="badge bg-success-subtle text-success">Approved</span>',
        'pending': '<span class="badge bg-warning-subtle text-warning">Pending</span>',
        'rejected': '<span class="badge bg-danger-subtle text-danger">Rejected</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}
</script>
@endsection
