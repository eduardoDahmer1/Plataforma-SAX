// ============================
// PRODUCTS-ADMIN.JS
// Scripts de admin/products (edit, index, review).
// Se carga en las 3 rutas vía scripts-master.blade.php.
// ============================

// ======== Edit: Buscador de productos hijo (tamaño/color) ========
// Archivo: resources/views/admin/products/edit.blade.php
document.addEventListener('DOMContentLoaded', function () {
    function setupProductDropzone(zoneId, inputId, multiple) {
        var zone = document.getElementById(zoneId);
        var fileInput = document.getElementById(inputId);
        if (!zone || !fileInput) return;

        ['dragenter', 'dragover'].forEach(function (eventName) {
            zone.addEventListener(eventName, function (event) {
                event.preventDefault();
                zone.classList.add('is-dragging');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            zone.addEventListener(eventName, function () { zone.classList.remove('is-dragging'); });
        });

        zone.addEventListener('drop', function (event) {
            event.preventDefault();
            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            var imageFiles = Array.from(event.dataTransfer.files).filter(function (file) {
                return allowedTypes.includes(file.type) && file.size <= 10 * 1024 * 1024;
            });
            if (!imageFiles.length) {
                if (window.saxToast) saxToast('warning', 'Use imagens JPG, PNG ou WEBP com até 10 MB.');
                return;
            }

            var transfer = new DataTransfer();
            (multiple ? imageFiles : imageFiles.slice(0, 1)).forEach(function (file) { transfer.items.add(file); });
            fileInput.files = transfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    setupProductDropzone('productPhotoDropzone', 'photoInput', false);
    setupProductDropzone('productGalleryDropzone', 'galleryInput', true);
});

document.addEventListener('DOMContentLoaded', function () {
    var parentResults = document.getElementById('parent_results');
    if (!parentResults) return;

    // Imagen fallback leída del data-attribute del Blade (evita asset() en JS)
    var noImage = parentResults.dataset.noimage || '/storage/uploads/noimage.webp';

    function setupSearch(inputId, btnId, resultsId, selectedId, hiddenName, searchUrl, context) {
        const searchInput = document.getElementById(inputId);
        const searchBtn = document.getElementById(btnId);
        const resultsDiv = document.getElementById(resultsId);
        const selectedDiv = document.getElementById(selectedId);
        const resultsLabel = document.getElementById(resultsId + '_label');

        function updateSelectedCount() {
            const badge = document.querySelector('[data-count-for="' + selectedId + '"]');
            if (badge) badge.textContent = selectedDiv.querySelectorAll(':scope > [data-id]').length;
        }

        function searchProducts() {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
                if (resultsLabel) resultsLabel.classList.add('d-none');
                return;
            }

            const excludeId = resultsDiv.dataset.currentProductId || '';
            const params = new URLSearchParams({ q: query });
            if (excludeId) params.append('exclude_id', excludeId);
            if (context) params.append('context', context);
            if ((context === 'size' || context === 'color') && resultsDiv.dataset.currentColorKey) {
                params.append('current_color_key', resultsDiv.dataset.currentColorKey);
            }
            if ((context === 'size' || context === 'color') && resultsDiv.dataset.currentReferenceKey) {
                params.append('current_reference_key', resultsDiv.dataset.currentReferenceKey);
            }

            fetch(searchUrl + '?' + params.toString())
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var html = '';
                    if (data.length) {
                        data.forEach(function (product) {
                            const alreadySelected = Array.from(
                                selectedDiv.querySelectorAll('input[name="' + hiddenName + '[]"]')
                            ).some(function (input) { return input.value == product.id; });

                            html += '<div class="col-6 col-md-4 col-lg-2">' +
                                '<div class="card h-100 card-hover ' + (alreadySelected ? 'border-success selected' : '') + '" ' +
                                'style="cursor:pointer;" data-id="' + product.id + '" ' +
                                'data-sku="' + (product.sku || '') + '" data-color="' + (product.color || '') + '" ' +
                                'data-inferred-color="' + (product.color_code || '') + '" data-size="' + (product.size || '') + '">' +
                                '<img src="' + (product.photo || noImage) + '" ' +
                                'class="img-fluid object-fit-cover" alt="' + (product.name || product.external_name) + '">' +
                                '<div class="card-body p-2">' +
                                '<span class="badge bg-light text-dark border mb-1">Sugestão</span>' +
                                '<p class="card-text m-0 fw-bold">' + (product.external_name || product.name) + '</p>' +
                                (product.sku ? '<small class="text-muted d-block mt-1">SKU: ' + product.sku + '</small>' : '') +
                                (product.color ? '<div class="d-flex align-items-center mt-1">' +
                                    '<span style="display:inline-block;width:16px;height:16px;background:' + product.color + ';border:1px solid #ccc;margin-right:5px;"></span>' +
                                    '<small>' + product.color + '</small></div>' : '') +
                                (product.color_code ?
                                    '<small class="text-muted d-block mt-1">Código original: *' + product.color_code + '</small>' : '') +
                                (product.size ? '<div class="mt-1"><small class="text-muted">Tamanho: ' + product.size + '</small></div>' : '') +
                                '</div></div></div>';
                        });
                    } else {
                        html = '<div class="col-12"><div class="alert alert-info m-0">Nenhum produto encontrado</div></div>';
                    }
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'flex';
                    resultsDiv.style.flexWrap = 'wrap';
                    if (resultsLabel) resultsLabel.classList.remove('d-none');
                })
                .catch(function (err) { console.error('Falha na busca de produtos:', err); });
        }

        searchBtn.addEventListener('click', searchProducts);
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchProducts();
            }
        });

        if (searchInput.dataset.autoSearch === '1' && searchInput.value.trim().length >= 2) {
            window.setTimeout(searchProducts, 80);
        }

        resultsDiv.addEventListener('click', function (e) {
            const card = e.target.closest('.card');
            if (!card) return;
            const id = card.getAttribute('data-id');
            if (selectedDiv.querySelector('div[data-id="' + id + '"]')) return;

            const name = card.querySelector('.card-text').textContent;
            const imgSrc = card.querySelector('img').src;
            const color = card.getAttribute('data-color');
            const inferredColor = card.getAttribute('data-inferred-color');
            const size = card.getAttribute('data-size');
            const sku = card.getAttribute('data-sku');

            const newCard = document.createElement('div');
            newCard.className = 'col-6 col-md-4 col-lg-2';
            newCard.setAttribute('data-id', id);
            newCard.innerHTML = '<div class="card border-success h-100 position-relative">' +
                '<img src="' + imgSrc + '" class="card-img-top" style="height:120px; object-fit:cover;">' +
                '<div class="card-body p-2">' +
                '<span class="badge bg-success mb-1">Relacionado</span>' +
                '<p class="card-text m-0 fw-bold">' + name + '</p>' +
                (sku ? '<small class="text-muted d-block mt-1">SKU: ' + sku + '</small>' : '') +
                (color ? '<div class="d-flex align-items-center mt-1">' +
                    '<span style="display:inline-block;width:16px;height:16px;background:' + color + ';border:1px solid #ccc;margin-right:5px;"></span>' +
                    '<small>' + color + '</small></div>' : '') +
                (context === 'color' && inferredColor ?
                    '<small class="text-muted d-block mt-1">Código original: *' + inferredColor + '</small>' : '') +
                (size ? '<div class="mt-1"><small class="text-muted">Tamanho: ' + size + '</small></div>' : '') +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item">' +
                '<i class="fas fa-times"></i></button>' +
                '<input type="hidden" name="' + hiddenName + '[]" value="' + id + '">' +
                '</div></div>';
            selectedDiv.appendChild(newCard);
            updateSelectedCount();
        });

        selectedDiv.addEventListener('click', function (e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('[data-id]').remove();
                updateSelectedCount();
            }
        });

        updateSelectedCount();

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#' + inputId + ', #' + resultsId + ', #' + btnId)) {
                resultsDiv.style.display = 'none';
                if (resultsLabel) resultsLabel.classList.add('d-none');
            }
        });
    }

    setupSearch('parent_search', 'parent_search_btn', 'parent_results', 'selected_parents', 'parent_id', '/admin/products/search', 'size');
    setupSearch('color_search', 'color_search_btn', 'color_results', 'selected_colors', 'color_parent_id', '/admin/products/search', 'color');
});

