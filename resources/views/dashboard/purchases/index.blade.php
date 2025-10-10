@extends('layouts.vertical', ['title' => 'Purchases'])

@section('css')
    @vite(['node_modules/flatpickr/dist/flatpickr.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Sales', 'title' => 'Purchases'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-success" onclick="exportPurchases()">
                    <i class="ti ti-file-export me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center mb-3" id="statsCards">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total</h5>
                    <h3 class="mb-0 fw-bold" id="stat-total">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Approved</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-approved">{{ $stats['approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Pending</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-pending">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Rejected</h5>
                    <h3 class="mb-0 fw-bold text-danger" id="stat-rejected">{{ $stats['rejected'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Revenue</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-revenue">${{ number_format($stats['total_revenue'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Commission</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-commission">${{ number_format($stats['total_commission'], 2) }}</h3>
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
                            <input type="text" class="form-control" id="searchInput" placeholder="Order ID...">
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
                        <div class="col-md-2">
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
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        
                        <!-- Customer Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Customer</label>
                            <select class="form-select" id="customerTypeFilter">
                                <option value="">All Types</option>
                                <option value="new">New</option>
                                <option value="returning">Returning</option>
                            </select>
                        </div>
                        
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label">Order Date Range</label>
                            <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                        </div>
                        
                        <!-- Revenue Range -->
                        <div class="col-md-2">
                            <label class="form-label">Min Revenue ($)</label>
                            <input type="number" step="0.01" class="form-control" id="revenueMin" placeholder="0.00">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Max Revenue ($)</label>
                            <input type="number" step="0.01" class="form-control" id="revenueMax" placeholder="1000.00">
                        </div>
                        
                        <!-- Per Page -->
                        <div class="col-md-2">
                            <label class="form-label">Per Page</label>
                            <select class="form-select" id="perPageSelect">
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
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

    <!-- Purchases Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <h4 class="header-title mb-0">All Purchases</h4>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0" id="purchasesTable">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Order ID</th>
                                <th>Campaign</th>
                                <th>Network</th>
                                <th>Coupon</th>
                                <th>Customer</th>
                                <th>Order Value</th>
                                <th>Commission</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="purchasesTableBody">
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Loading purchases...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
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
        defaultDate: [
            new Date(new Date().getFullYear(), new Date().getMonth(), 1),
            new Date()
        ],
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                filters.date_from = selectedDates[0].toISOString().split('T')[0];
                filters.date_to = selectedDates[1].toISOString().split('T')[0];
            }
        }
    });
    
    // Set default date range
    filters.date_from = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
    filters.date_to = new Date().toISOString().split('T')[0];
    
    // Load purchases
    loadPurchases();
    
    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filters.search = e.target.value;
            applyFilters();
        }, 500);
    });
    
    // Per page change
    document.getElementById('perPageSelect').addEventListener('change', function(e) {
        filters.per_page = e.target.value;
        loadPurchases(1);
    });
});

