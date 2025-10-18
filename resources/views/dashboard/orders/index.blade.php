@extends('layouts.vertical', ['title' => 'Orders'])

@section('css')
    @vite(['node_modules/flatpickr/dist/flatpickr.min.css'])
    <style>
        /* Loading indicator for Select2 */
        .select2-selection.loading {
            position: relative;
        }
        
        .select2-selection.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 20px;
            width: 16px;
            height: 16px;
            margin-top: -8px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Improved select2 styling */
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border: 1px solid #007bff;
            color: white;
            border-radius: 0.25rem;
            padding: 2px 8px;
            margin: 2px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffc107;
        }
    </style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Orders', 'title' => 'Reports'])
    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center mb-3" id="statsCards">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Orders</h5>
                    <h3 class="mb-0 fw-bold" id="stat-networks"></h3>
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
                    <h3 class="mb-0 fw-bold text-primary" id="stat-campaigns"></h3>
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
                    <h3 class="mb-0 fw-bold text-info" id="stat-coupons"></h3>
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
                    <h3 class="mb-0 fw-bold text-danger" id="stat-orders"></h3>
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
                    <h3 class="mb-0 fw-bold text-success" id="stat-revenue"></h3>
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
                    <h3 class="mb-0 fw-bold text-warning" id="stat-sales-amount"></h3>
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
                        {{-- <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by Order ID, Campaign, Network, Coupon...">
                        </div> --}}
                        
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
                        
                        <!-- Coupon Filter (by code, multi, tags) - moved next to Campaigns -->
                        <div class="col-md-2">
                            <label class="form-label">Coupons</label>
                            <select class="select2 form-control select2-multiple" id="couponFilter" multiple="multiple" data-toggle="select2" data-placeholder="Enter coupon codes...">
                                
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
                        <div class="col-md-2">
                            <label class="form-label">Order Date Range</label>
                            <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                        </div>
                        
                        {{-- <!-- Revenue Range -->
                        <div class="col-md-2">
                            <label class="form-label">Min Revenue ($)</label>
                            <input type="number" step="0.01" class="form-control" id="revenueMin" placeholder="0.00">
                        </div> --}}
                        
                        {{-- <div class="col-md-2">
                            <label class="form-label">Max Revenue ($)</label>
                            <input type="number" step="0.01" class="form-control" id="revenueMax" placeholder="1000.00">
                        </div> --}}
                        
                        {{-- <!-- Per Page -->
                        <div class="col-md-2">
                            <label class="form-label">Per Page</label>
                            <select class="select2 form-control" id="perPageSelect" data-toggle="select2">
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                         --}}
                        <!-- Actions -->
                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="applyFilters()" id="applyFiltersBtn">
                                <i class="ti ti-filter me-1"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-light" onclick="resetFilters()" id="resetFiltersBtn">
                                <i class="ti ti-x me-1"></i> Reset
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportPurchases()" id="exportBtn">
                                <i class="ti ti-file-export me-1"></i> Export
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
                    <h4 class="header-title mb-0">All Orders</h4>
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
                                <th>Revenue</th>
                                <th>Sales Amount</th>
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
                                    <p class="text-muted mt-2">Loading Orders ...</p>
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

// Helper function to format date in local time to avoid shifting a day back
function formatDate(date) {
    if (!date || !(date instanceof Date)) {
        return null;
    }
    
    // Use local getters to preserve the selected day exactly as chosen by the user
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
}

