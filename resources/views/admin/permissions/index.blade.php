@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Permissions Management</h4>
                <p class="text-muted mb-0">View and manage system permissions</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Permissions</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $permissions->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Permission Groups</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $permissions->groupBy('name')->keys()->map(function($name) { return explode('-', $name)[0]; })->unique()->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Most Used</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $permissions->max('roles_count') ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Unused</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $permissions->where('roles_count', 0)->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Permissions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                <th>Group</th>
                                <th>Roles</th>
                                <th>Usage</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary">
                                                    {{ substr($permission->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ ucfirst(str_replace('-', ' ', $permission->name)) }}</h6>
                                                <small class="text-muted">{{ $permission->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $group = explode('-', $permission->name)[0];
                                        @endphp
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ ucfirst($group) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="fw-bold">{{ $permission->roles_count }}</div>
                                            <small class="text-muted">roles</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($permission->roles_count > 0)
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 8px;">
                                                    @php
                                                        $maxUsage = $permissions->max('roles_count');
                                                        $percentage = $maxUsage > 0 ? ($permission->roles_count / $maxUsage) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar {{ $percentage > 50 ? 'bg-success' : ($percentage > 25 ? 'bg-warning' : 'bg-info') }}" 
                                                         style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="text-muted">{{ number_format($percentage, 1) }}%</span>
                                            </div>
                                        @else
                                            <span class="text-muted">Not used</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $permission->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $permission->created_at->format('H:i:s') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ti ti-shield-off fs-48 mb-3"></i>
                                            <p>No permissions found.</p>
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

<!-- Permission Groups Overview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Permission Groups Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $groupedPermissions = $permissions->groupBy(function($permission) {
                            return explode('-', $permission->name)[0];
                        });
                    @endphp
                    
                    @foreach($groupedPermissions as $group => $groupPermissions)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm bg-primary-subtle rounded me-2">
                                            <span class="avatar-title bg-primary-subtle text-primary">
                                                {{ substr($group, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ ucfirst($group) }}</h6>
                                            <small class="text-muted">{{ $groupPermissions->count() }} permissions</small>
                                        </div>
                                    </div>
                                    
                                    <div class="permission-list">
                                        @foreach($groupPermissions->take(5) as $permission)
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span class="text-muted">{{ ucfirst(str_replace('-', ' ', $permission->name)) }}</span>
                                            </div>
                                        @endforeach
                                        
                                        @if($groupPermissions->count() > 5)
                                            <div class="text-muted">
                                                <small>+{{ $groupPermissions->count() - 5 }} more permissions</small>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Total roles using:</small>
                                            <span class="badge bg-primary">{{ $groupPermissions->sum('roles_count') }}</span>
                                        </div>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive functionality here if needed
    console.log('Permissions page loaded');
});
</script>
@endpush

