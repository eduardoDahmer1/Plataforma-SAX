<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    {{-- Meta SEO e CSRF --}}
    <meta name="description" content="SAX - E-commerce de luxo com mais de 1000 marcas exclusivas.">
    <meta name="author" content="SAX Full Service">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">

    <title>SAX - Checkout Seguro</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    {{-- CSS Base --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">

    {{-- Bootstrap 5.3.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    {{-- Font Awesome & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
</head>

<body class="bg-light">

    {{-- Header específico do Checkout --}}
    @include('components.header-checkout')

    <main class="py-4 container">
        {{-- Aqui entrará o conteúdo do bancard.blade.php --}}
        @yield('content')
    </main>

    {{-- Footer padrão --}}
    @include('components.footer')

    {{-- Scripts globais da aplicação --}}
    @include('components.scripts')

    {{-- Stack para scripts específicos injetados pelas views (opcional, mas recomendado) --}}
    @stack('scripts')

</body>
</html>