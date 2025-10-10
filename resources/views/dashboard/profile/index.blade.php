@extends('dashboard.layouts.main')

@section('title', 'My Profile')

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
                                <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">My Profile</h4>
                        <p class="text-muted mb-0">Manage your personal information and account settings.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('dashboard.password.change') }}" class="btn btn-outline-warning">
                            <i class="ti ti-key me-1"></i> Change Password
                        </a>
                        <button class="btn btn-primary" onclick="saveProfile()">
                            <i class="ti ti-device-floppy me-1"></i> Save Changes
                        </button>
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
                <!-- Profile Information -->
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <form id="profileForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-user text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 border-0 bg-light" 
                                                   name="name" value="{{ auth()->user()->name }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-mail text-muted"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0 border-0 bg-light" 
                                                   name="email" value="{{ auth()->user()->email }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">User Type</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-tag text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 border-0 bg-light" 
                                                   value="{{ ucfirst(auth()->user()->user_type) }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-shield-check text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 border-0 bg-light" 
                                                   value="{{ auth()->user()->status === 'active' ? 'Active' : 'Inactive' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-phone text-muted"></i>
                                            </span>
                                            <input type="tel" class="form-control border-start-0 border-0 bg-light" 
                                                   name="phone" value="{{ auth()->user()->phone ?? '' }}" 
                                                   placeholder="Enter phone number">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-world text-muted"></i>
                                            </span>
                                            <select class="form-select border-start-0 border-0 bg-light" name="country_id">
                                                <option value="">Select Country</option>
                                                @foreach(\App\Models\Country::all() as $country)
                                                    <option value="{{ $country->id }}" 
                                                            {{ auth()->user()->country_id == $country->id ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Bio</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 align-items-start pt-3">
                                                <i class="ti ti-edit text-muted"></i>
                                            </span>
                                            <textarea class="form-control border-start-0 border-0 bg-light" 
                                                      name="bio" rows="4" placeholder="Tell us about yourself">{{ auth()->user()->bio ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="email_notifications" 
                                                   id="emailNotifications" {{ auth()->user()->email_notifications ? 'checked' : '' }}>
                                            <label class="form-check-label" for="emailNotifications">
                                                Receive email notifications
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Profile Sidebar -->
                <div class="col-xl-4">
                    <!-- Profile Picture -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Profile Picture</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block">
                                <div class="avatar-xl bg-primary-subtle rounded-circle mx-auto mb-3">
                                    <div class="avatar-title bg-primary-subtle text-primary fs-40">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle" 
                                        onclick="changeProfilePicture()">
                                    <i class="ti ti-camera"></i>
                                </button>
                            </div>
                            <p class="text-muted mb-3">Click the camera icon to change your profile picture</p>
                            <input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Member Since</label>
                                <p class="mb-0">{{ auth()->user()->created_at->format('F d, Y') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Last Login</label>
                                <p class="mb-0">{{ auth()->user()->last_seen ? auth()->user()->last_seen->diffForHumans() : 'Never' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Verified</label>
                                <p class="mb-0">
                                    @if(auth()->user()->email_verified_at)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="ti ti-check me-1"></i>Verified
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            <i class="ti ti-alert-triangle me-1"></i>Not Verified
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Role</label>
                                <p class="mb-0">
                                    @if(auth()->user()->roles->count() > 0)
                                        @foreach(auth()->user()->roles as $role)
                                            <span class="badge bg-primary-subtle text-primary me-1">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">No Role</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Quick Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border-end">
                                        <h4 class="mb-1 text-primary">6</h4>
                                        <p class="text-muted mb-0 fs-13">Connected Brokers</p>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <h4 class="mb-1 text-success">24</h4>
                                    <p class="text-muted mb-0 fs-13">Active Campaigns</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-1 text-info">156</h4>
                                    <p class="text-muted mb-0 fs-13">Total Coupons</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-1 text-warning">$12.5K</h4>
                                    <p class="text-muted mb-0 fs-13">Total Revenue</p>
                                </div>
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
    $('#profilePictureInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Update profile picture preview
                $('.avatar-xl').html(`<img src="${e.target.result}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">`);
                dashboardUtils.showSuccess('Profile picture updated successfully!');
            };
            reader.readAsDataURL(file);
        }
    });
});

function saveProfile() {
    const formData = new FormData(document.getElementById('profileForm'));
    
    dashboardUtils.showLoading('button[onclick="saveProfile()"]');
    
        fetch('{{ route("dashboard.profile.update") }}', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            dashboardUtils.showSuccess(data.message);
        } else {
            dashboardUtils.showError(data.message);
        }
    })
    .catch(error => {
        dashboardUtils.showError('Failed to update profile. Please try again.');
    })
    .finally(() => {
        dashboardUtils.hideLoading('button[onclick="saveProfile()"]', '<i class="ti ti-device-floppy me-1"></i> Save Changes');
    });
}

function changeProfilePicture() {
    document.getElementById('profilePictureInput').click();
}
</script>
@endpush