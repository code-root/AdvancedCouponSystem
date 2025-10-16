<!DOCTYPE html>
<html lang="en" dir="ltr" @yield('html-attribute')>

<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Dashboard'])

    @include('layouts.partials.head-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body>
<style>
   .select2-container--default .select2-selection--multiple .select2-selection__choice {background-color: #232e51 !important;}
</style>
    <div class="wrapper">

        @include('layouts.partials.sidenav')
        @include('layouts.partials.topbar')


        <div class="page-content">
            <div class="page-container">

                @yield('content')

            </div>
            @include('layouts.partials.footer')
        </div>

    </div>

    @include('layouts.partials.customizer')

    @include('layouts.partials.footer-scripts')
</body>

</html>

