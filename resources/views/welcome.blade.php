<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.title-meta', ['title' => 'Welcome'])

    @include('layouts.partials.head-css')
    
    <style>
        .landing-hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        .landing-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/images/png/arrows.svg') no-repeat center;
            opacity: 0.1;
        }
    </style>
</head>

<body>
    <div class="landing-hero d-flex align-items-center">
        <div class="container position-relative">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <div class="mb-4">
                        <img src="/images/logo.png" alt="logo" height="40" class="mb-4">
                    </div>
                    
                    <h1 class="display-3 fw-bold mb-4">AdvancedCouponSystem</h1>
                    <h3 class="fw-light mb-4">Your Complete Affiliate Marketing Platform</h3>
                    <p class="lead mb-5 text-white-75">
                        Connect with multiple brokers, create campaigns, generate coupons, and track your affiliate revenue all in one place.
                    </p>

                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-lg btn-light px-5">
                                <i class="ti ti-login me-2"></i> Login
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-lg btn-outline-light px-5">
                                <i class="ti ti-user-plus me-2"></i> Register
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-lg btn-light px-5">
                                <i class="ti ti-dashboard me-2"></i> Go to Dashboard
                            </a>
                        @endguest
                    </div>

                    <div class="mt-5 pt-5">
                        <div class="row row-cols-1 row-cols-md-3 g-4 text-center">
                            <div class="col">
                                <div class="bg-white bg-opacity-10 rounded-3 p-4">
                                    <i class="ti ti-affiliate fs-48 mb-3"></i>
                                    <h5 class="fw-semibold">Multiple Networks</h5>
                                    <p class="mb-0 text-white-75">Connect with top affiliate brokers worldwide</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white bg-opacity-10 rounded-3 p-4">
                                    <i class="ti ti-speakerphone fs-48 mb-3"></i>
                                    <h5 class="fw-semibold">Smart Campaigns</h5>
                                    <p class="mb-0 text-white-75">Create and manage marketing campaigns</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white bg-opacity-10 rounded-3 p-4">
                                    <i class="ti ti-chart-line fs-48 mb-3"></i>
                                    <h5 class="fw-semibold">Advanced Analytics</h5>
                                    <p class="mb-0 text-white-75">Track performance and optimize revenue</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.footer-scripts')
</body>

</html>
