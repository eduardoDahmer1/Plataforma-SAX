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
<script>
$(document).ready(function () {
    // Botões comuns para os editores
    var commonBtns = [
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
    ];

    // Editor simples (#editor)
    $('#editor').trumbowyg({
        btns: commonBtns,
        plugins: {
            resizimg: {
                minSize: 64,
                step: 16
            },
            autogrow: {}
        },
        autogrow: true,
        removeformatPasted: true,
        allowTagsFromPaste: true,
    });

    // Editor com upload (#editor-blog)
    $('#editor-blog').trumbowyg({
        btns: [
            ['viewHTML'],
            ['undo', 'redo'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['link'],
            ['upload'], // botão upload
            ['insertImage'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen']
        ],
        plugins: {
            resizimg: {
                minSize: 64,
                step: 16
            },
            autogrow: {},
            upload: {
                serverPath: '/admin/blogs/upload-image',
                fileFieldName: 'image',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
        },
        autogrow: true,
        removeformatPasted: true,
        allowTagsFromPaste: true,
    });

    // Salvar conteúdo no form
    $('#editForm').on('submit', function () {
        // Se existir textarea description, atualiza com editor #editor
        if ($('textarea[name="description"]').length) {
            $('textarea[name="description"]').val($('#editor').trumbowyg('html'));
        }
        // Atualiza o textarea content com o conteúdo do #editor-blog
        if ($('textarea[name="content"]').length) {
            $('textarea[name="content"]').val($('#editor-blog').trumbowyg('html'));
        }
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