// ======== Edit: Selector asistido de tamaño ========
document.addEventListener('DOMContentLoaded', function () {
    var hiddenSize = document.getElementById('size');
    var sizeSelect = document.querySelector('[data-size-select]');
    var manualInput = document.querySelector('[data-size-manual]');

    if (!hiddenSize || !sizeSelect || !manualInput) return;

    function syncSizeValue() {
        if (sizeSelect.value === '__manual__') {
            manualInput.classList.remove('d-none');
            hiddenSize.value = manualInput.value.trim();
            return;
        }

        manualInput.classList.add('d-none');
        manualInput.value = '';
        hiddenSize.value = sizeSelect.value;
    }

    sizeSelect.addEventListener('change', syncSizeValue);
    manualInput.addEventListener('input', syncSizeValue);
    syncSizeValue();
});


// ======== Edit: Galería multi-delete ========
// Archivo: resources/views/admin/products/edit.blade.php — modal de gestión de galería
function deleteSelectedImages() {
    const checkboxes = document.querySelectorAll('.gallery-checkbox:checked');
    const selectedNames = Array.from(checkboxes).map(cb => cb.value);
    if (selectedNames.length === 0) {
        alert('Selecione pelo menos uma imagem.');
        return;
    }
    if (confirm('Excluir ' + selectedNames.length + ' imagens selecionadas?')) {
        const form = document.getElementById('formMultiDeleteGallery');
        const input = document.getElementById('inputImageNames');
        if (form && input) {
            input.value = selectedNames.join(',');
            form.submit();
        }
    }
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.gallery-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}


