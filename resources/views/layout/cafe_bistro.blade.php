<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SAX Café & Bistrô')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@100;300;400;600&display=swap" rel="stylesheet">

    {{-- Vendor CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Estilos do Café & Bistrô --}}
    <link href="{{ asset('css/cafe_bistro.css') }}?v={{ filemtime(public_path('css/cafe_bistro.css')) }}" rel="stylesheet">

    @stack('styles')
    @stack('head-scripts')
</head>
<body>

    @include('cafe_bistro.componentes.header')

    <main>
        @yield('content')
    </main>

    @include('cafe_bistro.componentes.footer')

    @include('cafe_bistro.componentes.scripts')

    @stack('scripts')

    {{-- Vendor JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
