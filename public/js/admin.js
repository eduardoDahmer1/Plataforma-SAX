// ======== Feedback global: toast, loading y confirmación ========
// saxToast(type, message) — type: 'success' | 'error'
window.saxToast = function (type, message) {
    const toast = document.createElement('div');
    toast.className = `toast sax-toast align-items-center text-bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    new bootstrap.Toast(toast).show();
    setTimeout(() => toast.remove(), 5000);
};

// saxButtonLoading(btn, true, 'Salvando...') → spinner + disabled; (btn, false) → restaura
window.saxButtonLoading = function (btn, loading, label) {
    if (!btn) return;
    if (loading) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i> ${label || 'Aguarde...'}`;
        btn.disabled = true;
    } else {
        if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
        delete btn.dataset.originalHtml;
        btn.disabled = false;
    }
};

// Confirmación vía modal para <form data-confirm="mensaje">.
// Reemplaza los onsubmit="return confirm(...)" — migración opt-in por vista.
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;

    // Segundo submit (ya confirmado desde el modal): dejar pasar
    if (form.dataset.confirmed === '1') {
        delete form.dataset.confirmed;
        return;
    }

    const modalEl = document.getElementById('saxConfirmModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        // Fallback: sin modal en el layout, confirm nativo
        if (!confirm(form.dataset.confirm)) e.preventDefault();
        return;
    }

    e.preventDefault();
    document.getElementById('saxConfirmMessage').textContent = form.dataset.confirm;

    // onclick (no addEventListener) para no acumular handlers entre aperturas
    document.getElementById('saxConfirmAccept').onclick = function () {
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        form.dataset.confirmed = '1';
        form.requestSubmit();
    };
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});


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


// ── TinyMCE: editor de blog (com upload real de imagens) ──────
(function () {
    const editorEl = document.getElementById('editor-blog');
    if (!editorEl || typeof tinymce === 'undefined') return;

    const uploadUrl = editorEl.dataset.uploadUrl;

    tinymce.init({
        selector: '#editor-blog',
        height: 550,
        menubar: false,
        branding: false,
        statusbar: true,
        plugins: ['advlist autolink lists link image charmap print preview anchor',
                  'searchreplace visualblocks code fullscreen',
                  'insertdatetime media table paste code help wordcount', 'quickbars'],
        toolbar: 'formatselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | blockquote hr | table | link image media | removeformat | code fullscreen',
        quickbars_selection_toolbar: 'bold italic | quicklink blockquote',
        quickbars_insert_toolbar: false,
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; } img { max-width: 100%; height: auto; }',
        image_caption: true,
        image_title: true,
        automatic_uploads: true,
        paste_data_images: true,
        images_reuse_filename: true,
        convert_urls: true,
        relative_urls: false,
        remove_script_host: false,
        setup: function (editor) {
            editor.on('change keyup', function () { editor.save(); });
            editor.on('init', function () {
                const form = editorEl.closest('form');
                if (form) {
                    form.addEventListener('submit', function () { editor.save(); });
                }
            });
        },
        images_upload_handler: uploadUrl ? function (blobInfo, success, failure) {
            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            fetch(uploadUrl, { method: 'POST', headers: headers, body: formData })
                .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.location) {
                        success(data.location);
                    } else {
                        const message = data.message || 'Falha ao enviar imagem.';
                        failure(message);
                        if (window.saxToast) saxToast('error', message);
                    }
                })
                .catch(() => {
                    failure('Erro de rede ao enviar imagem.');
                    if (window.saxToast) saxToast('error', 'Erro ao enviar imagem.');
                });
        } : undefined,
    });
})();

