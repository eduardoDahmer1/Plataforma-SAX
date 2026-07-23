<!DOCTYPE html>
<html lang="pt">
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
