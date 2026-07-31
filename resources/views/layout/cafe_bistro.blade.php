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

    @include('cafe_bistro.componentes.footer')
    @include('components.whatsapp')

    <x-scripts-master />
</body>
</html>
