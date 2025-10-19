@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Admin Dashboard</h4>
                <p class="text-muted mb-0">System overview and management</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="date" class="form-control" id="dateFilter" value="{{ date('Y-m-d') }}">
                            <button class="btn btn-primary" onclick="loadDashboard()">
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
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
                <h5 class="text-muted fs-13 text-uppercase">Total Users</h5>
                <h3 class="mb-0 fw-bold text-primary" id="stat-total-users">{{ $stats['total_users'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-success" id="users-growth"><i class="ti ti-trending-up"></i> 0%</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-success" id="stat-active-subscriptions">{{ $stats['active_subscriptions'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-nowrap">Trial: {{ $stats['trial_users'] ?? 0 }}</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                <h3 class="mb-0 fw-bold text-warning" id="stat-total-revenue">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-success" id="revenue-growth"><i class="ti ti-trending-up"></i> 0%</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Networks</h5>
                <h3 class="mb-0 fw-bold text-info" id="stat-networks">{{ $stats['active_networks'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-nowrap">Active networks</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Syncs Today</h5>
                <h3 class="mb-0 fw-bold text-secondary" id="stat-syncs-today">{{ $stats['syncs_today'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-nowrap">Total sync operations</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Sessions</h5>
                <h3 class="mb-0 fw-bold" id="stat-active-sessions">{{ $stats['active_sessions'] ?? 0 }}</h3>
                <p class="mb-0 text-muted mt-2">
                    <span class="text-success"><i class="ti ti-check"></i> Online</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Revenue Trend (Last 30 Days)</h4>
            </div>
            <div class="card-body">
                <div id="revenueChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Plan Distribution</h4>
            </div>
            <div class="card-body">
                <div id="planChart" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Row -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title mb-0">Recent Users</h4>
            </div>
            <div class="card-body">
                <div id="recentUsersTable">
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
                <h4 class="card-title mb-0">Recent Subscriptions</h4>
            </div>
            <div class="card-body">
                <div id="recentSubscriptionsTable">
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

<!-- Failed Syncs and Active Sessions -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title mb-0">Failed Syncs Today</h4>
            </div>
            <div class="card-body">
                <div id="failedSyncsTable">
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
                <h4 class="card-title mb-0">Active Sessions</h4>
            </div>
            <div class="card-body">
                <div id="activeSessionsTable">
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

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>

<script>
let revenueChart = null;
let planChart = null;

window.addEventListener('load', function() {
    loadDashboard();
});

function loadDashboard() {
    const date = document.getElementById('dateFilter').value;
    
    $.ajax({
        url: '{{ route("admin.dashboard") }}',
        method: 'GET',
        data: { date: date },
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            if (data.success) {
                updateStats(data.stats);
                renderCharts(data.charts);
                renderTables(data.tables);
            }
        },
        error: function(error) {
            console.error('Error:', error);
        }
    });
}

function updateStats(stats) {
    document.getElementById('stat-total-users').textContent = stats.total_users || 0;
    document.getElementById('stat-active-subscriptions').textContent = stats.active_subscriptions || 0;
    document.getElementById('stat-total-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('stat-networks').textContent = stats.active_networks || 0;
    document.getElementById('stat-syncs-today').textContent = stats.syncs_today || 0;
    document.getElementById('stat-active-sessions').textContent = stats.active_sessions || 0;
    
    // Growth indicators
    const usersGrowth = stats.users_growth || 0;
    const revenueGrowth = stats.revenue_growth || 0;
    
    updateGrowthIndicator('users-growth', usersGrowth);
    updateGrowthIndicator('revenue-growth', revenueGrowth);
}

function updateGrowthIndicator(elementId, growth) {
    const element = document.getElementById(elementId);
    const isPositive = growth >= 0;
    const icon = isPositive ? 'ti-trending-up' : 'ti-trending-down';
    const color = isPositive ? 'text-success' : 'text-danger';
    
    element.className = color;
    element.innerHTML = `<i class="ti ${icon}"></i> ${Math.abs(growth).toFixed(1)}%`;
}

function renderCharts(charts) {
    try {
        // Revenue Trend Chart
        renderRevenueChart(charts.revenue_trend || []);
        
        // Plan Distribution Chart
        renderPlanChart(charts.plan_distribution || []);
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

function renderPlanChart(planData) {
    const container = document.querySelector("#planChart");
    if (!container) return;
    
    if (planChart) {
        planChart.destroy();
        planChart = null;
    }
    
    container.innerHTML = '';
    
    if (!planData || planData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    const labels = planData.map(p => p.plan_name);
    const counts = planData.map(p => p.subscriber_count);
    
    const options = {
        series: counts,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: labels,
        colors: ['#6ac75a', '#f7b84b', '#f1556c', '#465dff'],
        legend: {
            position: 'bottom'
        }
    };
    
    planChart = new ApexCharts(container, options);
    planChart.render();
}

function renderTables(tables) {
    renderRecentUsers(tables.recent_users || []);
    renderRecentSubscriptions(tables.recent_subscriptions || []);
    renderFailedSyncs(tables.failed_syncs || []);
    renderActiveSessions(tables.active_sessions || []);
}

function renderRecentUsers(users) {
    const container = document.getElementById('recentUsersTable');
    
    if (!users || users.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No recent users</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
    html += '<thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Status</th></tr></thead><tbody>';
    
    users.forEach(u => {
        const statusBadge = getSubscriptionStatusBadge(u.subscription_status);
        html += `
            <tr>
                <td><i class="ti ti-user text-primary me-2"></i>${u.name}</td>
                <td>${u.email}</td>
                <td>${new Date(u.created_at).toLocaleDateString()}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function renderRecentSubscriptions(subscriptions) {
    const container = document.getElementById('recentSubscriptionsTable');
    
    if (!subscriptions || subscriptions.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No recent subscriptions</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
    html += '<thead><tr><th>User</th><th>Plan</th><th>Status</th><th>Date</th></tr></thead><tbody>';
    
    subscriptions.forEach(s => {
        const statusBadge = getSubscriptionStatusBadge(s.status);
        html += `
            <tr>
                <td><i class="ti ti-user text-primary me-2"></i>${s.user_name}</td>
                <td>${s.plan_name}</td>
                <td>${statusBadge}</td>
                <td>${new Date(s.created_at).toLocaleDateString()}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function renderFailedSyncs(syncs) {
    const container = document.getElementById('failedSyncsTable');
    
    if (!syncs || syncs.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No failed syncs today</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
    html += '<thead><tr><th>User</th><th>Network</th><th>Error</th><th>Time</th></tr></thead><tbody>';
    
    syncs.forEach(s => {
        html += `
            <tr>
                <td><i class="ti ti-user text-primary me-2"></i>${s.user_name}</td>
                <td>${s.network_name}</td>
                <td><span class="text-danger">${s.error_message?.substring(0, 50)}...</span></td>
                <td>${new Date(s.started_at).toLocaleTimeString()}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function renderActiveSessions(sessions) {
    const container = document.getElementById('activeSessionsTable');
    
    if (!sessions || sessions.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No active sessions</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-sm mb-0">';
    html += '<thead><tr><th>User</th><th>IP</th><th>Device</th><th>Last Activity</th></tr></thead><tbody>';
    
    sessions.forEach(s => {
        html += `
            <tr>
                <td><i class="ti ti-user text-primary me-2"></i>${s.user_name}</td>
                <td>${s.ip_address}</td>
                <td>${s.user_agent?.substring(0, 30)}...</td>
                <td>${new Date(s.last_activity).toLocaleTimeString()}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function getSubscriptionStatusBadge(status) {
    const badges = {
        'active': '<span class="badge bg-success-subtle text-success">Active</span>',
        'trial': '<span class="badge bg-info-subtle text-info">Trial</span>',
        'expired': '<span class="badge bg-danger-subtle text-danger">Expired</span>',
        'cancelled': '<span class="badge bg-warning-subtle text-warning">Cancelled</span>',
        'no_subscription': '<span class="badge bg-secondary-subtle text-secondary">No Subscription</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}
</script>
@endsection




