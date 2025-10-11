@extends('layouts.vertical', ['title' => 'Coupon Reports'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Reports', 'title' => 'Coupon Analytics'])

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Network</label>
                            <select class="select2 form-control" id="networkFilter" data-toggle="select2">
                                <option value="">All Networks</option>
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Campaign</label>
                            <select class="select2 form-control" id="campaignFilter" data-toggle="select2">
                                <option value="">All Campaigns</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dateRange" 
                                       data-provider="flatpickr" 
                                       data-date-format="Y-m-d"
                                       data-range-date="true" 
                                       placeholder="Select date range">
                                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                    <i class="ti ti-filter"></i> Apply
                                </button>
                                <button type="button" class="btn btn-light" onclick="resetFilters()">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Coupons</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-total">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Active</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-active">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Used</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-used">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Expired</h5>
                    <h3 class="mb-0 fw-bold text-danger" id="stat-expired">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Coupons by Campaign</h4>
                </div>
                <div class="card-body">
                    <div id="campaignChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Coupon Status</h4>
                </div>
                <div class="card-body">
                    <div id="statusChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Campaigns Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top Campaigns by Coupons</h4>
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
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    
<script>
let filters = {};
let campaignChart = null;
let statusChart = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }
    
    loadReport();
});

function applyFilters() {
    filters = {
        network_id: $('#networkFilter').val(),
        campaign_id: $('#campaignFilter').val(),
    };
    
    const dateRange = document.getElementById('dateRange').value;
    if (dateRange) {
        const dates = dateRange.split(' to ');
        filters.date_from = dates[0];
        filters.date_to = dates[1] || dates[0];
    }
    
    loadReport();
}

function resetFilters() {
    filters = {};
    $('#networkFilter').val('').trigger('change');
    $('#campaignFilter').val('').trigger('change');
    document.getElementById('dateRange').value = '';
    loadReport();
}

function loadReport() {
    const params = new URLSearchParams(filters);
    
    fetch(`{{ route('reports.coupons') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStats(data.stats);
            renderCharts(data.stats);
            renderTable(data.stats.by_network);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total_coupons || 0;
    document.getElementById('stat-active').textContent = stats.active_coupons || 0;
    document.getElementById('stat-used').textContent = stats.used_coupons || 0;
    document.getElementById('stat-expired').textContent = stats.expired_coupons || 0;
}

function renderCharts(stats) {
    // Campaign Chart
    const campaigns = stats.by_network || [];
    const campaignNames = campaigns.map(c => c.campaign?.name || 'Unknown');
    const campaignTotals = campaigns.map(c => c.total);
    
    const campaignOptions = {
        series: [{
            name: 'Coupons',
            data: campaignTotals
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        colors: ['#6ac75a'],
        xaxis: {
            categories: campaignNames
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return Math.floor(val);
                }
            }
        }
    };

    if (campaignChart) campaignChart.destroy();
    campaignChart = new ApexCharts(document.querySelector("#campaignChart"), campaignOptions);
    campaignChart.render();

    // Status Donut Chart
    const statusOptions = {
        series: [
            stats.active_coupons || 0,
            stats.used_coupons || 0,
            stats.expired_coupons || 0
        ],
        chart: {
            type: 'donut',
            height: 350
        },
        labels: ['Active', 'Used', 'Expired'],
        colors: ['#6ac75a', '#f7b84b', '#f1556c'],
        legend: {
            position: 'bottom'
        }
    };

    if (statusChart) statusChart.destroy();
    statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
    statusChart.render();
}

function renderTable(data) {
    const container = document.getElementById('topCampaignsTable');
    
    if (!data || data.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No data available</p>';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-hover mb-0">';
    html += '<thead><tr><th>Campaign</th><th>Network</th><th class="text-end">Coupons</th></tr></thead><tbody>';
    
    data.forEach(item => {
        html += `
            <tr>
                <td>${item.campaign?.name || 'Unknown'}</td>
                <td>${item.campaign?.network?.display_name || 'N/A'}</td>
                <td class="text-end fw-semibold">${item.total}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    container.innerHTML = html;
}
</script>
@endsection

