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

    {{-- Vendor JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @include('cafe_bistro.componentes.scripts')

    @stack('scripts')
</body>
</html>
