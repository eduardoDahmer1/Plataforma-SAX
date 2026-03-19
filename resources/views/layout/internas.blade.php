<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>
    <x-header-internas />

    <main>
        @yield('content')
    </main>

    @yield('footer')

    @yield('section-scripts')

    @stack('scripts')
</body>

</html>
