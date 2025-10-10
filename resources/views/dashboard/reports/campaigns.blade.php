@extends('layouts.vertical', ['title' => 'Campaign Reports'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Reports', 'title' => 'Campaign Analytics'])

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-6">
                            <label class="form-label">Network</label>
                            <select class="form-select" id="networkFilter">
                                <option value="">All Networks</option>
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6">
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
                    <h5 class="text-muted fs-13 text-uppercase">Total Campaigns</h5>
                    <h3 class="mb-0 fw-bold" id="stat-total">0</h3>
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
                    <h5 class="text-muted fs-13 text-uppercase">Paused</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-paused">0</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Inactive</h5>
                    <h3 class="mb-0 fw-bold text-danger" id="stat-inactive">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top Performing Campaigns</h4>
                </div>
                <div class="card-body">
                    <div id="performanceChart" style="min-height: 400px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Campaign Types</h4>
                </div>
                <div class="card-body">
                    <div id="typeChart" style="min-height: 400px;"></div>
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
let performanceChart = null;
let typeChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadReport();
});

function applyFilters() {
    filters = {
        network_id: document.getElementById('networkFilter').value,
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
    document.getElementById('networkFilter').value = '';
    document.getElementById('dateRange').value = '';
    loadReport();
}

function loadReport() {
    const params = new URLSearchParams(filters);
    
    fetch(`{{ route('reports.campaigns') }}?${params}`, {
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
    document.getElementById('stat-total').textContent = stats.total_campaigns || 0;
    document.getElementById('stat-active').textContent = stats.active || 0;
    document.getElementById('stat-paused').textContent = stats.paused || 0;
    document.getElementById('stat-inactive').textContent = stats.inactive || 0;
}

function renderCharts(stats) {
    // Performance Chart
    const campaigns = stats.top_performers || [];
    const campaignNames = campaigns.map(c => c.campaign?.name || 'Unknown');
    const revenues = campaigns.map(c => parseFloat(c.total_revenue || 0));
    
    const performanceOptions = {
        series: [{
            name: 'Revenue',
            data: revenues
        }],
        chart: {
            type: 'bar',
            height: 400
        },
        colors: ['#6ac75a'],
        plotOptions: {
            bar: {
                horizontal: true
            }
        },
        xaxis: {
            categories: campaignNames,
            labels: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                }
            }
        }
    };

    if (performanceChart) performanceChart.destroy();
    performanceChart = new ApexCharts(document.querySelector("#performanceChart"), performanceOptions);
    performanceChart.render();

    // Type Chart
    const types = stats.by_type || [];
    const typeLabels = types.map(t => t.campaign_type.charAt(0).toUpperCase() + t.campaign_type.slice(1));
    const typeTotals = types.map(t => t.total);
    
    const typeOptions = {
        series: typeTotals,
        chart: {
            type: 'pie',
            height: 400
        },
        labels: typeLabels,
        colors: ['#6ac75a', '#465dff'],
        legend: {
            position: 'bottom'
        }
    };

    if (typeChart) typeChart.destroy();
    typeChart = new ApexCharts(document.querySelector("#typeChart"), typeOptions);
    typeChart.render();
}
</script>
@endsection

