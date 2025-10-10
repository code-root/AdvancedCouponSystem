<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

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

            <!-- Brokers Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarBrokers" aria-expanded="false" aria-controls="sidebarBrokers" class="side-nav-link {{ request()->is('brokers*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-affiliate"></i></span>
                    <span class="menu-text"> Brokers </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('brokers*') ? 'show' : '' }}" id="sidebarBrokers">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('brokers.index') }}" class="side-nav-link {{ request()->routeIs('brokers.index') ? 'active' : '' }}">
                                <span class="menu-text">All Brokers</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('brokers.create') }}" class="side-nav-link {{ request()->routeIs('brokers.create') ? 'active' : '' }}">
                                <span class="menu-text">Connect Broker</span>
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
                        <li class="side-nav-item">
                            <a href="{{ route('campaigns.create') }}" class="side-nav-link {{ request()->routeIs('campaigns.create') ? 'active' : '' }}">
                                <span class="menu-text">Create Campaign</span>
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
                        <li class="side-nav-item">
                            <a href="{{ route('coupons.create') }}" class="side-nav-link {{ request()->routeIs('coupons.create') ? 'active' : '' }}">
                                <span class="menu-text">Create Coupon</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Purchases Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPurchases" aria-expanded="false" aria-controls="sidebarPurchases" class="side-nav-link {{ request()->is('purchases*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                    <span class="menu-text"> Purchases </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('purchases*') ? 'show' : '' }}" id="sidebarPurchases">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('purchases.index') }}" class="side-nav-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                                <span class="menu-text">All Purchases</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('purchases.create') }}" class="side-nav-link {{ request()->routeIs('purchases.create') ? 'active' : '' }}">
                                <span class="menu-text">New Purchase</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('purchases.statistics') }}" class="side-nav-link {{ request()->routeIs('purchases.statistics') ? 'active' : '' }}">
                                <span class="menu-text">Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title mt-2">Configuration</li>

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

            @can('view users')
            <li class="side-nav-title mt-2">Administration</li>

            <!-- Users Management -->
            <li class="side-nav-item">
                <a href="{{ route('users.index') }}" class="side-nav-link {{ request()->is('users*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text"> Users </span>
                </a>
            </li>

            <!-- Settings -->
            <li class="side-nav-item">
                <a href="{{ route('settings.index') }}" class="side-nav-link {{ request()->is('settings*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-settings"></i></span>
                    <span class="menu-text"> Settings </span>
                </a>
            </li>
            @endcan

        </ul>
        <!--- End Sidenav -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->

