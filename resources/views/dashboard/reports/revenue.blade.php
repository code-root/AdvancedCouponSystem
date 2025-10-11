@extends('layouts.vertical', ['title' => 'Revenue Reports'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Reports', 'title' => 'Revenue Analytics'])

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
                    <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-revenue">$0.00</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Commission</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-commission">$0.00</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Order Value</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-order-value">$0.00</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Avg Order Value</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-avg-order">$0.00</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Charts -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Daily Revenue Trend</h4>
                </div>
                <div class="card-body">
                    <div id="revenueChart" style="min-height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Charts -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Revenue by Network</h4>
                </div>
                <div class="card-body">
                    <div id="networkChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Monthly Comparison (Last 12 Months)</h4>
                </div>
                <div class="card-body">
                    <div id="monthlyChart" style="min-height: 350px;"></div>
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
let networkChart = null;
let monthlyChart = null;

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
    
    fetch(`{{ route('reports.revenue') }}?${params}`, {
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
    document.getElementById('stat-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toFixed(2);
    document.getElementById('stat-commission').textContent = '$' + parseFloat(stats.total_commission || 0).toFixed(2);
    document.getElementById('stat-order-value').textContent = '$' + parseFloat(stats.total_order_value || 0).toFixed(2);
    document.getElementById('stat-avg-order').textContent = '$' + parseFloat(stats.avg_order_value || 0).toFixed(2);
}

function renderCharts(stats) {
    // Daily Revenue Chart
    const dailyData = stats.daily_revenue || [];
    const dates = dailyData.map(d => d.date);
    const revenues = dailyData.map(d => parseFloat(d.revenue || 0));
    
    const revenueOptions = {
        series: [{
            name: 'Revenue',
            data: revenues
        }],
        chart: {
            type: 'area',
            height: 400
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
            categories: dates
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
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

    if (revenueChart) revenueChart.destroy();
    revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();

    // Network Chart
    const networks = stats.by_network || [];
    const networkNames = networks.map(n => n.network?.display_name || 'Unknown');
    const networkRevenues = networks.map(n => parseFloat(n.total_revenue || 0));
    
    const networkOptions = {
        series: [{
            name: 'Revenue',
            data: networkRevenues
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        colors: ['#465dff'],
        plotOptions: {
            bar: {
                horizontal: true
            }
        },
        xaxis: {
            categories: networkNames,
            labels: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                }
            }
        }
    };

    if (networkChart) networkChart.destroy();
    networkChart = new ApexCharts(document.querySelector("#networkChart"), networkOptions);
    networkChart.render();

    // Monthly Comparison Chart
    const monthlyData = stats.monthly_comparison || [];
    const months = monthlyData.map(m => m.month);
    const monthlyRevenues = monthlyData.map(m => parseFloat(m.revenue || 0));
    
    const monthlyOptions = {
        series: [{
            name: 'Revenue',
            data: monthlyRevenues
        }],
        chart: {
            type: 'line',
            height: 350
        },
        colors: ['#f7b84b'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: months
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return '$' + val.toFixed(0);
                }
            }
        },
        markers: {
            size: 5
        }
    };

    if (monthlyChart) monthlyChart.destroy();
    monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions);
    monthlyChart.render();
}
</script>
@endsection