// ======== Index: Auto-submit de filtros ========
// Archivo: resources/views/admin/products/index.blade.php
document.addEventListener('DOMContentLoaded', function () {
    var filterForm = document.getElementById('filterForm');
    if (!filterForm) return;

    // Adicionado select[name="per_page"]
    var selects = filterForm.querySelectorAll(
        'select[name="status_filter"], select[name="sort_by"], select[name="highlight_filter"], select[name="brand_id"], select[name="category_id"], select[name="product_type"], select[name="per_page"]'
    );

    selects.forEach(function (select) {
        select.addEventListener('change', function () {
            filterForm.submit();
        });
    });
});


// ======== Index: Destaques AJAX ========
// Archivo: resources/views/admin/products/index.blade.php — modal de destaques
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('.form-highlights');
    if (!forms.length) return;

    forms.forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            var button = form.querySelector('button[type="submit"]');
            var originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Salvando...';

            try {
                var formData = new FormData(form);
                var response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (response.ok) {
                    var modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    if (modal) modal.hide();

                    if (window.saxToast) saxToast('success', 'Destaques atualizados com sucesso!');
                } else {
                    throw new Error('Erro ao salvar');
                }
            } catch (error) {
                if (window.saxToast) {
                    saxToast('error', 'Erro ao atualizar destaques. Tente novamente.');
                } else {
                    alert('Erro ao atualizar destaques. Tente novamente.');
                }
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    });
});


// ======== Review: Modal de detalhes ========
// Archivo: resources/views/admin/products/review.blade.php
function abrirModalLocal(data) {
    var corpo = document.getElementById('corpoTabelaLocal');
    var titulo = document.getElementById('tituloModal');
    if (!corpo || !titulo) return;

    var reviewData = document.getElementById('product-review-data');
    var dadosProdutos = reviewData ? JSON.parse(reviewData.dataset.products || '{}') : {};
    var produtosDoDia = dadosProdutos[data] || [];
    var escapeHtml = function (value) {
        return String(value).replace(/[&<>'"]/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char];
        });
    };

    titulo.innerText = 'Editados em ' + data;
    corpo.innerHTML = '';

    if (produtosDoDia.length === 0) {
        corpo.innerHTML = '<tr><td colspan="4" class="text-center py-4">Nenhum detalhe encontrado.</td></tr>';
    } else {
        produtosDoDia.forEach(function (p) {
            // Adicionamos a coluna do usuário
            corpo.innerHTML +=
                '<tr>' +
                '<td class="ps-4"><b>' + escapeHtml(p.name || 'Produto') + '</b></td>' +
                '<td class="text-center"><code class="small">' + escapeHtml(p.sku || '-') + '</code></td>' +
                '<td class="text-center"><span class="badge bg-dark text-white">' + escapeHtml(p.editor && p.editor.name ? p.editor.name : 'Usuário removido') + '</span></td>' +
                '<td class="pe-4 text-end"><span class="badge bg-light text-dark border">' + escapeHtml(p.ref_code || '-') + '</span></td>' +
                '</tr>';
        });
    }

    new bootstrap.Modal(document.getElementById('modalDetalhesLocal')).show();
}