// ── Blog: salvar formulário inteiro via AJAX (sem reload) ──────
(function () {
    const form = document.getElementById('blogForm');
    if (!form) return;

    function galleryItemHtml(path, url) {
        return '<div class="gallery-preview-item is-existing shadow-sm border">' +
            '<img src="' + url + '" class="w-100 h-100 object-fit-cover">' +
            '<input type="hidden" name="gallery_actual[]" value="' + path + '">' +
            '<button type="button" class="gallery-remove-btn"><i class="fas fa-times"></i></button>' +
            '</div>';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtns = form.querySelectorAll('button[type="submit"]');
        submitBtns.forEach(function (btn) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Salvando...';
        });

        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('[data-ajax-error]').forEach(function (el) { el.remove(); });

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: Object.assign({ 'Accept': 'application/json' }, headers),
            body: formData,
        })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data }; });
            })
            .then(function (result) {
                const status = result.status;
                const data = result.data;

                if (status === 200 && data.success) {
                    if (window.saxToast) saxToast('success', data.message || 'Salvo com sucesso!');

                    if (data.redirect) {
                        setTimeout(function () { window.location.href = data.redirect; }, 500);
                        return;
                    }

                    if (data.blog) {
                        const slugInput = document.getElementById('slug');
                        if (slugInput && data.blog.slug) slugInput.value = data.blog.slug;

                        const updatedLabel = document.querySelector('.sticky-header .x-small');
                        if (updatedLabel && data.blog.updated_at) {
                            updatedLabel.textContent = 'Última atualização: ' + data.blog.updated_at;
                        }

                        const imageInput = form.querySelector('input[name="image"]');
                        if (imageInput) imageInput.value = '';
                        if (data.blog.image_url) {
                            const coverPreview = document.getElementById('blogCoverPreview');
                            if (coverPreview) coverPreview.src = data.blog.image_url;
                        }

                        const galleryInput = form.querySelector('input[name="gallery[]"]');
                        if (galleryInput) galleryInput.value = '';

                        const galleryPreview = document.getElementById('blogGaleriaPreview');
                        if (galleryPreview && data.blog.gallery) {
                            galleryPreview.innerHTML = data.blog.gallery.map(function (item) {
                                return galleryItemHtml(item.path, item.url);
                            }).join('');
                            const counter = document.getElementById('blogGaleriaCount');
                            if (counter) counter.textContent = data.blog.gallery.length;
                        }

                        // Os arquivos pendentes já foram enviados e viraram imagens salvas acima —
                        // limpa o acumulador para não reenviá-los na próxima vez que o form for salvo.
                        if (window.resetBlogGalleryStore) window.resetBlogGalleryStore();
                    }
                } else if (status === 422 && data.errors) {
                    const unmatchedMessages = [];

                    Object.keys(data.errors).forEach(function (field) {
                        const input = form.querySelector('[name="' + field + '"]');
                        if (!input) {
                            unmatchedMessages.push(data.errors[field][0]);
                            return;
                        }
                        input.classList.add('is-invalid');
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback d-block';
                        feedback.setAttribute('data-ajax-error', '1');
                        feedback.textContent = data.errors[field][0];
                        input.insertAdjacentElement('afterend', feedback);
                    });

                    if (window.saxToast) {
                        // Erros de "gallery.N"/"image" não têm um input com esse name exato no DOM
                        // (são arquivos dentro de um input múltiplo) — mostra a mensagem real em vez
                        // do aviso genérico, senão o usuário nunca descobre que o motivo foi o tamanho do arquivo.
                        saxToast('error', unmatchedMessages[0] || 'Verifique os campos destacados.');
                    }
                } else {
                    if (window.saxToast) saxToast('error', data.message || 'Erro ao salvar o artigo.');
                }
            })
            .catch(function () {
                if (window.saxToast) saxToast('error', 'Erro de rede ao salvar.');
            })
            .finally(function () {
                submitBtns.forEach(function (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.originalHtml;
                });
            });
    });
})();

