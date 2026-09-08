@props([
    'mode' => 'create',
    'batch' => null,
    'existingSkus' => [],
    'brands' => collect(),
    'categories' => collect(),
    'subcategories' => collect(),
])

@php
    $isAppend = $mode === 'append';
    $prefix = $isAppend ? 'aiBatchCatalog' : 'aiCatalog';
    $modalId = $prefix.'Modal';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true"
    data-catalog-url="{{ route('admin.products.ai-batches.catalog-products') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold" id="{{ $modalId }}Title">{{ $isAppend ? 'Agregar productos al lote' : 'Seleccionar productos del catálogo' }}</h5>
                    <p class="small text-muted mb-0">{{ $isAppend ? 'Los productos que ya pertenecen al lote aparecen bloqueados.' : 'Buscá y filtrá productos para agregarlos al lote.' }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="ai-catalog-selector__toolbar mb-4">
                    <label for="{{ $prefix }}Search" class="visually-hidden">Buscar productos</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="fa fa-magnifying-glass text-muted"></i></span>
                        <input type="search" id="{{ $prefix }}Search" class="form-control" placeholder="Buscar por SKU, nombre o referencia…" autocomplete="off">
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6 col-xl-3"><select id="{{ $prefix }}Brand" class="form-select" aria-label="Marca"><option value="">Todas las marcas</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-6 col-xl-3"><select id="{{ $prefix }}Category" class="form-select" aria-label="Categoría"><option value="">Todas las categorías</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-6 col-xl-3"><select id="{{ $prefix }}Subcategory" class="form-select" aria-label="Subcategoría"><option value="">Todas las subcategorías</option>@foreach($subcategories as $subcategory)<option value="{{ $subcategory->id }}" data-category-id="{{ $subcategory->category_id }}">{{ $subcategory->name }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-6 col-xl-3"><select id="{{ $prefix }}Status" class="form-select" aria-label="Preparación con IA"><option value="">Todos los estados</option><option value="pending">Pendiente</option><option value="prepared">Producto preparado</option><option value="missing_photo">Falta fotografía</option><option value="review">Requiere revisión</option></select></div>
                    </div>
                    <div class="text-end mt-3"><button type="button" id="{{ $prefix }}ClearFilters" class="btn btn-sm btn-link text-dark text-decoration-none">Limpiar filtros</button></div>
                </div>

                <div class="d-flex justify-content-between mb-2"><span id="{{ $prefix }}Summary" class="small text-muted" aria-live="polite"></span><span class="small text-muted">20 por página</span></div>
                <div id="{{ $prefix }}Loading" class="text-center py-5 d-none"><div class="spinner-border text-dark" role="status"><span class="visually-hidden">Cargando…</span></div></div>
                <div id="{{ $prefix }}Error" class="alert alert-danger d-none">No pudimos cargar los productos del catálogo. <button type="button" class="btn btn-sm btn-outline-danger">Reintentar</button></div>
                <div id="{{ $prefix }}Empty" class="text-center text-muted py-5 d-none">No encontramos productos.</div>
                <div id="{{ $prefix }}List" class="ai-catalog-selector__list"></div>
                <nav class="mt-4"><ul id="{{ $prefix }}Pagination" class="pagination pagination-sm justify-content-center mb-0"></ul></nav>
            </div>

            <div class="modal-footer px-4 py-3 gap-2">
                <div class="me-auto">
                    <strong id="{{ $prefix }}SelectionCount" class="d-block" aria-live="polite">0 productos seleccionados</strong>
                    <button type="button" id="{{ $prefix }}ClearSelection" class="btn btn-sm btn-link text-danger text-decoration-none p-0" disabled>Limpiar selección</button>
                    <small id="{{ $prefix }}SelectionFeedback" class="text-danger"></small>
                </div>
                <button type="button" class="btn btn-outline-dark px-4" data-bs-dismiss="modal">Cancelar</button>
                @if($isAppend)
                    <form id="{{ $prefix }}Form" method="POST" action="{{ route('admin.products.ai-batches.products.add', $batch) }}">
                        @csrf
                        <input type="hidden" name="skus" id="{{ $prefix }}Skus">
                        <button type="submit" id="{{ $prefix }}Submit" class="btn btn-dark px-4" disabled>Agregar productos</button>
                    </form>
                @else
                    <button type="button" id="{{ $prefix }}Submit" class="btn btn-dark px-4" disabled>Agregar 0 productos al lote</button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #{{ $modalId }} .modal-dialog { max-width: min(1180px, calc(100vw - 2rem)); }
    #{{ $modalId }} .modal-body { background: #f8fafc; }
    .ai-catalog-selector__toolbar { padding: 1rem; background: #fff; border: 1px solid #e1e6ee; border-radius: 14px; }
    .ai-catalog-selector__list { display: grid; gap: .65rem; }
    .ai-catalog-selector__product { display: grid; grid-template-columns: auto 58px minmax(0, 1fr) auto; gap: .85rem; align-items: center; padding: .75rem; background: #fff; border: 1px solid #e1e6ee; border-radius: 12px; cursor: pointer; }
    .ai-catalog-selector__product.is-selected { border-color: #212529; background: #f3f5f7; }
    .ai-catalog-selector__product.is-in-batch { cursor: default; opacity: .7; }
    .ai-catalog-selector__product img { width: 58px; height: 58px; object-fit: cover; border-radius: 9px; background: #eef1f5; }
    @media (max-width: 767.98px) { #{{ $modalId }} .modal-dialog { max-width: none; margin: .5rem; } .ai-catalog-selector__product { grid-template-columns: auto 50px minmax(0, 1fr); } .ai-catalog-selector__product img { width: 50px; height: 50px; } .ai-catalog-selector__product__status { grid-column: 2 / -1; justify-self: start; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById(@json($modalId));
    if (!modal) return;
    const prefix = @json($prefix), isAppend = @json($isAppend);
    const existingSkus = new Set(@json($existingSkus).map(function (sku) { return String(sku).toLocaleLowerCase(); }));
    const selected = new Map(), get = function (name) { return document.getElementById(prefix + name); };
    const search = get('Search'), brand = get('Brand'), category = get('Category'), subcategory = get('Subcategory'), status = get('Status');
    const list = get('List'), loading = get('Loading'), error = get('Error'), empty = get('Empty'), summary = get('Summary'), pagination = get('Pagination');
    const selectionCount = get('SelectionCount'), clearSelection = get('ClearSelection'), feedback = get('SelectionFeedback'), submit = get('Submit');
    const skuInput = isAppend ? get('Skus') : document.getElementById('aiBatchSkus');
    const formFeedback = document.getElementById('aiCatalogFormFeedback');
    const subcategoryOptions = Array.from(subcategory.options).slice(1).map(function (option) { return { value: option.value, label: option.textContent.trim(), categoryId: option.dataset.categoryId || '' }; });
    const statusLabels = { pending: ['Pendiente', 'secondary'], prepared: ['Producto preparado', 'success'], missing_photo: ['Falta fotografía', 'warning'], review: ['Requiere revisión', 'danger'] };
    let timer = null, controller = null, currentPage = 1, loaded = false;

    function show(element, visible) { element.classList.toggle('d-none', !visible); }
    function updateSelection() {
        const total = selected.size;
        selectionCount.textContent = total === 1 ? '1 producto seleccionado' : total + ' productos seleccionados';
        submit.disabled = total === 0;
        clearSelection.disabled = total === 0;
        if (isAppend) skuInput.value = Array.from(selected.values()).join('\n');
        else submit.textContent = total === 1 ? 'Agregar 1 producto al lote' : 'Agregar ' + total + ' productos al lote';
    }
    function text(tag, className, value) { const element = document.createElement(tag); element.className = className; element.textContent = value || ''; return element; }
    function refreshSubcategories() {
        const fragment = document.createDocumentFragment(); const first = document.createElement('option'); first.value = ''; first.textContent = 'Todas las subcategorías'; fragment.appendChild(first);
        subcategoryOptions.filter(function (option) { return !category.value || option.categoryId === category.value; }).forEach(function (option) { const item = document.createElement('option'); item.value = option.value; item.textContent = option.label; fragment.appendChild(item); });
        subcategory.replaceChildren(fragment);
    }
    function renderProducts(products) {
        const fragment = document.createDocumentFragment();
        products.forEach(function (product) {
            const key = String(product.sku).toLocaleLowerCase(), inBatch = existingSkus.has(key), row = document.createElement('div');
            row.className = 'ai-catalog-selector__product' + (inBatch ? ' is-in-batch' : '') + (selected.has(key) ? ' is-selected' : '');
            const checkbox = document.createElement('input'); checkbox.type = 'checkbox'; checkbox.className = 'form-check-input m-0'; checkbox.disabled = inBatch; checkbox.checked = selected.has(key);
            const image = document.createElement('img'); image.src = product.image_url; image.alt = ''; image.loading = 'lazy';
            const copy = document.createElement('div'); copy.className = 'min-w-0'; copy.append(text('span', 'badge bg-light text-dark border mb-1', 'SKU: ' + product.sku), text('div', 'fw-bold text-truncate', product.name), text('small', 'text-muted', [product.brand, product.category, product.subcategory].filter(Boolean).join(' · ')));
            const state = statusLabels[product.ai_status] || [product.ai_status || 'Sin estado', 'secondary'];
            const badge = text('span', 'ai-catalog-selector__product__status badge ' + (inBatch ? 'text-bg-secondary' : 'text-bg-' + state[1]), inBatch ? 'Ya está en el lote' : state[0]);
            checkbox.addEventListener('change', function () { feedback.textContent = ''; if (checkbox.checked) selected.set(key, product.sku); else selected.delete(key); row.classList.toggle('is-selected', checkbox.checked); updateSelection(); });
            row.addEventListener('click', function (event) { if (!inBatch && event.target !== checkbox) checkbox.click(); });
            row.append(checkbox, image, copy, badge); fragment.appendChild(row);
        });
        list.replaceChildren(fragment);
    }
    function renderPagination(meta) {
        pagination.replaceChildren(); if (!meta || meta.last_page <= 1) return;
        for (let page = Math.max(1, meta.current_page - 2); page <= Math.min(meta.last_page, meta.current_page + 2); page++) { const item = document.createElement('li'); item.className = 'page-item' + (page === meta.current_page ? ' active' : ''); const button = document.createElement('button'); button.type = 'button'; button.className = 'page-link'; button.textContent = page; button.addEventListener('click', function () { load(page); }); item.appendChild(button); pagination.appendChild(item); }
    }
    async function load(page) {
        currentPage = page || 1; if (controller) controller.abort(); const requestController = new AbortController(); controller = requestController; show(loading, true); show(error, false); show(empty, false); list.replaceChildren();
        const url = new URL(modal.dataset.catalogUrl, window.location.origin); const filters = { search: search.value.trim(), brand_id: brand.value, category_id: category.value, subcategory_id: subcategory.value, ai_preparation_filter: status.value };
        Object.entries(filters).forEach(function (entry) { if (entry[1]) url.searchParams.set(entry[0], entry[1]); }); url.searchParams.set('page', currentPage); url.searchParams.set('per_page', '20');
        try { const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: requestController.signal }); if (!response.ok) throw new Error('Catalog request failed'); const payload = await response.json(); const products = Array.isArray(payload.data) ? payload.data : []; renderProducts(products); renderPagination(payload.meta); show(empty, products.length === 0); summary.textContent = payload.meta ? 'Mostrando ' + products.length + ' de ' + payload.meta.total + ' productos' : ''; loaded = true; } catch (requestError) { if (requestError.name !== 'AbortError') show(error, true); } finally { if (controller === requestController) show(loading, false); }
    }
    [brand, subcategory, status].forEach(function (element) { element.addEventListener('change', function () { load(1); }); });
    category.addEventListener('change', function () { refreshSubcategories(); load(1); });
    search.addEventListener('input', function () { window.clearTimeout(timer); timer = window.setTimeout(function () { load(1); }, 400); });
    get('ClearFilters').addEventListener('click', function () { search.value = ''; brand.value = ''; category.value = ''; status.value = ''; refreshSubcategories(); load(1); });
    error.querySelector('button').addEventListener('click', function () { load(currentPage); });
    submit.addEventListener('click', function () {
        // TODO: valorar guardado AJAX para actualizar el borrador sin recargar la página.
        if (isAppend) { submit.disabled = true; submit.closest('form').submit(); return; }
        const unique = new Map(); String(skuInput.value || '').split(/[\s,;]+/u).concat(Array.from(selected.values())).forEach(function (sku) { sku = sku.trim(); if (sku && !unique.has(sku.toLocaleLowerCase())) unique.set(sku.toLocaleLowerCase(), sku); });
        if (unique.size > 1000) { feedback.textContent = 'El lote admite como máximo 1.000 códigos únicos.'; return; }
        skuInput.value = Array.from(unique.values()).join('\n'); skuInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (formFeedback) { formFeedback.textContent = 'Se agregaron productos del catálogo. Total: ' + unique.size + '.'; show(formFeedback, true); }
        selected.clear(); updateSelection(); window.bootstrap.Modal.getOrCreateInstance(modal).hide();
    });
    clearSelection.addEventListener('click', function () {
        selected.clear(); feedback.textContent = ''; list.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) { checkbox.checked = false; });
        list.querySelectorAll('.is-selected').forEach(function (row) { row.classList.remove('is-selected'); }); updateSelection();
    });
    modal.addEventListener('shown.bs.modal', function () { if (!loaded) load(1); search.focus(); });
    modal.addEventListener('hidden.bs.modal', function () { window.clearTimeout(timer); if (controller) controller.abort(); });
    updateSelection();
});
</script>
@endpush
