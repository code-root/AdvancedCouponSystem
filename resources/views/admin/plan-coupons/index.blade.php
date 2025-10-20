@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Plan Coupons</h4>
                <p class="text-muted mb-0">Manage discount coupons for subscription plans</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.plan-coupons.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Add Coupon
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
                <h5 class="text-muted fs-13 text-uppercase">Total Coupons</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $coupons->total() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Coupons</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $coupons->where('active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Redemptions</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $coupons->sum('redemptions_count') }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Expired Coupons</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $coupons->filter(function($coupon) { return $coupon->expires_at && $coupon->expires_at->isPast(); })->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Coupons Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Coupons</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="couponsTable">
                        <thead>
                            <tr>
                                <th>Coupon Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Redemptions</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $coupon)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                                    {{ substr($coupon->code, 0, 1) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $coupon->code }}</h6>
                                            <small class="text-muted">{{ $coupon->description ?? 'No description' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $coupon->type === 'percentage' ? 'primary' : 'success' }}-subtle text-{{ $coupon->type === 'percentage' ? 'primary' : 'success' }}">
                                        {{ ucfirst($coupon->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">
                                        @if($coupon->type === 'percentage')
                                            {{ $coupon->value }}%
                                        @else
                                            ${{ number_format($coupon->value, 2) }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $coupon->redemptions_count }}</span>
                                    @if($coupon->max_redemptions)
                                        <br>
                                        <small class="text-muted">/ {{ $coupon->max_redemptions }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($coupon->expires_at)
                                        <span class="fw-semibold">{{ $coupon->expires_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted {{ $coupon->expires_at->isPast() ? 'text-danger' : ($coupon->expires_at->isToday() ? 'text-warning' : 'text-muted') }}">
                                            {{ $coupon->expires_at->isPast() ? 'Expired' : ($coupon->expires_at->isToday() ? 'Expires Today' : $coupon->expires_at->diffForHumans()) }}
                                        </small>
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $coupon->active ? 'success' : 'secondary' }}-subtle text-{{ $coupon->active ? 'success' : 'secondary' }}">
                                        {{ $coupon->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.plan-coupons.edit', $coupon) }}">
                                                    <i class="ti ti-edit me-2"></i>Edit Coupon
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.plan-coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this coupon? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="ti ti-trash me-2"></i>Delete Coupon
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
                                        <i class="ti ti-discount fs-48 mb-3"></i>
                                        <h5>No Coupons Found</h5>
                                        <p>No discount coupons have been created yet.</p>
                                        <a href="{{ route('admin.plan-coupons.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Create First Coupon
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($coupons->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $coupons->links() }}
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
    $('#couponsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search coupons...",
            infoFiltered: ""
        }
    });
});
</script>
@endsection


