// ============================
// PRODUCTS-ADMIN.JS
// Scripts de admin/products (edit, index, review).
// Se carga en las 3 rutas vía scripts-master.blade.php.
// ============================

// ======== Edit: Buscador de productos hijo (tamaño/color) ========
// Archivo: resources/views/admin/products/edit.blade.php
document.addEventListener('DOMContentLoaded', function () {
    var parentResults = document.getElementById('parent_results');
    if (!parentResults) return;

    // Imagen fallback leída del data-attribute del Blade (evita asset() en JS)
    var noImage = parentResults.dataset.noimage || '/storage/uploads/noimage.webp';

    function setupSearch(inputId, btnId, resultsId, selectedId, hiddenName, searchUrl) {
        const searchInput = document.getElementById(inputId);
        const searchBtn = document.getElementById(btnId);
        const resultsDiv = document.getElementById(resultsId);
        const selectedDiv = document.getElementById(selectedId);

        function searchProducts() {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
                return;
            }

            fetch(searchUrl + '?q=' + encodeURIComponent(query))
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
                                'data-color="' + (product.color || '') + '" data-size="' + (product.size || '') + '">' +
                                '<img src="' + (product.photo || noImage) + '" ' +
                                'class="img-fluid object-fit-cover" alt="' + (product.name || product.external_name) + '">' +
                                '<div class="card-body p-2">' +
                                '<p class="card-text m-0 fw-bold">' + (product.name || product.external_name) + '</p>' +
                                (product.color ? '<div class="d-flex align-items-center mt-1">' +
                                    '<span style="display:inline-block;width:16px;height:16px;background:' + product.color + ';border:1px solid #ccc;margin-right:5px;"></span>' +
                                    '<small>' + product.color + '</small></div>' : '') +
                                (product.size ? '<div class="mt-1"><small class="text-muted">Tamanho: ' + product.size + '</small></div>' : '') +
                                '</div></div></div>';
                        });
                    } else {
                        html = '<div class="col-12"><div class="alert alert-info m-0">Nenhum produto encontrado</div></div>';
                    }
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'flex';
                    resultsDiv.style.flexWrap = 'wrap';
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

        resultsDiv.addEventListener('click', function (e) {
            const card = e.target.closest('.card');
            if (!card) return;
            const id = card.getAttribute('data-id');
            if (selectedDiv.querySelector('div[data-id="' + id + '"]')) return;

            const name = card.querySelector('.card-text').textContent;
            const imgSrc = card.querySelector('img').src;
            const color = card.getAttribute('data-color');
            const size = card.getAttribute('data-size');

            const newCard = document.createElement('div');
            newCard.className = 'col-6 col-md-4 col-lg-2';
            newCard.setAttribute('data-id', id);
            newCard.innerHTML = '<div class="card border-success h-100 position-relative">' +
                '<img src="' + imgSrc + '" class="card-img-top" style="height:120px; object-fit:cover;">' +
                '<div class="card-body p-2">' +
                '<p class="card-text m-0 fw-bold">' + name + '</p>' +
                (color ? '<div class="d-flex align-items-center mt-1">' +
                    '<span style="display:inline-block;width:16px;height:16px;background:' + color + ';border:1px solid #ccc;margin-right:5px;"></span>' +
                    '<small>' + color + '</small></div>' : '') +
                (size ? '<div class="mt-1"><small class="text-muted">Tamanho: ' + size + '</small></div>' : '') +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item">' +
                '<i class="fas fa-times"></i></button>' +
                '<input type="hidden" name="' + hiddenName + '[]" value="' + id + '">' +
                '</div></div>';
            selectedDiv.appendChild(newCard);
        });

        selectedDiv.addEventListener('click', function (e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('[data-id]').remove();
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#' + inputId + ', #' + resultsId + ', #' + btnId)) {
                resultsDiv.style.display = 'none';
            }
        });
    }

    setupSearch('parent_search', 'parent_search_btn', 'parent_results', 'selected_parents', 'parent_id', '/admin/products/search');
    setupSearch('color_search', 'color_search_btn', 'color_results', 'selected_colors', 'color_parent_id', '/admin/products/search');
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

    // Al cambiar cualquier select de filtro, enviar el form automáticamente
    var selects = filterForm.querySelectorAll(
        'select[name="status_filter"], select[name="sort_by"], select[name="highlight_filter"], select[name="brand_id"], select[name="category_id"]'
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
                    modal.hide();

                    var alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML =
                        '<i class="fa fa-check-circle me-2"></i>Destaques atualizados com sucesso!' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.card'));

                    setTimeout(function () { alertDiv.remove(); }, 3000);
                } else {
                    throw new Error('Erro ao salvar');
                }
            } catch (error) {
                alert('Erro ao atualizar destaques. Tente novamente.');
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    });
});


// ======== Review: Modal de detalhes ========
// Archivo: resources/views/admin/products/review.blade.php
// NOTA: la variable `dadosProdutos` se define inline en el blade con @json()
function abrirModalLocal(data) {
    var corpo = document.getElementById('corpoTabelaLocal');
    var titulo = document.getElementById('tituloModal');
    if (!corpo || !titulo) return;

    var produtosDoDia = dadosProdutos[data] || [];

    titulo.innerText = 'Editados em ' + data;
    corpo.innerHTML = '';

    if (produtosDoDia.length === 0) {
        corpo.innerHTML = '<tr><td colspan="3" class="text-center py-4">Nenhum detalhe encontrado.</td></tr>';
    } else {
        produtosDoDia.forEach(function (p) {
            corpo.innerHTML +=
                '<tr>' +
                '<td class="ps-4"><b>' + p.name + '</b></td>' +
                '<td class="text-center"><code class="small">' + (p.sku || '-') + '</code></td>' +
                '<td class="pe-4 text-end"><span class="badge bg-light text-dark border">' + (p.ref_code || '-') + '</span></td>' +
                '</tr>';
        });
    }

    new bootstrap.Modal(document.getElementById('modalDetalhesLocal')).show();
}
