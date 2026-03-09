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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    {{-- Vendor CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Estilos globais do Café & Bistrô --}}
    <style>
        :root {
            --azul-profundo:  #0f1d35;
            --azul-navy:      #1a2a4a;
            --azul-medio:     #2d4a7a;
            --azul-claro:     #4a6fa5;
            --branco:         #ffffff;
            --branco-suave:   #e8edf5;
            --font-serif:     'Playfair Display', serif;
            --font-sans:      'Lato', sans-serif;
            --transition:     all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* --- Reset & Base --- */
        *, *::before, *::after { box-sizing: border-box; }

        html { overflow-x: hidden; }

        body {
            margin: 0;
            background-color: var(--azul-profundo);
            color: var(--branco-suave);
            font-family: var(--font-sans);
            font-size: 0.95rem;
            font-weight: 400;
            line-height: 1.7;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-serif);
            font-weight: 400;
            line-height: 1.2;
            color: var(--branco);
        }

        img { max-width: 100%; height: auto; }

        a { color: inherit; text-decoration: none; }

        /* --- Utilitários de tipografia --- */
        .eyebrow {
            font-family: var(--font-sans);
            font-size: 0.625rem;   /* 10px */
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--branco);
            display: block;
            margin-bottom: 0.75rem;
        }

        .divider {
            width: 2.5rem;
            height: 1px;
            background: var(--branco);
            opacity: 0.4;
            margin: 1rem 0;
        }

        .section-title {
            font-family: var(--font-serif);
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            line-height: 1.15;
            color: var(--branco);
            font-weight: 400;
        }

        /* --- Seções --- */
        .section-padding { padding: 5rem 0; }

        @media (min-width: 768px) {
            .section-padding { padding: 6.25rem 0; }
        }

        /* --- Botões --- */
        .btn-cafe-primary {
            background: var(--branco);
            color: var(--azul-profundo);
            border: none;
            padding: 0.875rem 2.25rem;
            font-family: var(--font-sans);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: inline-block;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-cafe-primary:hover {
            background: var(--branco-suave);
            color: var(--azul-profundo);
            transform: translateY(-2px);
        }

        .btn-cafe-outline {
            background: transparent;
            color: var(--branco);
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 0.875rem 2.25rem;
            font-family: var(--font-sans);
            font-size: 0.75rem;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: inline-block;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-cafe-outline:hover {
            border-color: var(--branco);
            background: rgba(255, 255, 255, 0.08);
            color: var(--branco);
        }

        .btn-whatsapp {
            background: #25D366;
            color: var(--branco);
            border: none;
            padding: 0.875rem 2.25rem;
            font-family: var(--font-sans);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-whatsapp:hover {
            background: #1ebe5c;
            color: var(--branco);
            transform: translateY(-2px);
        }

        /* --- Animações de entrada --- */
        [data-reveal] {
            opacity: 0;
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        [data-reveal="up"]    { transform: translateY(1.875rem); }
        [data-reveal="left"]  { transform: translateX(-1.875rem); }
        [data-reveal="right"] { transform: translateX(1.875rem); }
        [data-reveal="scale"] { transform: scale(1.03); }

        .revealed {
            opacity: 1 !important;
            transform: translate(0) scale(1) !important;
        }

        /* --- Placeholder de imagem --- */
        .img-placeholder {
            background: #2a3d5e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.3);
            font-family: var(--font-sans);
            font-size: 0.75rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>

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
