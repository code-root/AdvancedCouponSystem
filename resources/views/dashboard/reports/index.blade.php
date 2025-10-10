@extends('layouts.vertical', ['title' => 'Reports'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Analytics', 'title' => 'Reports'])

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filtersForm">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Network</label>
                                <select class="form-select" id="networkFilter" onchange="loadCampaignsForNetwork()">
                                    <option value="">All Networks</option>
                                    @foreach($networks as $network)
                                        <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">Campaign</label>
                                <select class="form-select" id="campaignFilter">
                                    <option value="">All Campaigns</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}" data-network="{{ $campaign->network_id }}">{{ $campaign->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">Date Range</label>
                                <input type="text" class="form-control" id="dateRange" 
                                       data-provider="flatpickr" 
                                       data-date-format="Y-m-d"
                                       data-range-date="true" 
                                       placeholder="Select date range">
                            </div>
                            <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">
                                <button type="button" class="btn btn-primary flex-grow-1" onclick="applyFilters()">
                                    <i class="ti ti-filter me-1"></i> Apply
                                </button>
                                <button type="button" class="btn btn-light" onclick="resetFilters()" title="Reset Filters">
                                    <i class="ti ti-refresh"></i>
                                </button>
                                <button type="button" class="btn btn-success d-none d-lg-block" onclick="exportReport()" title="Export Report">
                                    <i class="ti ti-download"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-revenue">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Commission</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-commission">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Purchases</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-purchases">{{ $stats['total_purchases'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Approved</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-approved">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Campaigns</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-campaigns">{{ $stats['total_campaigns'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Coupons</h5>
                    <h3 class="mb-0 fw-bold text-secondary" id="stat-coupons">{{ $stats['total_coupons'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">Daily Revenue Overview</h4>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="min-height: 365px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Top Campaigns by Revenue</h4>
                </div>
                <div class="card-body">
                    <div id="topCampaignsContent">
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
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Top Networks by Revenue</h4>
                </div>
                <div class="card-body">
                    <div id="topNetworksContent">
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    
<script>
// Filters
let filters = {};
let revenueChart = null;

// All campaigns data
const allCampaigns = @json($campaigns);

// Load reports on page load
document.addEventListener('DOMContentLoaded', function() {
    loadReports();
});

// Load campaigns based on selected network
function loadCampaignsForNetwork() {
    const networkId = document.getElementById('networkFilter').value;
    const campaignSelect = document.getElementById('campaignFilter');
    
    // Clear existing options
    campaignSelect.innerHTML = '<option value="">All Campaigns</option>';
    
    // Filter and add campaigns
    allCampaigns.forEach(campaign => {
        if (!networkId || campaign.network_id == networkId) {
            const option = document.createElement('option');
            option.value = campaign.id;
            option.textContent = campaign.name;
            option.setAttribute('data-network', campaign.network_id);
            campaignSelect.appendChild(option);
        }
    });
}

// Apply filters
function applyFilters() {
    filters = {
        network_id: document.getElementById('networkFilter').value,
        campaign_id: document.getElementById('campaignFilter').value,
    };
    
    // Get date range
    const dateRange = document.getElementById('dateRange').value;
    if (dateRange) {
        const dates = dateRange.split(' to ');
        filters.date_from = dates[0];
        filters.date_to = dates[1] || dates[0];
    }
    
    loadReports();
}

// Reset filters
function resetFilters() {
    filters = {};
    document.getElementById('networkFilter').value = '';
    document.getElementById('campaignFilter').value = '';
    document.getElementById('dateRange').value = '';
    
    loadReports();
}

// Load reports data
function loadReports() {
    const params = new URLSearchParams(filters);
    
    fetch(`{{ route('reports.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStats(data.stats);
            renderRevenueChart(data.stats.daily_revenue);
            renderTopCampaigns(data.stats.top_campaigns);
            renderTopNetworks(data.stats.by_network);
        }
    })
    .catch(error => {
        console.error('Error loading reports:', error);
    });
}

// Update statistics
function updateStats(stats) {
    document.getElementById('stat-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toFixed(2);
    document.getElementById('stat-commission').textContent = '$' + parseFloat(stats.total_commission || 0).toFixed(2);
    document.getElementById('stat-purchases').textContent = stats.total_purchases || 0;
    document.getElementById('stat-approved').textContent = stats.approved_purchases || 0;
    document.getElementById('stat-campaigns').textContent = stats.total_campaigns || 0;
    document.getElementById('stat-coupons').textContent = stats.total_coupons || 0;
}

// Render revenue chart
function renderRevenueChart(dailyData) {
    const container = document.querySelector("#revenueChart");
    
    if (!container) {
        console.error('Chart container not found');
        return;
    }
    
    if (!dailyData || dailyData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    // Destroy existing chart properly
    if (revenueChart && typeof revenueChart.destroy === 'function') {
        revenueChart.destroy();
        revenueChart = null;
    }
    
    // Clear container completely
    container.innerHTML = '';
    
    const categories = dailyData.map(d => d.date);
    const revenues = dailyData.map(d => parseFloat(d.revenue || 0));
    
    const options = {
        series: [{
            name: 'Revenue',
            data: revenues
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: false
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: ['#6ac75a'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: categories,
            labels: {
                rotate: -45
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return '$' + (val || 0).toFixed(2);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return '$' + (val || 0).toFixed(2);
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
            }
        }
    };

    // Create new chart instance
    try {
        revenueChart = new ApexCharts(container, options);
        revenueChart.render();
    } catch (error) {
        console.error('Error rendering chart:', error);
        container.innerHTML = '<p class="text-center text-danger py-5">Error rendering chart</p>';
    }
}

// Render top campaigns
function renderTopCampaigns(campaigns) {
    const container = document.getElementById('topCampaignsContent');
    
    if (!campaigns || campaigns.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="ti ti-speakerphone fs-48 text-muted"></i>
                <p class="text-muted mt-3 mb-0">No campaign data available</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table table-sm table-hover mb-0">';
    html += '<thead><tr><th>Campaign</th><th class="text-end">Revenue</th><th class="text-end">Orders</th></tr></thead>';
    html += '<tbody>';
    
    campaigns.forEach(campaign => {
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-speakerphone text-primary"></i>
                        <span>${campaign.campaign?.name || 'Unknown'}</span>
                    </div>
                </td>
                <td class="text-end fw-semibold text-success">$${parseFloat(campaign.total_revenue).toFixed(2)}</td>
                <td class="text-end">${campaign.total_purchases}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

// Render top networks
function renderTopNetworks(networks) {
    const container = document.getElementById('topNetworksContent');
    
    if (!networks || networks.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="ti ti-affiliate fs-48 text-muted"></i>
                <p class="text-muted mt-3 mb-0">No network data available</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table table-sm table-hover mb-0">';
    html += '<thead><tr><th>Network</th><th class="text-end">Revenue</th><th class="text-end">Orders</th></tr></thead>';
    html += '<tbody>';
    
    networks.forEach(network => {
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-affiliate text-info"></i>
                        <span>${network.network?.display_name || 'Unknown'}</span>
                    </div>
                </td>
                <td class="text-end fw-semibold text-success">$${parseFloat(network.total_revenue).toFixed(2)}</td>
                <td class="text-end">${network.total_purchases}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}

// Export report
function exportReport() {
    const params = new URLSearchParams(filters);
    window.open(`{{ route('reports.export', ['type' => 'pdf']) }}?${params}`, '_blank');
}
</script>
@endsection