// ── Blog: Galeria — acumula arquivos entre seleções, gera preview e trava em 10 imagens ──
// Sem isto, cada nova seleção no <input multiple> substitui a anterior (perdendo fotos)
// e não havia nenhuma pré-visualização das imagens ainda não enviadas.
(function () {
    var input = document.getElementById('blogGalleryInput');
    var preview = document.getElementById('blogGaleriaPreview');
    var counter = document.getElementById('blogGaleriaCount');
    if (!input || !preview) return;

    var MAX_TOTAL = 10;
    var store = new DataTransfer();
    var urls = [];

    function existingCount() {
        return preview.querySelectorAll('.gallery-preview-item.is-existing').length;
    }

    function renderPending() {
        urls.forEach(function (u) { URL.revokeObjectURL(u); });
        urls = [];
        preview.querySelectorAll('.gallery-preview-item.is-pending').forEach(function (el) { el.remove(); });

        Array.from(store.files).forEach(function (file, index) {
            var url = URL.createObjectURL(file);
            urls.push(url);

            var item = document.createElement('div');
            item.className = 'gallery-preview-item is-pending shadow-sm border';
            item.dataset.fileIndex = index;
            item.innerHTML =
                '<img src="' + url + '" class="w-100 h-100 object-fit-cover">' +
                '<button type="button" class="gallery-remove-btn"><i class="fas fa-times"></i></button>';
            preview.appendChild(item);
        });

        if (counter) counter.textContent = existingCount() + store.files.length;
    }

    input.addEventListener('change', function () {
        var incoming = Array.from(input.files);
        var room = MAX_TOTAL - existingCount() - store.files.length;

        if (incoming.length > room) {
            if (window.saxToast) saxToast('error', 'Você pode ter no máximo ' + MAX_TOTAL + ' imagens na galeria.');
            incoming = incoming.slice(0, Math.max(room, 0));
        }

        incoming.forEach(function (file) {
            var dup = Array.from(store.files).some(function (f) {
                return f.name === file.name && f.size === file.size;
            });
            if (!dup) store.items.add(file);
        });

        input.files = store.files;
        renderPending();
    });

    preview.addEventListener('click', function (e) {
        var btn = e.target.closest('.gallery-remove-btn');
        if (!btn) return;
        var item = btn.closest('.gallery-preview-item.is-pending');
        if (!item) return;

        e.stopPropagation();
        store.items.remove(parseInt(item.dataset.fileIndex, 10));
        input.files = store.files;
        renderPending();
    });

    // Chamado após um save AJAX bem-sucedido: os pendentes já foram persistidos no servidor.
    window.resetBlogGalleryStore = function () {
        store = new DataTransfer();
        input.files = store.files;
        input.value = '';
        renderPending();
    };
})();

// 1. Inicialização segura do Objeto de Estados
var currentLangs = {
    title: 'pt', content: 'pt', desc: 'pt', pilar1: 'pt', 
    pilar2: 'pt', pilar3: 'pt', name: 'pt',heroT: 'pt',
    heroD: 'pt', barT: 'pt', barD: 'pt', gastT: 'pt', gastD: 'pt'
};

