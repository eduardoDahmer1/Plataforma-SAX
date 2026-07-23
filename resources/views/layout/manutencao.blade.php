<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>
    <x-marketing-body-start />

    <main class="py-4 container-fluid">
        @yield('content')
    </main>

</body>

</html>
