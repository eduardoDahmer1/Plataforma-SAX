<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta tags essenciais -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Meta SEO / sociais -->
    <meta name="description" content="SAX - E-commerce de luxo com mais de 1000 marcas exclusivas.">
    <meta name="author" content="SAX Full Service">
    <meta name="keywords" content="luxo, e-commerce, moda, sapatos, roupas, acessórios, marca exclusiva">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d6efd">
    <meta name="application-name" content="SAX">
    <meta name="apple-mobile-web-app-title" content="SAX">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="SAX - E-commerce de luxo">
    <meta property="og:description" content="Descubra nossas marcas exclusivas e produtos de luxo.">
    <meta property="og:image" content="{{ asset('images/sax-og-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SAX">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SAX - E-commerce de luxo">
    <meta name="twitter:description" content="Descubra nossas marcas exclusivas e produtos de luxo.">
    <meta name="twitter:image" content="{{ asset('images/sax-og-image.jpg') }}">
    <meta name="twitter:site" content="@saxluxo">

    <title>SAX - E-commerce de Luxo</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Preconnect: otimiza carregamento de recursos externos -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>

    <!-- CSS do app compilado com Laravel Mix ou Vite -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS (SRI + crossorigin) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Trumbowyg Core CSS e Plugins CSS -->
    <link rel="stylesheet" href="https://unpkg.com/trumbowyg/dist/ui/trumbowyg.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/trumbowyg/dist/plugins/resizimg/trumbowyg.resizimg.min.css" />

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

</head>

<body>

    <main class="py-4 container-fluid">
        @yield('content')
    </main>

</body>

</html>
