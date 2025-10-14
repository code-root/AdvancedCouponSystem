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
                    <h3 class="mb-0 fw-bold text-primary" id="stat-revenue">${{ $stats['total_revenue'] ?? '0.00' }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Commission</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-commission">${{ $stats['total_commission'] ?? '0.00' }}</h3>
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
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by Order ID, Campaign, Network, Coupon...">
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
                        <div class="col-md-2">
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
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        
                        <!-- Customer Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Customer</label>
                            <select class="select2 form-control" id="customerTypeFilter" data-toggle="select2">
                                <option value="">All Types</option>
                                <option value="new">New</option>
                                <option value="returning">Returning</option>
                            </select>
                        </div>
                        
                        <!-- Purchase Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Purchase Type</label>
                            <select class="select2 form-control" id="purchaseTypeFilter" data-toggle="select2">
                                <option value="">All Types</option>
                                <option value="coupon">Coupon</option>
                                <option value="link">Direct Link</option>
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
                            <select class="select2 form-control" id="perPageSelect" data-toggle="select2">
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        
                        <!-- Actions -->
                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()" id="applyFiltersBtn">
                                <i class="ti ti-filter me-1"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-light" onclick="resetFilters()" id="resetFiltersBtn">
                                <i class="ti ti-x me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
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
                                <th>Type</th>
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
                                <td colspan="11" class="text-center py-4">
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

// Helper function to format date without timezone issues
function formatDate(date) {
    return date.getFullYear() + '-' + 
        String(date.getMonth() + 1).padStart(2, '0') + '-' + 
        String(date.getDate()).padStart(2, '0');
}

// Wait for window to fully load (including Vite assets)
window.addEventListener('load', function() {
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }
    
    // Initialize Flatpickr
    window.flatpickrInstance = flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        defaultDate: [
            new Date(new Date().getFullYear(), new Date().getMonth(), 1),
            new Date()
        ],
        // Ensure dates are displayed correctly
        parseDate: function(datestr, format) {
            return new Date(datestr);
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                // Format dates correctly without timezone issues
                filters.date_from = formatDate(selectedDates[0]);
                filters.date_to = formatDate(selectedDates[1]);
                // Auto-apply filters when date range changes
                applyFilters();
            } else if (selectedDates.length === 0) {
                // Clear date filters if no dates selected
                delete filters.date_from;
                delete filters.date_to;
            }
        }
    });
    
    // Set default date range
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    filters.date_from = formatDate(firstDayOfMonth);
    filters.date_to = formatDate(today);
    
    // Initialize DataTable
    initializeDataTable();
    
    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchValue = e.target.value.trim();
            if (searchValue !== filters.search_text) {
                filters.search_text = searchValue;
                applyFilters();
            }
        }, 500);
    });
    
    // Per page change
    $('#perPageSelect').on('change', function() {
        filters.per_page = $(this).val();
        reloadDataTable();
    });
    
    // Auto-apply filters on change
    $('#networkFilter, #campaignFilter, #statusFilter, #customerTypeFilter, #purchaseTypeFilter').on('change', function() {
        applyFilters();
    });
    
    // Revenue range filters with debounce
    let revenueTimeout;
    $('#revenueMin, #revenueMax').on('input', function() {
        clearTimeout(revenueTimeout);
        revenueTimeout = setTimeout(() => {
            applyFilters();
        }, 1000);
    });
});

// Helper functions for loading states
function showLoading() {
    const tbody = document.getElementById('purchasesTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading purchases...</p>
                </td>
            </tr>
        `;
    }
}

function showError(message) {
    const tbody = document.getElementById('purchasesTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-4">
                    <div class="alert alert-danger" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>
                        ${message}
                    </div>
                </td>
            </tr>
        `;
    }
}

// This function is replaced by renderPurchasesDataTable for DataTables

// Show empty state
function showEmptyState(message = 'No Orders found') {
    document.getElementById('purchasesTableBody').innerHTML = `
        <tr>
            <td colspan="11" class="text-center py-5">
                <div class="py-4">
                    <i class="ti ti-shopping-cart-off fs-64 text-muted mb-3"></i>
                    <h5 class="text-muted mb-3">${message}</h5>
                    <p class="text-muted mb-4">Try adjusting your filters or sync data from your networks.</p>
                </div>
            </td>
        </tr>
    `;
}

// Pagination is now handled by DataTables

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

