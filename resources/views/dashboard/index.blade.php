@extends('dashboard.layouts.main')

@section('title', 'Dashboard')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [breadcrumb] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="ti ti-home me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Overview</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Welcome back, {{ auth()->user()->name }}!</h4>
                        <p class="text-muted mb-0">Here's what's happening with your coupon campaigns today.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshData()" id="refresh-btn">
                            <i class="ti ti-refresh me-1"></i> Refresh
                        </button>
                        <button class="btn btn-primary" onclick="exportReport()">
                            <i class="ti ti-download me-1"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Stats Cards -->
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle rounded">
                                        <div class="avatar-title bg-primary-subtle text-primary fs-22">
                                            <i class="ti ti-building-store"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Connected Brokers</p>
                                    <h4 class="mb-0"><span class="counter-value" data-target="6">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-success-subtle rounded">
                                        <div class="avatar-title bg-success-subtle text-success fs-22">
                                            <i class="ti ti-target"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Active Campaigns</p>
                                    <h4 class="mb-0"><span class="counter-value" data-target="24">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-info-subtle rounded">
                                        <div class="avatar-title bg-info-subtle text-info fs-22">
                                            <i class="ti ti-shopping-cart"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Total Orders</p>
                                    <h4 class="mb-0"><span class="counter-value" data-target="156">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-warning-subtle rounded">
                                        <div class="avatar-title bg-warning-subtle text-warning fs-22">
                                            <i class="ti ti-currency-dollar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Total Revenue</p>
                                    <h4 class="mb-0">$<span class="counter-value" data-target="12500">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Broker Integration Section -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Broker Integration</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted font-13">Connect with affiliate marketing brokers to sync your campaigns and track performance.</p>
                            <a href="{{ route('brokers.index') }}" class="btn btn-primary mt-2">
                                <i class="ti ti-plug me-1"></i>Manage Brokers
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Connected Brokers -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Connected Brokers</h5>
                        </div>
                        <div class="card-body">
                            <div id="connected-brokers-list">
                                <!-- Broker cards will be loaded here -->
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                <i class="ti ti-bolt"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mt-0 mb-1 font-14">Boostiny</h6>
                                            <p class="mb-0 font-13 text-muted">Connected since Jan 15, 2024</p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-success-subtle text-success">Connected</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <div class="avatar-title bg-info-subtle text-info rounded-circle">
                                                <i class="ti ti-world"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mt-0 mb-1 font-14">Admitad</h6>
                                            <p class="mb-0 font-13 text-muted">Connected since Jan 10, 2024</p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-success-subtle text-success">Connected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Broker Data Section -->
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Broker Data</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted font-13">View and analyze data from your connected brokers.</p>
                            <div id="broker-data-section">
                                <!-- Broker data will be loaded here -->
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-primary-subtle">
                                            <div class="card-body text-center">
                                                <h5 class="text-primary">$3,250</h5>
                                                <p class="text-muted mb-0">Boostiny Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-info-subtle">
                                            <div class="card-body text-center">
                                                <h5 class="text-info">$2,890</h5>
                                                <p class="text-muted mb-0">Admitad Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-warning-subtle">
                                            <div class="card-body text-center">
                                                <h5 class="text-warning">$6,360</h5>
                                                <p class="text-muted mb-0">Optimize Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-success-subtle">
                                            <div class="card-body text-center">
                                                <h5 class="text-success">$890</h5>
                                                <p class="text-muted mb-0">Platformance Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Activity -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div id="recent-activity">
                                <!-- Recent activity will be loaded here -->
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="ti ti-cash-multiple font-20 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mt-0 mb-1 font-14">Boostiny</h6>
                                            <p class="mb-0 font-13 text-muted">Order #BOO-2024-001</p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="mt-0 mb-1 font-14">$125.50</h6>
                                        <p class="mb-0 font-13 text-muted">2 hours ago</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="ti ti-cash-multiple font-20 text-info"></i>
                                        </div>
                                        <div>
                                            <h6 class="mt-0 mb-1 font-14">Admitad</h6>
                                            <p class="mb-0 font-13 text-muted">Order #ADM-2024-002</p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="mt-0 mb-1 font-14">$89.75</h6>
                                        <p class="mb-0 font-13 text-muted">4 hours ago</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="ti ti-cash-multiple font-20 text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mt-0 mb-1 font-14">Optimize</h6>
                                            <p class="mb-0 font-13 text-muted">Order #OPT-2024-003</p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h6 class="mt-0 mb-1 font-14">$256.30</h6>
                                        <p class="mb-0 font-13 text-muted">6 hours ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    // Counter animation
    $('.counter-value').each(function() {
        var $this = $(this);
        var target = parseInt($this.data('target'));
        
        $({ Counter: 0 }).animate({ Counter: target }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(Math.ceil(this.Counter));
            }
        });
    });
});

