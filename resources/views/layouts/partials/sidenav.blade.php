<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="/images/logo-tr.png" alt="logo"></span>
            <span class="logo-sm"><img src="/images/logo-sm.png" alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="/images/logo-dark.png" alt="dark logo"></span>
            <span class="logo-sm"><img src="/images/logo-sm.png" alt="small logo"></span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-sm-hover">
        <i class="ti ti-circle align-middle"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <li class="side-nav-title">Dashboard</li>

            <li class="side-nav-item">
                <a href="{{ route('dashboard') }}" class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Affiliate Management</li>

            <!-- Networks Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarNetworks" aria-expanded="false" aria-controls="sidebarNetworks" class="side-nav-link {{ request()->is('networks*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-affiliate"></i></span>
                    <span class="menu-text"> Networks </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('networks*') ? 'show' : '' }}" id="sidebarNetworks">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('networks.index') }}" class="side-nav-link {{ request()->routeIs('networks.index') ? 'active' : '' }}">
                                <span class="menu-text">All Networks</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('networks.create') }}" class="side-nav-link {{ request()->routeIs('networks.create') ? 'active' : '' }}">
                                <span class="menu-text">Connect Network</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Campaigns Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCampaigns" aria-expanded="false" aria-controls="sidebarCampaigns" class="side-nav-link {{ request()->is('campaigns*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
                    <span class="menu-text"> Campaigns </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('campaigns*') ? 'show' : '' }}" id="sidebarCampaigns">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('campaigns.index') }}" class="side-nav-link {{ request()->routeIs('campaigns.index') ? 'active' : '' }}">
                                <span class="menu-text">All Campaigns</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <!-- Coupons Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCoupons" aria-expanded="false" aria-controls="sidebarCoupons" class="side-nav-link {{ request()->is('coupons*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-ticket"></i></span>
                    <span class="menu-text"> Coupons </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('coupons*') ? 'show' : '' }}" id="sidebarCoupons">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('coupons.index') }}" class="side-nav-link {{ request()->routeIs('coupons.index') ? 'active' : '' }}">
                                <span class="menu-text">All Coupons</span>
                            </a>
                        </li>
                      
                    </ul>
                </div>
            </li>

            <!-- Orders Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPurchases" aria-expanded="false" aria-controls="sidebarPurchases" class="side-nav-link {{ request()->is('purchases*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                    <span class="menu-text"> Orders </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('orders*') ? 'show' : '' }}" id="sidebarPurchases">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('orders.index') }}" class="side-nav-link {{ request()->routeIs('orders.index') ? 'active' : '' }}">
                                <span class="menu-text">All Orders</span>
                            </a>
                        </li>
                
                        <li class="side-nav-item">
                            <a href="{{ route('orders.statistics') }}" class="side-nav-link {{ request()->routeIs('orders.statistics') ? 'active' : '' }}">
                                <span class="menu-text">Order Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title mt-2">Configuration</li>

            <!-- Data Sync Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSync" aria-expanded="false" aria-controls="sidebarSync" class="side-nav-link {{ request()->is('sync*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-refresh"></i></span>
                    <span class="menu-text"> Data Sync </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('sync*') ? 'show' : '' }}" id="sidebarSync">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('sync.quick-sync') }}" class="side-nav-link {{ request()->routeIs('sync.quick-sync') ? 'active' : '' }}">
                                <span class="menu-text">Quick Sync</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('sync.schedules.index') }}" class="side-nav-link {{ request()->routeIs('sync.schedules.*') ? 'active' : '' }}">
                                <span class="menu-text">Schedules</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('sync.logs.index') }}" class="side-nav-link {{ request()->routeIs('sync.logs.*') ? 'active' : '' }}">
                                <span class="menu-text">Sync Logs</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('sync.settings.index') }}" class="side-nav-link {{ request()->routeIs('sync.settings.*') ? 'active' : '' }}">
                                <span class="menu-text">Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Countries Section -->
            <li class="side-nav-item">
                <a href="{{ route('countries.index') }}" class="side-nav-link {{ request()->is('countries*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-world"></i></span>
                    <span class="menu-text"> Countries </span>
                </a>
            </li>

            <!-- Reports Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarReports" aria-expanded="false" aria-controls="sidebarReports" class="side-nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-report"></i></span>
                    <span class="menu-text"> Reports </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('reports*') ? 'show' : '' }}" id="sidebarReports">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('reports.index') }}" class="side-nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <span class="menu-text">Overview</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.coupons') }}" class="side-nav-link {{ request()->routeIs('reports.coupons') ? 'active' : '' }}">
                                <span class="menu-text">Coupon Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.purchases') }}" class="side-nav-link {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                                <span class="menu-text">Purchase Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.campaigns') }}" class="side-nav-link {{ request()->routeIs('reports.campaigns') ? 'active' : '' }}">
                                <span class="menu-text">Campaign Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.revenue') }}" class="side-nav-link {{ request()->routeIs('reports.revenue') ? 'active' : '' }}">
                                <span class="menu-text">Revenue Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title mt-2">Administration</li>

            <!-- Users Management -->
            <li class="side-nav-item">
                <a href="{{ route('users.index') }}" class="side-nav-link {{ request()->is('users*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text"> Users </span>
                </a>
            </li>

            <!-- Login Sessions -->
            <li class="side-nav-item">
                <a href="{{ route('sessions.index') }}" class="side-nav-link {{ request()->is('dashboard/sessions*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-device-desktop-analytics"></i></span>
                    <span class="menu-text"> Login Sessions </span>
                    @if(auth()->check() && auth()->user()->sessions()->active()->count() > 1)
                    <span class="badge bg-success rounded-pill ms-auto">{{ auth()->user()->sessions()->active()->count() }}</span>
                    @endif
                </a>
            </li>
            
            <!-- Notifications -->
            <li class="side-nav-item">
                <a href="{{ route('notifications.index') }}" class="side-nav-link {{ request()->is('dashboard/notifications*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-bell"></i></span>
                    <span class="menu-text"> Notifications </span>
                    @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ auth()->user()->unreadNotifications->count() }}</span>
                    @endif
                </a>
            </li>

            <!-- Settings -->
            <li class="side-nav-item">
                <a href="{{ route('settings.index') }}" class="side-nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-settings"></i></span>
                    <span class="menu-text"> Settings </span>
                </a>
            </li>

        </ul>
        <!--- End Sidenav -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->