document.addEventListener("DOMContentLoaded", function() {
    
    // ==========================================
    // 2. INICIALIZAÇÃO DE CAMPOS (Títulos e Inputs)
    // ==========================================
    const inputsParaInicializar = {
        'visual-title-input': 'real-title-pt',
        'visual-name-input': 'real-name-pt', 
        'visual-pilar1t-input': 'real-pilar1t-pt', 'visual-pilar1b-input': 'real-pilar1b-pt',
        'visual-pilar2t-input': 'real-pilar2t-pt', 'visual-pilar2b-input': 'real-pilar2b-pt',
        'visual-pilar3t-input': 'real-pilar3t-pt', 'visual-pilar3b-input': 'real-pilar3b-pt'
    };

    Object.keys(inputsParaInicializar).forEach(visualId => {
        const vEl = document.getElementById(visualId);
        const rEl = document.getElementById(inputsParaInicializar[visualId]);
        if (vEl && rEl) vEl.value = rEl.value;
    });

// ==========================================
    // 3. INICIALIZAÇÃO DOS EDITORES (TinyMCE)
    // ==========================================
    if (typeof tinymce !== 'undefined') {
        const editorConfig = {
            height: 300, menubar: false, branding: false,
            plugins: ['advlist autolink lists link image charmap preview anchor', 'searchreplace visualblocks code fullscreen', 'table paste code help wordcount'],
            toolbar: 'formatselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | table | link | code fullscreen'
        };

        // Editor 1: Conteúdo Geral
        tinymce.init({
            ...editorConfig, 
            selector: '#editor-content',
            setup: (ed) => {
                ed.on('init', () => {
                    const real = document.getElementById('real-content-pt');
                    if (real) ed.setContent(real.value);
                });
                ed.on('change keyup', () => { 
                    ed.save(); 
                    const t = document.getElementById('real-content-' + currentLangs.content);
                    if (t) t.value = ed.getContent(); 
                });
            }
        });

        // Editor 2: Descrição do Produto
        tinymce.init({
            ...editorConfig, 
            selector: '#editor-product',
            setup: (ed) => {
                ed.on('init', () => {
                    const real = document.getElementById('real-desc-pt');
                    if (real) ed.setContent(real.value);
                });
                ed.on('change keyup', () => { 
                    ed.save(); 
                    const t = document.getElementById('real-desc-' + currentLangs.desc);
                    if (t) t.value = ed.getContent(); 
                });
            }
        });
    }

    // ==========================================
    // 4. OUVINTES DE INPUTS (Sincronização simples)
    // ==========================================
    const simpleInputs = ['title', 'name'];
    simpleInputs.forEach(type => {
        const vEl = document.getElementById(`visual-${type}-input`);
        if (vEl) vEl.addEventListener('input', function() {
            const target = document.getElementById(`real-${type}-${currentLangs[type]}`);
            if (target) target.value = this.value;
        });
    });
});

// ==========================================
// 5. MECANISMO SWITCHER UNIFICADO
// ==========================================
function switchLanguage(type, nextLang, element) {
    const prevLang = currentLangs[type];
    if (prevLang === nextLang) return;

    // A. Tratamento para Editores (content e desc)
    if (type === 'content' || type === 'desc') {
        const editorId = (type === 'content') ? 'editor-content' : 'editor-product';
        const realPrefix = (type === 'content') ? 'real-content-' : 'real-desc-';
        
        const editor = tinymce.get(editorId);
        if (editor) {
            const prevArea = document.getElementById(realPrefix + prevLang);
            const nextArea = document.getElementById(realPrefix + nextLang);
            if (prevArea) prevArea.value = editor.getContent();
            editor.setContent(nextArea ? nextArea.value : '');
        }
    } 
    // B. Tratamento para Inputs Visuais (title, name)
    else if (type === 'title' || type === 'name') {
        const prevInput = document.getElementById(`real-${type}-` + prevLang);
        const nextInput = document.getElementById(`real-${type}-` + nextLang);
        const vEl = document.getElementById(`visual-${type}-input`);
        
        if (vEl && prevInput) prevInput.value = vEl.value;
        if (vEl) vEl.value = nextInput ? nextInput.value : '';
    }
    // C. Tratamento para Pilares
    else if (type.startsWith('pilar')) {
        const vTitle = document.getElementById(`visual-${type}t-input`);
        const vBody = document.getElementById(`visual-${type}b-input`);
        
        const prevT = document.getElementById(`real-${type}t-${prevLang}`);
        const prevB = document.getElementById(`real-${type}b-${prevLang}`);
        if (vTitle && prevT) prevT.value = vTitle.value;
        if (vBody && prevB) prevB.value = vBody.value;
        
        const nextT = document.getElementById(`real-${type}t-${nextLang}`);
        const nextB = document.getElementById(`real-${type}b-${nextLang}`);
        if (vTitle) vTitle.value = nextT ? nextT.value : '';
        if (vBody) vBody.value = nextB ? nextB.value : '';
    }
    
    // D. Tratamento para Novos Campos (heroT, heroD, barT, barD, gastT, gastD)
    else {
        const vEl = document.getElementById(`visual-${type}-input`); // Ex: visual-gastD-input
        
        // O erro pode estar aqui: o ID no seu Blade é real-gastD-pt
        // Certifique-se que o 'type' que você passa no onclick seja EXATAMENTE 
        // a parte do meio do ID (ex: gastD)
        
        const prevInput = document.getElementById(`real-${type}-${prevLang}`); 
        const nextInput = document.getElementById(`real-${type}-${nextLang}`);

        if (vEl && prevInput) prevInput.value = vEl.value; // Salva o atual
        if (vEl) vEl.value = nextInput ? nextInput.value : ''; // Carrega o novo
    }

    currentLangs[type] = nextLang;

    // Atualiza classes dos botões
    const container = element.closest('.mb-3') || element.closest('.group-container');
    if (container) {
        container.querySelectorAll('.' + type + '-lang-btn').forEach(b => {
            b.classList.remove('bg-primary'); b.classList.add('bg-secondary');
        });
        element.classList.remove('bg-secondary'); element.classList.add('bg-primary');
    }
}

