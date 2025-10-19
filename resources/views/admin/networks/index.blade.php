@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Network Management</h4>
                <p class="text-muted mb-0">Manage all affiliate networks and their settings</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search networks...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Networks</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $networks->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Networks</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $networks->where('is_active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Users</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $networks->sum('connected_users_count') }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                <h3 class="mb-0 fw-bold text-warning">${{ number_format($networks->sum('total_revenue'), 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Networks Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Networks</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="networksTable">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>Status</th>
                                <th>Connected Users</th>
                                <th>Campaigns</th>
                                <th>Total Orders</th>
                                <th>Revenue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($networks as $network)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="{{ $network->logo_url ?? '/images/placeholder-network.png' }}" 
                                                 alt="{{ $network->display_name }}" 
                                                 class="rounded-circle avatar-sm">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $network->display_name }}</h6>
                                            <small class="text-muted">{{ $network->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $network->is_active ? 'success' : 'danger' }}-subtle text-{{ $network->is_active ? 'success' : 'danger' }}">
                                        {{ $network->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $network->connected_users_count }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $network->campaigns_count }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($network->total_orders) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">${{ number_format($network->total_revenue, 2) }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.networks.show', $network->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.networks.toggle-status', $network->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="is_active" value="{{ $network->is_active ? 0 : 1 }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ti ti-{{ $network->is_active ? 'ban' : 'check' }} me-2"></i>
                                                        {{ $network->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.networks.proxies') }}?network={{ $network->id }}">
                                                    <i class="ti ti-server me-2"></i>Manage Proxies
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-network fs-48 mb-3"></i>
                                        <h5>No Networks Found</h5>
                                        <p>No networks have been configured yet.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#networksTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search networks...",
            infoFiltered: ""
        }
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        $('#networksTable').DataTable().search(this.value).draw();
    });
});
</script>
@endsection



