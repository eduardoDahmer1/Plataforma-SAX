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

    const openDrawer = document.getElementById("openAdminDrawer");
    const closeDrawer = document.getElementById("closeAdminDrawer");
    const drawer = document.getElementById("adminDrawerMobile");
    const overlay = document.getElementById("adminDrawerOverlay");

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
// Usado en brands/create, brands/edit, categories/create (#name → #slug)
// y en blogs/create, blogs/edit (#title → #slug)
document.addEventListener('DOMContentLoaded', function () {
    const slugEl = document.getElementById('slug');
    if (!slugEl) return;

    const form = slugEl.closest('form');
    if (!form) return;

    const sourceEl = form.querySelector('#name') || form.querySelector('#title');
    if(!sourceEl) return;

    sourceEl.addEventListener('input', function () {
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


// ── TinyMCE: editor de blog ───────────────────────────────────
if (document.getElementById('editor-blog') && typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#editor-blog',
        height: 450,
        menubar: false,
        branding: false,
        statusbar: true,
        plugins: ['advlist autolink lists link image charmap print preview anchor',
                  'searchreplace visualblocks code fullscreen',
                  'insertdatetime media table paste code help wordcount'],
        toolbar: 'formatselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | table | link image | code fullscreen',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; }',
        setup: function(editor) { editor.on('change', function() { editor.save(); }); }
    });
}

// ── TinyMCE: editor de producto ───────────────────────────────
if (document.getElementById('editor-product') && typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#editor-product',
        height: 400,
        menubar: false,
        branding: false,
        statusbar: true,
        plugins: ['advlist autolink lists link image charmap print preview anchor',
                  'searchreplace visualblocks code fullscreen',
                  'insertdatetime media table paste code help wordcount'],
        toolbar: 'formatselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | table | link image | code fullscreen',
        content_style: 'body { font-family: -apple-system, sans-serif; font-size: 14px; }',
        setup: function(editor) { editor.on('change', function() { editor.save(); }); }
    });
}

// ── Image preview genérico (.img-trigger + data-prev) ─────────
// Cubre: bridal, cafe_bistro, palace, institucional
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('img-trigger')) return;
    if (!e.target.files || !e.target.files[0]) return;
    document.getElementById(e.target.dataset.prev).src = URL.createObjectURL(e.target.files[0]);
});

// ── Location image preview (.loc-img-trigger) ─────────────────
// Cubre: bridal — images dentro de location-items dinámicos
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('loc-img-trigger')) return;
    if (!e.target.files || !e.target.files[0]) return;
    e.target.closest('.location-item').querySelector('.loc-prev').src = URL.createObjectURL(e.target.files[0]);
});

// ── Bridal: agregar/eliminar sucursales ───────────────────────
(function() {
    const container = document.getElementById('locations-container');
    const btnAdd    = document.getElementById('btn-add-location');
    if (!container || !btnAdd) return;

    let locIndex = parseInt(container.dataset.locCount, 10);

    btnAdd.addEventListener('click', function() {
        const empty = document.getElementById('locations-empty');
        if (empty) empty.remove();
        const i = locIndex++;
        const col = document.createElement('div');
        col.className = 'col-md-4 location-item';
        col.innerHTML = `
            <div class="border rounded-3 p-3 bg-light position-relative">
                <button type="button" class="btn-remove-location position-absolute top-0 end-0 m-2 btn btn-sm btn-light border rounded-circle">
                    <i class="fas fa-times x-small"></i>
                </button>
                <div class="img-preview-box mb-2 rounded-2 overflow-hidden border" style="height:120px;">
                    <img class="loc-prev w-100 h-100 object-fit-cover"
                         src="https://placehold.co/400x200/121212/D4AF37?text=Sucursal">
                </div>
                <div class="upload-zone py-2 mb-2">
                    <input type="file" name="locations_items[${i}][image]"
                           class="upload-input loc-img-trigger" accept="image/*">
                    <p class="x-small text-muted m-0">Subir imagen</p>
                </div>
                <input type="hidden" name="locations_items[${i}][image_path]" value="">
                <div class="mb-2">
                    <label class="sax-form-label">Nombre</label>
                    <input type="text" name="locations_items[${i}][name]"
                           class="form-control sax-input" placeholder="Nombre de la sucursal">
                </div>
                <div class="mb-2">
                    <label class="sax-form-label">Dirección</label>
                    <input type="text" name="locations_items[${i}][address]"
                           class="form-control sax-input" placeholder="Dirección completa">
                </div>
                <div class="mb-0">
                    <label class="sax-form-label">Teléfono (WhatsApp)</label>
                    <input type="text" name="locations_items[${i}][phone]"
                           class="form-control sax-input" placeholder="+595 XXX XXX XXX">
                </div>
            </div>`;
        container.appendChild(col);
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-location');
        if (btn) btn.closest('.location-item').remove();
    });
})();


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