// ── Image preview genérico (.img-trigger + data-prev) ─────────
// Cubre: bridal, cafe_bistro, palace, institucional
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('img-trigger')) return;
    if (!e.target.files || !e.target.files[0]) return;
    document.getElementById(e.target.dataset.prev).src = URL.createObjectURL(e.target.files[0]);
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
                    <img id="prev-loc-${i}" class="w-100 h-100 object-fit-cover"
                         src="https://placehold.co/400x200/121212/D4AF37?text=Sucursal">
                </div>
                <div class="upload-zone py-2 mb-2">
                    <input type="file" name="locations_items[${i}][image]"
                           class="upload-input img-trigger" data-prev="prev-loc-${i}" accept="image/*">
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

    function populateSubcategories(categoryId, selectedSubcategoryId = '') {
        clearOptions(subcategorySelect);
        clearOptions(categoriasfilhasSelect);
        if (!categoryId) return;

        subcategories.filter(s => String(s.category_id) === String(categoryId)).forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.text = sub.name || sub.slug;

            if (String(sub.id) === String(selectedSubcategoryId)) {
                option.selected = true;
            }

            subcategorySelect.appendChild(option);
        });
    }

    function populateCategoriasFilhas(subcategoryId, selectedChildId = '') {
        clearOptions(categoriasfilhasSelect);
        if (!subcategoryId) return;

        categoriasfilhas.filter(c => String(c.subcategory_id) === String(subcategoryId)).forEach(child => {
            const option = document.createElement('option');
            option.value = child.id;
            option.text = child.name || child.slug;

            if (String(child.id) === String(selectedChildId)) {
                option.selected = true;
            }

            categoriasfilhasSelect.appendChild(option);
        });
    }

    categorySelect.addEventListener('change', () => populateSubcategories(categorySelect.value));
    subcategorySelect.addEventListener('change', () => populateCategoriasFilhas(subcategorySelect.value));

    const selectedSubcategoryId = subcategorySelect.dataset.selected || '';
    const selectedChildId = categoriasfilhasSelect.dataset.selected || '';

    if (categorySelect.value) {
        populateSubcategories(categorySelect.value, selectedSubcategoryId);
        if (selectedSubcategoryId) populateCategoriasFilhas(selectedSubcategoryId, selectedChildId);
    }
});


