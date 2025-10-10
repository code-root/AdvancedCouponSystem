@extends('layouts.vertical', ['title' => 'Broker Integration'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css', 'node_modules/sweetalert2/dist/sweetalert2.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Affiliate', 'title' => 'Broker Integration'])

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Connections</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                <i class="ti ti-affiliate"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ $userConnections->total() }}</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Broker connections</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Active</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-success rounded-circle fs-22">
                                <i class="ti ti-plug"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ auth()->user()->getActiveBrokerConnectionsCount() }}</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Connected now</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Available</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-info rounded-circle fs-22">
                                <i class="ti ti-building-store"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ $availableBrokers->count() }}</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Brokers to connect</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Brokers</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                <i class="ti ti-list"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">{{ \App\Models\Broker::where('is_active', true)->count() }}</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">In system</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Broker Connections -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h4 class="header-title mb-0">My Broker Connections</h4>
                        <div class="d-flex gap-2">
                            <div class="position-relative">
                                <input type="text" class="form-control ps-4 form-control-sm" id="searchConnections" placeholder="Search connections">
                                <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                            </div>
                            <a href="{{ route('brokers.create') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plug-connected me-1"></i> Connect Broker
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Broker</th>
                                <th>Connection Name</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Connected At</th>
                                <th>Last Sync</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($userConnections as $connection)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-md flex-shrink-0 me-3">
                                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                <i class="ti ti-building-store fs-18"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fs-14 fw-semibold">{{ $connection->broker->display_name }}</h6>
                                            <small class="text-muted">{{ $connection->broker->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $connection->connection_name }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <img src="/images/flags/{{ strtolower($connection->broker->country) }}.svg" 
                                             class="me-1" alt="{{ $connection->broker->country }}" height="16" 
                                             onerror="this.style.display='none'">
                                        <span>{{ $connection->broker->country }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($connection->is_connected && $connection->status === 'active')
                                        <span class="badge bg-success-subtle text-success p-1">
                                            <i class="ti ti-plug"></i> Connected
                                        </span>
                                    @elseif($connection->status === 'pending')
                                        <span class="badge bg-warning-subtle text-warning p-1">
                                            <i class="ti ti-clock"></i> Pending
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger p-1">
                                            <i class="ti ti-plug-off"></i> Disconnected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $connection->connected_at ? $connection->connected_at->format('M d, Y H:i') : '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $connection->last_sync ? $connection->last_sync->diffForHumans() : 'Never' }}</span>
                                </td>
                                <td class="pe-3">
                                    <div class="hstack gap-1 justify-content-end">
                                        @if($connection->is_connected)
                                        <button class="btn btn-soft-primary btn-icon btn-sm rounded-circle" onclick="syncConnection({{ $connection->id }})" title="Sync Data">
                                            <i class="ti ti-refresh"></i>
                                        </button>
                                        @endif
                                        <a href="{{ route('brokers.show', $connection->broker_id) }}" class="btn btn-soft-info btn-icon btn-sm rounded-circle" title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('brokers.edit', $connection->id) }}" class="btn btn-soft-warning btn-icon btn-sm rounded-circle" title="Edit">
                                            <i class="ti ti-edit fs-16"></i>
                                        </a>
                                        <button class="btn btn-soft-danger btn-icon btn-sm rounded-circle" onclick="disconnectBroker({{ $connection->id }})" title="Disconnect">
                                            <i class="ti ti-plug-off"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-plug-off fs-64 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-3">No Broker Connections Found</h5>
                                        <p class="text-muted mb-4">You haven't connected any brokers yet. Start connecting to manage your affiliate campaigns.</p>
                                        <a href="{{ route('brokers.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plug-connected me-1"></i> Connect Your First Broker
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($userConnections->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        {{ $userConnections->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Available Brokers to Connect -->
    @if($availableBrokers->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-0">Available Brokers to Connect</h4>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xxl-4 g-3">
                        @foreach($availableBrokers as $broker)
                        <div class="col">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="avatar-lg bg-primary-subtle mx-auto mb-3">
                                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-32">
                                                <i class="ti ti-building-store"></i>
                                            </span>
                                        </div>
                                        <h5 class="fw-semibold mb-1">{{ $broker->display_name }}</h5>
                                        <p class="text-muted mb-2">{{ $broker->country }}</p>
                                        <div class="flex-grow-1 d-inline-flex align-items-center fs-16 mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="ti ti-star-filled text-warning"></span>
                                            @endfor
                                            <span class="ms-1 fs-14">4.8</span>
                                        </div>
                                    </div>

                                    <div class="border-top border-dashed pt-3 mt-3">
                                        <div class="row text-center g-2">
                                            <div class="col-6">
                                                <p class="text-muted mb-1 fs-12">Commission</p>
                                                <h6 class="mb-0">Up to {{ $broker->commission_rate }}%</h6>
                                            </div>
                                            <div class="col-6">
                                                <p class="text-muted mb-1 fs-12">Products</p>
                                                <h6 class="mb-0">{{ $broker->total_products ?? '1000+' }}</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-grid">
                                        <a href="{{ route('brokers.create', ['broker' => $broker->id]) }}" class="btn btn-primary btn-sm">
                                            <i class="ti ti-plug-connected me-1"></i> Connect Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function syncConnection(connectionId) {
    Swal.fire({
        title: 'Syncing Data...',
        text: 'Fetching latest data from broker',
        icon: 'info',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/brokers/${connectionId}/sync`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Synced!',
                text: data.message || 'Data synced successfully',
                timer: 2000,
                showConfirmButton: false
            });
            setTimeout(() => location.reload(), 2000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Sync Failed',
                text: data.message || 'Failed to sync data'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while syncing'
        });
    });
}

function disconnectBroker(connectionId) {
    Swal.fire({
        title: 'Disconnect Broker?',
        text: 'Are you sure you want to disconnect this broker?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fa5c7c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, disconnect!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/brokers/${connectionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Disconnected!',
                        text: data.message || 'Broker disconnected successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to disconnect broker'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to disconnect broker'
                });
            });
        }
    });
}

// Search functionality
document.getElementById('searchConnections')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@endsection
