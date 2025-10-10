@extends('layouts.vertical', ['title' => 'Create Sub-User'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Users', 'title' => 'Create New Sub-User'])

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Sub-User Information</h4>
                    <p class="text-muted mb-0 small">Create a sub-user who will have access to your data</p>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter user name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="user@example.com" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                            
                            <!-- Info Box -->
                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <h6 class="mb-2"><i class="ti ti-info-circle me-1"></i> Sub-User Permissions</h6>
                                    <ul class="mb-0">
                                        <li>Will have access to <strong>your data</strong> (networks, campaigns, purchases)</li>
                                        <li>Can add/edit data on your behalf</li>
                                        <li>All actions will be tracked with their name</li>
                                        <li>Cannot delete or modify other sub-users</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-user-plus me-1"></i> Create Sub-User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('ti-eye');
        toggleIcon.classList.add('ti-eye-off');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('ti-eye-off');
        toggleIcon.classList.add('ti-eye');
    }
}
</script>
@endsection
