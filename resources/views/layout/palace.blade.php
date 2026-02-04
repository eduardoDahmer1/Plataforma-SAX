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
            background-color: var(--palace-dark);
            color: var(--palace-text);
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1,
        h2,
        h3,
        h4,
        .font-serif {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .gold-text {
            color: var(--palace-gold);
        }

        .letter-spacing-2 {
            letter-spacing: 2px;
        }

        .letter-spacing-5 {
            letter-spacing: 5px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--palace-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--palace-gold);
            border-radius: 10px;
        }

        /* Header e Navegação */
        .palace-nav {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(197, 160, 89, 0.15);
            padding: 1.2rem 0;
            z-index: 1050;
            transition: var(--transition-smooth);
        }

        .palace-nav.scrolled {
            padding: 0.8rem 0;
            background: #000;
        }

        .palace-nav .nav-link {
            color: var(--palace-text) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            margin: 0 15px;
            position: relative;
            transition: var(--transition-smooth);
        }

        .palace-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--palace-gold);
            transition: var(--transition-smooth);
        }

        .palace-nav .nav-link:hover::after {
            width: 100%;
        }

        .palace-nav .nav-link:hover {
            color: var(--palace-gold) !important;
        }

        /* Botões */
        .btn-palace {
            border: 1px solid var(--palace-gold);
            color: var(--palace-gold);
            background: transparent;
            padding: 12px 30px;
            border-radius: 0;
            /* Design Luxury costuma ser quadrado ou totalmente redondo */
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            display: inline-block;
            text-decoration: none;
        }

        .btn-palace:hover {
            background: var(--palace-gold);
            color: #000;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(197, 160, 89, 0.2);
        }

        /* Seções Gerais */
        .section-padding {
            padding: 100px 0;
        }

        .bg-palace-soft {
            background-color: var(--palace-dark-soft);
        }

        .gold-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--palace-gold), transparent);
            width: 150px;
            margin: 30px auto;
        }

        .section-title {
            margin-bottom: 60px;
        }

        .section-title span {
            display: block;
            color: var(--palace-gold);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 4px;
            margin-bottom: 10px;
        }

        /* Cards Gastronomia */
        .food-card {
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: var(--transition-smooth);
        }

        .food-card img {
            transition: transform 1.5s ease;
            filter: grayscale(30%);
        }

        .food-card:hover img {
            transform: scale(1.1);
            filter: grayscale(0%);
        }

        .food-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
        }

        /* Bodega & Vinhos */
        .bodega-item {
            border-left: 1px solid rgba(197, 160, 89, 0.3);
            padding-left: 30px;
            margin-bottom: 40px;
            transition: var(--transition-smooth);
        }

        .bodega-item:hover {
            border-left-color: var(--palace-gold);
            transform: translateX(10px);
        }

        .bodega-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: rgba(197, 160, 89, 0.2);
            line-height: 1;
        }

        /* Footer */
        .palace-footer {
            background: #000;
            padding-top: 80px;
            border-top: 1px solid rgba(197, 160, 89, 0.1);
        }

        .footer-logo img {
            height: 60px;
            margin-bottom: 30px;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 15px;
        }

        .footer-links a {
            color: var(--palace-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: var(--transition-smooth);
        }

        .footer-links a:hover {
            color: var(--palace-gold);
            padding-left: 5px;
        }

        .contact-info i {
            width: 30px;
            color: var(--palace-gold);
        }

        .social-circle {
            width: 45px;
            height: 45px;
            border: 1px solid rgba(197, 160, 89, 0.3);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 10px;
            transition: var(--transition-smooth);
        }

        .social-circle:hover {
            background: var(--palace-gold);
            color: #000;
            border-color: var(--palace-gold);
        }

        .copyright {
            background: #050505;
            padding: 25px 0;
            margin-top: 80px;
            font-size: 0.75rem;
            color: #555;
            letter-spacing: 1px;
        }

        /* Floating Menu Button */
        .menu-float {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 999;
            background: rgba(197, 160, 89, 0.9);
            color: #000;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        /* Garantir que os cards tenham proporção perfeita e não quebrem */
        .event-card {
            position: relative;
            overflow: hidden;
            border-radius: 0;
            /* Estilo Palace geralmente é reto/clássico */
            aspect-ratio: 4 / 5;
            /* Mantém a altura elegante e consistente */
            margin-bottom: 20px;
        }

        .event-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .event-card:hover .event-img {
            transform: scale(1.1);
        }

        .event-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            /* Overlay suave constante ou hover */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #fff;
            opacity: 1;
            /* Mantendo visível para melhor UX mobile, ou use hover se preferir */
            transition: background 0.3s ease;
            text-align: center;
        }

        .border-gold {
            border-color: #D4AF37 !important;
            /* Exemplo de cor dourada */
            opacity: 1;
        }

        @media (max-width: 768px) {
            .display-4 {
                font-size: 2.2rem;
            }
        }

        .menu-float:hover {
            background: #fff;
            transform: scale(1.05);
        }

        @media (max-width: 991px) {
            .section-padding {
                padding: 60px 0;
            }

            .display-4 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>

    <div class="menu-float">
        <i class="bi bi-journal-text me-2"></i> Nosso Menu
    </div>

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
