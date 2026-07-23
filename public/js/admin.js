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


// ======== Métodos de pagamento: confirmação conforme o ambiente ========
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('paymentMethodForm');
    const modalEl = document.getElementById('confirmPaymentChangesModal');
    const confirmButton = document.getElementById('confirmPaymentChangesButton');
    const environmentAlert = document.getElementById('paymentEnvironmentAlert');
    const environmentTitle = document.getElementById('paymentEnvironmentTitle');
    const environmentMessage = document.getElementById('paymentEnvironmentMessage');
    const typeField = document.getElementById('type');
    const nameField = document.getElementById('name');

    if (!form || !modalEl || !confirmButton || !environmentAlert || !environmentTitle || !environmentMessage) {
        return;
    }

    const togglePaymentFields = function () {
        const isGateway = typeField && typeField.value === 'gateway';
        document.querySelectorAll('.gateway-only').forEach(el => el.style.display = isGateway ? 'block' : 'none');
        document.querySelectorAll('.bank-only').forEach(el => el.style.display = isGateway ? 'none' : 'block');
    };

    if (typeField) typeField.addEventListener('change', togglePaymentFields);
    if (nameField) nameField.addEventListener('input', togglePaymentFields);
    togglePaymentFields();

    const currentHost = window.location.hostname.toLowerCase().replace(/^www\./, '');
    const isProduction = currentHost === 'saxdepartment.com';
    const isStage = currentHost === 'stage.saxdepartment.com';
    let changesConfirmed = false;

    if (isProduction) {
        environmentAlert.classList.add('alert-danger');
        environmentTitle.textContent = 'ATENÇÃO: VOCÊ ESTÁ EM PRODUÇÃO';
        environmentMessage.textContent = 'Esta alteração afetará os pagamentos reais dos clientes em saxdepartment.com. Revise cuidadosamente as credenciais, o sandbox e a ativação do método antes de continuar.';
        confirmButton.classList.add('btn-danger');
        confirmButton.textContent = 'Sim, alterar produção';
    } else if (isStage) {
        environmentAlert.classList.add('alert-warning');
        environmentTitle.textContent = 'Você está no ambiente de STAGE';
        environmentMessage.textContent = 'As alterações serão aplicadas somente em stage.saxdepartment.com. Use este ambiente para validar a configuração antes de levá-la para produção.';
        confirmButton.classList.add('btn-primary');
    } else {
        environmentAlert.classList.add('alert-secondary');
        environmentTitle.textContent = 'Ambiente não identificado';
        environmentMessage.textContent = `O endereço atual é ${currentHost}. Confirme que este é o ambiente correto antes de continuar.`;
        confirmButton.classList.add('btn-dark');
    }

    form.addEventListener('submit', function (event) {
        if (changesConfirmed) {
            return;
        }

        event.preventDefault();

        if (typeof bootstrap === 'undefined') {
            changesConfirmed = window.confirm(`${environmentTitle.textContent}\n\n${environmentMessage.textContent}`);
            if (changesConfirmed) form.requestSubmit();
            return;
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    confirmButton.addEventListener('click', function () {
        changesConfirmed = true;
        window.saxButtonLoading(confirmButton, true, 'Guardando...');
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        form.requestSubmit();
    });
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
        const setDrawer = (isOpen) => {
            drawer.classList.toggle("open", isOpen);
            overlay.classList.toggle("show", isOpen);
            document.body.classList.toggle("admin-drawer-open", isOpen);
            drawer.setAttribute("aria-hidden", String(!isOpen));
            openDrawer.setAttribute("aria-expanded", String(isOpen));

            if (isOpen) {
                closeDrawer.focus();
            } else {
                openDrawer.focus();
            }
        };

        openDrawer.addEventListener("click", () => {
            setDrawer(true);
        });
        closeDrawer.addEventListener("click", () => {
            setDrawer(false);
        });
        overlay.addEventListener("click", () => {
            setDrawer(false);
        });
        drawer.querySelectorAll("a.submenu-link").forEach(link => {
            link.addEventListener("click", () => setDrawer(false));
        });
        document.addEventListener("keydown", event => {
            if (event.key === "Escape" && drawer.classList.contains("open")) {
                setDrawer(false);
            }
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
                const syncDescription = () => {
                    const target = document.getElementById('real-desc-' + currentLangs.desc);
                    if (target) target.value = ed.getContent();
                };
                ed.on('init', () => {
                    const real = document.getElementById('real-desc-pt');
                    if (real) ed.setContent(real.value);

                    const form = document.getElementById('productEditForm');
                    if (form) form.addEventListener('submit', syncDescription);
                });
                ed.on('change keyup input undo redo', syncDescription);
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

    if (type === 'desc') {
        const languageLabel = document.getElementById('desc-current-language');
        const labels = { pt: 'Conteúdo em Português', es: 'Contenido en Español', en: 'Content in English' };
        if (languageLabel) languageLabel.textContent = labels[nextLang] || '';
    }

    // Atualiza classes dos botões
    const container = element.closest('.mb-3') || element.closest('.group-container');
    if (container) {
        container.querySelectorAll('.' + type + '-lang-btn').forEach(b => {
            b.classList.remove('bg-primary'); b.classList.add('bg-secondary');
        });
        element.classList.remove('bg-secondary'); element.classList.add('bg-primary');
    }
}

// Tradução auxiliar gratuita para o cadastro de produtos. O endpoint público
// do Google não exige chave, portanto pode sofrer indisponibilidade ou limites.
async function googleTranslateText(text, targetLanguage, sourceLanguage = 'auto') {
    const value = String(text || '').trim();
    if (!value) return '';

    const params = new URLSearchParams({
        client: 'gtx',
        sl: sourceLanguage,
        tl: targetLanguage,
        dt: 't',
        q: value
    });
    const response = await fetch(`https://translate.googleapis.com/translate_a/single?${params.toString()}`);
    if (!response.ok) throw new Error('O tradutor não respondeu.');

    const payload = await response.json();
    if (!Array.isArray(payload?.[0])) throw new Error('Resposta inválida do tradutor.');
    return payload[0].map(part => part?.[0] || '').join('');
}

async function googleTranslateHtml(html, targetLanguage, sourceLanguage = 'auto') {
    const container = document.createElement('div');
    container.innerHTML = html || '';

    const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT);
    const nodes = [];
    let currentNode;
    while ((currentNode = walker.nextNode())) {
        if (currentNode.nodeValue.trim()) nodes.push(currentNode);
    }

    const translations = await Promise.all(nodes.map(node => googleTranslateText(node.nodeValue, targetLanguage, sourceLanguage)));
    nodes.forEach((node, index) => {
        const leading = node.nodeValue.match(/^\s*/)?.[0] || '';
        const trailing = node.nodeValue.match(/\s*$/)?.[0] || '';
        node.nodeValue = leading + translations[index] + trailing;
    });

    return container.innerHTML;
}

async function translateProductField(type, options = {}) {
    const button = document.getElementById(`translate-${type}-btn`);
    if (!button) return;

    const languages = ['pt', 'es', 'en'];
    const fields = Object.fromEntries(languages.map(language => [language, document.getElementById(`real-${type}-${language}`)]));
    const currentField = fields[currentLangs[type]];
    const externalName = type === 'name' ? document.getElementById('external_name') : null;
    const sourceValue = options.sourceValue || [currentField?.value, fields.pt?.value, fields.es?.value, fields.en?.value, externalName?.value]
        .find(value => String(value || '').trim());

    if (!String(sourceValue || '').trim()) {
        window.saxToast ? saxToast('warning', 'Preencha um texto para que o idioma possa ser identificado.') : alert('Preencha um texto para que o idioma possa ser identificado.');
        return;
    }

    const translationsComplete = languages.every(language => String(fields[language]?.value || '').trim());
    if (options.onlyWhenIncomplete && translationsComplete) return false;

    const hasExistingTranslations = languages.some(language => String(fields[language]?.value || '').trim());
    if (!options.automatic && hasExistingTranslations && !confirm('O idioma será identificado automaticamente e os campos PT, ES e EN serão preenchidos novamente. Deseja continuar?')) return false;

    const originalButton = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Traduzindo...';

    try {
        const translatedValues = {};
        for (const language of languages) {
            translatedValues[language] = type === 'name'
                ? await googleTranslateText(sourceValue, language, 'auto')
                : await googleTranslateHtml(sourceValue, language, 'auto');
        }

        languages.forEach(language => {
            if (fields[language]) fields[language].value = translatedValues[language];
        });

        if (type === 'name') {
            const visibleName = document.getElementById('visual-name-input');
            if (visibleName) visibleName.value = translatedValues[currentLangs.name] || '';
        } else {
            const editor = typeof tinymce !== 'undefined' ? tinymce.get('editor-product') : null;
            if (editor) editor.setContent(translatedValues[currentLangs.desc] || '');
        }

        if (!options.silent) {
            window.saxToast ? saxToast('success', 'Idioma identificado e traduções PT, ES e EN preenchidas. Revise antes de salvar.') : alert('Idioma identificado e traduções PT, ES e EN preenchidas. Revise antes de salvar.');
        }
        return true;
    } catch (error) {
        console.error('Falha na tradução automática:', error);
        window.saxToast ? saxToast('error', 'Não foi possível traduzir agora. Tente novamente em alguns instantes.') : alert('Não foi possível traduzir agora. Tente novamente em alguns instantes.');
        return false;
    } finally {
        button.disabled = false;
        button.innerHTML = originalButton;
    }
}

let saxDescriptionProfilesPromise;

async function loadSaxDescriptionProfiles() {
    if (!saxDescriptionProfilesPromise) {
        saxDescriptionProfilesPromise = fetch('/data/product_description_profiles.json', {
            headers: { Accept: 'application/json' },
            cache: 'no-cache'
        }).then(response => {
            if (!response.ok) throw new Error(`Não foi possível carregar os perfis editoriais (${response.status}).`);
            return response.json();
        }).catch(error => {
            saxDescriptionProfilesPromise = null;
            throw error;
        });
    }

    return saxDescriptionProfilesPromise;
}

async function buildSaxProductDescription() {
    const productName = document.getElementById('real-name-pt')?.value?.trim()
        || document.getElementById('external_name')?.value?.trim()
        || 'este produto';
    const brandSelect = document.getElementById('brand_id');
    const categorySelect = document.getElementById('category_id');
    const sizeField = document.querySelector('[name="size"]');
    const brand = brandSelect?.selectedOptions?.[0]?.textContent?.trim();
    const category = categorySelect?.selectedOptions?.[0]?.textContent?.trim();
    const size = sizeField?.value?.trim();

    const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const safe = value => String(value || '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char]);
    const pick = values => values[Math.floor(Math.random() * values.length)];
    const categoryKey = normalize(category);
    const name = safe(productName);
    const brandName = brand && !/selecione/i.test(brand) ? safe(brand) : '';
    const sizeText = size ? ` Consulte a disponibilidade no tamanho ${safe(size)}.` : '';

    const content = await loadSaxDescriptionProfiles();
    const matchingAlias = Object.keys(content.aliases)
        .sort((a, b) => b.length - a.length)
        .find(alias => categoryKey === alias || categoryKey.includes(alias));
    const profileKey = content.aliases[matchingAlias] || 'default';
    const profile = content.profiles[profileKey] || content.profiles.default;
    const interpolate = template => template
        .replaceAll('{{name}}', name)
        .replaceAll('{{brand}}', brandName)
        .replaceAll('{{size}}', sizeText);
    const brandTemplates = brandName ? content.brand.with_brand : content.brand.without_brand;

    return [pick(profile.opening), pick(profile.body), pick(brandTemplates), pick(profile.closing)]
        .map(template => `<p>${interpolate(template)}</p>`)
        .join('');
}

async function generateSaxDescriptionTranslations() {
    const fields = ['pt', 'es', 'en'].map(language => document.getElementById(`real-desc-${language}`));
    if (fields.some(field => !field)) return;
    if (fields.some(field => field.value.trim()) && !confirm('As descrições atuais em PT, ES e EN serão substituídas por uma nova descrição SAX. Deseja continuar?')) return;

    try {
        await translateProductField('desc', {
            automatic: true,
            sourceValue: await buildSaxProductDescription()
        });
    } catch (error) {
        console.error('Falha ao gerar descrição SAX:', error);
        window.saxToast ? saxToast('error', 'Não foi possível carregar a curadoria editorial. Tente novamente.') : alert('Não foi possível carregar a curadoria editorial.');
    }
}

async function autoPopulateProductTranslations() {
    if (!document.getElementById('productEditForm')) return;

    const nameFields = ['pt', 'es', 'en'].map(language => document.getElementById(`real-name-${language}`));
    const descriptionFields = ['pt', 'es', 'en'].map(language => document.getElementById(`real-desc-${language}`));
    if (nameFields.some(field => !field) || descriptionFields.some(field => !field)) return;

    const namesIncomplete = nameFields.some(field => !field.value.trim());
    let nameFilled = false;
    if (namesIncomplete) {
        const originalName = document.getElementById('external_name')?.value?.trim()
            || nameFields.find(field => field.value.trim())?.value;
        nameFilled = await translateProductField('name', {
            automatic: true,
            silent: true,
            onlyWhenIncomplete: true,
            sourceValue: originalName
        });
    }

    const descriptionsIncomplete = descriptionFields.some(field => !field.value.trim());
    let descriptionFilled = false;
    if (descriptionsIncomplete) {
        const existingDescription = descriptionFields.find(field => field.value.trim())?.value;
        descriptionFilled = await translateProductField('desc', {
            automatic: true,
            silent: true,
            onlyWhenIncomplete: true,
            sourceValue: existingDescription || await buildSaxProductDescription()
        });
    }

    if (nameFilled || descriptionFilled) {
        window.saxToast && saxToast('info', 'Traduções automáticas preenchidas. Revise os três idiomas antes de salvar.');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Aguarda o TinyMCE carregar o conteúdo inicial antes do preenchimento automático.
    setTimeout(() => autoPopulateProductTranslations().catch(error => {
        console.error('Falha no preenchimento editorial automático:', error);
    }), 700);
});

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


document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('activate-app');
    if (!app) return;

    const feedback = document.getElementById('activate-feedback');
    const busca = document.getElementById('activate-search');
    const chips = app.querySelectorAll('.sax-chip');
    let filtroStatus = 'all';
    let timerFeedback = null;

    function avisar(mensagem, erro) {
        feedback.textContent = mensagem;
        feedback.classList.toggle('is-error', Boolean(erro));
        clearTimeout(timerFeedback);
        timerFeedback = setTimeout(function () {
            feedback.textContent = '';
        }, 2500);
    }

    function nomeDe(item) {
        if (!item._nome) {
            item._nome = item.querySelector('.ai-n').textContent.trim().toLowerCase();
        }
        return item._nome;
    }

    function aplicarFiltros() {
        const termo = busca.value.trim().toLowerCase();

        app.querySelectorAll('[data-section]').forEach(function (secao) {
            let visiveis = 0;

            secao.querySelectorAll('.ai').forEach(function (item) {
                const casaTexto = !termo || nomeDe(item).includes(termo);
                const casaStatus = filtroStatus === 'all' || item.dataset.s === filtroStatus;
                const mostrar = casaTexto && casaStatus;

                item.hidden = !mostrar;
                if (mostrar) visiveis++;
            });

            secao.querySelector('[data-empty]').hidden = visiveis > 0;
        });
    }

    busca.addEventListener('input', aplicarFiltros);

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (item) {
                item.classList.remove('is-on');
            });
            chip.classList.add('is-on');
            filtroStatus = chip.dataset.filter;
            aplicarFiltros();
        });
    });

    app.addEventListener('click', function (event) {
        const botao = event.target.closest('.sax-toggle');
        if (!botao) return;

        const item = botao.closest('.ai');
        const secao = botao.closest('[data-section]');
        const ligadoAntes = botao.classList.contains('is-on');
        const url = app.dataset.toggleUrl
            .replace('__TYPE__', secao.dataset.section)
            .replace('__ID__', botao.dataset.id);

        botao.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(function (response) {
                return response.ok ? response.json() : Promise.reject();
            })
            .then(function (data) {
                botao.classList.toggle('is-on', data.ativo);
                item.dataset.s = data.status;

                const contador = secao.querySelector('[data-count-active]');
                contador.textContent = Number(contador.textContent) + (data.ativo ? 1 : -1);

                avisar(data.message, false);
                aplicarFiltros();
            })
            .catch(function () {
                botao.classList.toggle('is-on', ligadoAntes);
                avisar(app.dataset.errorMessage, true);
            })
            .finally(function () {
                botao.disabled = false;
            });
    });
});


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


