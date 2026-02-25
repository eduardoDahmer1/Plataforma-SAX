<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SAX Bridal')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@200;300;400;500&family=Cinzel:wght@400;700&family=Dancing+Script:wght@400;500&display=swap" rel="stylesheet">

    {{-- Vendor CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    {{-- Estilos globales de bridal --}}
    <style>
        :root {
            --bridal-white: #ffffff;
            --bridal-cream: #FAF8F5;
            --bridal-gold: #C9A961;
            --bridal-gold-light: #e2d1b0;
            --bridal-dark: #2C2C2C;
            --font-serif: 'Playfair Display', serif;
            --font-sans: 'Montserrat', sans-serif;
            --font-display: 'Cinzel', serif;
            --transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* --- Reset & Base --- */
        *, *::before, *::after { box-sizing: border-box; }

        html {
            overflow-x: hidden;
        }

        body {
            margin: 0;
            background-color: var(--bridal-cream);
            color: var(--bridal-dark);
            font-family: var(--font-sans);
            font-size: 0.95rem;
            font-weight: 300;
            line-height: 1.7;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-serif);
            font-weight: 400;
            line-height: 1.2;
        }

        img { max-width: 100%; height: auto; }

        /* --- Typography Utilities --- */
        .title-gold {
            color: var(--bridal-gold);
            font-family: var(--font-display);
            font-size: 0.7rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 12px;
            font-weight: 400;
        }

        .section-title {
            font-family: var(--font-serif);
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            line-height: 1.15;
            color: var(--bridal-dark);
            font-weight: 400;
        }

        .serif-italic {
            font-family: var(--font-serif);
            font-style: italic;
            text-transform: none;
        }

        /* --- Sections --- */
        .section-padding { padding: 80px 0; }

        @media (min-width: 768px) {
            .section-padding { padding: 100px 0; }
        }

        /* --- Buttons --- */
        .btn-sax {
            padding: 14px 36px;
            background: var(--bridal-dark);
            color: var(--bridal-white);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.7rem;
            font-weight: 500;
            border: none;
            transition: var(--transition);
            display: inline-block;
            text-decoration: none;
        }

        .btn-sax:hover {
            background: var(--bridal-gold);
            color: var(--bridal-white);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(201, 169, 97, 0.25);
        }

        .btn-sax-outline {
            padding: 13px 34px;
            border: 1px solid var(--bridal-gold);
            background: transparent;
            color: var(--bridal-gold);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.7rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            display: inline-block;
        }

        .btn-sax-outline:hover {
            background: var(--bridal-gold);
            color: var(--bridal-white);
            transform: translateY(-2px);
        }

        /* --- Reveal Animations --- */
        [data-reveal] {
            opacity: 0;
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        [data-reveal="up"]    { transform: translateY(30px); }
        [data-reveal="left"]  { transform: translateX(-30px); }
        [data-reveal="right"] { transform: translateX(30px); }
        [data-reveal="scale"] { transform: scale(1.03); }

        .revealed {
            opacity: 1 !important;
            transform: translate(0) scale(1) !important;
        }
    </style>

    @stack('styles')
    @stack('head-scripts')
</head>
<body>

    @include('bridal.componentes.header')

    <main>
        @yield('content')
    </main>

    @include('bridal.componentes.footer')

    @include('bridal.componentes.scripts')

    @stack('scripts')
</body>
</html>