document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('photoInput');
    if (!input) return;

    input.addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        var box = document.getElementById('photoPreviewBox');
        var img = document.getElementById('photoPreviewImg');

        // libera el objectURL anterior (evita fuga de memoria al cambiar varias veces)
        if (img.dataset.objectUrl) URL.revokeObjectURL(img.dataset.objectUrl);

        var url = URL.createObjectURL(file);
        img.src = url;
        img.dataset.objectUrl = url;
        box.style.display = '';
        document.getElementById('productPhotoDropzone')?.classList.add('mt-3');
        var status = document.getElementById('photoSelectionStatus');
        if (status) status.textContent = file.name + ' · ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
    });
});



// Acumula las imágenes elegidas con DataTransfer y reescribe input.files,
// para que al guardar se manden TODAS. No toca backend (update ya acumula sobre lo guardado).
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('galleryInput');
    if (!input) return;

    var preview = document.getElementById('galleryPreview');
    var countLabel = document.getElementById('gallerySelectionCount');
    var store = new DataTransfer(); // acumula TODOS los archivos elegidos
    var urls = [];                  // objectURLs activos, para revocarlos en cada render

    function render() {
        urls.forEach(function (u) { URL.revokeObjectURL(u); });
        urls = [];
        preview.innerHTML = '';

        var files = Array.from(store.files);
        preview.style.display = files.length ? 'grid' : 'none';
        if (countLabel) countLabel.textContent = files.length
            ? files.length + (files.length === 1 ? ' nova imagem selecionada' : ' novas imagens selecionadas')
            : 'Nenhuma nova imagem selecionada';

        files.forEach(function (file, index) {
            var url = URL.createObjectURL(file);
            urls.push(url);

            var col = document.createElement('div');
            col.className = 'product-gallery-pending';
            col.innerHTML =
                '<img src="' + url + '" alt="Prévia da imagem">' +
                '<button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1" data-index="' + index + '" aria-label="Remover imagem">' +
                '<i class="fas fa-times"></i></button>';
            preview.appendChild(col);
        });
    }

    input.addEventListener('change', function () {
        Array.from(input.files).forEach(function (file) {
            var dup = Array.from(store.files).some(function (f) {
                return f.name === file.name && f.size === file.size; // evita duplicados
            });
            if (!dup) store.items.add(file);
        });
        input.files = store.files; // reescribe el input → al guardar va TODO lo acumulado
        render();
    });

    preview.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-index]');
        if (!btn) return;
        store.items.remove(parseInt(btn.dataset.index, 10)); // quita esa foto del set
        input.files = store.files;
        render();
    });
});


// ======== Index: aplica visualmente o status (ativo/inativo) num card ========
// Archivo: resources/views/admin/products/index.blade.php
// Usado tanto pelo toggle individual quanto pela revalidação em massa.
function applyProductStatusUI(productId, isActive) {
    var card = document.getElementById('product-card-' + productId);
    if (!card) return;

    var pill = card.querySelector('.product-status-pill');
    var btn = card.querySelector('.btn-toggle-status');

    if (pill && btn) {
        pill.classList.toggle('is-on', isActive);
        pill.classList.toggle('is-off', !isActive);
        pill.textContent = isActive ? btn.dataset.labelActive : btn.dataset.labelInactive;
    }

    var img = card.querySelector('.sax-product-img-box img');
    if (img) img.classList.toggle('is-inactive', !isActive);

    if (!btn) return;

    btn.classList.toggle('is-on', isActive);

    var icon = btn.querySelector('.icon-toggle');
    if (icon) {
        icon.classList.toggle('fa-toggle-on', isActive);
        icon.classList.toggle('fa-toggle-off', !isActive);
    }
}

