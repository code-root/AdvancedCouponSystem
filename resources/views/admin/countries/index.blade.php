@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Countries Management</h4>
                <p class="text-muted mb-0">Manage countries and their settings for the system</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Country
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Countries</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_countries'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Countries</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_countries'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Inactive Countries</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['inactive_countries'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Recently Added</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['recent_countries'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Countries List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Flag</th>
                                <th>Country</th>
                                <th>Code</th>
                                <th>Currency</th>
                                <th>Timezone</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($countries as $country)
                                <tr>
                                    <td>
                                        @if($country->flag)
                                            <img src="{{ $country->flag }}" alt="{{ $country->name }}" 
                                                 class="img-thumbnail" style="width: 32px; height: 24px;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 24px;">
                                                <i class="ti ti-world text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $country->name }}</h6>
                                            @if($country->native_name && $country->native_name !== $country->name)
                                                <small class="text-muted">{{ $country->native_name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $country->code }}</code>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-bold">{{ $country->currency_code }}</span>
                                            @if($country->currency_symbol)
                                                <small class="text-muted">({{ $country->currency_symbol }})</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $country->timezone ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($country->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $country->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $country->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.countries.edit', $country->id) }}">
                                                        <i class="ti ti-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="toggleCountryStatus({{ $country->id }}, {{ $country->is_active ? 'false' : 'true' }})">
                                                        <i class="ti ti-{{ $country->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                        {{ $country->is_active ? 'Deactivate' : 'Activate' }}
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="deleteCountry({{ $country->id }})">
                                                        <i class="ti ti-trash me-2"></i>Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ti ti-world-off fs-48 mb-3"></i>
                                            <p>No countries found. <a href="{{ route('admin.countries.create') }}">Add the first country</a></p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($countries->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $countries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCountryStatus(countryId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this country?`)) {
        fetch(`/admin/countries/${countryId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ is_active: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Country ${action}d successfully`);
                location.reload();
            } else {
                alert(`Failed to ${action} country: ` + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Error ${action}ing country`);
        });
    }
}

function deleteCountry(countryId) {
    if (confirm('Are you sure you want to delete this country? This action cannot be undone.')) {
        fetch(`/admin/countries/${countryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Country deleted successfully');
                location.reload();
            } else {
                alert('Failed to delete country: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting country');
        });
    }
}
</script>
@endpush
