<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>

    <main class="py-4 container-fluid">
        @yield('content')
    </main>

</body>

</html>
