@extends('dashboard.layouts.main')

@section('title', 'Connected Networks')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [breadcrumb] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="ti ti-home me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('networks.index') }}" class="text-decoration-none">Networks</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Connected Networks</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Connected Networks</h4>
                        <p class="text-muted mb-0">Manage your connected affiliate marketing networks and monitor their status.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshAllConnections()">
                            <i class="ti ti-refresh me-1"></i> Refresh All
                        </button>
                        <a href="{{ route('networks.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Connect New Network
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Connection Stats -->
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-success-subtle rounded">
                                        <div class="avatar-title bg-success-subtle text-success fs-22">
                                            <i class="ti ti-check"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Connected</p>
                                    <h4 class="mb-0"><span class="counter-value" data-target="3">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-warning-subtle rounded">
                                        <div class="avatar-title bg-warning-subtle text-warning fs-22">
                                            <i class="ti ti-alert-triangle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Pending</p>
                                    <h4 class="mb-0"><span class="counter-value" data-target="1">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-danger-subtle rounded">
                                        <div class="avatar-title bg-danger-subtle text-danger fs-22">
                                            <i class="ti ti-x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Disconnected</p>
                                    <h4 class="mb-0"><span class="counter-value" data-target="2">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-info-subtle rounded">
                                        <div class="avatar-title bg-info-subtle text-info fs-22">
                                            <i class="ti ti-currency-dollar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-1">Total Revenue</p>
                                    <h4 class="mb-0">$<span class="counter-value" data-target="12500">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Connected Networks Table -->
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Network Connections</h5>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="testAllConnections()">
                                        <i class="ti ti-plug me-1"></i> Test All
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="syncAllData()">
                                        <i class="ti ti-refresh me-1"></i> Sync All
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="networksTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Network</th>
                                            <th>Status</th>
                                            <th>Last Sync</th>
                                            <th>Campaigns</th>
                                            <th>Revenue</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                            <i class="ti ti-bolt"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">Boostiny</h6>
                                                        <small class="text-muted">Connected since 2024-01-15</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-success-subtle text-success">Connected</span></td>
                                            <td>2 hours ago</td>
                                            <td>12</td>
                                            <td>$3,250</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="syncNetwork('boostiny')" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewNetworkData('boostiny')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editNetwork('boostiny')" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectNetwork('boostiny')" title="Disconnect">
                                                        <i class="ti ti-plug-off"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <div class="avatar-title bg-info-subtle text-info rounded-circle">
                                                            <i class="ti ti-world"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">Admitad</h6>
                                                        <small class="text-muted">Connected since 2024-01-10</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-success-subtle text-success">Connected</span></td>
                                            <td>1 hour ago</td>
                                            <td>8</td>
                                            <td>$2,890</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="syncNetwork('admitad')" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewNetworkData('admitad')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editNetwork('admitad')" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectNetwork('admitad')" title="Disconnect">
                                                        <i class="ti ti-plug-off"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                                            <i class="ti ti-optimization"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">Optimize</h6>
                                                        <small class="text-muted">Connected since 2024-01-08</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-success-subtle text-success">Connected</span></td>
                                            <td>30 minutes ago</td>
                                            <td>15</td>
                                            <td>$6,360</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="syncNetwork('optimize')" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewNetworkData('optimize')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editNetwork('optimize')" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectNetwork('optimize')" title="Disconnect">
                                                        <i class="ti ti-plug-off"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <div class="avatar-title bg-secondary-subtle text-secondary rounded-circle">
                                                            <i class="ti ti-device-desktop"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">Digizag</h6>
                                                        <small class="text-muted">Pending connection</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="completeConnection('digizag')" title="Complete">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="cancelConnection('digizag')" title="Cancel">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                                            <i class="ti ti-building"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">Platformance</h6>
                                                        <small class="text-muted">Disconnected on 2024-01-12</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-danger-subtle text-danger">Disconnected</span></td>
                                            <td>2 days ago</td>
                                            <td>5</td>
                                            <td>$890</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="reconnectNetwork('platformance')" title="Reconnect">
                                                        <i class="ti ti-plug"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewNetworkData('platformance')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <div class="avatar-title bg-danger-subtle text-danger rounded-circle">
                                                            <i class="ti ti-chart-pie"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">Marketeers</h6>
                                                        <small class="text-muted">Never connected</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-danger-subtle text-danger">Not Connected</span></td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                <button class="btn btn-outline-primary btn-sm" onclick="connectNetwork('marketeers')" title="Connect">
                                                    <i class="ti ti-plug me-1"></i>Connect
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Counter animation
    $('.counter-value').each(function() {
        var $this = $(this);
        var target = parseInt($this.data('target'));
        
        $({ Counter: 0 }).animate({ Counter: target }, {
            duration: 2000,
            easing: 'swing',
            step: function() {
                $this.text(Math.ceil(this.Counter));
            }
        });
    });

    // Initialize DataTable
    $('#networksTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });
});

function refreshAllConnections() {
    dashboardUtils.showSuccess('Refreshing all connections...');
}

function testAllConnections() {
    dashboardUtils.showSuccess('Testing all connections...');
}

function syncAllData() {
    dashboardUtils.showSuccess('Syncing all data...');
}

function syncNetwork(network) {
    dashboardUtils.showSuccess(`Syncing ${network} data...`);
}

function viewNetworkData(network) {
    window.location.href = `/networks/${network}/data`;
}

function editNetwork(network) {
    window.location.href = `/networks/${network}`;
}

function disconnectNetwork(network) {
    dashboardUtils.showConfirm(
        'Disconnect Network',
        `Are you sure you want to disconnect ${network}?`,
        () => {
            dashboardUtils.showSuccess(`${network} disconnected successfully!`);
        }
    );
}

function completeConnection(network) {
    dashboardUtils.showSuccess(`Completing ${network} connection...`);
}

function cancelConnection(network) {
    dashboardUtils.showConfirm(
        'Cancel Connection',
        `Cancel ${network} connection?`,
        () => {
            dashboardUtils.showSuccess(`${network} connection cancelled!`);
        }
    );
}

function reconnectNetwork(network) {
    window.location.href = `/networks/${network}`;
}

function connectNetwork(network) {
    window.location.href = `/networks/${network}`;
}
</script>
@endpush