// ======== Gestor genérico de galerias (mantém/remove/adiciona) ========
// Usado por <x-admin.gallery-field>. Acumula seleções entre múltiplos cliques
// (sem perder as anteriores), gera preview local e permite remover tanto
// imagens já salvas quanto pendentes, tudo sem reload da página.
window.saxGalleryManagers = {};

(function () {
    document.querySelectorAll('[data-gallery-field]').forEach(function (manager) {
        var field = manager.dataset.galleryField;
        var max = parseInt(manager.dataset.max, 10) || 20;
        var input = document.getElementById('galleryInput_' + field);
        var preview = document.getElementById('galleryPreview_' + field);
        var counter = document.getElementById('galleryCount_' + field);
        if (!input || !preview) return;

        var store = new DataTransfer();
        var urls = [];

        function existingCount() {
            return preview.querySelectorAll('.gallery-preview-item.is-existing').length;
        }

        function updateCounter() {
            if (counter) counter.textContent = existingCount() + store.files.length;
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

            updateCounter();
        }

        input.addEventListener('change', function () {
            var incoming = Array.from(input.files);
            var room = max - existingCount() - store.files.length;

            if (incoming.length > room) {
                if (window.saxToast) saxToast('error', 'Você pode ter no máximo ' + max + ' imagens.');
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
            e.stopPropagation();

            var pendingItem = btn.closest('.gallery-preview-item.is-pending');
            if (pendingItem) {
                store.items.remove(parseInt(pendingItem.dataset.fileIndex, 10));
                input.files = store.files;
                renderPending();
                return;
            }

            var existingItem = btn.closest('.gallery-preview-item.is-existing');
            if (existingItem) {
                existingItem.remove();
                updateCounter();
            }
        });

        // Chamado após um save AJAX bem-sucedido: reconstrói o preview a partir do
        // que o servidor confirmou ter persistido e limpa os pendentes já enviados.
        window.saxGalleryManagers[field] = {
            reset: function (items) {
                store = new DataTransfer();
                input.files = store.files;
                input.value = '';

                preview.innerHTML = '';
                (items || []).forEach(function (item) {
                    var el = document.createElement('div');
                    el.className = 'gallery-preview-item is-existing shadow-sm border';
                    el.innerHTML =
                        '<img src="' + item.url + '" class="w-100 h-100 object-fit-cover">' +
                        '<input type="hidden" name="' + field + '_actual[]" value="' + item.path + '">' +
                        '<button type="button" class="gallery-remove-btn"><i class="fas fa-times"></i></button>';
                    preview.appendChild(el);
                });

                renderPending();
            },
        };
    });
})();


// ── Institucional: salvar formulário inteiro via AJAX (sem reload) ──────
(function () {
    var form = document.getElementById('formInstitucional');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var submitBtns = form.querySelectorAll('button[type="submit"]');
        submitBtns.forEach(function (btn) { window.saxButtonLoading(btn, true, 'Salvando...'); });

        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('[data-ajax-error]').forEach(function (el) { el.remove(); });

        var formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: Object.assign({ 'Accept': 'application/json' }, headers),
            body: formData,
        })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data }; });
            })
            .then(function (result) {
                var status = result.status;
                var data = result.data;

                if (status === 200 && data.success) {
                    if (window.saxToast) saxToast('success', data.message || 'Salvo com sucesso!');

                    var inst = data.institucional || {};

                    var updatedLabel = document.querySelector('.sticky-header .x-small');
                    if (updatedLabel && inst.updated_at) {
                        updatedLabel.textContent = 'Última atualização: ' + inst.updated_at;
                    }

                    var coverInput = form.querySelector('input[name="section_one_image"]');
                    if (coverInput) coverInput.value = '';
                    if (inst.section_one_image) {
                        var coverPreview = document.getElementById('preview-section_one_image');
                        if (coverPreview) coverPreview.src = inst.section_one_image;
                    }

                    ['top_sliders', 'gallery_images'].forEach(function (field) {
                        if (window.saxGalleryManagers[field] && inst[field]) {
                            window.saxGalleryManagers[field].reset(inst[field]);
                        }
                    });
                } else if (status === 422 && data.errors) {
                    var unmatchedMessages = [];

                    Object.keys(data.errors).forEach(function (field) {
                        var input = form.querySelector('[name="' + field + '"]');
                        if (!input) {
                            unmatchedMessages.push(data.errors[field][0]);
                            return;
                        }
                        input.classList.add('is-invalid');
                        var feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback d-block';
                        feedback.setAttribute('data-ajax-error', '1');
                        feedback.textContent = data.errors[field][0];
                        input.insertAdjacentElement('afterend', feedback);
                    });

                    if (unmatchedMessages.length) {
                        if (window.saxToast) saxToast('error', unmatchedMessages[0]);
                    } else {
                        if (window.saxToast) saxToast('error', 'Verifique os campos destacados.');
                    }
                } else {
                    if (window.saxToast) saxToast('error', data.message || 'Erro ao salvar os dados.');
                }
            })
            .catch(function () {
                if (window.saxToast) saxToast('error', 'Erro de rede ao salvar.');
            })
            .finally(function () {
                submitBtns.forEach(function (btn) { window.saxButtonLoading(btn, false); });
            });
    });
})();


