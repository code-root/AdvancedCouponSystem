<!DOCTYPE html>
<html @yield('html-attribute')>

<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Dashboard'])

    @include('layouts.partials.head-css')
</head>

<body>

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

