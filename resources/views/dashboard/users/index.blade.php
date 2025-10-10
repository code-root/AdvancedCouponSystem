@extends('layouts.vertical', ['title' => 'Users Management'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Administration', 'title' => 'Users Management'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="ti ti-user-plus me-1"></i> Add New User
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h4 class="header-title mb-0">My Sub-Users ({{ $users->total() }})</h4>
                            <p class="text-muted mb-0 small">Users created by you</p>
                        </div>
                        <div class="position-relative">
                            <input type="text" class="form-control ps-4" placeholder="Search User" id="searchUser">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">User</th>
                                <th>Email</th>
                                <th class="text-center">Networks</th>
                                <th class="text-center">Campaigns</th>
                                <th class="text-center">Purchases</th>
                                <th>Joined</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <span class="fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                                            @if($user->id === auth()->id())
                                                <span class="badge bg-info-subtle text-info badge-sm">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="ti ti-affiliate me-1"></i>{{ $user->network_connections_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ti ti-speakerphone me-1"></i>{{ $user->campaigns_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ti ti-shopping-cart me-1"></i>{{ $user->purchases_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="pe-3">
                                    <div class="hstack gap-1 justify-content-end">
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-soft-primary btn-icon btn-sm rounded-circle" title="Edit">
                                            <i class="ti ti-edit fs-16"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-icon btn-sm rounded-circle" 
                                                    onclick="return confirm('Are you sure you want to delete this user?')"
                                                    title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @else
                                        <button type="button" class="btn btn-soft-secondary btn-icon btn-sm rounded-circle" disabled title="Cannot delete yourself">
                                            <i class="ti ti-lock"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-users-off fs-64 text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted mb-3">No Sub-Users Yet</h5>
                                        <p class="text-muted mb-3">You haven't created any users yet</p>
                                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                                            <i class="ti ti-user-plus me-1"></i> Create Your First Sub-User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        {{ $users->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="ti ti-users fs-36 text-primary mb-2"></i>
                    <h3 class="mb-0 fw-bold">{{ $users->total() }}</h3>
                    <p class="text-muted mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="ti ti-affiliate fs-36 text-success mb-2"></i>
                    <h3 class="mb-0 fw-bold">{{ $users->sum('network_connections_count') }}</h3>
                    <p class="text-muted mb-0">Total Network Connections</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="ti ti-speakerphone fs-36 text-warning mb-2"></i>
                    <h3 class="mb-0 fw-bold">{{ $users->sum('campaigns_count') }}</h3>
                    <p class="text-muted mb-0">Total Campaigns</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
// Simple search functionality
document.getElementById('searchUser')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@endsection
