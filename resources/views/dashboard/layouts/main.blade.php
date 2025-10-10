<!doctype html>
<html lang="en">
@include('dashboard.layouts.head')
<body>
    <!-- Begin page -->
    <div class="wrapper">

        @include('dashboard.layouts.sidebar')

        @include('dashboard.layouts.header')

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        <div class="page-content">
            <div class="page-container">

                @yield('content')

            </div> <!-- container -->

            @include('dashboard.layouts.footer')

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    @include('dashboard.layouts.scripts')

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/locales-all.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    @yield('footer-script')
    @stack('scripts')
</body>
</html> 