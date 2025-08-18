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
    @include('components.styles')
</head>

<body>

    {{-- Header --}}
    @include('components.header-checkout')

    <main class="py-4 container">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')
    @include('components.scripts')
    @include('components.javascripts')

</body>

</html>