// ======== Index: Ativar/Desativar via AJAX (sem reload) ========
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-toggle-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled) return;
            btn.disabled = true;

            fetch(btn.dataset.url, {
                method: 'POST',
                headers: Object.assign({ 'Accept': 'application/json' }, headers),
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok || !result.data.success) {
                        throw new Error(result.data.message || 'Erro ao atualizar status.');
                    }
                    var card = btn.closest('[id^="product-card-"]');
                    if (card) applyProductStatusUI(card.id.replace('product-card-', ''), !!result.data.status);
                    if (window.saxToast) saxToast('success', result.data.message || 'Status atualizado!');
                })
                .catch(function (err) {
                    if (window.saxToast) saxToast('error', err.message || 'Erro ao atualizar status.');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    });
});

// ======== Index: Excluir produto via AJAX (com modal de confirmação) ========
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = document.getElementById('product-card-' + btn.dataset.productId);

            function doDelete() {
                btn.disabled = true;

                fetch(btn.dataset.url, {
                    method: 'DELETE',
                    headers: Object.assign({ 'Accept': 'application/json' }, headers),
                })
                    .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                    .then(function (result) {
                        if (!result.ok || !result.data.success) {
                            throw new Error(result.data.message || 'Erro ao excluir produto.');
                        }
                        if (window.saxToast) saxToast('success', result.data.message || 'Produto excluído com sucesso!');
                        if (card) {
                            card.style.transition = 'opacity .25s';
                            card.style.opacity = '0';
                            setTimeout(function () { card.remove(); }, 250);
                        }
                    })
                    .catch(function (err) {
                        if (window.saxToast) saxToast('error', err.message || 'Erro ao excluir produto.');
                        btn.disabled = false;
                    });
            }

            var confirmModalEl = document.getElementById('saxConfirmModal');
            if (confirmModalEl && typeof bootstrap !== 'undefined') {
                document.getElementById('saxConfirmMessage').textContent = 'Tem certeza que deseja excluir este produto? Essa ação não pode ser desfeita.';
                document.getElementById('saxConfirmAccept').onclick = function () {
                    bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
                    doDelete();
                };
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            } else if (confirm('Tem certeza que deseja excluir este produto?')) {
                doDelete();
            }
        });
    });
});

// ======== Index: Verificar produtos (revalida em massa, sem reload) ========
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnRevalidateProducts');
    if (!btn) return;

    btn.addEventListener('click', function () {
        if (btn.disabled) return;
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Verificando...';

        fetch(btn.dataset.url, {
            method: 'POST',
            headers: Object.assign({ 'Accept': 'application/json' }, headers),
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Erro ao verificar produtos.');
                }
                (result.data.activated_ids || []).forEach(function (id) { applyProductStatusUI(id, true); });
                (result.data.deactivated_ids || []).forEach(function (id) { applyProductStatusUI(id, false); });
                if (window.saxToast) saxToast('success', result.data.message || 'Verificação concluída!');
            })
            .catch(function (err) {
                if (window.saxToast) saxToast('error', err.message || 'Erro ao verificar produtos.');
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    });
});


