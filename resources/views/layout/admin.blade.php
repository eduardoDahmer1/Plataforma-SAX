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

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <!-- Sidebar -->
                    <div class="list-group">

                        <a href="{{ route('admin.index') }}" class="list-group-item list-group-item-action">Admin</a>
                        <a class="list-group-item list-group-item-action">Atualizar produtos</a>
                        <a href="{{ route('pages.home') }}" class="list-group-item list-group-item-action">Home</a>
                        <a href="{{ route('admin.users.index') }}"
                            class="list-group-item list-group-item-action">Usuários</a>

                        <!-- Catálogos -->
                        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
                            href="#menuCatalogos" role="button" aria-expanded="false" aria-controls="menuCatalogos">
                            Catálogos
                        </a>
                        <div class="collapse" id="menuCatalogos">
                            <a class="list-group-item list-group-item-action ps-4">Produtos</a>
                            <a href="{{ route('admin.brands.index') }}"
                                class="list-group-item list-group-item-action ps-4">Marcas</a>
                            <a href="{{ route('admin.categories.index') }}"
                                class="list-group-item list-group-item-action ps-4">Categorias</a>
                        </div>

                        <!-- Vendas -->
                        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
                            href="#menuVendas" role="button" aria-expanded="false" aria-controls="menuVendas">
                            Vendas
                        </a>
                        <div class="collapse" id="menuVendas">
                            <a class="list-group-item list-group-item-action ps-4">Pedidos</a>
                            <a class="list-group-item list-group-item-action ps-4">Clientes</a>
                        </div>

                        <!-- Conteúdos -->
                        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
                            href="#menuConteudos" role="button" aria-expanded="false" aria-controls="menuConteudos">
                            Conteúdos
                        </a>
                        <div class="collapse" id="menuConteudos">
                            <a class="list-group-item list-group-item-action ps-4">Blog</a>
                            <a class="list-group-item list-group-item-action ps-4">Serviços</a>
                            <a class="list-group-item list-group-item-action ps-4">Slider</a>
                            <a class="list-group-item list-group-item-action ps-4">Banners</a>
                            <a class="list-group-item list-group-item-action ps-4">Popup</a>
                            <a class="list-group-item list-group-item-action ps-4">Páginas</a>
                            <a class="list-group-item list-group-item-action ps-4">Contato</a>
                            <a class="list-group-item list-group-item-action ps-4">Página Não Encontrada</a>
                            <a class="list-group-item list-group-item-action ps-4">Política de Compra/Devolução</a>
                            <a class="list-group-item list-group-item-action ps-4">Política de Privacidade</a>
                        </div>

                        <!-- Marketing -->
                        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
                            href="#menuMarketing" role="button" aria-expanded="false" aria-controls="menuMarketing">
                            Marketing
                        </a>
                        <div class="collapse" id="menuMarketing">
                            <a class="list-group-item list-group-item-action ps-4">Cupons</a>
                            <a class="list-group-item list-group-item-action ps-4">Palavras Chave Meta</a>
                        </div>

                        <!-- Sistema -->
                        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
                            href="#menuSistema" role="button" aria-expanded="false" aria-controls="menuSistema">
                            Sistema
                        </a>
                        <div class="collapse" id="menuSistema">
                            <a class="list-group-item list-group-item-action ps-4">Limpar Cache</a>
                            <a class="list-group-item list-group-item-action ps-4">Manutenção</a>
                            <a class="list-group-item list-group-item-action ps-4">Termos de Serviços Gerais</a>
                        </div>

                        <!-- Uploads -->
                        <a href="{{ route('admin.uploads.index') }}" class="list-group-item list-group-item-action">
                            Adicionar novos arquivos
                        </a>
                    </div>
                </div>

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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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