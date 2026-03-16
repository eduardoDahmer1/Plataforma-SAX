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

    @include('institucional.componentes.footer')

    @include('institucional.componentes.script')

    @stack('scripts')
</body>

</html>