(function() {
                    if (!document.getElementById('color-section')) return;
                    let colorEntries = [];
                    let showAll = false;

                    async function initPalette() {
                        try {
                            const response = await fetch('/data/color.json');
                            const data = await response.json();

                            colorEntries = Object.entries(data).map(([hex, name]) => ({
                                hex: String(hex).toUpperCase(),
                                name: String(name),
                            }));

                            renderColors();
                            detectDominantImageColor();
                        } catch (error) {
                            console.error("Erro ao carregar cores:", error);
                        }
                    }

                    function normalizeHex(value) {
                        if (!value) return null;
                        let hex = String(value).trim().replace('#', '').toUpperCase();

                        if (/^[0-9A-F]{3}$/.test(hex)) {
                            hex = hex.split('').map(ch => ch + ch).join('');
                        }

                        if (!/^[0-9A-F]{6}$/.test(hex)) {
                            return null;
                        }

                        return `#${hex}`;
                    }

                    function hexToRgb(hex) {
                        return {
                            r: parseInt(hex.slice(1, 3), 16),
                            g: parseInt(hex.slice(3, 5), 16),
                            b: parseInt(hex.slice(5, 7), 16),
                        };
                    }

                    function colorDistance(a, b) {
                        const dr = a.r - b.r;
                        const dg = a.g - b.g;
                        const db = a.b - b.b;
                        return dr * dr + dg * dg + db * db;
                    }

                    function closestByHex(hex, limit = 6) {
                        const target = hexToRgb(hex);

                        return colorEntries
                            .map(entry => ({
                                ...entry,
                                dist: colorDistance(target, hexToRgb(entry.hex))
                            }))
                            .sort((a, b) => a.dist - b.dist)
                            .slice(0, limit);
                    }

                    function closestByName(term, limit = 6) {
                        const query = term.toLowerCase().trim();
                        if (!query) return [];

                        const words = query.split(/\s+/).filter(Boolean);

                        return colorEntries
                            .map(entry => {
                                const name = entry.name.toLowerCase();
                                const score = words.reduce((acc, word) => {
                                    if (name === word) return acc + 120;
                                    if (name.startsWith(word)) return acc + 50;
                                    if (name.includes(word)) return acc + 25;
                                    return acc;
                                }, 0);

                                return { ...entry, score };
                            })
                            .filter(entry => entry.score > 0)
                            .sort((a, b) => b.score - a.score)
                            .slice(0, limit);
                    }

                    function applyColor(hex) {
                        const colorInput = document.getElementById('color-input');
                        const colorSearch = document.getElementById('color-search');
                        const noColor = document.getElementById('no_color');

                        if (colorInput) colorInput.value = hex;
                        if (colorSearch) colorSearch.value = hex;
                        if (noColor) noColor.checked = false;
                        addSelectedColor(hex);
                    }

                    function addSelectedColor(hex) {
                        const normalized = normalizeHex(hex);
                        const container = document.getElementById('selected-colors');
                        if (!normalized || !container || container.querySelector(`[data-color="${normalized}"]`)) return;
                        if (container.querySelectorAll('.selected-color').length >= 8) return;

                        const chip = document.createElement('span');
                        chip.className = 'selected-color badge bg-light text-dark border d-inline-flex align-items-center gap-2 p-2';
                        chip.dataset.color = normalized;
                        chip.innerHTML = `<i style="width:18px;height:18px;border-radius:50%;background:${normalized};border:1px solid #bbb"></i>${normalized}<button type="button" class="btn-close" style="font-size:8px" aria-label="Remover cor"></button><input type="hidden" name="colors_values[]" value="${normalized}">`;
                        container.appendChild(chip);
                    }

                    function removeSelectedColor(event) {
                        const removeButton = event.target.closest('.btn-close');
                        if (removeButton) removeButton.closest('.selected-color')?.remove();
                    }

                    function detectDominantImageColor() {
                        const section = document.getElementById('color-section');
                        const image = document.getElementById('photoPreviewImg');
                        if (!section || section.dataset.detectColorFromImage !== '1' || !image) return;
                        if (!image.getAttribute('src')) {
                            image.addEventListener('load', detectDominantImageColor, { once: true });
                            return;
                        }

                        const analyze = () => {
                            try {
                                const canvas = document.createElement('canvas');
                                const context = canvas.getContext('2d', { willReadFrequently: true });
                                const size = 72;
                                canvas.width = size;
                                canvas.height = size;
                                context.drawImage(image, 0, 0, size, size);

                                const pixels = context.getImageData(0, 0, size, size).data;
                                const buckets = new Map();
                                for (let index = 0; index < pixels.length; index += 4) {
                                    const r = pixels[index];
                                    const g = pixels[index + 1];
                                    const b = pixels[index + 2];
                                    if (pixels[index + 3] < 180 || (r > 235 && g > 235 && b > 235)) continue;

                                    const saturation = Math.max(r, g, b) - Math.min(r, g, b);
                                    const key = `${Math.round(r / 24)},${Math.round(g / 24)},${Math.round(b / 24)}`;
                                    const bucket = buckets.get(key) || { score: 0, r: 0, g: 0, b: 0, count: 0 };
                                    bucket.score += 1 + saturation / 80;
                                    bucket.r += r;
                                    bucket.g += g;
                                    bucket.b += b;
                                    bucket.count += 1;
                                    buckets.set(key, bucket);
                                }

                                const dominant = Array.from(buckets.values()).sort((a, b) => b.score - a.score)[0];
                                if (!dominant || dominant.count < 10) return;

                                const toHex = value => Math.round(value).toString(16).padStart(2, '0').toUpperCase();
                                const hex = `#${toHex(dominant.r / dominant.count)}${toHex(dominant.g / dominant.count)}${toHex(dominant.b / dominant.count)}`;
                                applyColor(hex);
                                renderColors(hex);

                                const message = document.getElementById('image-color-suggestion');
                                if (message) {
                                    message.classList.remove('d-none');
                                    message.innerHTML = `<i class="fas fa-image me-1"></i>Cor sugerida pela foto: <strong>${hex}</strong>. Confira antes de salvar.`;
                                }
                                section.dataset.detectColorFromImage = '0';
                            } catch (error) {
                                console.warn('Não foi possível identificar a cor pela imagem:', error);
                            }
                        };

                        if (image.complete && image.naturalWidth) analyze();
                        else image.addEventListener('load', analyze, { once: true });
                    }

                    function renderSuggestions(filter, filteredCount) {
                        const suggestionsContainer = document.getElementById('color-suggestions');
                        if (!suggestionsContainer) return;

                        suggestionsContainer.innerHTML = '';

                        const normalizedHex = normalizeHex(filter);
                        let suggestions = [];

                        if (normalizedHex) {
                            const exact = colorEntries.some(entry => entry.hex === normalizedHex);
                            if (!exact) {
                                suggestions = closestByHex(normalizedHex, 8);
                            }
                        } else if (String(filter).trim() && filteredCount === 0) {
                            suggestions = closestByName(filter, 8);
                        }

                        if (!suggestions.length) {
                            return;
                        }

                        const wrap = document.createElement('div');
                        wrap.className = 'd-flex flex-wrap align-items-center gap-2';

                        const title = document.createElement('span');
                        title.className = 'text-muted';
                        title.textContent = 'Nomes parecidos:';
                        wrap.appendChild(title);

                        suggestions.forEach(entry => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'btn btn-outline-secondary btn-sm py-1 px-2';
                            btn.innerHTML = `<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${entry.hex};margin-right:6px;"></span>${entry.name}`;
                            btn.onclick = () => applyColor(entry.hex);
                            wrap.appendChild(btn);
                        });

                        suggestionsContainer.appendChild(wrap);
                    }

                    window.renderColors = function(filter = '') {
                        const container = document.getElementById('color-palette');
                        if (!container) return;

                        container.innerHTML = '';
                        const normalizedFilter = String(filter).toLowerCase();
                        const filtered = colorEntries.filter(entry =>
                            entry.name.toLowerCase().includes(normalizedFilter) || entry.hex.toLowerCase().includes(normalizedFilter)
                        );

                        const limit = showAll ? filtered.length : 20;

                        filtered.slice(0, limit).forEach(entry => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'btn btn-sm border shadow-sm';
                            btn.style.backgroundColor = entry.hex;
                            btn.style.width = '32px';
                            btn.style.height = '32px';
                            btn.title = `${entry.name} (${entry.hex})`;
                            btn.onclick = () => applyColor(entry.hex);
                            container.appendChild(btn);
                        });

                        renderSuggestions(filter, filtered.length);
                    };

                    window.toggleColors = function() {
                        showAll = !showAll;
                        document.getElementById('btn-toggle').innerText = showAll ? 'Mostrar menos...' : 'Mostrar mais cores...';
                        renderColors(document.getElementById('color-search') ? document.getElementById('color-search').value : '');
                    };

                    document.addEventListener('DOMContentLoaded', () => {
                        initPalette();

                        const colorSearch = document.getElementById('color-search');
                        const colorInput = document.getElementById('color-input');
                        const selectedColors = document.getElementById('selected-colors');
                        const addColor = document.getElementById('add-color');
                        const noColor = document.getElementById('no_color');

                        selectedColors?.addEventListener('click', removeSelectedColor);
                        addColor?.addEventListener('click', () => applyColor(colorInput?.value));
                        noColor?.addEventListener('change', function () {
                            if (this.checked && selectedColors) selectedColors.innerHTML = '';
                        });

                        if (colorSearch) {
                            colorSearch.addEventListener('input', function() {
                                renderColors(this.value);
                            });
                        }

                        if (colorInput) {
                            colorInput.addEventListener('input', function() {
                                const colorSearchField = document.getElementById('color-search');
                                if (colorSearchField) {
                                    colorSearchField.value = this.value;
                                    renderColors(this.value);
                                }
                            });
                        }
                    });
                })();

