@extends('layouts.vertical', ['title' => 'Coupons'])

@section('css')
    @vite(['node_modules/flatpickr/dist/flatpickr.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Marketing', 'title' => 'Coupons'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('coupons.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Create Coupon
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-3">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total</h5>
                    <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Active</h5>
                    <h3 class="mb-0 fw-bold text-success">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Used</h5>
                    <h3 class="mb-0 fw-bold text-warning">{{ $stats['used'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Expired</h5>
                    <h3 class="mb-0 fw-bold text-danger">{{ $stats['expired'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Search -->
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Coupon code...">
                        </div>
                        
                        <!-- Network Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Network</label>
                            <select class="form-select" id="networkFilter">
                                <option value="">All Networks</option>
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Campaign Filter -->
                        <div class="col-md-3">
                            <label class="form-label">Campaign</label>
                            <select class="form-select" id="campaignFilter">
                                <option value="">All Campaigns</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                        
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                        </div>
                        
                        <!-- Actions -->
                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="ti ti-filter me-1"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-light" onclick="resetFilters()">
                                <i class="ti ti-x me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coupons Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <h4 class="header-title mb-0">All Coupons</h4>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Coupon Code</th>
                                <th>Campaign</th>
                                <th>Network</th>
                                <th>Used</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th class="text-center pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="couponsTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Loading coupons...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer" id="paginationContainer"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
let currentPage = 1;
let filters = {};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr
    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                filters.date_from = selectedDates[0].toISOString().split('T')[0];
                filters.date_to = selectedDates[1].toISOString().split('T')[0];
            }
        }
    });
    
    loadCoupons();
    
    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filters.search = e.target.value;
            applyFilters();
        }, 500);
    });
});

function loadCoupons(page = 1) {
    currentPage = page;
    const params = new URLSearchParams({ page: currentPage, ...filters });
    
    fetch(`{{ route('coupons.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCoupons(data.data.data);
            renderPagination(data.data);
        }
    })
    .catch(error => showEmptyState('Error loading coupons'));
}

function renderCoupons(coupons) {
    const tbody = document.getElementById('couponsTableBody');
    
    if (coupons.length === 0) {
        showEmptyState('No coupons found');
        return;
    }
    
    let html = '';
    coupons.forEach(coupon => {
        const statusBadge = getStatusBadge(coupon.status);
        const expires = coupon.expires_at ? new Date(coupon.expires_at).toLocaleDateString() : 'Never';
        
        html += `
            <tr>
                <td class="ps-3">
                    <code class="fs-16 text-primary">${coupon.code}</code>
                </td>
                <td>${coupon.campaign?.name || 'N/A'}</td>
                <td>
                    <span class="badge bg-primary-subtle text-primary">${coupon.campaign?.network?.display_name || 'N/A'}</span>
                </td>
                <td>
                    <span class="badge bg-info">${coupon.used_count || 0}</span>
                    ${coupon.usage_limit ? `/ ${coupon.usage_limit}` : ''}
                </td>
                <td>${expires}</td>
                <td>${statusBadge}</td>
                <td class="pe-3 text-center">
                    <div class="hstack gap-1 justify-content-center">
                        <a href="/coupons/${coupon.id}" class="btn btn-soft-info btn-icon btn-sm rounded-circle">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="/coupons/${coupon.id}/edit" class="btn btn-soft-warning btn-icon btn-sm rounded-circle">
                            <i class="ti ti-edit"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function showEmptyState(message) {
    document.getElementById('couponsTableBody').innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5">
                <div class="py-4">
                    <i class="ti ti-ticket-off fs-64 text-muted mb-3"></i>
                    <h5 class="text-muted mb-3">${message}</h5>
                    <p class="text-muted">Adjust your filters or sync data from networks.</p>
                </div>
            </td>
        </tr>
    `;
}

function renderPagination(data) {
    const container = document.getElementById('paginationContainer');
    if (data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination justify-content-end mb-0">';
    html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadCoupons(${data.current_page - 1}); return false;">Previous</a>
    </li>`;
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadCoupons(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadCoupons(${data.current_page + 1}); return false;">Next</a>
    </li></ul></nav>`;
    
    container.innerHTML = html;
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge bg-success-subtle text-success">Active</span>',
        'inactive': '<span class="badge bg-danger-subtle text-danger">Inactive</span>',
        'expired': '<span class="badge bg-warning-subtle text-warning">Expired</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function applyFilters() {
    filters.network_id = document.getElementById('networkFilter').value;
    filters.campaign_id = document.getElementById('campaignFilter').value;
    filters.status = document.getElementById('statusFilter').value;
    filters.search = document.getElementById('searchInput').value;
    loadCoupons(1);
}

function resetFilters() {
    filters = {};
    document.getElementById('searchInput').value = '';
    document.getElementById('networkFilter').value = '';
    document.getElementById('campaignFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateRange').value = '';
    loadCoupons(1);
}
</script>
@endsection
