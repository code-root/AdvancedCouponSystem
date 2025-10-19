<!DOCTYPE html>
<html lang="en">
<head>
    @include('admin.layouts.partials.title-meta', ['title' => 'Admin Login'])
    @include('admin.layouts.partials.head-css')
</head>
<body class="h-100">
    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                    <a href="{{ route('admin.dashboard') }}" class="auth-brand mb-3">
                        <img src="{{ optional(\App\Models\SiteSetting::where('key','logo_dark')->first())->value ?? '/images/trakifi-m.png' }}" alt="dark logo" height="24" class="logo-dark">
                        <img src="{{ optional(\App\Models\SiteSetting::where('key','logo_light')->first())->value ?? '/images/logo-tr.png' }}" alt="logo light" height="24" class="logo-light">
                    </a>

                    <h3 class="fw-semibold mb-2">Admin Panel Login</h3>

                    <form method="POST" action="{{ route('admin.post-login') }}" class="text-start mb-3">
                        @csrf

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary" type="submit">Login</button>
                        </div>
                    </form>

                    <!-- User Login Link -->
                    <div class="text-center mt-3 pt-3 border-top">
                        <p class="text-muted small mb-2">User Access</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="mdi mdi-account-circle me-1"></i>
                            User Login
                        </a>
                    </div>

                    <p class="mt-auto mb-0">
                        <script>document.write(new Date().getFullYear())</script> © Admin Panel - {{ optional(\App\Models\SiteSetting::where('key','site_name')->first())->value ?? config('app.name') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.partials.footer-scripts')
</body>
</html>


