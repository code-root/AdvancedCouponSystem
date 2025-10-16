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

    <!-- Statistics Cards (aligned with Orders page) -->
    <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center mb-3" id="statsCards">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Networks</h5>
                    <h3 class="mb-0 fw-bold" id="stat-networks">{{ $stats['networks'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted mt-2">
                        <span class="text-success" id="networks-growth"><i class="ti ti-trending-up"></i> 0%</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Campaigns</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-campaigns">{{ $stats['campaigns'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted mt-2">
                        <span class="text-success" id="campaigns-growth"><i class="ti ti-trending-up"></i> 0%</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Coupons</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-coupons">{{ $stats['coupons'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted mt-2">
                        <span class="text-success" id="coupons-growth"><i class="ti ti-trending-up"></i> 0%</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Orders</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-orders">{{ $stats['total'] ?? 0 }}</h3>
                    <p class="mb-0 text-muted mt-2">
                        <span class="text-success" id="orders-growth"><i class="ti ti-trending-up"></i> 0%</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Revenue</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-revenue">${{ $stats['total_revenue'] ?? '0.00' }}</h3>
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
                    <h3 class="mb-0 fw-bold text-danger" id="stat-sales-amount">${{ $stats['total_sales'] ?? '0.00' }}</h3>
                    <p class="mb-0 text-muted mt-2">
                        <span class="text-success" id="sales-growth"><i class="ti ti-trending-up"></i> 0%</span>
                    </p>
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
                            <label class="form-label">Networks</label>
                            <select class="select2 form-control select2-multiple" id="networkFilter" multiple="multiple" data-toggle="select2" data-placeholder="Choose Networks...">
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Campaign Filter -->
                        <div class="col-md-3">
                            <label class="form-label">Campaigns</label>
                            <select class="select2 form-control select2-multiple" id="campaignFilter" multiple="multiple" data-toggle="select2" data-placeholder="Choose Campaigns...">
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="select2 form-control" id="statusFilter" data-toggle="select2">
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

window.addEventListener('load', function() {
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }
    
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
    
    // Prepare params with proper array handling
    const params = new URLSearchParams();
    params.append('page', currentPage);
    
    // Add filters
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
        url: '{{ route("coupons.index") }}?' + params.toString(),
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            if (data.success) {
                renderCoupons(data.data.data);
                renderPagination(data.data);
                updateStats(data.stats);
            }
        },
        error: function(error) {
            console.error('Error:', error);
            showEmptyState('Error loading coupons');
        }
    });
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

// Update statistics
function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total || 0;
    document.getElementById('stat-active').textContent = stats.active || 0;
    document.getElementById('stat-used').textContent = stats.used || 0;
    document.getElementById('stat-expired').textContent = stats.expired || 0;
}

function applyFilters() {
    const networkIds = $('#networkFilter').val() || [];
    const campaignIds = $('#campaignFilter').val() || [];
    
    filters.network_ids = networkIds.length > 0 ? networkIds : null;
    filters.campaign_ids = campaignIds.length > 0 ? campaignIds : null;
    filters.status = $('#statusFilter').val();
    filters.search = document.getElementById('searchInput').value;
    loadCoupons(1);
}

function resetFilters() {
    filters = {};
    document.getElementById('searchInput').value = '';
    $('#networkFilter').val(null).trigger('change');
    $('#campaignFilter').val(null).trigger('change');
    $('#statusFilter').val('').trigger('change');
    document.getElementById('dateRange').value = '';
    loadCoupons(1);
}
</script>
@endsection
