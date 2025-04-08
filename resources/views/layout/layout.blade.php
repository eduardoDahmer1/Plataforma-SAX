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

    <link rel="stylesheet" href="{{ asset('resources/css/app.css') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Splide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">

    <!-- Flickity CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flickity@2.3.0/dist/flickity.min.css">

    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/css/glide.core.min.css">

    <!-- LightGallery CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery-bundle.min.css" />

    <style>
    body {
        font-family: 'Roboto', sans-serif;
    }

    .slider-section {
        margin: 60px auto;
    }

    .swiper,
    .splide,
    .glide,
    .flickity-carousel,
    #lightgallery {
        max-width: 900px;
        margin: auto;
    }

    .swiper-slide img,
    .splide__slide img,
    .glide__slide img,
    .flickity-carousel img,
    #lightgallery a img {
        width: 100%;
        border-radius: 10px;
    }
    </style>
</head>

<body>

    {{-- Header --}}
    @include('components.header')

    <main class="py-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    <!-- JS Bibliotecas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Splide JS -->
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>

    <!-- Flickity JS -->
    <script src="https://cdn.jsdelivr.net/npm/flickity@2.3.0/dist/flickity.pkgd.min.js"></script>

    <!-- Glide.js JS -->
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide@3.6.0/dist/glide.min.js"></script>

    <!-- LightGallery JS -->
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/zoom/lg-zoom.umd.min.js"></script>

    <!-- Inicialização dos sliders
    <script>
    new Swiper(".mySwiper", {
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    new Splide('#splide', {
        type: 'loop',
        perPage: 1,
        autoplay: true,
    }).mount();

    new Glide('.glide', {
        type: 'carousel',
        autoplay: 2000,
    }).mount();

    lightGallery(document.getElementById('lightgallery'), {
        plugins: [lgZoom],
        speed: 500,
    });
    </script> -->

</body>

</html>