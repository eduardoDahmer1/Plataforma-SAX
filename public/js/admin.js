// ============================
// ADMIN.JS
// Scripts exclusivos del panel administrativo.
// Se carga DESPUÉS de app-custom.js, solo en rutas admin.*
// ============================


// ======== Back to Top + Drawer Mobile (Layout Admin) ========
document.addEventListener('DOMContentLoaded', function () {
    const backToTop = document.getElementById("backToTop");
    if (backToTop) {
        window.addEventListener("scroll", () => {
            backToTop.style.display = window.scrollY > 200 ? "block" : "none";
        });
        backToTop.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    }

    const openDrawer = document.getElementById("openDrawer");
    const closeDrawer = document.getElementById("closeDrawer");
    const drawer = document.getElementById("drawerMobile");
    const overlay = document.getElementById("drawerOverlay");

    if (openDrawer && closeDrawer && drawer && overlay) {
        openDrawer.addEventListener("click", () => {
            drawer.classList.add("open");
            overlay.classList.add("show");
        });
        closeDrawer.addEventListener("click", () => {
            drawer.classList.remove("open");
            overlay.classList.remove("show");
        });
        overlay.addEventListener("click", () => {
            drawer.classList.remove("open");
            overlay.classList.remove("show");
        });
    }
});


// ======== Auto-Slug ========
// Usado en brands/create, brands/edit, categories/create
// Convierte el campo #name en slug automáticamente
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.sax-form');
    if (!form) return;

    const nameEl = form.querySelector('#name');
    const slugEl = form.querySelector('#slug');
    if (!nameEl || !slugEl) return;

    nameEl.addEventListener('input', function () {
        slugEl.value = this.value.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });
});


// ======== Clear Cache ========
// Usado en menu-lateral.blade.php — botón #clearCacheBtn
document.addEventListener('DOMContentLoaded', function () {
    const clearCacheBtn = document.getElementById('clearCacheBtn');

    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function (e) {
            e.preventDefault();

            let url = this.dataset.url.replace('http://', 'https://');
            console.log("Executando limpeza em:", url);

            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Limpando...';
            this.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.dataset.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Erro no servidor');
                return data;
            })
            .then(data => {
                alert(data.message);
                window.location.reload();
            })
            .catch(err => {
                console.error('Erro detalhado:', err);
                alert('Erro ao limpar o cache: ' + err.message);
            })
            .finally(() => {
                this.innerHTML = originalContent;
                this.disabled = false;
            });
        });
    }
});


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
            ['insertImage'],
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

        // Editor del blog (con upload)
        if ($('#editor-blog').length) {
            $('#editor-blog').trumbowyg({
                btns: commonBtns.concat([['upload']]),
                plugins: getPlugins('/admin/blogs/upload-image'),
                autogrow: true,
                removeformatPasted: true,
                allowTagsFromPaste: true,
            });
        }

        // Editor de producto (sin upload)
        if ($('#editor').length) {
            $('#editor').trumbowyg({
                btns: commonBtns,
                plugins: getPlugins(),
                autogrow: true,
                removeformatPasted: true,
                allowTagsFromPaste: true,
            });
        }

        // Sincronizar contenido al enviar el formulario
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


// ======== Categoría / Subcategoría / Hija (Cascada) ========
// Pendiente de activar: requiere data-categories, data-subcategories,
// data-categoriasfilhas en los <select> correspondientes de las vistas.
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const categoriasfilhasSelect = document.getElementById('categoriasfilhas_id');

    if (!categorySelect || !subcategorySelect || !categoriasfilhasSelect) return;

    const categories = JSON.parse(categorySelect.dataset.categories || '[]');
    const subcategories = JSON.parse(subcategorySelect.dataset.subcategories || '[]');
    const categoriasfilhas = JSON.parse(categoriasfilhasSelect.dataset.categoriasfilhas || '[]');

    function clearOptions(select) {
        select.innerHTML = '<option value="">Selecione uma opção</option>';
    }

    function populateSubcategories(categoryId) {
        clearOptions(subcategorySelect);
        clearOptions(categoriasfilhasSelect);
        if (!categoryId) return;
        subcategories.filter(s => s.category_id == categoryId).forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.text = sub.name || sub.slug;
            subcategorySelect.appendChild(option);
        });
    }

    function populateCategoriasFilhas(subcategoryId) {
        clearOptions(categoriasfilhasSelect);
        if (!subcategoryId) return;
        categoriasfilhas.filter(c => c.subcategory_id == subcategoryId).forEach(child => {
            const option = document.createElement('option');
            option.value = child.id;
            option.text = child.name || child.slug;
            categoriasfilhasSelect.appendChild(option);
        });
    }

    categorySelect.addEventListener('change', () => populateSubcategories(categorySelect.value));
    subcategorySelect.addEventListener('change', () => populateCategoriasFilhas(subcategorySelect.value));

    if (categorySelect.value) {
        populateSubcategories(categorySelect.value);
        if (subcategorySelect.value) populateCategoriasFilhas(subcategorySelect.value);
    }
});
