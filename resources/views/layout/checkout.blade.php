<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body class="sax-storefront sax-checkout-page bg-light">
    <x-marketing-body-start />

    @include('components.header')
    @include('components.catalog-integration-notice')
    @include('components.store-control-notice')

    <main class="sax-checkout-main py-4 container">
        @yield('content')
    </main>

    @include('components.footer')

    <x-scripts-master />

</body>

</html>