document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.upload-form').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const card = form.closest('.banner-admin-card');
                const btn = form.querySelector('.btn-submit');
                const img = card.querySelector('.banner-preview-img');
                const empty = card.querySelector('.empty-state');
                const delBtn = card.querySelector('.btn-delete');

                btn.disabled = true;

                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await res.json();

                if (data.success) {
                    img.src = data.url;
                    img.style.display = 'block';
                    empty.style.display = 'none';
                    delBtn.style.display = 'block';
                }

                btn.disabled = false;
            });
        });

        document.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (!confirm(document.getElementById('banner-admin-config').dataset.confirmDelete)) {
                    return;
                }

                const form = btn.closest('.delete-form');
                const card = form.closest('.banner-admin-card');
                const img = card.querySelector('.banner-preview-img');
                const empty = card.querySelector('.empty-state');

                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await res.json();

                if (data.success) {
                    img.style.display = 'none';
                    empty.style.display = 'block';
                    btn.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('.banner-link-form').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const card = form.closest('.banner-admin-card');
                const input = form.querySelector('.banner-link-input');
                const btn = form.querySelector('.btn-link-submit');
                const activeLink = card.querySelector('.banner-active-link');
                const originalButtonHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Salvando...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                        throw new Error(firstError || data?.message || 'Erro ao salvar o link.');
                    }

                    const currentValue = (input.value || '').trim();

                    if (activeLink) {
                        if (currentValue) {
                            activeLink.href = currentValue;
                            activeLink.classList.remove('d-none');
                        } else {
                            activeLink.href = '#';
                            activeLink.classList.add('d-none');
                        }
                    }

                    btn.innerHTML = '<i class="fas fa-check me-1"></i>Salvo';
                } catch (error) {
                    alert(error.message || 'Nao foi possivel salvar o link agora.');
                    btn.innerHTML = '<i class="fas fa-triangle-exclamation me-1"></i>Tentar novamente';
                } finally {
                    setTimeout(function () {
                        btn.disabled = false;
                        btn.innerHTML = originalButtonHtml;
                    }, 900);
                }
            });
        });
    });


