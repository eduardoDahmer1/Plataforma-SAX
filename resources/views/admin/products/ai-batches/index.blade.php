@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Generación masiva con IA"
        description="Prepará hasta 1.000 SKU, agrupados en un máximo de 100 generaciones de IA.">
        <x-slot:actions>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark btn-sax-lg px-4">
                <i class="fa fa-arrow-left me-2"></i> Volver a productos
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-xl-5">
                    <h5 class="fw-bold mb-1">Crear un lote</h5>
                    <p class="text-muted mb-4">La vista previa no consume IA. Podrás confirmar después de revisar los códigos encontrados.</p>

                    <form method="POST" action="{{ route('admin.products.ai-batches.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="aiBatchFile" class="form-label fw-bold">Archivo Excel (.xlsx)</label>
                            <input id="aiBatchFile" type="file" name="file" accept=".xlsx" class="form-control @error('file') is-invalid @enderror">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Debe incluir una columna código, codigo, sku o referencia. Máximo 1.000 SKU únicos.</div>
                        </div>

                        <div class="d-flex align-items-center gap-3 my-4">
                            <hr class="flex-grow-1"><span class="text-muted small fw-bold">O PEGÁ LOS CÓDIGOS</span><hr class="flex-grow-1">
                        </div>

                        <div class="mb-4">
                            <label for="aiBatchSkus" class="form-label fw-bold">Lista de códigos</label>
                            <textarea id="aiBatchSkus" name="skus" rows="10" class="form-control @error('skus') is-invalid @enderror"
                                placeholder="Un código por línea. También se aceptan comas y punto y coma.">{{ old('skus') }}</textarea>
                            @error('skus')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex align-items-center gap-3 my-4">
                            <hr class="flex-grow-1"><span class="text-muted small fw-bold">O SELECCIONÁ DEL CATÁLOGO</span><hr class="flex-grow-1">
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-dark w-100 py-3 fw-bold"
                                data-bs-toggle="modal" data-bs-target="#aiCatalogModal">
                                <i class="fa fa-boxes-stacked me-2"></i> Seleccionar productos del catálogo
                            </button>
                            <div class="form-text">Buscá productos por SKU, nombre o referencia y filtrá el catálogo sin salir de esta pantalla.</div>
                            <div id="aiCatalogFormFeedback" class="form-text text-success fw-bold d-none" aria-live="polite"></div>
                        </div>

                        <button type="submit" class="btn btn-dark px-5 py-3 fw-bold">
                            <i class="fa fa-magnifying-glass me-2"></i> Validar y ver resumen
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="alert alert-light border rounded-4 p-4 mb-4">
                <h6 class="fw-bold"><i class="fa fa-shield-halved me-2"></i>Proceso seguro</h6>
                <p class="small text-muted mb-0">La IA guarda la información, pero no activa productos. Los ya preparados o publicados se omiten automáticamente.</p>
            </div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Lotes recientes</h6>
                    @forelse($batches as $batch)
                        <a href="{{ route('admin.products.ai-batches.show', $batch) }}" class="d-flex justify-content-between align-items-center text-decoration-none border-bottom py-3">
                            <span>
                                <strong class="text-dark">Lote #{{ $batch->id }}</strong>
                                <small class="d-block text-muted">{{ $batch->created_at->format('d/m/Y H:i') }} · {{ $batch->input_count }} códigos</small>
                            </span>
                            <span class="badge {{ $batch->status === 'completed' ? 'text-bg-success' : ($batch->status === 'draft' ? 'text-bg-secondary' : 'text-bg-primary') }}">{{ $batch->status }}</span>
                        </a>
                    @empty
                        <p class="text-muted mb-0">Todavía no hay lotes.</p>
                    @endforelse
                    <div class="mt-3">{{ $batches->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>

<div class="modal fade" id="aiCatalogModal" tabindex="-1" aria-labelledby="aiCatalogModalTitle" aria-hidden="true"
    data-catalog-url="{{ route('admin.products.ai-batches.catalog-products') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold" id="aiCatalogModalTitle">Seleccionar productos del catálogo</h5>
                    <p class="small text-muted mb-0">Buscá y filtrá productos para agregarlos al lote.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="ai-catalog-toolbar mb-4">
                    <label for="aiCatalogSearch" class="visually-hidden">Buscar productos</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="fa fa-magnifying-glass text-muted"></i></span>
                        <input type="search" id="aiCatalogSearch" class="form-control"
                            placeholder="Buscar por SKU, nombre o referencia…" autocomplete="off">
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="aiCatalogBrand" class="form-label small fw-bold mb-1">Marca</label>
                            <select id="aiCatalogBrand" class="form-select">
                                <option value="">Todas las marcas</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="aiCatalogCategory" class="form-label small fw-bold mb-1">Categoría</label>
                            <select id="aiCatalogCategory" class="form-select">
                                <option value="">Todas las categorías</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="aiCatalogSubcategory" class="form-label small fw-bold mb-1">Subcategoría</label>
                            <select id="aiCatalogSubcategory" class="form-select">
                                <option value="">Todas las subcategorías</option>
                                @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" data-category-id="{{ $subcategory->category_id }}">
                                        {{ $subcategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="aiCatalogAiStatus" class="form-label small fw-bold mb-1">Preparación con IA</label>
                            <select id="aiCatalogAiStatus" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="pending">Pendiente</option>
                                <option value="prepared">Producto preparado</option>
                                <option value="missing_photo">Falta fotografía</option>
                                <option value="review">Requiere revisión</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" id="aiCatalogClearFilters" class="btn btn-sm btn-link text-dark text-decoration-none">
                            <i class="fa fa-filter-circle-xmark me-1"></i> Limpiar filtros
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                    <span id="aiCatalogResultSummary" class="small text-muted" aria-live="polite"></span>
                    <span class="small text-muted">20 por página</span>
                </div>

                <div id="aiCatalogResults" aria-live="polite" aria-busy="false">
                    <div id="aiCatalogLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-dark" role="status"><span class="visually-hidden">Cargando…</span></div>
                        <p class="text-muted mt-3 mb-0">Cargando productos…</p>
                    </div>

                    <div id="aiCatalogError" class="alert alert-danger d-none" role="alert">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <span>No pudimos cargar los productos del catálogo.</span>
                            <button type="button" id="aiCatalogRetry" class="btn btn-sm btn-outline-danger">Reintentar</button>
                        </div>
                    </div>

                    <div id="aiCatalogEmpty" class="text-center py-5 d-none">
                        <i class="fa fa-box-open fa-2x text-muted mb-3"></i>
                        <p class="fw-bold mb-1">No encontramos productos</p>
                        <p class="small text-muted mb-0">Probá con otra búsqueda o limpiá los filtros.</p>
                    </div>

                    <div id="aiCatalogList" class="ai-catalog-list"></div>
                </div>

                <nav id="aiCatalogPaginationWrap" class="d-none mt-4" aria-label="Paginación del catálogo">
                    <ul id="aiCatalogPagination" class="pagination pagination-sm justify-content-center mb-0"></ul>
                </nav>
            </div>

            <div class="modal-footer px-4 py-3 gap-2">
                <div class="me-auto">
                    <strong id="aiCatalogSelectionCount" class="d-block" aria-live="polite">0 productos seleccionados</strong>
                    <button type="button" id="aiCatalogClearSelection" class="btn btn-sm btn-link text-danger text-decoration-none p-0" disabled>
                        Limpiar selección
                    </button>
                    <small id="aiCatalogSelectionFeedback" class="d-block text-danger" role="status"></small>
                </div>
                <button type="button" class="btn btn-outline-dark px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="aiCatalogAddSelected" class="btn btn-dark px-4" disabled>
                    Agregar 0 productos al lote
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #aiCatalogModal .modal-dialog { max-width: min(1180px, calc(100vw - 2rem)); }
    #aiCatalogModal .modal-body { background: #f8fafc; }
    .ai-catalog-toolbar { padding: 1rem; background: #fff; border: 1px solid #e1e6ee; border-radius: 14px; }
    .ai-catalog-list { display: grid; gap: .65rem; }
    .ai-catalog-product { display: grid; grid-template-columns: auto 58px minmax(0, 1fr) auto; gap: .85rem; align-items: center; padding: .75rem; background: #fff; border: 1px solid #e1e6ee; border-radius: 12px; cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease; }
    .ai-catalog-product:hover { border-color: #9aa3ad; }
    .ai-catalog-product.is-selected { border-color: #212529; background: #f3f5f7; box-shadow: 0 0 0 2px rgba(33, 37, 41, .08); }
    .ai-catalog-product__image { width: 58px; height: 58px; object-fit: cover; border-radius: 9px; background: #eef1f5; }
    .ai-catalog-product__identity, .ai-catalog-product__copy { min-width: 0; }
    .ai-catalog-product__name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ai-catalog-product__meta { display: flex; flex-wrap: wrap; gap: .3rem .75rem; color: #6c757d; font-size: .75rem; }
    .ai-catalog-product__status { white-space: nowrap; }
    #aiCatalogPagination .page-link { color: #212529; cursor: pointer; }
    #aiCatalogPagination .page-item.active .page-link { color: #fff; background: #212529; border-color: #212529; }

    @media (max-width: 767.98px) {
        #aiCatalogModal .modal-dialog { max-width: none; margin: .5rem; }
        #aiCatalogModal .modal-body { padding: 1rem !important; }
        .ai-catalog-product { grid-template-columns: auto 50px minmax(0, 1fr); }
        .ai-catalog-product__image { width: 50px; height: 50px; }
        .ai-catalog-product__status { grid-column: 2 / -1; justify-self: start; }
        #aiCatalogModal .modal-footer small { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('aiCatalogModal');
    if (!modal) return;

    const skusTextarea = document.getElementById('aiBatchSkus');
    const formFeedback = document.getElementById('aiCatalogFormFeedback');
    const searchInput = document.getElementById('aiCatalogSearch');
    const brandSelect = document.getElementById('aiCatalogBrand');
    const categorySelect = document.getElementById('aiCatalogCategory');
    const subcategorySelect = document.getElementById('aiCatalogSubcategory');
    const aiStatusSelect = document.getElementById('aiCatalogAiStatus');
    const clearButton = document.getElementById('aiCatalogClearFilters');
    const retryButton = document.getElementById('aiCatalogRetry');
    const results = document.getElementById('aiCatalogResults');
    const loading = document.getElementById('aiCatalogLoading');
    const error = document.getElementById('aiCatalogError');
    const empty = document.getElementById('aiCatalogEmpty');
    const list = document.getElementById('aiCatalogList');
    const summary = document.getElementById('aiCatalogResultSummary');
    const paginationWrap = document.getElementById('aiCatalogPaginationWrap');
    const pagination = document.getElementById('aiCatalogPagination');
    const selectionCount = document.getElementById('aiCatalogSelectionCount');
    const clearSelectionButton = document.getElementById('aiCatalogClearSelection');
    const selectionFeedback = document.getElementById('aiCatalogSelectionFeedback');
    const addSelectedButton = document.getElementById('aiCatalogAddSelected');
    const subcategoryOptions = Array.from(subcategorySelect.options).slice(1).map(function (option) {
        return {
            value: option.value,
            label: option.textContent.trim(),
            categoryId: option.dataset.categoryId || ''
        };
    });

    const statusPresentation = {
        pending: ['Pendiente', 'secondary'],
        prepared: ['Producto preparado', 'success'],
        missing_photo: ['Falta fotografía', 'warning'],
        review: ['Requiere revisión', 'danger']
    };

    let debounceTimer = null;
    let requestController = null;
    let currentPage = 1;
    let loadedOnce = false;
    const selectedProducts = new Map();
    const selectionLimit = 1000;

    function setVisibility(element, visible) {
        element.classList.toggle('d-none', !visible);
    }

    function setLoadingState(isLoading) {
        results.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        setVisibility(loading, isLoading);
        if (isLoading) {
            setVisibility(error, false);
            setVisibility(empty, false);
            list.replaceChildren();
            pagination.replaceChildren();
            setVisibility(paginationWrap, false);
            summary.textContent = '';
        }
    }

    function refreshSubcategories(resetValue) {
        const categoryId = categorySelect.value;
        const currentValue = resetValue ? '' : subcategorySelect.value;
        const fragment = document.createDocumentFragment();
        const allOption = document.createElement('option');
        allOption.value = '';
        allOption.textContent = 'Todas las subcategorías';
        fragment.appendChild(allOption);

        subcategoryOptions
            .filter(function (option) { return !categoryId || option.categoryId === categoryId; })
            .forEach(function (option) {
                const element = document.createElement('option');
                element.value = option.value;
                element.textContent = option.label;
                fragment.appendChild(element);
            });

        subcategorySelect.replaceChildren(fragment);
        subcategorySelect.value = currentValue;
        if (subcategorySelect.value !== currentValue) subcategorySelect.value = '';
    }

    function createTextElement(tag, className, text) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        element.textContent = text || '';
        return element;
    }

    function selectedLabel(count) {
        return count === 1 ? '1 producto seleccionado' : count + ' productos seleccionados';
    }

    function updateSelectionUi() {
        const count = selectedProducts.size;
        selectionCount.textContent = selectedLabel(count);
        clearSelectionButton.disabled = count === 0;
        addSelectedButton.disabled = count === 0;
        addSelectedButton.textContent = count === 1
            ? 'Agregar 1 producto al lote'
            : 'Agregar ' + count + ' productos al lote';
    }

    function parseSkus(value) {
        return String(value || '')
            .split(/[\s,;]+/u)
            .map(function (sku) { return sku.trim(); })
            .filter(Boolean);
    }

    function uniqueSkus(skus) {
        const unique = new Map();
        skus.forEach(function (sku) {
            const normalized = sku.toLocaleLowerCase();
            if (!unique.has(normalized)) unique.set(normalized, sku);
        });
        return Array.from(unique.values());
    }

    function addSelectionToForm() {
        if (selectedProducts.size === 0) return;

        const existingRaw = parseSkus(skusTextarea.value);
        const existingUnique = uniqueSkus(existingRaw);
        const selectedSkus = Array.from(selectedProducts.values()).map(function (product) {
            return product.sku;
        });
        const combined = uniqueSkus(existingUnique.concat(selectedSkus));

        if (combined.length > selectionLimit) {
            selectionFeedback.textContent = 'El lote admite como máximo 1.000 códigos únicos. Quitá algunos antes de agregarlos.';
            return;
        }

        const addedCount = combined.length - existingUnique.length;
        const duplicateCount = existingRaw.length + selectedSkus.length - combined.length;
        skusTextarea.value = combined.join('\n');
        skusTextarea.dispatchEvent(new Event('input', { bubbles: true }));

        formFeedback.textContent = addedCount === 1
            ? 'Se agregó 1 producto del catálogo. Total: ' + combined.length + '.'
            : 'Se agregaron ' + addedCount + ' productos del catálogo. Total: ' + combined.length + '.';
        if (duplicateCount > 0) {
            formFeedback.textContent += ' Se omitieron ' + duplicateCount + ' códigos repetidos.';
        }
        setVisibility(formFeedback, true);

        clearSelection();
        window.bootstrap.Modal.getOrCreateInstance(modal).hide();
        window.setTimeout(function () { skusTextarea.focus(); }, 200);
    }

    function clearSelection() {
        selectedProducts.clear();
        selectionFeedback.textContent = '';
        list.querySelectorAll('.ai-catalog-product').forEach(function (row) {
            row.classList.remove('is-selected');
            const checkbox = row.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        });
        updateSelectionUi();
    }

    function renderProducts(products) {
        const fragment = document.createDocumentFragment();

        products.forEach(function (product) {
            const row = document.createElement('div');
            row.className = 'ai-catalog-product';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input m-0';
            checkbox.checked = selectedProducts.has(String(product.id));
            checkbox.setAttribute('aria-label', 'Seleccionar ' + product.name);
            row.classList.toggle('is-selected', checkbox.checked);

            checkbox.addEventListener('change', function () {
                const productId = String(product.id);
                selectionFeedback.textContent = '';

                if (checkbox.checked) {
                    if (selectedProducts.size >= selectionLimit && !selectedProducts.has(productId)) {
                        checkbox.checked = false;
                        selectionFeedback.textContent = 'La selección admite como máximo 1.000 productos.';
                        return;
                    }

                    selectedProducts.set(productId, {
                        id: product.id,
                        sku: product.sku,
                        name: product.name
                    });
                } else {
                    selectedProducts.delete(productId);
                }

                row.classList.toggle('is-selected', checkbox.checked);
                updateSelectionUi();
            });

            row.addEventListener('click', function (event) {
                if (event.target === checkbox || event.target.closest('button, a, input, select')) return;
                checkbox.click();
            });

            const image = document.createElement('img');
            image.className = 'ai-catalog-product__image';
            image.src = product.image_url;
            image.alt = '';
            image.loading = 'lazy';

            const copy = document.createElement('div');
            copy.className = 'ai-catalog-product__copy';
            const badges = document.createElement('div');
            badges.className = 'd-flex flex-wrap gap-2 mb-1';
            badges.appendChild(createTextElement('span', 'badge bg-light text-dark border', 'SKU: ' + product.sku));
            if (product.ref_code) badges.appendChild(createTextElement('span', 'badge bg-light text-muted border', 'Ref: ' + product.ref_code));
            copy.appendChild(badges);
            copy.appendChild(createTextElement('div', 'ai-catalog-product__name fw-bold', product.name));

            const meta = document.createElement('div');
            meta.className = 'ai-catalog-product__meta mt-1';
            if (product.brand) meta.appendChild(createTextElement('span', '', product.brand));
            if (product.category) meta.appendChild(createTextElement('span', '', product.category));
            if (product.subcategory) meta.appendChild(createTextElement('span', '', product.subcategory));
            copy.appendChild(meta);

            const status = statusPresentation[product.ai_status] || [product.ai_status || 'Sin estado', 'secondary'];
            const statusBadge = createTextElement('span', 'ai-catalog-product__status badge text-bg-' + status[1], status[0]);

            row.appendChild(checkbox);
            row.appendChild(image);
            row.appendChild(copy);
            row.appendChild(statusBadge);
            fragment.appendChild(row);
        });

        list.replaceChildren(fragment);
    }

    function paginationButton(label, page, disabled, active, ariaLabel) {
        const item = document.createElement('li');
        item.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-link';
        button.textContent = label;
        button.disabled = disabled;
        if (ariaLabel) button.setAttribute('aria-label', ariaLabel);
        if (active) button.setAttribute('aria-current', 'page');
        button.addEventListener('click', function () { if (!disabled && !active) loadProducts(page); });
        item.appendChild(button);
        return item;
    }

    function renderPagination(meta) {
        pagination.replaceChildren();
        if (!meta || meta.last_page <= 1) {
            setVisibility(paginationWrap, false);
            return;
        }

        const fragment = document.createDocumentFragment();
        fragment.appendChild(paginationButton('‹', meta.current_page - 1, meta.current_page <= 1, false, 'Página anterior'));
        const firstPage = Math.max(1, meta.current_page - 2);
        const lastPage = Math.min(meta.last_page, meta.current_page + 2);
        for (let page = firstPage; page <= lastPage; page++) {
            fragment.appendChild(paginationButton(String(page), page, false, page === meta.current_page));
        }
        fragment.appendChild(paginationButton('›', meta.current_page + 1, meta.current_page >= meta.last_page, false, 'Página siguiente'));
        pagination.appendChild(fragment);
        setVisibility(paginationWrap, true);
    }

    async function loadProducts(page) {
        currentPage = page || 1;
        if (requestController) requestController.abort();
        const controller = new AbortController();
        requestController = controller;
        setLoadingState(true);

        const url = new URL(modal.dataset.catalogUrl, window.location.origin);
        const filters = {
            search: searchInput.value.trim(),
            brand_id: brandSelect.value,
            category_id: categorySelect.value,
            subcategory_id: subcategorySelect.value,
            ai_preparation_filter: aiStatusSelect.value
        };
        Object.entries(filters).forEach(function ([key, value]) {
            if (value) url.searchParams.set(key, value);
        });
        url.searchParams.set('page', currentPage);
        url.searchParams.set('per_page', '20');

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: controller.signal
            });
            if (!response.ok) throw new Error('Catalog request failed');

            const payload = await response.json();
            const products = Array.isArray(payload.data) ? payload.data : [];
            renderProducts(products);
            renderPagination(payload.meta);
            setVisibility(empty, products.length === 0);
            summary.textContent = payload.meta
                ? 'Mostrando ' + products.length + ' de ' + payload.meta.total + ' productos'
                : '';
            loadedOnce = true;
        } catch (requestError) {
            if (requestError.name === 'AbortError') return;
            setVisibility(error, true);
            summary.textContent = '';
        } finally {
            if (requestController === controller && !controller.signal.aborted) setLoadingState(false);
        }
    }

    searchInput.addEventListener('input', function () {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () { loadProducts(1); }, 400);
    });
    searchInput.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        window.clearTimeout(debounceTimer);
        loadProducts(1);
    });
    brandSelect.addEventListener('change', function () { loadProducts(1); });
    categorySelect.addEventListener('change', function () {
        refreshSubcategories(true);
        loadProducts(1);
    });
    subcategorySelect.addEventListener('change', function () { loadProducts(1); });
    aiStatusSelect.addEventListener('change', function () { loadProducts(1); });
    clearButton.addEventListener('click', function () {
        searchInput.value = '';
        brandSelect.value = '';
        categorySelect.value = '';
        aiStatusSelect.value = '';
        refreshSubcategories(true);
        loadProducts(1);
        searchInput.focus();
    });
    clearSelectionButton.addEventListener('click', clearSelection);
    addSelectedButton.addEventListener('click', addSelectionToForm);
    skusTextarea.addEventListener('input', function () { setVisibility(formFeedback, false); });
    retryButton.addEventListener('click', function () { loadProducts(currentPage); });
    modal.addEventListener('shown.bs.modal', function () {
        if (!loadedOnce) loadProducts(1);
        searchInput.focus();
    });
    modal.addEventListener('hidden.bs.modal', function () {
        window.clearTimeout(debounceTimer);
        if (requestController) requestController.abort();
    });

    updateSelectionUi();
});
</script>
@endpush
