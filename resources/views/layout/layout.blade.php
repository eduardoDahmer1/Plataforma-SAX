<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>

    {{-- Header --}}
    @include('components.header')

    <main>
        @yield('content')
    </main>

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn btn-primary position-fixed"
        style="bottom:30px; right:1em; display:none; z-index:1050;width: 3em;">
        <i class="fa fa-arrow-up"></i>
    </button>

    {{-- Footer --}}
    @include('components.footer')

    <x-scripts-master />

</body>

</html>