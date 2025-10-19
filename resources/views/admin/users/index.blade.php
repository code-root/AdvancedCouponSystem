@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">User Management</h4>
                <p class="text-muted mb-0">Manage all system users and their subscriptions</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.user-management.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Add User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Users</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_users'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Subscriptions</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_subscriptions'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Trial Users</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['trial_users'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Expired</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['expired_subscriptions'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">No Subscription</h5>
                <h3 class="mb-0 fw-bold text-secondary">{{ $stats['no_subscription'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">This Month</h5>
                <h3 class="mb-0 fw-bold text-dark">{{ $stats['users_this_month'] }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Users</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Subscription</th>
                                <th>Networks</th>
                                <th>Campaigns</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->subscription)
                                        <div>
                                            <span class="badge bg-{{ $user->subscription->status === 'active' ? 'success' : ($user->subscription->status === 'trial' ? 'info' : 'warning') }}-subtle text-{{ $user->subscription->status === 'active' ? 'success' : ($user->subscription->status === 'trial' ? 'info' : 'warning') }}">
                                                {{ ucfirst($user->subscription->status) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $user->subscription->plan->name ?? 'N/A' }}</small>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">No Subscription</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $user->connected_networks_count }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $user->campaigns_count }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($user->purchases_count) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">${{ number_format($user->total_revenue, 2) }}</span>
                                </td>
                                <td>
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.user-management.show', $user->id) }}">
                                                    <i class="ti ti-eye me-2"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.user-management.edit', $user->id) }}">
                                                    <i class="ti ti-edit me-2"></i>Edit User
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" onclick="impersonateUser({{ $user->id }})">
                                                    <i class="ti ti-user-check me-2"></i>Impersonate
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.user-management.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="active" value="{{ $user->active ?? true ? 0 : 1 }}">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ti ti-{{ $user->active ?? true ? 'ban' : 'check' }} me-2"></i>
                                                        {{ $user->active ?? true ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.user-management.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ti ti-trash me-2"></i>Delete User
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-users fs-48 mb-3"></i>
                                        <h5>No Users Found</h5>
                                        <p>No users have been registered yet.</p>
                                        <a href="{{ route('admin.user-management.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Add First User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($users->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Wait for jQuery to be available
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // Initialize DataTable
    $('#usersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[6, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search users...",
            infoFiltered: ""
        }
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        $('#usersTable').DataTable().search(this.value).draw();
    });
});

function impersonateUser(userId) {
    if (confirm('Are you sure you want to impersonate this user? You will be logged in as them.')) {
        // Show loading indicator
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="ti ti-loader me-1"></i>Impersonating...';
        button.disabled = true;
        
        window.ajaxHelper.post(`/admin/user-management/${userId}/impersonate`)
            .then(data => {
                if (data.success) {
                    // Show success message briefly before redirect
                    alert('تم تسجيل الدخول كالمستخدم بنجاح! سيتم توجيهك الآن...');
                    // Redirect to user dashboard
                    window.location.href = data.redirect_url;
                } else {
                    alert('فشل في تسجيل الدخول كالمستخدم: ' + (data.message || 'خطأ غير معروف'));
                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('خطأ في تسجيل الدخول كالمستخدم: ' + error.message);
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }
}
</script>
@endsection



