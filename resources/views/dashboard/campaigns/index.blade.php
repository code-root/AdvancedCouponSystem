@extends('layouts.vertical', ['title' => 'Campaigns'])

@section('css')
    @vite(['node_modules/flatpickr/dist/flatpickr.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Marketing', 'title' => 'Campaigns'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Create Campaign
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center mb-3">
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
                    <h5 class="text-muted fs-13 text-uppercase">Active</h5>
                    <h3 class="mb-0 fw-bold text-success" id="stat-active">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Paused</h5>
                    <h3 class="mb-0 fw-bold text-warning" id="stat-paused">{{ $stats['paused'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Inactive</h5>
                    <h3 class="mb-0 fw-bold text-danger" id="stat-inactive">{{ $stats['inactive'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Coupons</h5>
                    <h3 class="mb-0 fw-bold text-primary" id="stat-coupon">{{ $stats['coupon_type'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Links</h5>
                    <h3 class="mb-0 fw-bold text-info" id="stat-link">{{ $stats['link_type'] }}</h3>
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
                            <input type="text" class="form-control" id="searchInput" placeholder="Campaign name or ID...">
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
                        
                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="select2 form-control" id="statusFilter" data-toggle="select2">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="paused">Paused</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Campaign Type Filter -->
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select class="select2 form-control" id="typeFilter" data-toggle="select2">
                                <option value="">All Types</option>
                                <option value="coupon">Coupon</option>
                                <option value="link">Link</option>
                                <option value="app">App</option>
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

    <!-- Campaigns Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <h4 class="header-title mb-0">All Campaigns</h4>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0" id="campaignsTable">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Campaign Name</th>
                                <th>Network</th>
                                <th>Type</th>
                                <th>Coupons</th>
                                <th>Purchases</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="campaignsTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Loading campaigns...</p>
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

window.addEventListener('load', function() {
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }
    
    // Initialize Flatpickr for date range
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
    
    // Load campaigns on page load
    loadCampaigns();
    
    // Search on keyup with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filters.search = e.target.value;
            applyFilters();
        }, 500);
    });
});

// Load campaigns data
function loadCampaigns(page = 1) {
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
        url: '{{ route("campaigns.index") }}?' + params.toString(),
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            if (data.success) {
                renderCampaigns(data.data.data);
                renderPagination(data.data);
                updateStats(data.stats);
            }
        },
        error: function(error) {
            console.error('Error loading campaigns:', error);
            showEmptyState('Error loading campaigns');
        }
    });
}

// Render campaigns table
function renderCampaigns(campaigns) {
    const tbody = document.getElementById('campaignsTableBody');
    
    if (campaigns.length === 0) {
        showEmptyState('No campaigns found');
        return;
    }
    
    let html = '';
    campaigns.forEach(campaign => {
        const statusBadge = getStatusBadge(campaign.status);
        const typeBadge = getTypeBadge(campaign.campaign_type);
        
        html += `
            <tr>
                <td class="ps-3">
                    <div>
                        <h6 class="mb-0 fs-14 fw-semibold">${campaign.name}</h6>
                        <small class="text-muted">#${campaign.network_campaign_id || campaign.id}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-primary-subtle text-primary">${campaign.network?.display_name || 'N/A'}</span>
                </td>
                <td>${typeBadge}</td>
                <td><span class="badge bg-info">${campaign.coupons?.length || 0}</span></td>
                <td><span class="badge bg-success">${campaign.purchases_count || 0}</span></td>
                <td><strong class="text-success">$${parseFloat(campaign.total_revenue || 0).toFixed(2)}</strong></td>
                <td>${statusBadge}</td>
                <td class="pe-3 text-center">
                    <div class="hstack gap-1 justify-content-center">
                        <a href="/campaigns/${campaign.id}" class="btn btn-soft-info btn-icon btn-sm rounded-circle">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="/campaigns/${campaign.id}/edit" class="btn btn-soft-warning btn-icon btn-sm rounded-circle">
                            <i class="ti ti-edit"></i>
                        </a>
                        <button class="btn btn-soft-danger btn-icon btn-sm rounded-circle" onclick="deleteCampaign(${campaign.id})">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Show empty state
function showEmptyState(message = 'No campaigns found') {
    document.getElementById('campaignsTableBody').innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-5">
                <div class="py-4">
                    <i class="ti ti-speakerphone fs-64 text-muted mb-3"></i>
                    <h5 class="text-muted mb-3">${message}</h5>
                    <p class="text-muted mb-4">Try adjusting your filters or create a new campaign.</p>
                    <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Create Campaign
                    </a>
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
        <a class="page-link" href="#" onclick="loadCampaigns(${data.current_page - 1}); return false;">Previous</a>
    </li>`;
    
    // Pages
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadCampaigns(${i}); return false;">${i}</a>
            </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="loadCampaigns(${data.current_page + 1}); return false;">Next</a>
    </li>`;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

// Helper functions
function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge bg-success-subtle text-success">Active</span>',
        'paused': '<span class="badge bg-warning-subtle text-warning">Paused</span>',
        'inactive': '<span class="badge bg-danger-subtle text-danger">Inactive</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function getTypeBadge(type) {
    const badges = {
        'coupon': '<span class="badge bg-primary-subtle text-primary"><i class="ti ti-ticket me-1"></i>Coupon</span>',
        'link': '<span class="badge bg-info-subtle text-info"><i class="ti ti-link me-1"></i>Link</span>',
        'app': '<span class="badge bg-success-subtle text-success"><i class="ti ti-apps me-1"></i>App</span>'
    };
    return badges[type] || '<span class="badge bg-secondary">Unknown</span>';
}

// Apply filters
function applyFilters() {
    const networkIds = $('#networkFilter').val() || [];
    filters.network_ids = networkIds.length > 0 ? networkIds : null;
    filters.status = $('#statusFilter').val();
    filters.campaign_type = $('#typeFilter').val();
    filters.search = document.getElementById('searchInput').value;
    
    loadCampaigns(1);
}

// Reset filters
function resetFilters() {
    filters = {};
    document.getElementById('searchInput').value = '';
    $('#networkFilter').val(null).trigger('change');
    $('#statusFilter').val('').trigger('change');
    $('#typeFilter').val('').trigger('change');
    document.getElementById('dateRange').value = '';
    
    loadCampaigns(1);
}

// Update statistics
function updateStats(stats) {
    document.getElementById('stat-total').textContent = stats.total || 0;
    document.getElementById('stat-active').textContent = stats.active || 0;
    document.getElementById('stat-paused').textContent = stats.paused || 0;
    document.getElementById('stat-inactive').textContent = stats.inactive || 0;
    document.getElementById('stat-coupon').textContent = stats.coupon_type || 0;
    document.getElementById('stat-link').textContent = stats.link_type || 0;
}

// Delete campaign
function deleteCampaign(campaignId) {
    if (!confirm('Are you sure you want to delete this campaign?')) {
        return;
    }
    
    fetch(`/campaigns/${campaignId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCampaigns(currentPage);
        } else {
            alert('Failed to delete campaign');
        }
    });
}
</script>
@endsection
