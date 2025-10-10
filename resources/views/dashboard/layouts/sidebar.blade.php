<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="https://rukn.ae/public/logo.png" alt="logo"></span>
            <span class="logo-sm"><img src="https://rukn.ae/public/logo.png" alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="https://rukn.ae/public/logo.png" alt="dark logo"></span>
            <span class="logo-sm"><img src="https://rukn.ae/public/logo.png" alt="small logo"></span>
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
            <li class="side-nav-title">Navigation</li>

            <li class="side-nav-item">
                <a href="{{ route('dashboard') }}"
                    class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>
            <!-- Brokers Section -->
            @can('view brokers')
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarBrokers" aria-expanded="false"
                    aria-controls="sidebarBrokers"
                    class="side-nav-link {{ request()->routeIs('brokers.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-building-store"></i></span>
                    <span class="menu-text"> Brokers</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('brokers.*') ? 'show' : '' }}" id="sidebarBrokers">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('brokers.index') }}"
                                class="side-nav-link {{ request()->routeIs('brokers.index') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-list"></i></span>
                                <span class="menu-text">All Brokers</span>
                            </a>
                        </li>
                        @can('create brokers')
                        <li class="side-nav-item">
                            <a href="{{ route('brokers.create') }}"
                                class="side-nav-link {{ request()->routeIs('brokers.create') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-plus"></i></span>
                                <span class="menu-text">Connect Broker</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            <!-- Campaigns Section -->
            @can('view campaigns')
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCampaigns" aria-expanded="false"
                    aria-controls="sidebarCampaigns"
                    class="side-nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-target"></i></span>
                    <span class="menu-text"> Campaigns</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('campaigns.*') ? 'show' : '' }}" id="sidebarCampaigns">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('campaigns.index') }}"
                                class="side-nav-link {{ request()->routeIs('campaigns.index') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-list"></i></span>
                                <span class="menu-text">All Campaigns</span>
                            </a>
                        </li>
                        @can('create campaigns')
                        <li class="side-nav-item">
                            <a href="{{ route('campaigns.create') }}"
                                class="side-nav-link {{ request()->routeIs('campaigns.create') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-plus"></i></span>
                                <span class="menu-text">Create Campaign</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            <!-- Coupons Section -->
            @can('view coupons')
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarCoupons" aria-expanded="false"
                    aria-controls="sidebarCoupons"
                    class="side-nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-ticket"></i></span>
                    <span class="menu-text"> Coupons</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('coupons.*') ? 'show' : '' }}" id="sidebarCoupons">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('coupons.index') }}"
                                class="side-nav-link {{ request()->routeIs('coupons.index') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-list"></i></span>
                                <span class="menu-text">All Coupons</span>
                            </a>
                        </li>
                        @can('create coupons')
                        <li class="side-nav-item">
                            <a href="{{ route('coupons.create') }}"
                                class="side-nav-link {{ request()->routeIs('coupons.create') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-plus"></i></span>
                                <span class="menu-text">Create Coupon</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            <!-- Purchases Section -->
            @can('view purchases')
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPurchases" aria-expanded="false"
                    aria-controls="sidebarPurchases"
                    class="side-nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                    <span class="menu-text"> Purchases</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('purchases.*') ? 'show' : '' }}" id="sidebarPurchases">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('purchases.index') }}"
                                class="side-nav-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-list"></i></span>
                                <span class="menu-text">All Purchases</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('purchases.statistics') }}"
                                class="side-nav-link {{ request()->routeIs('purchases.statistics') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-chart-line"></i></span>
                                <span class="menu-text">Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            <!-- Countries Section -->
            @can('view countries')
            <li class="side-nav-item">
                <a href="{{ route('countries.index') }}"
                    class="side-nav-link {{ request()->routeIs('countries.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-world"></i></span>
                    <span class="menu-text"> Countries</span>
                </a>
            </li>
            @endcan

            <!-- Reports Section -->
            @can('view reports')
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarReports" aria-expanded="false"
                    aria-controls="sidebarReports"
                    class="side-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-chart-bar"></i></span>
                    <span class="menu-text"> Reports</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="sidebarReports">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('reports.index') }}"
                                class="side-nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-file-text"></i></span>
                                <span class="menu-text">All Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.coupons') }}"
                                class="side-nav-link {{ request()->routeIs('reports.coupons') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-ticket"></i></span>
                                <span class="menu-text">Coupons Report</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.purchases') }}"
                                class="side-nav-link {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                                <span class="menu-text">Purchases Report</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.campaigns') }}"
                                class="side-nav-link {{ request()->routeIs('reports.campaigns') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-target"></i></span>
                                <span class="menu-text">Campaigns Report</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('reports.revenue') }}"
                                class="side-nav-link {{ request()->routeIs('reports.revenue') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="ti ti-currency-dollar"></i></span>
                                <span class="menu-text">Revenue Report</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            @can('manage roles')
            <li class="side-nav-title">Management</li>

            <!-- Users Section (Admin Only) -->
            <li class="side-nav-item">
                <a href="{{ route('users.index') }}"
                    class="side-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text"> Users</span>
                </a>
            </li>

            <!-- Settings Section (Admin Only) -->
            <li class="side-nav-item">
                <a href="{{ route('settings.index') }}"
                    class="side-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-settings"></i></span>
                    <span class="menu-text"> Settings</span>
                </a>
            </li>
            @endcan

            <li class="side-nav-title">Account</li>

            <!-- Profile -->
            <li class="side-nav-item">
                <a href="{{ route('dashboard.profile') }}"
                    class="side-nav-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-user"></i></span>
                    <span class="menu-text"> My Profile</span>
                </a>
            </li>

            <!-- Logout -->
            <li class="side-nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="side-nav-link text-start w-100 bg-transparent border-0">
                        <span class="menu-icon"><i class="ti ti-logout"></i></span>
                        <span class="menu-text"> Logout </span>
                    </button>
                </form>
            </li>

        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->