function getPurchaseTypeBadge(purchaseType) {
    if (purchaseType === 'coupon') {
        return '<span class="badge bg-info-subtle text-info"><i class="ti ti-ticket me-1"></i>Coupon</span>';
    } else {
        return '<span class="badge bg-warning-subtle text-warning"><i class="ti ti-link me-1"></i>Direct Link</span>';
    }
}

// Apply filters
function applyFilters() {
    // Show loading state
    const applyBtn = document.getElementById('applyFiltersBtn');
    const originalText = applyBtn.innerHTML;
    applyBtn.innerHTML = '<i class="ti ti-loader me-1"></i> Applying...';
    applyBtn.disabled = true;
    
    const networkIds = $('#networkFilter').val() || [];
    const campaignIds = $('#campaignFilter').val() || [];
    
    // Clear previous filters
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    filters = {
        date_from: filters.date_from || formatDate(firstDayOfMonth),
        date_to: filters.date_to || formatDate(today)
    };
    
    // Apply new filters
    if (networkIds.length > 0) {
        filters.network_ids = networkIds;
    }
    if (campaignIds.length > 0) {
        filters.campaign_ids = campaignIds;
    }
    
    const status = $('#statusFilter').val();
    if (status) {
        filters.status = status;
    }
    
    const customerType = $('#customerTypeFilter').val();
    if (customerType) {
        filters.customer_type = customerType;
    }
    
    const purchaseType = $('#purchaseTypeFilter').val();
    if (purchaseType) {
        filters.purchase_type = purchaseType;
    }
    
    const searchText = document.getElementById('searchInput').value.trim();
    if (searchText) {
        filters.search_text = searchText;
    }
    
    const revenueMin = document.getElementById('revenueMin').value;
    if (revenueMin) {
        filters.revenue_min = parseFloat(revenueMin);
    }
    
    const revenueMax = document.getElementById('revenueMax').value;
    if (revenueMax) {
        filters.revenue_max = parseFloat(revenueMax);
    }
    
    reloadDataTable();
    
    // Reset button state
    setTimeout(() => {
        applyBtn.innerHTML = originalText;
        applyBtn.disabled = false;
    }, 1000);
}

// Reset filters
function resetFilters() {
    // Keep default date range
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    filters = {
        date_from: formatDate(firstDayOfMonth),
        date_to: formatDate(today)
    };
    
    document.getElementById('searchInput').value = '';
    $('#networkFilter').val(null).trigger('change');
    $('#campaignFilter').val(null).trigger('change');
    $('#statusFilter').val('').trigger('change');
    $('#customerTypeFilter').val('').trigger('change');
    $('#purchaseTypeFilter').val('').trigger('change');
    $('#perPageSelect').val('15').trigger('change');
    document.getElementById('revenueMin').value = '';
    document.getElementById('revenueMax').value = '';
    
    // Reset date range picker
    if (typeof flatpickrInstance !== 'undefined' && flatpickrInstance) {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        flatpickrInstance.setDate([firstDayOfMonth, today]);
    }
    
    reloadDataTable();
}

// Update statistics
function updateStats(stats) {
    if (!stats) return;
    
    // Update main statistics
    document.getElementById('stat-total').textContent = stats.total || 0;
    document.getElementById('stat-approved').textContent = stats.approved || 0;
    document.getElementById('stat-pending').textContent = stats.pending || 0;
    document.getElementById('stat-rejected').textContent = stats.rejected || 0;
    document.getElementById('stat-revenue').textContent = '$' + (stats.total_revenue || '0.00');
    document.getElementById('stat-commission').textContent = '$' + (stats.total_commission || '0.00');
    
    // Update purchase type breakdown if available
    if (stats.purchase_type_breakdown) {
        console.log('Purchase type breakdown:', stats.purchase_type_breakdown);
        // You can add UI elements to display this data if needed
    }
    
    // Add visual feedback for updated stats
    const statsCards = document.querySelectorAll('#statsCards .card');
    statsCards.forEach(card => {
        card.style.transition = 'all 0.3s ease';
        card.style.transform = 'scale(1.02)';
        setTimeout(() => {
            card.style.transform = 'scale(1)';
        }, 200);
    });
}

// Export purchases
function exportPurchases() {
    const params = new URLSearchParams(filters);
    window.location.href = `{{ route('purchases.export') }}?${params}`;
}

