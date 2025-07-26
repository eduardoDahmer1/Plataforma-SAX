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

    <!-- CSS do app compilado com Laravel Mix ou Vite -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS do Trumbowyg -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">

    <!-- jQuery (obrigatório) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- JS do Trumbowyg -->
    <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
</head>

<body>
    {{-- Header --}}
    @include('components.header-admin')

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

    <script>
        document.getElementById('clearCacheBtn').addEventListener('click', function () {
            fetch('{{ secure_url(route('admin.clear-cache', [], false)) }}', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert('Erro ao limpar o cache');
                console.error(error);
            });
        });
    </script>

    <!-- Scripts -->
    <script>
        /** ========================
         * Iniciar Trumbowyg para os editores
         ========================== */
        $(document).ready(function () {
            // Inicia Trumbowyg para o primeiro editor
            $('#editor').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['undo', 'redo'],
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                ]
            });

            // Inicia Trumbowyg para o segundo editor (se houver)
            $('#editor-blog').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['undo', 'redo'],
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                ]
            });

            // Garante que o conteúdo do editor seja salvo no formulário ao enviar
            $('#editForm').on('submit', function () {
                const htmlEditor = $('#editor').trumbowyg('html');
                const htmlEditorBlog = $('#editor-blog').trumbowyg('html');

                // Salva o conteúdo de cada editor nas textareas correspondentes
                $('textarea[name="description"]').val(htmlEditor); // Para o primeiro editor
                $('textarea[name="blog_description"]').val(htmlEditorBlog); // Para o segundo editor (se necessário)
            });
        });
    </script>

    <script>
        /** ========================
         * Categoria/Subcategoria/Filha
         ========================== */
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const subcategorySelect = document.getElementById('subcategory_id');
            const childcategorySelect = document.getElementById('childcategory_id');

            if (!categorySelect || !subcategorySelect || !childcategorySelect) return;

            const categories = JSON.parse(categorySelect.dataset.categories || '[]');
            const subcategories = JSON.parse(subcategorySelect.dataset.subcategories || '[]');
            const childcategories = JSON.parse(childcategorySelect.dataset.childcategories || '[]');

            function clearOptions(select) {
                select.innerHTML = '<option value="">Selecione uma opção</option>';
            }

            function populateSubcategories(categoryId) {
                clearOptions(subcategorySelect);
                clearOptions(childcategorySelect);
                if (!categoryId) return;

                const filteredSubs = subcategories.filter(s => s.category_id == categoryId);
                filteredSubs.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.text = sub.name || sub.slug;
                    subcategorySelect.appendChild(option);
                });
            }

            function populateChildcategories(subcategoryId) {
                clearOptions(childcategorySelect);
                if (!subcategoryId) return;

                const filteredChilds = childcategories.filter(c => c.subcategory_id == subcategoryId);
                filteredChilds.forEach(child => {
                    const option = document.createElement('option');
                    option.value = child.id;
                    option.text = child.name || child.slug;
                    childcategorySelect.appendChild(option);
                });
            }

            categorySelect.addEventListener('change', function () {
                populateSubcategories(this.value);
            });

            subcategorySelect.addEventListener('change', function () {
                populateChildcategories(this.value);
            });

            // Se quiser valores setados inicialmente, use atributos data-* para eles também
            if (categorySelect.value) {
                populateSubcategories(categorySelect.value);
                if (subcategorySelect.value) {
                    populateChildcategories(subcategorySelect.value);
                }
            }
        });
    </script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
