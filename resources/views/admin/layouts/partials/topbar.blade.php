<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            @php
                $logoSm = \App\Services\SiteSettingService::getSiteLogo('small');
                $logoLight = \App\Services\SiteSettingService::getSiteLogo('light');
                $logoDark = \App\Services\SiteSettingService::getSiteLogo('dark');
            @endphp
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="{{ $logoLight }}" alt="logo"></span>
                    <span class="logo-sm"><img src="{{ $logoSm }}" alt="small logo"></span>
                </span>

                <span class="logo-dark">
                    <span class="logo-lg"><img src="{{ $logoDark }}" alt="dark logo"></span>
                    <span class="logo-sm"><img src="{{ $logoSm }}" alt="small logo"></span>
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
                <span class="me-2">Search<span>
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
                <button class="topbar-link" type="button" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="ti ti-search fs-22"></i></button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,19" type="button" aria-haspopup="false" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img src="/images/users/avatar-1.jpg" alt="user-image" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                            <span class="d-none d-md-block ms-1">
                                <span class="fw-medium">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                                <i class="ti ti-chevron-down ms-1 fs-16 align-middle"></i>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <div class="dropdown-header">
                            <h6 class="m-0"> Welcome {{ Auth::guard('admin')->user()->name ?? 'Admin' }}!</h6>
                            <span class="text-muted fs-13">{{ Auth::guard('admin')->user()->email ?? '' }}</span>
                        </div>
                        <!-- item-->
                        <a href="{{ route('admin.profile') }}" class="dropdown-item">
                            <i class="ti ti-user me-2 fs-18 align-middle"></i>
                            <span>My Account</span>
                        </a>

                        <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                            <i class="ti ti-settings me-2 fs-18 align-middle"></i>
                            <span>Settings</span>
                        </a>

                        <div class="dropdown-divider my-1"></div>

                        <!-- item-->
                        <form method="POST" action="{{ route('admin.logout') }}">
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
                        <input type="text" class="form-control form-control-lg" id="search-input" placeholder="Search for users, networks, campaigns...">
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Quick Links</h6>
                        <div class="list-group">
                            <a href="{{ route('admin.user-management.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-users me-2"></i> User Management
                            </a>
                            <a href="{{ route('admin.networks.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-affiliate me-2"></i> Networks
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-chart-bar me-2"></i> Reports
                            </a>
                            <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                                <i class="ti ti-settings me-2"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>