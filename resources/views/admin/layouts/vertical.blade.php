<!DOCTYPE html>
<html lang="en" dir="ltr" @yield('html-attribute')}>

<head>
    @include('admin.layouts.partials.title-meta', ['title' => $title ?? 'Admin'])

    @include('admin.layouts.partials.head-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body>
<style>
   .select2-container--default .select2-selection--multiple .select2-selection__choice {background-color: #232e51 !important;}
</style>
    <div class="wrapper">

        @include('admin.layouts.partials.sidenav')
        @include('admin.layouts.partials.topbar')


        <div class="page-content">
            <div class="page-container">

                @yield('content')

            </div>
            @include('admin.layouts.partials.footer')
        </div>

    </div>

    @include('admin.layouts.partials.customizer')

    @include('admin.layouts.partials.footer-scripts')
</body>

</html>


