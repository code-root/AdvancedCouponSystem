@extends('layouts.dashboard')

@section('title', 'Connected Brokers')

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
                                    <a href="{{ route('brokers.index') }}" class="text-decoration-none">Brokers</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Connected Brokers</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Connected Brokers</h4>
                        <p class="text-muted mb-0">Manage your connected affiliate marketing brokers and monitor their status.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshAllConnections()">
                            <i class="ti ti-refresh me-1"></i> Refresh All
                        </button>
                        <a href="{{ route('brokers.connect') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Connect New Broker
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
                <!-- Connected Brokers Table -->
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold">Broker Connections</h5>
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
                                <table class="table table-striped table-hover align-middle" id="brokersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Broker</th>
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
                                                    <button class="btn btn-outline-primary" onclick="syncBroker('boostiny')" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewBrokerData('boostiny')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editBroker('boostiny')" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectBroker('boostiny')" title="Disconnect">
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
                                                    <button class="btn btn-outline-primary" onclick="syncBroker('admitad')" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewBrokerData('admitad')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editBroker('admitad')" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectBroker('admitad')" title="Disconnect">
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
                                                    <button class="btn btn-outline-primary" onclick="syncBroker('optimize')" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewBrokerData('optimize')" title="View Data">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning" onclick="editBroker('optimize')" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectBroker('optimize')" title="Disconnect">
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
                                                    <button class="btn btn-outline-primary" onclick="reconnectBroker('platformance')" title="Reconnect">
                                                        <i class="ti ti-plug"></i>
                                                    </button>
                                                    <button class="btn btn-outline-info" onclick="viewBrokerData('platformance')" title="View Data">
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
                                                <button class="btn btn-outline-primary btn-sm" onclick="connectBroker('marketeers')" title="Connect">
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
    $('#brokersTable').DataTable({
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

function syncBroker(broker) {
    dashboardUtils.showSuccess(`Syncing ${broker} data...`);
}

function viewBrokerData(broker) {
    window.location.href = `/brokers/${broker}/data`;
}

function editBroker(broker) {
    window.location.href = `/brokers/${broker}`;
}

function disconnectBroker(broker) {
    dashboardUtils.showConfirm(
        'Disconnect Broker',
        `Are you sure you want to disconnect ${broker}?`,
        () => {
            dashboardUtils.showSuccess(`${broker} disconnected successfully!`);
        }
    );
}

function completeConnection(broker) {
    dashboardUtils.showSuccess(`Completing ${broker} connection...`);
}

function cancelConnection(broker) {
    dashboardUtils.showConfirm(
        'Cancel Connection',
        `Cancel ${broker} connection?`,
        () => {
            dashboardUtils.showSuccess(`${broker} connection cancelled!`);
        }
    );
}

function reconnectBroker(broker) {
    window.location.href = `/brokers/${broker}`;
}

function connectBroker(broker) {
    window.location.href = `/brokers/${broker}`;
}
</script>
@endpush