// Load purchases
function loadPurchases(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        page: currentPage,
        ...filters
    });
    
    fetch(`{{ route('purchases.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderPurchases(data.data.data);
            renderPagination(data.data);
            updateStats(data.stats); // Update statistics
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showEmptyState('Error loading purchases');
    });
}

// Render purchases
function renderPurchases(purchases) {
    const tbody = document.getElementById('purchasesTableBody');
    
    if (purchases.length === 0) {
        showEmptyState('No purchases found');
        return;
    }
    
    let html = '';
    purchases.forEach(purchase => {
        const statusBadge = getStatusBadge(purchase.status);
        const customerBadge = getCustomerBadge(purchase.customer_type);
        
        html += `
            <tr>
                <td class="ps-3">
                    <div>
                        <h6 class="mb-0 fs-14 fw-semibold">#${purchase.order_id || purchase.id}</h6>
                        ${purchase.network_order_id ? `<small class="text-muted">Network: ${purchase.network_order_id}</small>` : ''}
                    </div>
                </td>
                <td>
                    <span class="text-muted">${purchase.campaign?.name || 'N/A'}</span>
                </td>
                <td>
                    <span class="badge bg-primary-subtle text-primary">${purchase.network?.display_name || 'N/A'}</span>
                </td>
                <td>
                    ${purchase.coupon ? `<code class="text-primary">${purchase.coupon.code}</code>` : '<span class="text-muted">Direct Link</span>'}
                </td>
                <td>${customerBadge}</td>
                <td><strong>$${parseFloat(purchase.order_value || 0).toFixed(2)}</strong></td>
                <td><strong class="text-success">$${parseFloat(purchase.revenue || 0).toFixed(2)}</strong></td>
                <td>${new Date(purchase.order_date).toLocaleDateString()}</td>
                <td>${statusBadge}</td>
                <td class="pe-3 text-center">
                    <div class="hstack gap-1 justify-content-center">
                        <a href="/purchases/${purchase.id}" class="btn btn-soft-info btn-icon btn-sm rounded-circle" title="View">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Show empty state
function showEmptyState(message = 'No purchases found') {
    document.getElementById('purchasesTableBody').innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-5">
                <div class="py-4">
                    <i class="ti ti-shopping-cart-off fs-64 text-muted mb-3"></i>
                    <h5 class="text-muted mb-3">${message}</h5>
                    <p class="text-muted mb-4">Try adjusting your filters or sync data from your networks.</p>
                </div>
            </td>
        </tr>
    `;
}

// Render pagination
function renderPagination(data) {
    const container = document.getElementById('paginationContainer');
    
    if (data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination justify-content-end mb-0">';
    
    // Previous
    html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadPurchases(${data.current_page - 1}); return false;">Previous</a>
    </li>`;
    
    // Pages
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadPurchases(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadPurchases(${data.current_page + 1}); return false;">Next</a>
    </li>`;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

// Helper functions
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning-subtle text-warning">Pending</span>',
        'approved': '<span class="badge bg-success-subtle text-success">Approved</span>',
        'rejected': '<span class="badge bg-danger-subtle text-danger">Rejected</span>',
        'paid': '<span class="badge bg-info-subtle text-info">Paid</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function getCustomerBadge(type) {
    const badges = {
        'new': '<span class="badge bg-success-subtle text-success"><i class="ti ti-user-plus me-1"></i>New</span>',
        'returning': '<span class="badge bg-primary-subtle text-primary"><i class="ti ti-user-check me-1"></i>Returning</span>'
    };
    return badges[type] || '<span class="badge bg-secondary">Unknown</span>';
}

// Apply filters
function applyFilters() {
    filters.network_id = document.getElementById('networkFilter').value;
    filters.campaign_id = document.getElementById('campaignFilter').value;
    filters.status = document.getElementById('statusFilter').value;
    filters.customer_type = document.getElementById('customerTypeFilter').value;
    filters.search = document.getElementById('searchInput').value;
    filters.revenue_min = document.getElementById('revenueMin').value;
    filters.revenue_max = document.getElementById('revenueMax').value;
    
    loadPurchases(1);
}

// Reset filters
function resetFilters() {
    // Keep default date range
    const defaultDateFrom = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
    const defaultDateTo = new Date().toISOString().split('T')[0];
    
    filters = {
        date_from: defaultDateFrom,
        date_to: defaultDateTo
    };
    
    document.getElementById('searchInput').value = '';
    document.getElementById('networkFilter').value = '';
    document.getElementById('campaignFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('customerTypeFilter').value = '';
    document.getElementById('revenueMin').value = '';
    document.getElementById('revenueMax').value = '';
    document.getElementById('perPageSelect').value = '15';
    
    loadPurchases(1);
}

// Update statistics
function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total || 0;
    document.getElementById('stat-approved').textContent = stats.approved || 0;
    document.getElementById('stat-pending').textContent = stats.pending || 0;
    document.getElementById('stat-rejected').textContent = stats.rejected || 0;
    document.getElementById('stat-revenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toFixed(2);
    document.getElementById('stat-commission').textContent = '$' + parseFloat(stats.total_commission || 0).toFixed(2);
}

// Export purchases
function exportPurchases() {
    const params = new URLSearchParams(filters);
    window.location.href = `{{ route('purchases.export') }}?${params}`;
}
</script>
@endsection
