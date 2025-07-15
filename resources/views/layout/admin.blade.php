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

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (Remover o `integrity`) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

    {{-- Header --}}
    @include('components.header')

    <main class="py-4">
        <div class="container mt-4">
            <h2>Página Administrativa</h2>
            <p>Bem-vindo ao painel de administração.</p>

            <!-- Quadro com links laterais -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <!-- Links laterais -->
                    <div class="list-group">
                        <a href="{{ route('admin.users.index') }}"
                            class="list-group-item list-group-item-action">Usuários</a>
                        <!-- Adicione outros links conforme necessário -->
                        <a href="{{ route('admin.uploads.index') }}" class="list-group-item list-group-item-action">Adicionar
                            novos
                            arquivos</a>
                        <a href="{{ route('pages.home') }}" class="list-group-item list-group-item-action">Home</a>
                        <a href="{{ route('admin.categories.index') }}"
                            class="list-group-item list-group-item-action">Categorias</a>
                        <a href="{{ route('admin.brands.index') }}" class="list-group-item list-group-item-action">Marcas</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <!-- Conteúdo principal que você vai ver depois -->
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

    <!-- JS Bibliotecas -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Inicialização dos sliders -->
    <script>
    new Swiper(".mySwiper", {
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    new Splide('#splide', {
        type: 'loop',
        perPage: 1,
        autoplay: true,
    }).mount();

    new Glide('.glide', {
        type: 'carousel',
        autoplay: 2000,
    }).mount();

    lightGallery(document.getElementById('lightgallery'), {
        plugins: [lgZoom],
        speed: 500,
    });
    </script>

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/43mbnibu3ong3lcvte3voj7cmoja1hxwscj81q2ublgk3rju/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>

    <script>
    tinymce.init({
        selector: 'textarea[name=description]',
        height: 400,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'print', 'preview', 'anchor',
            'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table',
            'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        language: 'pt_BR',
        setup: function(editor) {
            editor.on('change', function() {
                var content = editor.getContent();
                editor.targetElm.value = content;
            });
        }
    });
    </script>


</body>

</html>