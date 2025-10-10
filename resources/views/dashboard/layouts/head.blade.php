<head>
    <title>Dashboard | @yield('title')</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description"content="CRM Rukn - Professional real estate management system" />
    <meta name="author" content="CRM Rukn" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="https://rukn.ae/public/logo.png">

    <!-- Vector Maps css -->
    <link href="https://coderthemes.com/greeva/layouts/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- Theme Config Js -->
    <script src="https://coderthemes.com/greeva/layouts/assets/js/config.js"></script>

    <!-- Vendor css -->
    <link href="https://coderthemes.com/greeva/layouts/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="https://coderthemes.com/greeva/layouts/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="{{ asset('assets/admin/css/thame/icons.min.css') }}" rel="stylesheet" type="text/css" />
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/1.34.0/iconfont/tabler-icons.min.css" integrity="sha512-mWpmj8VqORtX/CTiI5Mypqx75NqtF3Ddym7C94bpi8d8nVW46OlJbtdGDcGsQDZ4VJARIgMLbzm8zyN1Ies3Qw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- DataTables CSS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    
    <!-- Custom DataTables Optimized CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/datatables-optimized.css') }}"> --}}

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Simplebar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@4.7.0/dist/apexcharts.min.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</head>
