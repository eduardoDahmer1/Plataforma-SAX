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

    <x-scripts-master />

    @yield('section-scripts')
</body>

</html>
