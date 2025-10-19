@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Campaigns Management</h4>
                <p class="text-muted mb-0">Monitor and manage affiliate campaigns across all networks</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Campaigns</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_campaigns'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Campaigns</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_campaigns'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Networks</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['total_networks'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                <h3 class="mb-0 fw-bold text-warning">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Campaigns Overview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Network</th>
                                <th>Status</th>
                                <th>Commission</th>
                                <th>Revenue</th>
                                <th>Orders</th>
                                <th>Last Sync</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary">
                                                    {{ substr($campaign->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $campaign->name }}</h6>
                                                <small class="text-muted">ID: {{ $campaign->campaign_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info-subtle rounded me-2">
                                                <span class="avatar-title bg-info-subtle text-info">
                                                    {{ substr($campaign->network->display_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $campaign->network->display_name }}</h6>
                                                <small class="text-muted">{{ $campaign->network->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($campaign->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($campaign->status === 'paused')
                                            <span class="badge bg-warning">Paused</span>
                                        @elseif($campaign->status === 'inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($campaign->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $campaign->commission_rate }}%</div>
                                        @if($campaign->commission_type)
                                            <small class="text-muted">{{ ucfirst($campaign->commission_type) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success">${{ number_format($campaign->total_revenue, 2) }}</div>
                                        <small class="text-muted">{{ $campaign->total_orders }} orders</small>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="fw-bold">{{ $campaign->total_orders }}</div>
                                            @if($campaign->pending_orders > 0)
                                                <small class="text-warning">{{ $campaign->pending_orders }} pending</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($campaign->last_sync_at)
                                            <div>{{ $campaign->last_sync_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $campaign->last_sync_at->format('H:i:s') }}</small>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="viewCampaignDetails({{ $campaign->id }})">
                                                        <i class="ti ti-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="viewCampaignStats({{ $campaign->id }})">
                                                        <i class="ti ti-chart-bar me-2"></i>View Statistics
                                                    </a>
                                                </li>
                                                @if($campaign->status === 'active')
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="#" onclick="pauseCampaign({{ $campaign->id }})">
                                                            <i class="ti ti-pause me-2"></i>Pause Campaign
                                                        </a>
                                                    </li>
                                                @elseif($campaign->status === 'paused')
                                                    <li>
                                                        <a class="dropdown-item text-success" href="#" onclick="activateCampaign({{ $campaign->id }})">
                                                            <i class="ti ti-play me-2"></i>Activate Campaign
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item text-info" href="#" onclick="syncCampaign({{ $campaign->id }})">
                                                        <i class="ti ti-refresh me-2"></i>Sync Now
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ti ti-speakerphone-off fs-48 mb-3"></i>
                                            <p>No campaigns found. Campaigns will appear here once users connect their networks.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($campaigns->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $campaigns->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Campaign Details Modal -->
<div class="modal fade" id="campaignDetailsModal" tabindex="-1" aria-labelledby="campaignDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="campaignDetailsModalLabel">Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="campaignDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Campaign Statistics Modal -->
<div class="modal fade" id="campaignStatsModal" tabindex="-1" aria-labelledby="campaignStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="campaignStatsModalLabel">Campaign Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="campaignStatsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewCampaignDetails(campaignId) {
    // Load campaign details via AJAX
    fetch(`/admin/campaigns/${campaignId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('campaignDetailsContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('campaignDetailsModal')).show();
            } else {
                alert('Failed to load campaign details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading campaign details');
        });
}

function viewCampaignStats(campaignId) {
    // Load campaign statistics via AJAX
    fetch(`/admin/campaigns/${campaignId}/statistics`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('campaignStatsContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('campaignStatsModal')).show();
            } else {
                alert('Failed to load campaign statistics');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading campaign statistics');
        });
}

function pauseCampaign(campaignId) {
    if (confirm('Are you sure you want to pause this campaign?')) {
        updateCampaignStatus(campaignId, 'paused');
    }
}

function activateCampaign(campaignId) {
    if (confirm('Are you sure you want to activate this campaign?')) {
        updateCampaignStatus(campaignId, 'active');
    }
}

function updateCampaignStatus(campaignId, status) {
    fetch(`/admin/campaigns/${campaignId}/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Campaign ${status} successfully`);
            location.reload();
        } else {
            alert(`Failed to ${status} campaign: ` + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`Error ${status}ing campaign`);
    });
}

function syncCampaign(campaignId) {
    if (confirm('Are you sure you want to sync this campaign now?')) {
        fetch(`/admin/campaigns/${campaignId}/sync`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Campaign sync initiated successfully');
                location.reload();
            } else {
                alert('Failed to sync campaign: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error syncing campaign');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 5 minutes for campaign data
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 300000);
});
</script>
@endpush
