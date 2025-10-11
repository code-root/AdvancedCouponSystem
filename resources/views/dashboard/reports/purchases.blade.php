@extends('layouts.vertical', ['title' => 'Purchase Reports'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Reports', 'title' => 'Purchase Analytics'])

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
    <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total</h5>
                    <h3 class="mb-0 fw-bold" id="stat-total">0</h3>
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
                    <h5 class="text-muted fs-13 text-uppercase">Pending</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-pending">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Rejected</h5>
                    <h3 class="mb-0 fw-bold text-danger" id="stat-rejected">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Revenue</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-revenue">$0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Commission</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-commission">$0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Daily Purchase Trend</h4>
                </div>
                <div class="card-body">
                    <div id="dailyChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">By Status</h4>
                </div>
                <div class="card-body">
                    <div id="statusChart" style="min-height: 350px;"></div>
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
let dailyChart = null;
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
    
    fetch(`{{ route('reports.purchases') }}?${params}`, {
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
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total_purchases || 0;
    document.getElementById('stat-approved').textContent = stats.approved || 0;
    document.getElementById('stat-pending').textContent = stats.pending || 0;
    document.getElementById('stat-rejected').textContent = stats.rejected || 0;
    document.getElementById('stat-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toFixed(2);
    document.getElementById('stat-commission').textContent = '$' + parseFloat(stats.total_commission || 0).toFixed(2);
}

function renderCharts(stats) {
    // Daily Trend Chart
    const dailyData = stats.daily_trend || [];
    const dates = dailyData.map(d => d.date);
    const revenues = dailyData.map(d => parseFloat(d.revenue || 0));
    
    const dailyOptions = {
        series: [{
            name: 'Revenue',
            data: revenues
        }],
        chart: {
            type: 'area',
            height: 350
        },
        colors: ['#6ac75a'],
        xaxis: {
            categories: dates
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                }
            }
        },
        stroke: {
            curve: 'smooth'
        },
        fill: {
            type: 'gradient'
        }
    };

    if (dailyChart) dailyChart.destroy();
    dailyChart = new ApexCharts(document.querySelector("#dailyChart"), dailyOptions);
    dailyChart.render();

    // Status Chart
    const statusData = stats.by_status || [];
    const statusLabels = statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1));
    const statusTotals = statusData.map(s => s.total);
    
    const statusOptions = {
        series: statusTotals,
        chart: {
            type: 'donut',
            height: 350
        },
        labels: statusLabels,
        colors: ['#6ac75a', '#f7b84b', '#f1556c'],
        legend: {
            position: 'bottom'
        }
    };

    if (statusChart) statusChart.destroy();
    statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
    statusChart.render();
}
</script>
@endsection

