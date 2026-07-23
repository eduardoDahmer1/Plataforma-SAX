<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>
    <x-marketing-body-start />

    {{-- Header --}}
    @include('components.header')

    <main>
        @yield('content')
    </main>

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn btn-primary position-fixed">
        <i class="fa fa-arrow-up"></i>
    </button>

    @include('components.whatsapp')

    {{-- Footer --}}
    @include('components.footer')

    <x-scripts-master />

</body>

</html>
