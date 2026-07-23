<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body class="palace-page">
    <x-marketing-body-start />
    <x-header-internas :palace-whatsapp="$palace->contato_whatsapp" />

    <main class="palace-main">
        @yield('content')

        @include('palace.tematica')

        @include('palace.location')
    </main>

    @include('palace.footer')
    @include('components.whatsapp')

    <x-scripts-master />
</body>

</html>
