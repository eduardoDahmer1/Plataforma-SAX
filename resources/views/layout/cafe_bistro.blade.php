<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-head-master />
</head>
<body class="experience-page experience-page--bistro{{ ($cafeBistro->slug ?? null) === 'asuncion' ? ' experience-page--bistro-asuncion' : '' }}">
    <x-marketing-body-start />

    <x-header-internas :cafe-bistro="$cafeBistro" :cafe-bistro-locations="$cafeBistroLocations" />

    <main>
        @yield('content')
    </main>

    @include('cafe_bistro.componentes.footer')
    <x-scripts-master />
</body>
</html>
