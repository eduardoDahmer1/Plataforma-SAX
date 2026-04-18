<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body class="bg-light">

    @include('components.header')

    <main class="py-4 container">
        @yield('content')
    </main>

    @include('components.footer')

    <x-scripts-master />

</body>

</html>
