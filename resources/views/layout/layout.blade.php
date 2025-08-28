<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Descrição da sua aplicação.">
    <meta name="author" content="Seu Nome ou Empresa">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- CSS do app compilado com Laravel Mix ou Vite -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/plugins/resizimg/trumbowyg.resizimg.min.css" />

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS do Trumbowyg -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body>

    {{-- Header --}}
    @include('components.header')

    <main class="py-4 container">
        @yield('content')
    </main>
    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn btn-primary position-fixed"
        style="bottom:30px; right:30px; display:none; z-index:1050;">
        <i class="fa fa-arrow-up"></i>
    </button>

    {{-- Footer --}}
    @include('components.footer')
    @include('components.scripts')
    @include('components.javascripts')

</body>

</html>
