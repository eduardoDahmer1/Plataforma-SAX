<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

@php
    $headerLayoutService = app(\App\Services\StorefrontLayoutService::class);
    $checkoutHeaderLayout = $headerLayoutService->headerLayout();
@endphp
<body class="sax-storefront sax-checkout-page bg-light storefront-layout-{{ $checkoutHeaderLayout }} header-layout-{{ $checkoutHeaderLayout }}">
    <x-marketing-body-start />

    @include($headerLayoutService->headerPartial())
    @include('components.catalog-integration-notice')
    @include('components.store-control-notice')

    <main class="sax-checkout-main py-4 container">
        @yield('content')
    </main>

    @include('components.footer')

    <x-scripts-master />

</body>

</html>
