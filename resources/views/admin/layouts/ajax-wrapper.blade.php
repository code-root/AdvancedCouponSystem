<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ \App\Services\SiteSettingService::getSiteName() }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">
    
    <!-- CSS -->
    @include('admin.layouts.partials.head-css')
    
    <!-- Custom CSS for AJAX functionality -->
    <style>
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .notification-container {
            z-index: 9999;
        }
        
        .form-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .datatable-filters {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .bulk-actions {
            background: #e3f2fd;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #bbdefb;
        }
        
        .auto-refresh-indicator {
            position: fixed;
            top: 10px;
            left: 10px;
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            z-index: 1000;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .modal-ajax .modal-body {
            min-height: 200px;
        }
        
        .inline-edit {
            cursor: pointer;
        }
        
        .inline-edit:hover {
            background-color: #f8f9fa;
        }
        
        .inline-edit.editing {
            background-color: #fff3cd;
        }
    </style>
    
    @stack('styles')
</head>

<body>
    <!-- Auto-refresh indicator -->
    <div id="auto-refresh-indicator" class="auto-refresh-indicator" style="display: none;">
        <i class="ti ti-refresh"></i> Auto-refresh active
    </div>

    <!-- Page Loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- Topbar -->
        @include('admin.layouts.partials.topbar')
        
        <!-- Left Sidebar -->
        @include('admin.layouts.partials.sidenav')
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Page Title -->
                    @include('admin.layouts.partials.page-title')
                    
                    <!-- Content -->
                    <div id="main-content">
                        @yield('content')
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            @include('admin.layouts.partials.footer')
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="rightbar-overlay"></div>

    <!-- Scripts -->
    @include('admin.layouts.partials.footer-scripts')
    
    <!-- AJAX Scripts -->
    <script src="{{ asset('js/admin/ajax-helper.js') }}"></script>
    <script src="{{ asset('js/admin/form-handler.js') }}"></script>
    <script src="{{ asset('js/admin/datatable-handler.js') }}"></script>
    <script src="{{ asset('js/admin/notifications.js') }}"></script>
    <script src="{{ asset('js/admin/admin.js') }}"></script>
    
    <!-- Global AJAX Configuration -->
    <script>
        // Configure AJAX defaults
        window.ajaxConfig = {
            baseUrl: '{{ url("/") }}',
            adminUrl: '{{ url("/admin") }}',
            csrfToken: '{{ csrf_token() }}',
            locale: '{{ app()->getLocale() }}',
            timezone: '{{ config("app.timezone") }}',
            pusherKey: '{{ config("broadcasting.connections.pusher.key") }}',
            pusherCluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}'
        };
        
        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            if (window.notificationManager) {
                window.notificationManager.error('An unexpected error occurred');
            }
        });
        
        // Global AJAX error handler
        document.addEventListener('ajax:error', function(e) {
            console.error('AJAX error:', e.detail);
        });
        
        // Global AJAX success handler
        document.addEventListener('ajax:success', function(e) {
            console.log('AJAX success:', e.detail);
        });
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Hide page loader
            const loader = document.getElementById('loader');
            if (loader) {
                loader.style.display = 'none';
            }
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
            
            // Setup auto-refresh indicator
            const autoRefreshElements = document.querySelectorAll('[data-auto-refresh]');
            if (autoRefreshElements.length > 0) {
                const indicator = document.getElementById('auto-refresh-indicator');
                if (indicator) {
                    indicator.style.display = 'block';
                }
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>