(async function() {
                    const sizeContainer = document.getElementById('size-container');
                    if (!sizeContainer) return;
                    const typeSelector = document.getElementById('type_selector');
                    const sizeSelect = document.getElementById('size_select');
                    const inputManual = document.getElementById('size_manual');
                    const detectionMsg = document.getElementById('detection-msg');

                    const currentSize = sizeContainer.dataset.currentSize;
                    const detectedSize = sizeContainer.dataset.detectedSize;
                    const activeGroup = sizeContainer.dataset.activeGroup;

                    const response = await fetch('/data/tamanho.json');
                    const sizeGroups = await response.json();

                    Object.keys(sizeGroups).forEach(key => {
                        const opt = document.createElement('option');
                        opt.value = key; opt.text = key.charAt(0).toUpperCase() + key.slice(1);
                        if(key === activeGroup) opt.selected = true;
                        typeSelector.appendChild(opt);
                    });

                    function populateSizes(group) {
                        sizeSelect.innerHTML = '<option value="">Selecione o tamanho</option>';
                        const valToSet = currentSize || detectedSize;

                        if (group && sizeGroups[group]) {
                            sizeGroups[group].forEach(val => {
                                const opt = document.createElement('option');
                                opt.value = val; opt.text = val;
                                if(val === valToSet) opt.selected = true;
                                sizeSelect.appendChild(opt);
                            });
                            sizeSelect.classList.remove('d-none');
                            sizeSelect.setAttribute('name', 'size');
                            inputManual.removeAttribute('name');
                            inputManual.classList.add('d-none');
                        } else if (group === 'manual') {
                            sizeSelect.classList.add('d-none');
                            sizeSelect.removeAttribute('name');
                            inputManual.value = valToSet;
                            inputManual.classList.remove('d-none');
                            inputManual.setAttribute('name', 'size');
                        } else {
                            sizeSelect.classList.add('d-none');
                            inputManual.classList.add('d-none');
                        }
                    }

                    typeSelector.addEventListener('change', (e) => {
                        detectionMsg.classList.add('d-none');
                        populateSizes(e.target.value);
                    });

                    if(activeGroup) populateSizes(activeGroup);
                })();

document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('productEditForm');
        const saveBtn = document.getElementById('saveProductBtn');
        const feedback = document.getElementById('productEditFeedback');

        if (!form || !saveBtn || !feedback) {
            return;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const originalHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';

            feedback.className = 'd-none';
            feedback.innerHTML = '';

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
                    throw new Error(firstError || data?.message || 'Erro ao salvar produto.');
                }

                feedback.className = 'alert alert-success';
                feedback.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + (data.message || 'Produto atualizado com sucesso!');

                if (data.redirect) {
                    setTimeout(function () {
                        window.location.href = data.redirect;
                    }, 650);
                }
            } catch (error) {
                feedback.className = 'alert alert-danger';
                feedback.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + (error.message || 'Erro ao salvar.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHtml;
            }
        });
    });