// ======== Cafe Bistro: Eliminar imagen de galería (cardápio y eventos) ========
// Delegación en document — cubre items renderizados por Blade y nuevos
(function () {
    function updateCardapioCount() {
        var preview = document.getElementById('cardapioGaleriaPreview');
        var counter = document.getElementById('cardapioGaleriaCount');
        if (!preview || !counter) return;
        counter.textContent = preview.querySelectorAll('.gallery-preview-item').length;
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.gallery-remove-btn');
        if (!btn) return;
        var item = btn.closest('.gallery-preview-item');
        if (!item) return;
        var isCardapio = !!item.closest('#cardapioGaleriaPreview');
        item.remove();
        if (isCardapio) updateCardapioCount();
    });
})();


// ======== Cafe Bistro: Tags dinámicos de tipos de eventos ========
(function () {
    var input = document.getElementById('eventosTipoInput');
    if (!input) return;

    input.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        var val = this.value.trim();
        if (!val) return;
        var container = document.getElementById('eventosTiposContainer');
        var tag = document.createElement('span');
        tag.className = 'eventos-tag';
        tag.innerHTML = val +
            '<input type="hidden" name="eventos_tipos[]" value="' + val.replace(/"/g, '&quot;') + '">' +
            '<button type="button" class="eventos-tag-remove" onclick="this.parentElement.remove()">&times;</button>';
        container.appendChild(tag);
        this.value = '';
    });
})();


// ======== Cafe Bistro: Checkbox "Fechado" deshabilita inputs de horario ========
(function () {
    var checks = document.querySelectorAll('.horario-fechado-check');
    if (!checks.length) return;

    checks.forEach(function (check) {
        var row = check.closest('.horario-row');
        var timeInputs = row.querySelectorAll('input[type="time"]');

        function toggle() {
            timeInputs.forEach(function (input) {
                input.disabled = check.checked;
                if (check.checked) input.value = '';
                input.style.opacity = check.checked ? '0.4' : '1';
            });
        }

        toggle();
        check.addEventListener('change', toggle);
    });
})();


// ======== Activate: Toggle Status ========
// Usado en activate/index.blade.php — botones de status de categorías y marcas
function toggleUI(btn) {
    const input = btn.previousElementSibling;
    const isCurrentlyActive = input.value == "1";
    if (isCurrentlyActive) {
        input.value = "2";
        btn.className = "status-badge status-inactive";
        btn.innerHTML = 'Inativo <span>▾</span>';
    } else {
        input.value = "1";
        btn.className = "status-badge status-active";
        btn.innerHTML = 'Ativo <span>▴</span>';
    }
}


// ======== Categories Edit: Media ========
// Usado en categories/edit.blade.php — confirmación de borrado y preview de imagen
function confirmDelete(type) {
    if (confirm('Deseja excluir esta imagem?')) {
        document.getElementById('delete-' + type + '-form').submit();
    }
}

function previewImg(input, targetId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById(targetId);
            if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}


// ======== Clients: Filtro por tipo de usuario ========
(function () {
    const filterSelect = document.getElementById('filterUserType');
    if (!filterSelect) return;

    filterSelect.addEventListener('change', function () {
        const filter = this.value;
        const rows = document.querySelectorAll('#usersTable tbody tr[data-usertype]');
        rows.forEach(function (row) {
            row.style.display = (filter === 'all' || row.getAttribute('data-usertype') === filter) ? '' : 'none';
        });
    });
})();

// ======== File Input: mostrar nombre del archivo seleccionado ========
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('custom-file-input')) {
        let fileName = e.target.files[0].name;
        let label = e.target.closest('.upload-wrapper').querySelector('.btn-upload-label');
        if (label) label.innerHTML = `<i class="fas fa-file-image me-1"></i> ${fileName}`;
    }
});
