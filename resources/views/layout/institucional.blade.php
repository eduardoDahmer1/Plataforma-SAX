<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body class="experience-page experience-page--institutional">
    <x-marketing-body-start />
    <x-header-internas />

    <main>
        @yield('content')
    </main>

    @include('institucional.componentes.footer')
    @include('components.whatsapp')

    <x-scripts-master />
</body>

</html>
