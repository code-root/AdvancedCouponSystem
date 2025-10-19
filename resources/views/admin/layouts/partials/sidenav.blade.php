<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="{{ \App\Services\SiteSettingService::getSiteLogo() }}" alt="logo"></span>
            <span class="logo-sm"><img src="{{ \App\Services\SiteSettingService::getFavicon() }}" alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="{{ \App\Services\SiteSettingService::getSiteLogo() }}" alt="dark logo"></span>
            <span class="logo-sm"><img src="{{ \App\Services\SiteSettingService::getFavicon() }}" alt="small logo"></span>
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
                <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <li class="side-nav-title mt-2">User Management</li>

            <!-- User Management Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarUsers" aria-expanded="false" aria-controls="sidebarUsers" class="side-nav-link {{ request()->is('admin/user-management*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text"> User Management </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/user-management*') ? 'show' : '' }}" id="sidebarUsers">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.user-management.index') }}" class="side-nav-link {{ request()->routeIs('admin.user-management.index') ? 'active' : '' }}">
                                <span class="menu-text">All Users</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.user-management.create') }}" class="side-nav-link {{ request()->routeIs('admin.user-management.create') ? 'active' : '' }}">
                                <span class="menu-text">Add User</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Admin Users Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAdmins" aria-expanded="false" aria-controls="sidebarAdmins" class="side-nav-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-user-shield"></i></span>
                    <span class="menu-text"> Admin Users </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.admin-users.*') ? 'show' : '' }}" id="sidebarAdmins">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.admin-users.index') }}" class="side-nav-link {{ request()->routeIs('admin.admin-users.index') ? 'active' : '' }}">
                                <span class="menu-text">All Admins</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.admin-users.create') }}" class="side-nav-link {{ request()->routeIs('admin.admin-users.create') ? 'active' : '' }}">
                                <span class="menu-text">Add Admin</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title mt-2">System Management</li>

            <!-- Networks Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarNetworks" aria-expanded="false" aria-controls="sidebarNetworks" class="side-nav-link {{ request()->is('admin/networks*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-affiliate"></i></span>
                    <span class="menu-text"> Networks </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/networks*') ? 'show' : '' }}" id="sidebarNetworks">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.networks.index') }}" class="side-nav-link {{ request()->routeIs('admin.networks.index') ? 'active' : '' }}">
                                <span class="menu-text">All Networks</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.networks.proxies') }}" class="side-nav-link {{ request()->routeIs('admin.networks.proxies') ? 'active' : '' }}">
                                <span class="menu-text">Network Proxies</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Plans Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPlans" aria-expanded="false" aria-controls="sidebarPlans" class="side-nav-link {{ request()->is('admin/plans*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-package"></i></span>
                    <span class="menu-text"> Plans </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/plans*') ? 'show' : '' }}" id="sidebarPlans">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.plans.index') }}" class="side-nav-link {{ request()->routeIs('admin.plans.index') ? 'active' : '' }}">
                                <span class="menu-text">All Plans</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.plans.create') }}" class="side-nav-link {{ request()->routeIs('admin.plans.create') ? 'active' : '' }}">
                                <span class="menu-text">Add Plan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Subscriptions Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSubscriptions" aria-expanded="false" aria-controls="sidebarSubscriptions" class="side-nav-link {{ request()->is('admin/subscriptions*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-credit-card"></i></span>
                    <span class="menu-text"> Subscriptions </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/subscriptions*') ? 'show' : '' }}" id="sidebarSubscriptions">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.subscriptions.index') }}" class="side-nav-link {{ request()->routeIs('admin.subscriptions.index') ? 'active' : '' }}">
                                <span class="menu-text">All Subscriptions</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.subscriptions.statistics') }}" class="side-nav-link {{ request()->routeIs('admin.subscriptions.statistics') ? 'active' : '' }}">
                                <span class="menu-text">Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>


            <!-- Sessions Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSessions" aria-expanded="false" aria-controls="sidebarSessions" class="side-nav-link {{ request()->is('admin/sessions*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-device-desktop"></i></span>
                    <span class="menu-text"> Sessions </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/sessions*') ? 'show' : '' }}" id="sidebarSessions">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sessions.index') }}" class="side-nav-link {{ request()->routeIs('admin.sessions.index') ? 'active' : '' }}">
                                <span class="menu-text">Active Sessions</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sessions.my-sessions') }}" class="side-nav-link {{ request()->routeIs('admin.sessions.my-sessions') ? 'active' : '' }}">
                                <span class="menu-text">My Sessions</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sessions.statistics') }}" class="side-nav-link {{ request()->routeIs('admin.sessions.statistics') ? 'active' : '' }}">
                                <span class="menu-text">Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Roles & Permissions Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarRoles" aria-expanded="false" aria-controls="sidebarRoles" class="side-nav-link {{ request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-shield"></i></span>
                    <span class="menu-text"> Roles & Permissions </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'show' : '' }}" id="sidebarRoles">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="side-nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <span class="menu-text">Roles</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="side-nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                <span class="menu-text">Permissions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Audit Logs Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAuditLogs" aria-expanded="false" aria-controls="sidebarAuditLogs" class="side-nav-link {{ request()->is('admin/audit-logs*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-file-text"></i></span>
                    <span class="menu-text"> Audit Logs </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/audit-logs*') ? 'show' : '' }}" id="sidebarAuditLogs">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.audit-logs.index') }}" class="side-nav-link {{ request()->routeIs('admin.audit-logs.index') ? 'active' : '' }}">
                                <span class="menu-text">All Logs</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.audit-logs.export') }}" class="side-nav-link {{ request()->routeIs('admin.audit-logs.export') ? 'active' : '' }}">
                                <span class="menu-text">Export Logs</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Notifications Section -->
            <li class="side-nav-item">
                <a href="{{ route('admin.notifications.index') }}" class="side-nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-bell"></i></span>
                    <span class="menu-text"> Notifications </span>
                </a>
            </li>

            <li class="side-nav-title mt-2">Reports & Analytics</li>

            <!-- Reports Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarReports" aria-expanded="false" aria-controls="sidebarReports" class="side-nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-report"></i></span>
                    <span class="menu-text"> Reports </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/reports*') ? 'show' : '' }}" id="sidebarReports">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.advanced') }}" class="side-nav-link {{ request()->routeIs('admin.reports.advanced') ? 'active' : '' }}">
                                <span class="menu-text">Advanced Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="side-nav-link {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                <span class="menu-text">Overview</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.user-sessions.index') }}" class="side-nav-link {{ request()->routeIs('admin.reports.user-sessions.*') ? 'active' : '' }}">
                                <span class="menu-text">User Sessions</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.network-sessions.index') }}" class="side-nav-link {{ request()->routeIs('admin.reports.network-sessions.*') ? 'active' : '' }}">
                                <span class="menu-text">Network Sessions</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.sync-logs.index') }}" class="side-nav-link {{ request()->routeIs('admin.reports.sync-logs.*') ? 'active' : '' }}">
                                <span class="menu-text">Sync Logs</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.sync-statistics.index') }}" class="side-nav-link {{ request()->routeIs('admin.reports.sync-statistics.*') ? 'active' : '' }}">
                                <span class="menu-text">Sync Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title mt-2">System Configuration</li>

            <!-- Countries Section -->
            <li class="side-nav-item">
                <a href="{{ route('admin.countries.index') }}" class="side-nav-link {{ request()->routeIs('admin.countries.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-world"></i></span>
                    <span class="menu-text"> Countries </span>
                </a>
            </li>

            <!-- Campaigns Section -->
            <li class="side-nav-item">
                <a href="{{ route('admin.campaigns.index') }}" class="side-nav-link {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
                    <span class="menu-text"> Campaigns </span>
                </a>
            </li>


            <!-- Settings Section -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSettings" aria-expanded="false" aria-controls="sidebarSettings" class="side-nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-settings"></i></span>
                    <span class="menu-text"> Settings </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->is('admin/settings*') ? 'show' : '' }}" id="sidebarSettings">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.settings.index') }}" class="side-nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                                <span class="menu-text">General</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.settings.branding.index') }}" class="side-nav-link {{ request()->routeIs('admin.settings.branding.*') ? 'active' : '' }}">
                                <span class="menu-text">Branding</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.settings.smtp.index') }}" class="side-nav-link {{ request()->routeIs('admin.settings.smtp.*') ? 'active' : '' }}">
                                <span class="menu-text">SMTP</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.settings.seo.index') }}" class="side-nav-link {{ request()->routeIs('admin.settings.seo.*') ? 'active' : '' }}">
                                <span class="menu-text">SEO</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.settings.payment.index') }}" class="side-nav-link {{ request()->routeIs('admin.settings.payment.*') ? 'active' : '' }}">
                                <span class="menu-text">Payment</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Site Settings Section -->
            <li class="side-nav-item">
                <a href="{{ route('admin.site-settings.dashboard') }}" class="side-nav-link {{ request()->routeIs('admin.site-settings.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-database"></i></span>
                    <span class="menu-text"> Site Settings </span>
                </a>
            </li>

        </ul>
        <!--- End Sidenav -->

        <div class="clearfix"></div>
    </div>
</div>
<!-- Sidenav Menu End -->