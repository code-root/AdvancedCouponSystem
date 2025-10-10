<!-- Topbar Start -->
<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="/images/logo.png" alt="logo"></span>
                    <span class="logo-sm"><img src="/images/logo-sm.png" alt="small logo"></span>
                </span>

                <span class="logo-dark">
                    <span class="logo-lg"><img src="/images/logo-dark.png" alt="dark logo"></span>
                    <span class="logo-sm"><img src="/images/logo-sm.png" alt="small logo"></span>
                </span>
            </a>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button px-2">
                <i class="ti ti-menu-deep fs-24"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="ti ti-menu-deep fs-22"></i>
            </button>

            <!-- Button Trigger Search Modal -->
            <div class="topbar-search text-muted d-none d-xl-flex gap-2 align-items-center" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                <i class="ti ti-search fs-18"></i>
                <span class="me-2">Search brokers, coupons, campaigns...</span>
                <span class="ms-auto fw-medium">⌘K</span>
            </div>

        </div>

        <div class="d-flex align-items-center gap-1">

            <!-- Theme Color (Light/Dark) -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i class="ti ti-moon fs-22"></i>
                </button>
            </div>

            <!-- Search Modal Button for Mobile -->
            <div class="topbar-item d-flex d-xl-none">
                <button class="topbar-link" type="button" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="ti ti-search fs-22"></i>
                </button>
            </div>

            <!-- Notifications Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,25" type="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-bell fs-22"></i>
                        <span class="position-absolute start-100 top-0 translate-middle badge rounded-pill bg-danger">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg">

                        <div class="dropdown-header card shadow-none rounded-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="m-0 fw-semibold"> Notification </h6>
                                    <p class="mb-0 fs-12 fw-medium text-muted">You have <span class="text-danger">3</span> unread notifications</p>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="dropdown-toggle drop-arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">Mark as Read</a>
                                        <a href="javascript:void(0);" class="dropdown-item">Delete All</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative z-2 card shadow-none rounded-0" style="max-height: 300px;" data-simplebar>
                            <!-- Notifications will be dynamically loaded here -->
                            <div class="text-center py-5">
                                <i class="ti ti-bell-off fs-48 text-muted"></i>
                                <p class="text-muted mt-3">No new notifications</p>
                            </div>
                        </div>

                        <a href="javascript:void(0);" class="dropdown-item notification-item position-fixed z-2 bottom-0 text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,19" type="button" aria-haspopup="false" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img src="/images/users/avatar-1.jpg" alt="user-image" class="rounded-circle">
                            <span class="d-none d-md-block ms-1">
                                <span class="fw-medium">{{ auth()->user()->name ?? 'User' }}</span>
                                <i class="ti ti-chevron-down ms-1 fs-16 align-middle"></i>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <div class="dropdown-header">
                            <h6 class="m-0"> Welcome {{ auth()->user()->name ?? 'User' }}!</h6>
                            <span class="text-muted fs-13">{{ auth()->user()->email ?? '' }}</span>
                        </div>
                        <!-- item-->
                        <a href="{{ route('dashboard.profile') }}" class="dropdown-item">
                            <i class="ti ti-user me-2 fs-18 align-middle"></i>
                            <span>My Account</span>
                        </a>

                        <a href="{{ route('dashboard.password.change') }}" class="dropdown-item">
                            <i class="ti ti-lock me-2 fs-18 align-middle"></i>
                            <span>Change Password</span>
                        </a>

                        @can('view settings')
                        <a href="{{ route('settings.index') }}" class="dropdown-item">
                            <i class="ti ti-settings me-2 fs-18 align-middle"></i>
                            <span>Settings</span>
                        </a>
                        @endcan

                        <div class="dropdown-divider my-1"></div>

                        <!-- item-->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2 fs-18 align-middle"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
<!-- Topbar End -->

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent shadow-none">
            <div class="card mb-0">
                <div class="card-body p-4">
                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="mb-3">
                        <label for="search-input" class="form-label">Search</label>
                        <input type="text" class="form-control form-control-lg" id="search-input" placeholder="Search for brokers, campaigns, coupons...">
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Quick Links</h6>
                        <div class="list-group">
                            <a href="{{ route('brokers.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-affiliate me-2"></i> Brokers
                            </a>
                            <a href="{{ route('campaigns.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-speakerphone me-2"></i> Campaigns
                            </a>
                            <a href="{{ route('coupons.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-ticket me-2"></i> Coupons
                            </a>
                            <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-shopping-cart me-2"></i> Purchases
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

