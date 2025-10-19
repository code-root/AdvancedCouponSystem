@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Network Proxies</h4>
                <p class="text-muted mb-0">Manage proxy servers for network connections</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.networks.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Networks
                        </a>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProxyModal">
                            <i class="ti ti-plus me-1"></i>Add Proxy
                        </button>
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
                <h5 class="text-muted fs-13 text-uppercase">Total Proxies</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $proxies->total() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Proxies</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $proxies->where('is_active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Networks</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $networks->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Inactive Proxies</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $proxies->where('is_active', false)->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Proxies Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Proxies</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="proxiesTable">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>Proxy URL</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proxies as $proxy)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                                    <i class="ti ti-affiliate"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $proxy->network->display_name }}</h6>
                                            <small class="text-muted">{{ $proxy->network->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code>{{ $proxy->proxy_url }}</code>
                                </td>
                                <td>
                                    {{ $proxy->username ?: 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $proxy->is_active ? 'success' : 'danger' }}-subtle text-{{ $proxy->is_active ? 'success' : 'danger' }}">
                                        {{ $proxy->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $proxy->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button class="dropdown-item" onclick="editProxy({{ $proxy->id }})">
                                                    <i class="ti ti-edit me-2"></i>Edit
                                                </button>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.networks.proxies.update', $proxy->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="is_active" value="{{ $proxy->is_active ? 0 : 1 }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ti ti-{{ $proxy->is_active ? 'ban' : 'check' }} me-2"></i>
                                                        {{ $proxy->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.networks.proxies.destroy', $proxy->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this proxy?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ti ti-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-server fs-48 mb-3"></i>
                                        <h5>No Proxies Found</h5>
                                        <p>No proxy servers have been configured yet.</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProxyModal">
                                            <i class="ti ti-plus me-1"></i>Add First Proxy
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($proxies->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $proxies->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Proxy Modal -->
<div class="modal fade" id="addProxyModal" tabindex="-1" aria-labelledby="addProxyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.networks.proxies.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addProxyModalLabel">Add New Proxy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="network_id" class="form-label">Network <span class="text-danger">*</span></label>
                        <select class="form-select" id="network_id" name="network_id" required>
                            <option value="">Select Network</option>
                            @foreach($networks as $network)
                            <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="proxy_url" class="form-label">Proxy URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="proxy_url" name="proxy_url" placeholder="http://proxy.example.com:8080" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Optional username">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Optional password">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Proxy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Proxy Modal -->
<div class="modal fade" id="editProxyModal" tabindex="-1" aria-labelledby="editProxyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editProxyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editProxyModalLabel">Edit Proxy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_network_id" class="form-label">Network <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_network_id" name="network_id" required>
                            <option value="">Select Network</option>
                            @foreach($networks as $network)
                            <option value="{{ $network->id }}">{{ $network->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_proxy_url" class="form-label">Proxy URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="edit_proxy_url" name="proxy_url" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username">
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Proxy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#proxiesTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: [5] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search proxies...",
            infoFiltered: ""
        }
    });
});

function editProxy(proxyId) {
    // Fetch proxy data and populate edit modal
    fetch(`/admin/networks/proxies/${proxyId}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editProxyForm').action = `/admin/networks/proxies/${proxyId}`;
            document.getElementById('edit_network_id').value = data.network_id;
            document.getElementById('edit_proxy_url').value = data.proxy_url;
            document.getElementById('edit_username').value = data.username || '';
            document.getElementById('edit_is_active').checked = data.is_active;
            
            // Show modal
            new bootstrap.Modal(document.getElementById('editProxyModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading proxy data');
        });
}
</script>
@endsection



