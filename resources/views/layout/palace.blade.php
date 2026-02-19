<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description"
        content="SAX Palace - Experiência gastronômica e eventos de luxo no coração de Ciudad del Este.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">

    <title>SAX Palace - Gastronomia & Eventos de Luxo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@200;300;400;600;700&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />

    <style>
        :root {
            --palace-dark: #0a0a0a;
            --palace-dark-soft: #141414;
            --palace-gold: #c5a059;
            --palace-gold-hover: #e2b86d;
            --palace-text: #ffffff;
            --palace-muted: #a0a0a0;
            --transition-smooth: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset & Base */
        body {
            background-color: black !important;
            color: var(--palace-text);
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    @include('palace.header')

    <main>
        @yield('content')

        @include('palace.tematica')

        @include('palace.location')
    </main>

    @include('palace.footer')

    @include('palace.script')

    @stack('scripts')
</body>

</html>
