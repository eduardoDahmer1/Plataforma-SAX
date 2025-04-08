<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Meta tags essenciais -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO e redes sociais -->
    <meta name="description" content="Descrição da sua aplicação.">
    <meta name="author" content="Seu Nome ou Empresa">

    <!-- CSRF Token Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Título da página -->
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts (opcional) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-6Y6sD9aZTvcTrIvKbQ0ZP3N4LQOd8Uv+fKz0JBg2nE8yVXlN06L6WwbMDeW08NDI" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Estilos compilados pelo Laravel Vite -->
    
</head>
<body>

    {{-- Header --}}
    @include('components.header')

    {{-- Conteúdo das páginas --}}
    <main class="py-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-AuWmPXZoOPvZWuMYFd6r5QFsygxF1zV/UrEzUpOZ9+PshujAJ+3uQ7KcEcbTL0Jy" crossorigin="anonymous"></script>

</body>
</html>
