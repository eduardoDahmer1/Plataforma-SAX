<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

@php($activeStorefrontLayout = $storefrontLayout ?? app(\App\Services\StorefrontLayoutService::class)->current())
<body class="sax-storefront storefront-layout-{{ $activeStorefrontLayout }}">
    <x-marketing-body-start />

    {{-- Header --}}
    @include(app(\App\Services\StorefrontLayoutService::class)->partial('header'))
    @include('components.catalog-integration-notice')
    @include('components.store-control-notice')

    <main>
        @yield('content')
    </main>

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn btn-primary position-fixed">
        <i class="fa fa-arrow-up"></i>
    </button>

    {{-- Footer --}}
    @include(app(\App\Services\StorefrontLayoutService::class)->partial('footer'))

    <x-scripts-master />

</body>

</html>
