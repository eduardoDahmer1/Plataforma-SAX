<meta charset="utf-8">
<script>document.documentElement.dataset.analyticsEndpoint = @json(route('analytics.store'));</script>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="currency-sign" content="{{ session('currency_sign', 'US$') }}">
<meta name="currency-code" content="PYG">
<meta name="currency-value" content="{{ session('currency_value', 1) }}">

<script>
  // Configuração global para o Fetch API
  const headers = { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
</script>

@php
    $marketingSettings = \App\Models\MarketingSetting::current();
    $isProductionDomain = request()->getHost() === 'saxdepartment.com';
    $robotsContent = !Route::is('admin.*') && $isProductionDomain ? 'index,follow' : 'noindex,nofollow';
    $canonicalPath = request()->path() === '/' ? '' : '/'.ltrim(request()->path(), '/');
    $canonicalUrl = 'https://saxdepartment.com'.$canonicalPath;
    $titleDefault = $marketingSettings->default_meta_title ?: 'SAX - E-commerce de Luxo';
    if(Route::is('admin.*')) $titleDefault = 'SAX - Painel Administrativo';
    elseif(Request::is('*bridal*')) $titleDefault = 'SAX Bridal';
    elseif(Request::is('*cafe*') || Request::is('*bistro*')) $titleDefault = 'SAX Café & Bistrô';
    elseif(Route::is('checkout.*')) $titleDefault = 'SAX - Checkout Seguro';
    elseif(Request::is('*palace*')) $titleDefault = 'SAX Palace - Gastronomia & Eventos';
@endphp

<title>@yield('title', $titleDefault)</title>
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

<meta name="description" content="@yield('meta_description', $marketingSettings->default_meta_description ?: 'SAX - E-commerce de luxo com mais de 1000 marcas exclusivas.')">
@if($marketingSettings->default_meta_keywords)<meta name="keywords" content="{{ $marketingSettings->default_meta_keywords }}">@endif
<meta name="robots" content="{{ $robotsContent }}">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta name="author" content="{{ $marketingSettings->organization_name ?: 'SAX Full Service' }}">
<meta property="og:site_name" content="{{ $marketingSettings->site_name ?: 'SAX Department' }}">
<meta property="og:title" content="@yield('title', $marketingSettings->og_title ?: $titleDefault)">
<meta property="og:description" content="@yield('meta_description', $marketingSettings->og_description ?: $marketingSettings->default_meta_description ?: 'SAX - E-commerce de luxo com mais de 1000 marcas exclusivas.')">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:type" content="website">
<meta property="og:image" content="{{ $marketingSettings->og_image_url ?: asset('images/sax-og-image.jpg') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('title', $marketingSettings->og_title ?: $titleDefault)">
<meta name="twitter:description" content="@yield('meta_description', $marketingSettings->og_description ?: $marketingSettings->default_meta_description ?: 'SAX - E-commerce de luxo')">
<meta name="twitter:image" content="{{ $marketingSettings->og_image_url ?: asset('images/sax-og-image.jpg') }}">
@if($marketingSettings->twitter_site)<meta name="twitter:site" content="{{ str_starts_with($marketingSettings->twitter_site, '@') ? $marketingSettings->twitter_site : '@'.$marketingSettings->twitter_site }}">@endif
@if($marketingSettings->google_site_verification)<meta name="google-site-verification" content="{{ $marketingSettings->google_site_verification }}">@endif
@if($marketingSettings->bing_site_verification)<meta name="msvalidate.01" content="{{ $marketingSettings->bing_site_verification }}">@endif
@if($marketingSettings->meta_domain_verification)<meta name="facebook-domain-verification" content="{{ $marketingSettings->meta_domain_verification }}">@endif
<meta name="currency-sign" content="{{ session('currency_sign', 'US$') }}">
<meta name="currency-value" content="{{ session('currency_value', 1) }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}" rel="stylesheet">
@if(!Request::is('*cafe*') && !Request::is('*bistro*') && !Request::is('*bridal*') && !Request::is('*palace*') && !Request::is('*institucional*'))
    <link href="{{ asset('css/auth.css') }}?v={{ file_exists(public_path('css/auth.css')) ? filemtime(public_path('css/auth.css')) : time() }}" rel="stylesheet">
@endif

{{-- Temáticas: Café & Bistrô, Bridal, Palace, Institucional (solo público, nunca en admin) --}}
@if(!Route::is('admin.*') && (Request::is('*cafe*') || Request::is('*bistro*') || Request::is('*bridal*') || Request::is('*palace*') || Request::is('*institucional*')))
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

    @if(Request::is('*cafe*') || Request::is('*bistro*'))
        <link href="{{ asset('css/cafe_bistro.css') }}?v={{ file_exists(public_path('css/cafe_bistro.css')) ? filemtime(public_path('css/cafe_bistro.css')) : time() }}" rel="stylesheet">
    @endif

    @if(Request::is('*bridal*') || Request::is('*palace*') || Request::is('*institucional*'))
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    @endif

    @if(Request::is('*bridal*')) <link href="{{ asset('css/bridal.css') }}?v={{ filemtime(public_path('css/bridal.css')) }}" rel="stylesheet"> @endif
    @if(Request::is('*palace*')) <link href="{{ asset('css/palace.css') }}?v={{ filemtime(public_path('css/palace.css')) }}" rel="stylesheet"> @endif
    @if(Request::is('*institucional*'))
        <link href="{{ asset('css/institucional.css') }}?v={{ filemtime(public_path('css/institucional.css')) }}" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    @endif
@endif

{{-- 3. Checkout + Carrinho --}}
@if(Route::is('checkout.*') || Route::is('cart.*'))
    <link href="{{ asset('css/checkout.css') }}?v={{ file_exists(public_path('css/checkout.css')) ? filemtime(public_path('css/checkout.css')) : time() }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
@endif

{{-- 4. Detalhes do Produto --}}
@if(Route::is('produto.show') || Route::is('product.show'))
    <link href="{{ asset('css/show-products.css') }}?v={{ filemtime(public_path('css/show-products.css')) }}" rel="stylesheet">
@endif

{{-- 5. Blog --}}
@if(Request::is('*blog*') || Request::is('*blogs*'))
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/blog.css') }}?v={{ filemtime(public_path('css/blog.css')) }}" rel="stylesheet">
@endif

{{-- 6. Admin --}}
@if(Route::is('admin.*') || Route::is('manutencao') || (Route::is('receipts.*') && auth()->user()?->user_type == 1))
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}" rel="stylesheet">
@endif

{{-- 7. User / Dashboard (Adicionado aqui) --}}
@if(Route::is('user.*') || Route::is('dashboard') || (Route::is('receipts.*') && auth()->user()?->user_type != 1))
    <link href="{{ asset('css/user.css') }}?v={{ file_exists(public_path('css/user.css')) ? filemtime(public_path('css/user.css')) : time() }}" rel="stylesheet">
@endif

@stack('styles')
@stack('head-scripts')
<x-marketing-head :settings="$marketingSettings" />
