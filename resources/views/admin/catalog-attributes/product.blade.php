@php($attributeCatalog = app(\App\Services\CatalogAttributeService::class))
@if($attributeCatalog->enabledFor($item))
@php($attributeProtected = \App\Models\ProductAttributeValue::where('product_id', $item->id)->where('source', 'manual')->pluck('attribute_id'))
<details class="col-12 product-attributes mb-3" id="catalogProductAttributes" @if($errors->has('catalog_values') || $errors->has('catalog_values.*') || old('catalog_values')) open @endif
         data-base-url="{{ route('admin.catalog-attributes.index') }}" data-translate-url="{{ route('admin.catalog-attributes.translate') }}"
         data-ai-url="{{ route('admin.products.catalog-attributes.complete', $item) }}" data-product-id="{{ $item->id }}" data-product-sku="{{ $item->sku }}">
    <summary class="product-attributes__summary">
        <span class="product-attributes__heading"><span>Atributos</span><span class="product-attributes__count" data-attribute-count>Sin atributos asignados</span><i class="fas fa-chevron-down product-attributes__chevron" aria-hidden="true"></i></span>
        <span class="product-attributes__preview" data-attribute-preview hidden></span>
    </summary>
    <div class="product-attributes__body">
        <div class="alert d-none" data-attribute-feedback role="status"></div>
        <div class="product-attributes__toolbar">
            <div class="dropdown product-attributes__selector">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" id="catalogAttributePicker" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Agregar atributo</button>
                <div class="dropdown-menu product-attributes__picker" data-attribute-picker aria-labelledby="catalogAttributePicker">
                    <label class="visually-hidden" for="catalogAttributeSearch">Buscar atributo en el diccionario</label>
                    <input id="catalogAttributeSearch" type="search" class="form-control form-control-sm" data-attribute-selector-search placeholder="Buscar atributo…" autocomplete="off">
                    <div class="product-attributes__choices">
                        <div data-attribute-picker-items></div>
                        <p class="small text-muted p-2 mb-0" data-attribute-picker-empty hidden>No hay coincidencias.</p>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-link" data-create-attribute>Crear atributo</button>
            <button type="button" class="btn btn-sm btn-link" data-manage-attributes>Gestionar diccionario</button>
        </div>
        <p class="product-attributes__hint">Seleccioná un valor para editarlo. Las opciones del diccionario y la herencia están en el menú de cada atributo.</p>
        <div class="product-attributes__grid" data-product-attribute-rows></div>
        <div class="product-attributes__footer">
            <span>Las asignaciones se guardan con el producto.</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-complete-attributes><i class="fas fa-wand-magic-sparkles me-1" aria-hidden="true"></i>Completar faltantes con IA</button>
        </div>
    </div>
    <script type="application/json" data-attribute-dictionary>@json($attributeCatalog->editorDictionary($item, (array) old('catalog_values', [])))</script>
    <script type="application/json" data-attribute-effective>@json($attributeCatalog->effective($item))</script>
    <script type="application/json" data-attribute-protected>@json($attributeProtected)</script>
    <script type="application/json" data-attribute-old>@json(old('catalog_values', []))</script>
</details>
@endif