// Initialize DataTables
let purchasesDataTable = null;

function initializeDataTable() {
    if (purchasesDataTable) {
        purchasesDataTable.destroy();
    }
    
    purchasesDataTable = $('#purchasesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("purchases.index") }}',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            timeout: 30000, // 30 seconds timeout
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                showError('Error loading data. Please try again.');
            },
            data: function(d) {
                // Add custom filters
                if (filters.network_ids) {
                    d.network_ids = filters.network_ids;
                }
                if (filters.campaign_ids) {
                    d.campaign_ids = filters.campaign_ids;
                }
                if (filters.status) {
                    d.status = filters.status;
                }
                if (filters.purchase_type) {
                    d.purchase_type = filters.purchase_type;
                }
                if (filters.customer_type) {
                    d.customer_type = filters.customer_type;
                }
                if (filters.date_from) {
                    d.date_from = filters.date_from;
                }
                if (filters.date_to) {
                    d.date_to = filters.date_to;
                }
                if (filters.search_text) {
                    d.search_text = filters.search_text;
                }
                if (filters.revenue_min) {
                    d.revenue_min = filters.revenue_min;
                }
                if (filters.revenue_max) {
                    d.revenue_max = filters.revenue_max;
                }
            },
            dataSrc: function(json) {
                // Update stats when data is loaded
                if (json.stats) {
                    updateStats(json.stats);
                }
                return json.data;
            }
        },
        columns: [
            { 
                data: 'order_id',
                name: 'order_id',
                title: 'Order ID',
                render: function(data, type, row) {
                    return `<span class="fw-bold text-primary" title="Order ID: ${data}">${data}</span>`;
                }
            },
            { 
                data: 'campaign',
                name: 'campaign.name',
                title: 'Campaign',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <img src="${data.logo_url}" class="avatar-xs rounded">
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0">${data.name}</h6>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: 'network',
                name: 'networks.display_name',
                title: 'Network',
                render: function(data, type, row) {
                    return `<span class="badge bg-primary-subtle text-primary">${data}</span>`;
                }
            },
            { 
                data: 'purchase_type',
                name: 'purchase_type',
                title: 'Type',
                render: function(data, type, row) {
                    return getPurchaseTypeBadge(data);
                }
            },
            { 
                data: 'coupon_code',
                name: 'coupons.code',
                title: 'Coupon',
                render: function(data, type, row) {
                    return `<code class="text-muted">${data}</code>`;
                }
            },
            { 
                data: 'customer_type',
                name: 'customer_type',
                title: 'Customer',
                render: function(data, type, row) {
                    return getCustomerBadge(data);
                }
            },
            { 
                data: 'order_value',
                name: 'order_value',
                title: 'Order Value',
                render: function(data, type, row) {
                    return `$${data}`;
                }
            },
            { 
                data: 'commission',
                name: 'commission',
                title: 'Commission',
                render: function(data, type, row) {
                    return `$${data}`;
                }
            },
            { 
                data: 'order_date',
                name: 'order_date',
                title: 'Date',
                render: function(data, type, row) {
                    return new Date(data).toLocaleDateString('en-US');
                }
            },
            { 
                data: 'status',
                name: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    return getStatusBadge(data);
                }
            },
            { 
                data: 'id',
                name: 'action',
                title: 'Action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/purchases/${data}"><i class="ti ti-eye me-2"></i>View Details</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><span class="dropdown-item-text text-muted small">Order ID: ${row.order_id}</span></li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[8, 'desc']], // Sort by date column (index 8) descending
        deferRender: true, // Defer rendering for better performance
        stateSave: true, // Save table state
        columnDefs: [
            { type: 'date', targets: [8] }, // Date column
            { type: 'num', targets: [6, 7] }, // Numeric columns for Order Value, Commission
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: "No data available in table",
            zeroRecords: "No matching records found",
            processing: "Loading..."
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function() {
            // Hide the original pagination since we're using DataTables
            document.getElementById('paginationContainer').style.display = 'none';
            
            // Add loading indicator
            this.api().on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    showLoading();
                }
            });
        }
    });
}

// Reload DataTable with new filters
function reloadDataTable() {
    if (purchasesDataTable) {
        // Show loading state
        showLoading();
        purchasesDataTable.ajax.reload(null, false); // false = stay on current page
    }
}

// DataTable is initialized in the window load event above
</script>
@endsection
