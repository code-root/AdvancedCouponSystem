@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit User</h4>
                <p class="text-muted mb-0">Update user information and settings</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <a href="{{ route('admin.user-management.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Users
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.user-management.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Account Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="active" {{ old('status', $user->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="suspended" {{ old('status', $user->status ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Subscription Plan</label>
                                <select class="form-select @error('plan_id') is-invalid @enderror" 
                                        id="plan_id" name="plan_id">
                                    <option value="">No Plan</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id', $user->subscription?->plan_id) == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} - ${{ $plan->price }}/{{ $plan->billing_cycle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subscription_status" class="form-label">Subscription Status</label>
                                <select class="form-select @error('subscription_status') is-invalid @enderror" 
                                        id="subscription_status" name="subscription_status">
                                    <option value="">No Subscription</option>
                                    <option value="active" {{ old('subscription_status', $user->subscription?->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="trial" {{ old('subscription_status', $user->subscription?->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                                    <option value="expired" {{ old('subscription_status', $user->subscription?->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="cancelled" {{ old('subscription_status', $user->subscription?->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('subscription_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Admin Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Internal notes about this user...">{{ old('notes', $user->admin_notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Update User
                        </button>
                        <a href="{{ route('admin.user-management.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.user-management.password', $user->id) }}" id="password-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" name="new_password" required>
                            <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                    onclick="togglePassword('new_password')">
                                <i class="ti ti-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                                   id="new_password_confirmation" name="new_password_confirmation" required>
                            <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                    onclick="togglePassword('new_password_confirmation')">
                                <i class="ti ti-eye" id="new_password_confirmation_icon"></i>
                            </button>
                        </div>
                        @error('new_password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="ti ti-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Overview</h5>
            </div>
            <div class="card-body text-center">
                <div class="avatar-lg bg-primary-subtle rounded mx-auto mb-3">
                    <span class="avatar-title bg-primary-subtle text-primary fs-24">
                        {{ substr($user->name, 0, 1) }}
                    </span>
                </div>
                
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                @if($user->phone)
                    <div class="mb-2">
                        <i class="ti ti-phone me-2"></i>
                        <span class="text-muted">{{ $user->phone }}</span>
                    </div>
                @endif
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold text-primary">{{ $user->created_at->format('M Y') }}</div>
                        <small class="text-muted">Member since</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold {{ $user->status === 'active' ? 'text-success' : 'text-danger' }}">
                            {{ ucfirst($user->status ?? 'active') }}
                        </div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
                
                @if($user->subscription)
                    <hr>
                    <div class="text-center">
                        <h6 class="text-muted">Current Plan</h6>
                        <div class="fw-bold text-info">{{ $user->subscription->plan->name ?? 'Unknown' }}</div>
                        <small class="text-muted">{{ ucfirst($user->subscription->status) }}</small>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-info" onclick="viewUserStats({{ $user->id }})">
                        <i class="ti ti-chart-bar me-2"></i>View Statistics
                    </button>
                    
                    <button type="button" class="btn btn-outline-warning" onclick="impersonateUser({{ $user->id }})">
                        <i class="ti ti-user-check me-2"></i>Impersonate User
                    </button>
                    
                    @if($user->status === 'active')
                        <button type="button" class="btn btn-outline-danger" onclick="suspendUser({{ $user->id }})">
                            <i class="ti ti-user-x me-2"></i>Suspend User
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-success" onclick="activateUser({{ $user->id }})">
                            <i class="ti ti-user-check me-2"></i>Activate User
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-outline-secondary" onclick="sendEmailToUser({{ $user->id }})">
                        <i class="ti ti-mail me-2"></i>Send Email
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted">User ID:</small>
                    </div>
                    <div class="col-6">
                        <code>{{ $user->id }}</code>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted">Email Verified:</small>
                    </div>
                    <div class="col-6">
                        @if($user->email_verified_at)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning">Unverified</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted">Last Login:</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</small>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted">Created:</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Updated:</small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">{{ $user->updated_at->format('M d, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Wait for jQuery to be available
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}

function viewUserStats(userId) {
    // Load user statistics via AJAX
    window.ajaxHelper.get(`/admin/user-management/${userId}/stats`)
        .then(data => {
            if (data.success) {
                // Show statistics in a modal or redirect
                alert('User statistics loaded');
            } else {
                alert('Failed to load user statistics: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading user statistics: ' + error.message);
        });
}

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
                    window.showNotification('تم تسجيل الدخول كالمستخدم بنجاح! سيتم توجيهك الآن...', 'success');
                    // Redirect to user dashboard
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    window.showNotification('فشل في تسجيل الدخول كالمستخدم: ' + (data.message || 'خطأ غير معروف'), 'error');
                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('خطأ في تسجيل الدخول كالمستخدم: ' + error.message, 'error');
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }
}

function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user? They will not be able to access their account.')) {
        updateUserStatus(userId, 'suspended');
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        updateUserStatus(userId, 'active');
    }
}

function updateUserStatus(userId, status) {
    window.ajaxHelper.post(`/admin/user-management/${userId}/toggle-status`, { status: status })
        .then(data => {
            if (data.success) {
                alert(`User ${status} successfully`);
                location.reload();
            } else {
                alert(`Failed to ${status} user: ` + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(`Error ${status}ing user: ` + error.message);
        });
}

function sendEmailToUser(userId) {
    const subject = prompt('Email subject:');
    if (subject) {
        const message = prompt('Email message:');
        if (message) {
            window.ajaxHelper.post(`/admin/user-management/${userId}/send-email`, { 
                subject: subject, 
                message: message 
            })
            .then(data => {
                if (data.success) {
                    alert('Email sent successfully');
                } else {
                    alert('Failed to send email: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending email: ' + error.message);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Password confirmation validation
    const passwordForm = document.getElementById('password-form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    
    function validatePasswordMatch() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);
});
</script>
@endpush