// Wait for window to fully load (including Vite assets)
window.addEventListener('load', function() {
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
        // Enable tags for coupon codes entry
        if ($('#couponFilter').length) {
            $('#couponFilter').select2({
                tags: true,
                tokenSeparators: [',', ' ']
            });
        }
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
        // Use native parsing; we will format using local time via formatDate
        parseDate: function(datestr, format) {
            const parts = datestr.split('-');
            if (parts.length === 3) {
                return new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            }
            return new Date(datestr);
        },
        formatDate: function(date, format) {
            // Format date consistently
            return formatDate(date);
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                // Ensure correct date order (earliest first, latest second)
                const startDate = selectedDates[0] < selectedDates[1] ? selectedDates[0] : selectedDates[1];
                const endDate = selectedDates[0] < selectedDates[1] ? selectedDates[1] : selectedDates[0];
                
                // Format dates correctly without timezone issues
                filters.date_from = formatDate(startDate);
                filters.date_to = formatDate(endDate);
                
                // Save date filters to localStorage
                localStorage.setItem('orders_date_from', filters.date_from);
                localStorage.setItem('orders_date_to', filters.date_to);
                
                // Update the flatpickr instance with correct order
                instance.setDate([startDate, endDate], false);
                
                // Auto-apply filters when date range changes
                applyFilters();
            } else if (selectedDates.length === 0) {
                // Clear date filters if no dates selected
                delete filters.date_from;
                delete filters.date_to;
                localStorage.removeItem('orders_date_from');
                localStorage.removeItem('orders_date_to');
            }
        }
    });
    
    // Set default date range
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    // Try to restore date filters from localStorage
    const savedDateFrom = localStorage.getItem('orders_date_from');
    const savedDateTo = localStorage.getItem('orders_date_to');
    
    // Initialize filters with saved dates or defaults
    filters = {
        date_from: savedDateFrom || formatDate(firstDayOfMonth),
        date_to: savedDateTo || formatDate(today)
    };
    
    // Set the date range in flatpickr to match filters
    if (typeof flatpickrInstance !== 'undefined' && flatpickrInstance) {
        const startDate = savedDateFrom ? new Date(savedDateFrom) : firstDayOfMonth;
        const endDate = savedDateTo ? new Date(savedDateTo) : today;
        flatpickrInstance.setDate([startDate, endDate], false);
    }
    
    // Initialize DataTable
    initializeDataTable();
    
    // Initialize chart after other components (removed unused placeholder)
    

    
    // Removed unused per-page change handler (no #perPageSelect in DOM)
    
    // Auto-apply filters on change
    $('#networkFilter, #campaignFilter, #couponFilter, #statusFilter, #purchaseTypeFilter').on('change', function() {
        applyFilters();
    });
    
    // Load campaigns when network selection changes
    $('#networkFilter').on('change', function() {
        loadCampaignsByNetwork();
    });
    
    // Removed unused revenue range handlers (inputs are not present)
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
                    <p class="text-muted mt-2">Loading orders...</p>
                </td>
            </tr>
        `;
    }
}

// Toggle loading state for filters and Apply button
function setFiltersLoading(isLoading) {
    const applyBtn = document.getElementById('applyFiltersBtn');
    if (applyBtn) {
        if (isLoading) {
            applyBtn.innerHTML = '<i class="ti ti-loader me-1"></i> Applying...';
            applyBtn.disabled = true;
        } else {
            applyBtn.innerHTML = '<i class="ti ti-filter me-1"></i> Apply Filters';
            applyBtn.disabled = false;
        }
    }
    const selects = ['#networkFilter', '#campaignFilter', '#statusFilter', '#purchaseTypeFilter'];
    selects.forEach(sel => {
        const $el = $(sel);
        if ($el && $el.length) {
            $el.prop('disabled', !!isLoading);
            const $selection = $el.next('.select2-container').find('.select2-selection');
            if ($selection && $selection.length) {
                $selection.toggleClass('loading', !!isLoading);
            }
        }
    });
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



function getPurchaseTypeBadge(purchaseType) {
    if (purchaseType === 'coupon') {
        return '<span class="badge bg-info-subtle text-info"><i class="ti ti-ticket me-1"></i>Coupon</span>';
    } else {
        return '<span class="badge bg-warning-subtle text-warning"><i class="ti ti-link me-1"></i>Direct Link</span>';
    }
}

// Apply filters
function applyFilters() {
    // Global loading state
    setFiltersLoading(true);
    
    const networkIds = $('#networkFilter').val() || [];
    const campaignIds = $('#campaignFilter').val() || [];
    const couponCodes = $('#couponFilter').val() || [];
    
    // Preserve existing date filters or set defaults
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    // Only set default dates if they don't exist
    if (!filters.date_from) {
        filters.date_from = formatDate(firstDayOfMonth);
    }
    if (!filters.date_to) {
        filters.date_to = formatDate(today);
    }
    
    // Apply new filters (and clear when empty)
    if (networkIds.length > 0) {
        filters.network_ids = networkIds;
    } else {
        delete filters.network_ids;
    }
    if (campaignIds.length > 0) {
        filters.campaign_ids = campaignIds;
    } else {
        delete filters.campaign_ids;
    }
    if (couponCodes.length > 0) {
        filters.coupon_codes = couponCodes;
    } else {
        delete filters.coupon_codes;
    }
    
    const status = $('#statusFilter').val();
    if (status) {
        filters.status = status;
    } else {
        delete filters.status;
    }
    
    const purchaseType = $('#purchaseTypeFilter').val();
    if (purchaseType) {
        filters.purchase_type = purchaseType;
    } else {
        delete filters.purchase_type;
    }
    
    reloadDataTable();
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
    
    // Clear optional inputs if present
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    $('#networkFilter').val(null).trigger('change');
    
    // Reset campaigns to show all campaigns
    loadAllCampaigns();
    
    $('#statusFilter').val('').trigger('change');
    $('#purchaseTypeFilter').val('').trigger('change');
    $('#couponFilter').val(null).trigger('change');
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) $('#perPageSelect').val('25').trigger('change');
    const revenueMinEl = document.getElementById('revenueMin');
    if (revenueMinEl) revenueMinEl.value = '';
    const revenueMaxEl = document.getElementById('revenueMax');
    if (revenueMaxEl) revenueMaxEl.value = '';
    
    // Reset date range picker
    if (typeof flatpickrInstance !== 'undefined' && flatpickrInstance) {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        flatpickrInstance.setDate([firstDayOfMonth, today]);
    }
    
    // Clear saved date filters from localStorage
    localStorage.removeItem('orders_date_from');
    localStorage.removeItem('orders_date_to');
    
    reloadDataTable();
}

// Update statistics
function updateStats(stats) {
    if (!stats) return;
    
    // Update main statistics with new order
    document.getElementById('stat-networks').textContent = stats.networks || 0;
    document.getElementById('stat-campaigns').textContent = stats.campaigns || 0;
    document.getElementById('stat-coupons').textContent = stats.coupons || 0;
    document.getElementById('stat-orders').textContent = stats.total || 0;
    document.getElementById('stat-revenue').textContent = '$' + (stats.total_revenue || '0.00');
    document.getElementById('stat-sales-amount').textContent = '$' + (stats.total_sales || '0.00');
    
    // Update growth percentages
    updateGrowthPercentage('networks-growth', stats.networks_growth || 0);
    updateGrowthPercentage('campaigns-growth', stats.campaigns_growth || 0);
    updateGrowthPercentage('coupons-growth', stats.coupons_growth || 0);
    updateGrowthPercentage('orders-growth', stats.orders_growth || 0);
    updateGrowthPercentage('revenue-growth', stats.revenue_growth || 0);
    updateGrowthPercentage('sales-growth', stats.sales_growth || 0);
    
 
    
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

// Update growth percentage with proper styling
function updateGrowthPercentage(elementId, percentage) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const isPositive = percentage >= 0;
    const icon = isPositive ? 'ti-trending-up' : 'ti-trending-down';
    const colorClass = isPositive ? 'text-success' : 'text-danger';
    
    element.innerHTML = `<i class="ti ${icon}"></i> ${Math.abs(percentage).toFixed(1)}%`;
    element.className = `mb-0 text-muted mt-2 ${colorClass}`;
}

// Load campaigns by selected networks - Optimized with caching and better UX
function loadCampaignsByNetwork() {
    const selectedNetworks = $('#networkFilter').val();
    const campaignSelect = $('#campaignFilter');
    
    if (!selectedNetworks || selectedNetworks.length === 0) {
        // If no networks selected, load all campaigns
        loadAllCampaigns();
        return;
    }
    
    // If multiple networks selected, get campaigns for all of them
    if (selectedNetworks.length > 1) {
        loadAllCampaigns();
        return;
    }
    
    // Single network selected - load campaigns for this network
    const networkId = selectedNetworks[0];
    
    // Validate networkId
    if (!networkId || isNaN(networkId) || networkId <= 0) {
        console.warn('Invalid network ID:', networkId);
        loadAllCampaigns();
        return;
    }
    
    // Check cache first (simple in-memory cache)
    if (window.campaignsCache && window.campaignsCache[networkId]) {
        populateCampaignsSelect(window.campaignsCache[networkId]);
        return;
    }
    
    // Show loading state with better UX
    const originalPlaceholder = campaignSelect.attr('data-placeholder');
    campaignSelect.attr('data-placeholder', 'Loading campaigns...');
    campaignSelect.prop('disabled', true);
    
    // Add loading indicator
    campaignSelect.next('.select2-container').find('.select2-selection').addClass('loading');
    
    // Make AJAX request to get campaigns
    $.ajax({
        url: `{{ route('orders.campaigns-by-network', ['networkId' => '__NETWORK_ID__']) }}`.replace('__NETWORK_ID__', networkId),
        type: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        timeout: 15000, // Increased timeout
        success: function(response) {
            if (response.success && response.campaigns && response.campaigns.length > 0) {
                // Cache the result
                if (!window.campaignsCache) {
                    window.campaignsCache = {};
                }
                window.campaignsCache[networkId] = response.campaigns;
                
                // Populate the select
                populateCampaignsSelect(response.campaigns);
                
                // Show success message if campaigns found
                if (response.count > 0) {
                    console.log(`تم تحميل ${response.count} حملة للشبكة المختارة`);
                }
            } else {
                console.warn('لا توجد حملات للشبكة المختارة:', networkId);
                loadAllCampaigns();
            }
        },
        error: function(xhr, status, error) {
            console.error('خطأ في تحميل الحملات:', error);
            
            // Show user-friendly error message
            if (xhr.status === 404) {
                console.warn('الشبكة غير موجودة');
            } else if (xhr.status === 500) {
                console.error('خطأ في الخادم');
            } else if (status === 'timeout') {
                console.error('انتهت مهلة الطلب');
            }
            
            // Fallback to loading all campaigns
            loadAllCampaigns();
        },
        complete: function() {
            // Restore original state
            campaignSelect.attr('data-placeholder', originalPlaceholder);
            campaignSelect.prop('disabled', false);
            campaignSelect.next('.select2-container').find('.select2-selection').removeClass('loading');
        }
    });
}

// Helper function to populate campaigns select
function populateCampaignsSelect(campaigns) {
    const campaignSelect = $('#campaignFilter');
    
    // Clear existing options
    campaignSelect.empty();
    
    // Add default option
    campaignSelect.append('<option value="">All Campaigns</option>');
    
    // Add campaigns from the network
    campaigns.forEach(function(campaign) {
        campaignSelect.append(`<option value="${campaign.id}">${campaign.text}</option>`);
    });
    
    // Trigger change to update Select2
    campaignSelect.trigger('change');
}

// Load all campaigns (fallback function) - Optimized
function loadAllCampaigns() {
    const campaignSelect = $('#campaignFilter');
    
    // Clear existing options
    campaignSelect.empty();
    
    // Add default option
    campaignSelect.append('<option value="">All Campaigns</option>');
    
    // Add all campaigns from the original data
    @if(isset($campaigns))
        @foreach($campaigns as $campaign)
            campaignSelect.append('<option value="{{ $campaign->id }}">{{ $campaign->name }}</option>');
        @endforeach
    @endif
    
    // Trigger change to update Select2
    campaignSelect.trigger('change');
    
    // Clear cache when loading all campaigns
    if (window.campaignsCache) {
        window.campaignsCache = {};
    }
}

// Export purchases
function exportPurchases() {
    const params = new URLSearchParams(filters);
    window.location.href = `{{ route('orders.export') }}?${params}`;
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
            url: '{{ route("orders.index") }}',
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
                if (filters.coupon_codes) {
                    // Ensure array is serialized as coupon_codes[]
                    d['coupon_codes[]'] = filters.coupon_codes;
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
                                <img src="${data.logo_url}" class="avatar-xs rounded" loading="lazy" referrerpolicy="no-referrer">
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
                data: 'revenue',
                name: 'revenue',
                title: 'Revenue',
                render: function(data, type, row) {
                    return `$${data}`;
                }
            },
            { 
                data: 'sales_amount',
                name: 'sales_amount',
                title: 'Sales Amount',
                render: function(data, type, row) {
                    return `$${data}`;
                }
            },
            { 
                data: 'order_date',
                name: 'order_date',
                title: 'Date',
                type: 'date',
                render: function(data, type, row) {
                    if (type === 'display' || type === 'type') {
                        if (data && data !== 'N/A') {
                            const date = new Date(data);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            });
                        }
                        return 'N/A';
                    }
                    // For sorting and filtering, return the raw date
                    return data;
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
        pageLength: 100,
        lengthMenu: [[100, 250, 500, 1000, -1], [100, 250, 500, 1000, "All"]],
        // Ensure default ordering by Date desc, then Order ID desc
        order: [[7, 'desc'], [0, 'desc']],
        deferRender: true, // Defer rendering for better performance
        stateSave: true, // Save table state
        columnDefs: [
            // Force proper sorting for date and numeric columns
            { type: 'date', targets: [7] },
            { type: 'num', targets: [5, 6] },
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "",
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

            // Ensure we clear loading states after any draw completes
            this.api().on('draw', function() {
                setFiltersLoading(false);
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
