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
    @include('components.styles')
    
</head>

<body>
    {{-- Header --}}
    @include('components.header')
    <!-- header-admin -->
     
    <main class="py-4">
        <div class="container mt-4">
            <h2>Página Administrativa</h2>
            <p>Bem-vindo ao painel de administração.</p>

            <!-- Quadro com links laterais -->
            <div class="row mt-4">
                @include('admin.menu-lateral')
                <div class="col-md-9">
                    <!-- Conteúdo principal -->
                    <div class="card">
                        <div class="card-header">
                            <strong>Quadro Principal</strong>
                        </div>
                        <div class="card-body">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    @include('components.footer')
    @include('components.scripts')
    @include('components.javascripts')
</body>

</html>
