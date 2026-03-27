<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $titleDefault = 'SAX - E-commerce de Luxo';
    if(Route::is('admin.*')) $titleDefault = 'SAX - Painel Administrativo';
    elseif(Request::is('*bridal*')) $titleDefault = 'SAX Bridal';
    elseif(Request::is('*cafe*') || Request::is('*bistro*')) $titleDefault = 'SAX Café & Bistrô';
    elseif(Route::is('checkout.*')) $titleDefault = 'SAX - Checkout Seguro';
    elseif(Request::is('*palace*')) $titleDefault = 'SAX Palace - Gastronomia & Eventos';
@endphp

<title>@yield('title', $titleDefault)</title>
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

<meta name="description" content="@yield('meta_description', 'SAX - E-commerce de luxo com mais de 1000 marcas exclusivas.')">
<meta name="author" content="SAX Full Service">
<meta property="og:title" content="@yield('title', $titleDefault)">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<meta property="og:image" content="{{ asset('images/sax-og-image.jpg') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

{{-- 1. Café & Bistrô --}}
@if(Request::is('*cafe*') || Request::is('*bistro*'))
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@100;300;400;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/cafe_bistro.css') }}?v={{ file_exists(public_path('css/cafe_bistro.css')) ? filemtime(public_path('css/cafe_bistro.css')) : time() }}" rel="stylesheet">
@endif

{{-- 2. Bridal, Palace e Institucionais (Compartilham Swiper, AOS e Fontes Elegantes) --}}
@if(Request::is('*bridal*') || Request::is('*palace*') || Request::is('*institucional*'))
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />

    @if(Request::is('*bridal*')) <link href="{{ asset('css/bridal.css') }}" rel="stylesheet"> @endif
    @if(Request::is('*palace*')) <link href="{{ asset('css/palace.css') }}" rel="stylesheet"> @endif
    @if(Request::is('*institucional*'))
        <link href="{{ asset('css/institucional.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    @endif
@endif

{{-- 3. Checkout --}}
@if(Route::is('checkout.*'))
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
@endif

{{-- 4. Detalhes do Produto --}}
@if(Route::is('produto.show'))
    <link href="{{ asset('css/show-products.css') }}" rel="stylesheet">
@endif

{{-- 5. Admin --}}
@if(Route::is('admin.*') || Route::is('manutencao'))
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endif

@stack('styles')
@stack('head-scripts')