<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>
    <x-marketing-body-start />
    <x-header-internas />

    <main>
        @yield('content')
    </main>

    @yield('footer')
    @include('components.whatsapp')

    <x-scripts-master />

    @yield('section-scripts')
</body>

</html>
