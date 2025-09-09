// ============================
// APP-CUSTOM.JS
// ============================

// ======== Back to Top ========
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.style.display = window.scrollY > 100 ? 'block' : 'none';
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});

// ======== setFormType ========
function setFormType(type) {
    const contactType = document.getElementById('contact_type');
    if (!contactType) return;

    contactType.value = type;

    document.querySelectorAll('.form-field').forEach(el => {
        const types = el.dataset.type.split(' ');
        const show = types.includes(String(type));
        el.style.display = show ? 'block' : 'none';
        el.querySelectorAll('input, textarea').forEach(input => input.required = show);
    });

    document.querySelector('input[name="name"]').required = true;
    document.querySelector('input[name="email"]').required = true;
}
setFormType(1);

// ======== Categoria / Subcategoria / Filha ========
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
        subcategories.filter(s => s.category_id == categoryId).forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.text = sub.name || sub.slug;
            subcategorySelect.appendChild(option);
        });
    }

    function populateChildcategories(subcategoryId) {
        clearOptions(childcategorySelect);
        if (!subcategoryId) return;
        childcategories.filter(c => c.subcategory_id == subcategoryId).forEach(child => {
            const option = document.createElement('option');
            option.value = child.id;
            option.text = child.name || child.slug;
            childcategorySelect.appendChild(option);
        });
    }

    categorySelect.addEventListener('change', () => populateSubcategories(categorySelect.value));
    subcategorySelect.addEventListener('change', () => populateChildcategories(subcategorySelect.value));

    if (categorySelect.value) {
        populateSubcategories(categorySelect.value);
        if (subcategorySelect.value) populateChildcategories(subcategorySelect.value);
    }
});

// ======== Clear Cache Button ========
const clearCacheBtn = document.getElementById('clearCacheBtn');
if (clearCacheBtn) {
    clearCacheBtn.addEventListener('click', function () {
        fetch(clearCacheBtn.dataset.url, { 
            headers: { 'X-CSRF-TOKEN': clearCacheBtn.dataset.csrf } 
        })
        .then(res => res.json())
        .then(data => alert(data.message))
        .catch(() => alert('Erro ao limpar o cache'));
    });
}

// ======== Trumbowyg Editors (jQuery) ========
if (window.jQuery) {
    $(document).ready(function () {
        const commonBtns = [
            ['viewHTML'],
            ['undo', 'redo'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['link'],
            ['insertImage'], // apenas inserir imagem via URL
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen']
        ];

        function getPlugins(serverPath = null) {
            const plugins = {
                resizimg: { minSize: 64, step: 16 },
                autogrow: {},
            };
            if (serverPath) {
                plugins.upload = {
                    serverPath: serverPath,
                    fileFieldName: 'image',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
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
                btns: commonBtns,
                plugins: getPlugins(),
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
}
