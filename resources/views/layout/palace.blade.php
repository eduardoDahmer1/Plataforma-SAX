<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>
    <x-header-internas />

    <main>
        @yield('content')

        @include('palace.tematica')

        @include('palace.location')
    </main>

    @include('palace.footer')

    @include('palace.script')

    @stack('scripts')
</body>

</html>
