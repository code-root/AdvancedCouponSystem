@extends('dashboard.layouts.vertical', ['title' => 'Edit User'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Users', 'title' => 'Edit User'])

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Edit Sub-User Information</h4>
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

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="ti ti-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
                            
                            <!-- User Info -->
                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <h6 class="mb-2"><i class="ti ti-info-circle me-1"></i> User Information</h6>
                                    <ul class="mb-0">
                                        <li><strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i') }}</li>
                                        <li><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</li>
                                        @if($user->creator)
                                            <li><strong>Created By:</strong> {{ $user->creator->name }}</li>
                                        @endif
                                        @if($user->parentUser)
                                            <li><strong>Parent Account:</strong> {{ $user->parentUser->name }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update User
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
