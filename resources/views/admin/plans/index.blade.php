@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Subscription Plans</h4>
                <p class="text-muted mb-0">Manage subscription plans and pricing</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Add Plan
                        </a>
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
                <h5 class="text-muted fs-13 text-uppercase">Total Plans</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $plans->total() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Plans</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $plans->where('is_active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Inactive Plans</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $plans->where('is_active', false)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Average Price</h5>
                <h3 class="mb-0 fw-bold text-info">${{ number_format($plans->avg('price'), 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Plans Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Plans</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="plansTable">
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Price</th>
                                <th>Trial</th>
                                <th>Max Networks</th>
                                <th>Sync Window</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ substr($plan->name, 0, 1) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $plan->name }}</h6>
                                            <small class="text-muted">{{ $plan->description ?? 'No description' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">${{ number_format($plan->price, 2) }}</span>
                                    <br>
                                    <small class="text-muted">{{ $plan->currency }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $plan->trial_days }}</span>
                                    <br>
                                    <small class="text-muted">days</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $plan->max_networks }}</span>
                                    <br>
                                    <small class="text-muted">networks</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $plan->sync_window_size }}</span>
                                    <br>
                                    <small class="text-muted">{{ $plan->sync_window_unit }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}-subtle text-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.plans.edit', $plan) }}">
                                                    <i class="ti ti-edit me-2"></i>Edit Plan
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this plan? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ti ti-trash me-2"></i>Delete Plan
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-credit-card fs-48 mb-3"></i>
                                        <h5>No Plans Found</h5>
                                        <p>No subscription plans have been created yet.</p>
                                        <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Create First Plan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($plans->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $plans->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // Initialize DataTable
    $('#plansTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search plans...",
            infoFiltered: ""
        }
    });
});
</script>
@endsection