function loadDashboardData() {
    fetch('/api/dashboard/overview')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOverviewCards(data.data.overview);
                updateConnectedBrokers(data.data.connected_brokers);
                updateRecentActivity(data.data.recent_purchases);
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
        });
}

function refreshData() {
    dashboardUtils.showLoading('#refresh-btn');
    loadDashboardData();
    setTimeout(() => {
        dashboardUtils.hideLoading('#refresh-btn', '<i class="ti ti-refresh me-1"></i> Refresh');
        dashboardUtils.showSuccess('Data refreshed successfully!');
    }, 1000);
}

function exportReport() {
    dashboardUtils.showSuccess('Exporting report...');
}

function updateOverviewCards(overview) {
    document.getElementById('connected-brokers').textContent = overview.connected_brokers;
    document.getElementById('total-campaigns').textContent = overview.campaigns_count;
    document.getElementById('total-orders').textContent = overview.total_orders;
    document.getElementById('total-revenue').textContent = '$' + parseFloat(overview.total_revenue || 0).toFixed(2);
}

function updateConnectedBrokers(brokers) {
    const container = document.getElementById('connected-brokers-list');
    
    if (brokers.length === 0) {
        container.innerHTML = '<p class="text-muted">No brokers connected yet. <a href="{{ route("brokers.index") }}" class="text-primary">Connect your first broker</a></p>';
        return;
    }

    const brokersHtml = brokers.map(broker => `
        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
            <div class="d-flex align-items-center">
                <div class="avatar-xs me-3">
                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                        <i class="ti ti-${getBrokerIcon(broker.broker.name)}"></i>
                    </div>
                </div>
                <div>
                    <h6 class="mt-0 mb-1 font-14">${broker.broker.display_name}</h6>
                    <p class="mb-0 font-13 text-muted">Connected since ${new Date(broker.connected_at).toLocaleDateString()}</p>
                </div>
            </div>
            <div>
                <span class="badge bg-success-subtle text-success">Connected</span>
                <a href="/brokers/${broker.broker.id}/data" class="btn btn-sm btn-link text-primary">View Data</a>
            </div>
        </div>
    `).join('');

    container.innerHTML = brokersHtml;
}

function updateRecentActivity(purchases) {
    const container = document.getElementById('recent-activity');
    
    if (purchases.length === 0) {
        container.innerHTML = '<p class="text-muted">No recent activity</p>';
        return;
    }

    const activityHtml = purchases.slice(0, 5).map(purchase => `
        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <i class="ti ti-cash-multiple font-20 text-primary"></i>
                </div>
                <div>
                    <h6 class="mt-0 mb-1 font-14">${purchase.broker.name}</h6>
                    <p class="mb-0 font-13 text-muted">Order #${purchase.order_id || 'N/A'}</p>
                </div>
            </div>
            <div class="text-end">
                <h6 class="mt-0 mb-1 font-14">$${parseFloat(purchase.revenue).toFixed(2)}</h6>
                <p class="mb-0 font-13 text-muted">${new Date(purchase.order_date).toLocaleDateString()}</p>
            </div>
        </div>
    `).join('');

    container.innerHTML = activityHtml;
}

function getBrokerIcon(brokerName) {
    const icons = {
        'boostiny': 'bolt',
        'digizag': 'device-desktop',
        'platformance': 'building',
        'optimize': 'optimization',
        'marketeers': 'chart-pie',
        'admitad': 'world'
    };
    return icons[brokerName.toLowerCase()] || 'building';
}
</script>
@endpush