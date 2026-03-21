<!DOCTYPE html>
<html lang="pt">
<head>
    <x-head-master />
</head>
<body>

    <x-header-internas />

    <main>
        @yield('content')
    </main>

    @include('cafe_bistro.componentes.footer')

    <x-scripts-master />
</body>
</html>