window.applyFilters = function () {
    const form = document.getElementById('filterForm');
    if (!form) return;
    window.location.href = window.location.pathname + '?' + new URLSearchParams(new FormData(form)).toString();
};


document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('msg-app');
    if (!app) return;

    const feedback = document.getElementById('msg-feedback');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let timer = null;

    function avisar(msg, erro) {
        feedback.textContent = msg;
        feedback.classList.toggle('is-error', !!erro);
        clearTimeout(timer);
        timer = setTimeout(() => (feedback.textContent = ''), 2500);
    }

    // Abre/fecha o detalhe da mensagem
    function alternar(item) {
        const detalhe = item.querySelector('.sax-msg__detail');
        const aberto = !detalhe.hidden;

        detalhe.hidden = aberto;
        item.classList.toggle('is-open', !aberto);
        item.querySelector('[data-toggle]').setAttribute('aria-expanded', String(!aberto));
    }

    app.addEventListener('click', function (e) {
        const excluir = e.target.closest('[data-del]');
        const cabecalho = e.target.closest('[data-toggle]');

        if (excluir) {
            const item = excluir.closest('[data-row]');
            if (!confirm(app.dataset.confirmDelete)) return;

            excluir.disabled = true;

            fetch(app.dataset.deleteUrl + '/' + item.dataset.id, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
                item.remove();
                avisar(data.message, false);
            })
            .catch(() => {
                excluir.disabled = false;
                avisar(app.dataset.errorMessage, true);
            });

            return;
        }

        if (cabecalho) {
            alternar(cabecalho.closest('[data-row]'));
        }
    });

    // Teclado: Enter/Espaço abrem a mensagem
    app.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const cabecalho = e.target.closest('[data-toggle]');
        if (!cabecalho) return;
        e.preventDefault();
        alternar(cabecalho.closest('[data-row]'));
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('tr-app');
    if (!app) return;

    const feedback = document.getElementById('tr-feedback');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let timer = null;

    function avisar(msg, erro) {
        feedback.textContent = msg;
        feedback.classList.toggle('is-error', !!erro);
        clearTimeout(timer);
        timer = setTimeout(() => (feedback.textContent = ''), 2500);
    }

    function url(base, id) {
        return app.dataset[base].replace('__ID__', id);
    }

    // Marca a linha como alterada e revela o botão de salvar.
    app.addEventListener('input', function (e) {
        const input = e.target.closest('.sax-tr__table input');
        if (!input) return;

        const linha = input.closest('[data-row]');
        input.classList.toggle('is-dirty', input.value !== input.defaultValue);

        const alterada = [...linha.querySelectorAll('input')].some(i => i.value !== i.defaultValue);
        linha.querySelector('[data-save]').hidden = !alterada;
    });

    // Ctrl+Enter ou Enter salva a linha
    app.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const linha = e.target.closest('[data-row]');
        if (!linha) return;
        e.preventDefault();
        salvar(linha);
    });

    function salvar(linha) {
        const botao = linha.querySelector('[data-save]');
        const inputs = linha.querySelectorAll('input');
        const corpo = {};
        inputs.forEach(i => (corpo[i.name] = i.value));

        botao.disabled = true;

        fetch(url('updateUrl', linha.dataset.id), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-HTTP-Method-Override': 'PUT'
            },
            body: JSON.stringify({ ...corpo, _method: 'PUT' })
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                const erro = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Erro');
                avisar(erro, true);
                return;
            }

            // O valor salvo vira o novo "original": a linha deixa de estar alterada.
            inputs.forEach(i => {
                i.defaultValue = i.value;
                i.classList.remove('is-dirty');
            });

            botao.hidden = true;
            linha.classList.toggle('is-missing', data.falta);
            avisar(data.message, false);
        })
        .catch(() => avisar(app.dataset.errorMessage, true))
        .finally(() => (botao.disabled = false));
    }

    app.addEventListener('click', function (e) {
        const salvarBtn = e.target.closest('[data-save]');
        const delBtn = e.target.closest('[data-del]');

        if (salvarBtn) {
            salvar(salvarBtn.closest('[data-row]'));
            return;
        }

        if (delBtn) {
            const linha = delBtn.closest('[data-row]');
            if (!confirm(app.dataset.confirmDelete)) return;

            delBtn.disabled = true;

            fetch(url('deleteUrl', linha.dataset.id), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
                linha.remove();
                avisar(data.message, false);
            })
            .catch(() => {
                delBtn.disabled = false;
                avisar(app.dataset.errorMessage, true);
            });
        }
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const modelo = document.getElementById('modelo-cupon');
    const tipo = document.getElementById('tipo-cupon');
    if (!modelo || !tipo) return;
    const camposEscopo = document.querySelectorAll('.cupon-escopo-field');
    const descontoMaximo = document.getElementById('desconto-maximo-field');
    const prefixo = document.getElementById('montante-prefixo');
    const ajudaMontante = document.getElementById('montante-ajuda');

    const couponFields = document.getElementById('coupon-form-fields');
    const textos = { percentual: couponFields.dataset.percentHelp, valorFixo: couponFields.dataset.fixedHelp };

    // Mostra só o campo do escopo escolhido.
    function alternarEscopo() {
        camposEscopo.forEach(function (campo) {
            campo.style.display = campo.dataset.modelo === modelo.value ? 'block' : 'none';
        });
    }

    // Teto de desconto só faz sentido para percentual: um valor fixo já é o próprio teto.
    function alternarTipo() {
        const ehPercentual = tipo.value === 'percentual';
        prefixo.textContent = ehPercentual ? '%' : 'US$';
        ajudaMontante.textContent = ehPercentual ? textos.percentual : textos.valorFixo;
        descontoMaximo.style.display = ehPercentual ? 'block' : 'none';
    }

    modelo.addEventListener('change', alternarEscopo);
    tipo.addEventListener('change', alternarTipo);

    alternarEscopo();
    alternarTipo();

    // --- Autocomplete de produto ---
    const buscaWrap = document.getElementById('produto-busca-wrap');
    const buscaInput = document.getElementById('produto-busca');
    const buscaResultados = document.getElementById('produto-resultados');
    const produtoIdInput = document.getElementById('produto_id');
    let debounce = null;

    function fecharResultados() {
        buscaResultados.classList.add('d-none');
        buscaResultados.innerHTML = '';
    }

    buscaInput.addEventListener('input', function () {
        const termo = this.value.trim();

        // Digitar de novo invalida o produto escolhido antes.
        produtoIdInput.value = '';
        clearTimeout(debounce);

        if (termo.length < 2) {
            fecharResultados();
            return;
        }

        debounce = setTimeout(function () {
            fetch(buscaWrap.dataset.url + '?q=' + encodeURIComponent(termo), {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(function (produtos) {
                buscaResultados.innerHTML = '';

                if (!produtos.length) {
                    fecharResultados();
                    return;
                }

                produtos.forEach(function (produto) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action x-small text-start';
                    item.textContent = produto.texto + ' · ' + produto.preco;

                    item.addEventListener('click', function () {
                        produtoIdInput.value = produto.id;
                        buscaInput.value = produto.texto;
                        fecharResultados();
                    });

                    buscaResultados.appendChild(item);
                });

                buscaResultados.classList.remove('d-none');
            })
            .catch(fecharResultados);
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!buscaWrap.contains(e.target)) fecharResultados();
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('type');
    const name = document.getElementById('name');
    if (!type || !name || document.getElementById('paymentMethodForm')) return;

    function toggleFields() {
        const isGateway = type.value === 'gateway';
        const isBancardV2 = name.value.trim().toLowerCase() === 'bancard v2';
        document.querySelectorAll('.gateway-only').forEach(el => el.style.display = isGateway ? 'block' : 'none');
        document.querySelectorAll('.bank-only').forEach(el => el.style.display = isGateway ? 'none' : 'block');
        const sandboxControl = document.getElementById('sandboxControl');
        if (sandboxControl) sandboxControl.style.display = isGateway && isBancardV2 ? 'block' : 'none';
    }

    type.addEventListener('change', toggleFields);
    name.addEventListener('input', toggleFields);
    toggleFields();
});


document.querySelectorAll('.toggle-active').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = this.dataset.id;
            const active = this.checked ? 1 : 0;

            fetch(`/admin/payments/${id}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ active })
            })
            .then(res => {
                if (!res.ok) throw new Error('Error');
                return res.json();
            })
            .then(data => {
                const row = this.closest('tr');
                const dot = row.querySelector('.status-dot');
                const text = row.querySelector('.status-indicator span:last-child');
                const status = row.querySelector('.status-indicator');

                if(active === 1){
                    dot.classList.add('active');
                    status.classList.add('is-active');
                    status.classList.remove('is-inactive');
                    text.textContent = 'ATIVO';
                    text.classList.replace('text-muted', 'text-dark');
                } else {
                    dot.classList.remove('active');
                    status.classList.add('is-inactive');
                    status.classList.remove('is-active');
                    text.textContent = 'INATIVO';
                    text.classList.replace('text-dark', 'text-muted');
                }
            })
            .catch(() => {
                alert('Erro ao atualizar');
                this.checked = !this.checked;
            });
        });
    });


document.addEventListener('DOMContentLoaded', function () {
    const chartData = document.getElementById('dashboard-chart-data');
    if (!chartData) return;
    const loadCharts = function () {
        if (typeof Chart === 'undefined') return;
        const read = name => JSON.parse(chartData.dataset[name] || '[]');
        Chart.defaults.font.family = 'Montserrat, Arial, sans-serif';
        Chart.defaults.color = '#667085';
        const grid = { color: 'rgba(16,24,40,.06)' };
        new Chart(document.getElementById('trafficChart'), { type: 'line', data: { labels: read('trafficLabels'), datasets: [{ label: 'Páginas vistas', data: read('trafficViews'), borderColor: '#2970ff', backgroundColor: 'rgba(41,112,255,.1)', fill: true, tension: .35, pointRadius: 2 }, { label: 'Visitantes únicos', data: read('trafficVisitors'), borderColor: '#b39154', backgroundColor: 'transparent', tension: .35, pointRadius: 2 }] }, options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid }, x: { grid: { display: false } } } } });
        const doughnut = (id, labels, data, colors) => new Chart(document.getElementById(id), { type: 'doughnut', data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, hoverOffset: 5 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } } });
        doughnut('paymentsChart', read('paymentLabels'), read('paymentValues'), ['#2970ff','#b39154','#12b76a','#98a2b3']);
        doughnut('ordersChart', read('orderLabels'), read('orderValues'), ['#12b76a','#f79009','#2970ff','#d92d20','#7f56d9','#98a2b3']);
        doughnut('devicesChart', read('deviceLabels'), read('deviceValues'), ['#101828','#b39154','#2970ff']);
    };
    if (typeof Chart !== 'undefined') return loadCharts();
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js';
    script.onload = loadCharts;
    document.head.appendChild(script);
});