// ======== Galerías (cardápio, eventos, blog, ...): eliminar imagen ========
// Delegación en document — cubre items renderizados por Blade y nuevos.
// Convención: cada grid de preview con id "xGaleriaPreview" tem um contador
// opcional "xGaleriaCount" que é atualizado automaticamente ao remover.
(function () {
    function updateCounter(preview) {
        if (!preview || !preview.id) return;
        var counter = document.getElementById(preview.id.replace(/Preview$/, 'Count'));
        if (counter) counter.textContent = preview.querySelectorAll('.gallery-preview-item').length;
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.gallery-remove-btn');
        if (!btn) return;
        var item = btn.closest('.gallery-preview-item');
        if (!item) return;
        var preview = item.closest('[id$="GaleriaPreview"]');
        item.remove();
        updateCounter(preview);
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


// ======== Admin: AJAX image upload genérico ========
// Activa en cualquier input con data-upload-url (recursos con id: categories, brands, etc.)
// Sin data-upload-url → el handler ignora el input (compatibilidad con CREATE)
document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input.dataset.uploadUrl) return;
    if (!input.files || !input.files[0]) return;

    var previewImg = document.getElementById(input.dataset.previewId);
    var wrapper    = input.closest('.media-upload-preview');
    var emptyState = wrapper ? wrapper.querySelector('.empty-upload') : null;
    var deleteBtn  = wrapper ? wrapper.querySelector('.btn-delete-media') : null;

    // Preview instantáneo antes de confirmar la subida
    var objectUrl = URL.createObjectURL(input.files[0]);
    if (previewImg) {
        previewImg.src = objectUrl;
        previewImg.style.display = '';
    }
    if (emptyState) emptyState.style.display = 'none';

    // Nombre del campo: 'preview-photo' → 'photo', 'preview-banner' → 'banner'
    var fieldName = input.dataset.previewId.replace('preview-', '');
    var formData  = new FormData();
    formData.append(fieldName, input.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

    fetch(input.dataset.uploadUrl, {
        method:  'POST',
        body:    formData,
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            URL.revokeObjectURL(objectUrl);
            if (data.success) {
                if (previewImg) previewImg.src = data.url;
                if (deleteBtn)  deleteBtn.style.display = '';
                // Limpia el input para que el submit del form principal no reenvíe el archivo
                input.value = '';
            }
        })
        .catch(function (err) {
            URL.revokeObjectURL(objectUrl);
            console.error('Erro ao enviar imagem:', err);
        });
});

// ======== Categories Edit: confirmación de borrado ========
// Mantiene compatibilidad con los forms ocultos de delete (full reload)
function confirmDelete(type) {
    if (confirm('Deseja excluir esta imagem?')) {
        document.getElementById('delete-' + type + '-form').submit();
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


/* Switcher de idioma por-campo para forms traducibles. Usado por <x-admin.lang-field>. */
(function () {
    function visualToHidden(field, lang) {
        var visual = field.querySelector('[data-lang-visual]');
        var hidden = field.querySelector('[data-lang-real="' + lang + '"]');
        if (visual && hidden) hidden.value = visual.value;
    }

    function hiddenToVisual(field, lang) {
        var visual = field.querySelector('[data-lang-visual]');
        var hidden = field.querySelector('[data-lang-real="' + lang + '"]');
        if (visual && hidden) visual.value = hidden.value;
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-lang-field-btn]');
        if (!btn) return;

        var field = btn.closest('[data-lang-field]');
        if (!field) return;

        var next = btn.getAttribute('data-lang-field-btn');
        var current = field.getAttribute('data-current-lang') || 'pt-br';
        if (next === current) return;

        visualToHidden(field, current);
        hiddenToVisual(field, next);
        field.setAttribute('data-current-lang', next);

        field.querySelectorAll('[data-lang-field-btn]').forEach(function (b) {
            var isActive = b.getAttribute('data-lang-field-btn') === next;
            b.classList.toggle('active', isActive);
            b.classList.toggle('bg-primary', isActive);
            b.classList.toggle('bg-secondary', !isActive);
        });
    });

    document.addEventListener('submit', function (e) {
        if (!e.target.querySelectorAll) return;
        e.target.querySelectorAll('[data-lang-field]').forEach(function (field) {
            visualToHidden(field, field.getAttribute('data-current-lang') || 'pt-br');
        });
    });
})();


// ======== Blog: contador de caracteres da meta description ========
(function () {
    var field = document.getElementById('meta_description');
    var counter = document.getElementById('metaDescCount');
    if (!field || !counter) return;

    field.addEventListener('input', function () {
        counter.textContent = field.value.length;
    });
})();
