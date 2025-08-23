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
    const commonBtns = [
        ['viewHTML'],
        ['undo', 'redo'],
        ['formatting'],
        ['strong', 'em', 'del'],
        ['superscript', 'subscript'],
        ['link'],
        ['insertImage'], // Apenas inserir imagem via URL
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
        ['unorderedList', 'orderedList'],
        ['horizontalRule'],
        ['removeformat'],
        ['fullscreen']
    ];

    function getPlugins(serverPath = null) {
        const plugins = {
            resizimg: {
                minSize: 64,
                step: 16
            },
            autogrow: {},
        };

        // Plugin de upload apenas se serverPath for definido
        if (serverPath) {
            plugins.upload = {
                serverPath: serverPath,
                fileFieldName: 'image',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            };
        }

        return plugins;
    }

    // Editor do blog (com upload)
    if ($('#editor-blog').length) {
        $('#editor-blog').trumbowyg({
            btns: commonBtns.concat([['upload']]),
            plugins: getPlugins('/admin/blogs/upload-image'),
            autogrow: true,
            removeformatPasted: true,
            allowTagsFromPaste: true,
        });
    }

    // Editor de produto (sem upload)
    if ($('#editor').length) {
        $('#editor').trumbowyg({
            // btns: commonBtns.concat([['upload']]),
            btns: commonBtns, // Sem botão de upload
            plugins: getPlugins(), // Sem upload
            autogrow: true,
            removeformatPasted: true,
            allowTagsFromPaste: true,
        });
    }

    // Sincronizar conteúdo do editor ao enviar o formulário
    $('#editForm').on('submit', function () {
        if ($('#editor').length) {
            $('textarea[name="description"]').val($('#editor').trumbowyg('html'));
        }

        if ($('#editor-blog').length) {
            $('textarea[name="content"]').val($('#editor-blog').trumbowyg('html'));
        }
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const backToTop = document.getElementById('backToTop');
    
        // Mostra o botão quando o scroll passa 100px
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
    
        // Scroll suave ao topo
        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    </script>

<script>
function setFormType(type) {
    document.getElementById('contact_type').value = type;

    document.querySelectorAll('.form-field').forEach(el => {
        const types = el.getAttribute('data-type').split(' ');
        const show = types.includes(String(type));
        el.style.display = show ? 'block' : 'none';

        // Toggle required attr nos campos dinâmicos
        el.querySelectorAll('input, textarea').forEach(input => {
            input.required = show;
        });
    });

    // Campo nome e email sempre required
    document.querySelector('input[name="name"]').required = true;
    document.querySelector('input[name="email"]').required = true;
}

// Inicializa no Fale Conosco
setFormType(1);
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