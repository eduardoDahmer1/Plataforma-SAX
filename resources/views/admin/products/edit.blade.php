@extends('layout.admin')

@section('content')
    @php
        // Isso garante que a variável exista para o restante da página
        $galleryImages = [];
        if (isset($item->gallery)) {
            $galleryImages = is_array($item->gallery) ? $item->gallery : json_decode($item->gallery, true);
            $galleryImages = is_array($galleryImages) ? array_values($galleryImages) : [];
        }
    @endphp
    @php
        $type = $type ?? 'product';
    @endphp

    <x-admin.card>
    <x-admin.page-header title="Editar Produto" description="Edite as informações do produto no catálogo"></x-admin.page-header>

        <form action="{{ isset($item) ? route('admin.products.update', $item->id) : route('admin.products.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ request('return_to') }}">

            <div class="row g-4 product-edit-grid">
                @if ($type === 'product')
                    <!-- SKU e Nome Externo -->
                    <div class="col-md-6">
                        <label for="sku" class="form-label"><i class="fas fa-barcode me-1"></i>SKU</label>
                        <input readonly type="text" id="sku" name="sku" class="form-control"
                            value="{{ old('sku', $item->sku ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="external_name" class="form-label"><i class="fas fa-tag me-1"></i>Nome Externo</label>
                        <input readonly type="text" id="external_name" name="external_name" class="form-control"
                            value="{{ old('external_name', $item->external_name ?? '') }}">
                    </div>

                    <!-- Nome -->
                    <div class="col-md-6">
                        <label for="name" class="form-label"><i class="fas fa-pencil-alt me-1"></i>Nome</label>
                        @php $nameValue = old('name') ?? ($item->name ?? ($item->external_name ?? '')); @endphp
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ $nameValue }}">
                    </div>

                    <!-- Marca -->
                    <div class="col-md-6">
                        <label for="brand_id" class="form-label"><i class="fas fa-industry me-1"></i>Marca</label>
                        <select name="brand_id" id="brand_id" class="form-select">
                            <option value="">Selecione uma marca</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ (int) old('brand_id', $item->brand_id ?? 0) === (int) $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name ?: $brand->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categoria / Subcategoria / CategoriasFilhas -->
                    <div class="col-md-4">
                        <label for="category_id" class="form-label"><i class="fas fa-folder me-1"></i>Categoria</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $item->category_id === $category->id ? 'selected' : '' }}>
                                    {{ $category->name ?: $category->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="subcategory_id" class="form-label"><i
                                class="fas fa-folder-open me-1"></i>Subcategoria</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-select" data-subcategories='@json($subcategories)' data-selected="{{ old('subcategory_id', $item->subcategory_id ?? '') }}">
                            <option value="">Selecione uma subcategoria</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}"
                                    {{ $item->subcategory_id === $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name ?: $subcategory->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="categoriasfilhas_id" class="form-label"><i class="fas fa-sitemap me-1"></i>Categorias
                            Filhas</label>
                        <select name="childcategory_id" id="categoriasfilhas_id" class="form-select" data-categoriasfilhas='@json($categoriasfilhas)' data-selected="{{ old('childcategory_id', $item->childcategory_id ?? '') }}">
                            <option value="">Selecione uma categorias filhas</option>
                            @foreach ($categoriasfilhas as $categoriasfilhas)
                                <option value="{{ $categoriasfilhas->id }}"
                                    {{ (int) old('childcategory_id', $item->childcategory_id ?? 0) === (int) $categoriasfilhas->id ? 'selected' : '' }}>
                                    {{ $categoriasfilhas->name ?: $categoriasfilhas->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Preço / Estoque -->
                    <div class="col-md-6">
                        <label for="price" class="form-label"><i class="fas fa-dollar-sign me-1"></i>Preço</label>
                        <input type="number" step="0.01" id="price" name="price" class="form-control"
                            value="{{ old('price', $item->price ?? 0) }}">
                    </div>

                    <div class="col-md-6">
                        <label for="stock" class="form-label"><i class="fas fa-boxes me-1"></i>Estoque</label>
                        <input type="number" id="stock" name="stock" class="form-control"
                            value="{{ old('stock', $item->stock ?? 0) }}">
                    </div>

                    {{-- 2. INTERFACE --}}
                    <div class="col-12">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="photo" class="form-label text-bold">
                                    <i class="fas fa-image me-1"></i>Foto Principal
                                </label>
                                <div class="d-flex align-items-start gap-2">
                                    <input type="file" name="photo" class="form-control" id="photoInput">
                                    @if ($item->photo)
                                        <div class="position-relative border rounded p-1 bg-white"
                                            style="width: 80px; height: 50px;">
                                            <img src="{{ Storage::url($item->photo) }}" class="w-100 h-100"
                                                style="object-fit: cover;">
                                            <button type="button"
                                                class="btn btn-danger btn-xs position-absolute top-0 end-0 m-n1 shadow-sm"
                                                style="padding: 2px 5px; font-size: 10px;"
                                                onclick="if(confirm('Excluir foto principal?')) document.getElementById('deletePhotoForm').submit();">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-bold">
                                    <i class="fas fa-images me-1"></i>Galeria de Imagens
                                </label>
                                <div class="d-flex flex-column gap-2">
                                    <input type="file" name="gallery[]" class="form-control" multiple id="galleryInput">
                                    @if (!empty($galleryImages))
                                        <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal"
                                            data-bs-target="#modalGerenciarGaleria">
                                            <i class="fas fa-tasks me-1"></i> Gerenciar Galeria ({{ count($galleryImages) }}
                                            fotos)
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. MODAL --}}
                    <div class="modal fade" id="modalGerenciarGaleria" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title"><i class="fas fa-images me-2"></i>Gerenciar Galeria</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info py-2" style="font-size: 13px;">
                                        <i class="fas fa-info-circle me-1"></i> Selecione as imagens que deseja remover.
                                    </div>

                                    <div class="d-flex justify-content-between mb-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="toggleSelectAll()">
                                            <i class="fas fa-check-double me-1"></i> Selecionar Todas
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="deleteSelectedImages()">
                                            <i class="fas fa-trash-alt me-1"></i> Excluir Selecionadas
                                        </button>
                                    </div>

                                    <div class="row g-2 overflow-auto" style="max-height: 400px;">
                                        @foreach ($galleryImages as $idx => $img)
                                            <div class="col-4 col-sm-3 col-md-2 position-relative gallery-item">
                                                <label class="d-block border rounded p-1 bg-white cursor-pointer h-100">
                                                    <input type="checkbox" value="{{ basename($img) }}"
                                                        class="gallery-checkbox position-absolute top-0 start-0 m-2">
                                                    <img src="{{ Storage::url($img) }}" class="w-100 rounded"
                                                        style="aspect-ratio: 1/1; object-fit: cover;">
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Container de cores -->
                    <div class="mb-3 col-md-6">
                        <label for="color" class="form-label"><i class="fas fa-palette me-1"></i>Cor do
                            Produto</label>
                        <input type="color" id="color" name="colors_values[]"
                            value="{{ old('color', $item->color ?? '#000000') }}"
                            class="form-control form-control-color">
                        <small class="form-text text-muted">Escolha a cor do produto. A cor será salva
                            automaticamente.</small>
                    </div>

                    <!-- Tamanho do Produto -->
                    @php
                        $sizeOptions = [
                            'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL',
                            '3XL', '4XL', '5XL', '6XL', 'U',
                            '2', '4', '6', '8', '10', '12', '14', '16', '18',
                        ];

                        for ($sizeNumber = 34; $sizeNumber <= 60; $sizeNumber++) {
                            $sizeOptions[] = (string) $sizeNumber;
                        }

                        $sizeOptions = array_values(array_unique(array_merge($sizeOptions, [
                            '3M', '6M', '9M', '12M', '18M',
                            '2Y', '3Y', '4Y', '5Y', '6Y', '8Y', '10Y', '12Y',
                        ])));

                        $currentSize = old('size', $item->size ?? '');
                        $categorySlug = strtolower($item->category->slug ?? '');
                        $excludedSizeCategories = [
                            'optico',
                            'perfumes-and-cosmeticos',
                            'casa',
                            'bebidas',
                            'electronicos',
                            'patrimonio',
                            'propaganda',
                            'joyas-and-relojes',
                            'habanos',
                            'repuestos',
                            'unisex',
                        ];
                        $isFashionCategory = !in_array($categorySlug, $excludedSizeCategories, true)
                            && (
                                in_array($categorySlug, ['masculino', 'feminino', 'infantil'], true)
                                || str_starts_with($categorySlug, 'boss-')
                                || str_starts_with($categorySlug, 'hugo-')
                            );

                        $detectedSize = '';
                        if (preg_match('/#([^\s*]+)/', $item->external_name ?? '', $sizeMatch)) {
                            $detectedSize = trim($sizeMatch[1]);
                        }

                        $looksLikeNonFashionMeasure = (bool) preg_match(
                            '/(?:ML|CM|MM|KG|750|X\d|^\d+X|^H\d+)/i',
                            $detectedSize
                        );
                        $suggestedSize = ($isFashionCategory && !$looksLikeNonFashionMeasure) ? $detectedSize : '';
                        $initialSize = $currentSize ?: $suggestedSize;
                        $hasInitialSizeOption = $initialSize !== '' && in_array($initialSize, $sizeOptions, true);
                    @endphp
                    <div class="col-md-6">
                        <label for="size_select" class="form-label"><i
                                class="fas fa-ruler-combined me-1"></i>Tamanho</label>
                        <input type="hidden" id="size" name="size" value="{{ $initialSize }}">
                        <select id="size_select" class="form-select" data-size-select>
                            <option value="">Selecione o tamanho</option>
                            @if ($initialSize !== '' && !$hasInitialSizeOption)
                                <option value="{{ $initialSize }}" selected>
                                    {{ $currentSize ? 'Atual' : 'Detectada' }}: {{ $initialSize }}
                                </option>
                            @endif
                            @foreach ($sizeOptions as $sizeOption)
                                <option value="{{ $sizeOption }}" {{ $initialSize === $sizeOption ? 'selected' : '' }}>
                                    {{ $sizeOption }}
                                </option>
                            @endforeach
                            <option value="__manual__">Outro / manual</option>
                        </select>
                        <input type="text" id="size_manual" class="form-control mt-2 d-none"
                            value="" placeholder="Digite o tamanho do produto" data-size-manual>
                        @if ($suggestedSize !== '' && $currentSize === '')
                            <small class="form-text text-muted">Detectado desde integração: #{{ $suggestedSize }}</small>
                        @endif
                    </div>

                    <!-- Descrição -->
                    <div class="col-12">
                        <label for="editor-product" class="sax-label"><i
                                class="fas fa-align-left me-1"></i>Descrição</label>
                        <div class="editor-rich-wrapper">
                            <textarea name="description" id="editor-product" class="form-control" rows="5">{{ old('description', $item->description ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="sax-label mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i> Disponible para retirar en tienda
                        </label>

                        <div class="d-flex flex-column gap-2 bg-light p-3 border">
                            {{-- Loja Asunción --}}
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input sax-checkbox me-2" type="checkbox" name="stores[]"
                                    value="asuncion" id="store_asuncion"
                                    {{ isset($item) && is_array($item->stores) && in_array('asuncion', $item->stores) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold x-small tracking-wider text-uppercase"
                                    for="store_asuncion">
                                    Asunción
                                </label>
                            </div>

                            {{-- Loja Ciudad Del Este --}}
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input sax-checkbox me-2" type="checkbox" name="stores[]"
                                    value="cde" id="store_cde"
                                    {{ isset($item) && is_array($item->stores) && in_array('cde', $item->stores) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold x-small tracking-wider text-uppercase"
                                    for="store_cde">
                                    Ciudad Del Este
                                </label>
                            </div>

                            {{-- Loja Pedro Juan Caballero --}}
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input sax-checkbox me-2" type="checkbox" name="stores[]"
                                    value="pjc" id="store_pjc"
                                    {{ isset($item) && is_array($item->stores) && in_array('pjc', $item->stores) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold x-small tracking-wider text-uppercase"
                                    for="store_pjc">
                                    Pedro Juan Caballero
                                </label>
                            </div>
                        </div>
                    </div>

                    @if (!empty($item->parent_id) || (($item->product_role ?? null) === 'F'))
                        @php
                            $currentParentProduct = $item->parent;
                            $currentParentLabel = $currentParentProduct
                                ? ($currentParentProduct->name ?: ($currentParentProduct->external_name ?: ('#' . $item->parent_id)))
                                : ('#' . $item->parent_id);
                        @endphp
                        <div class="col-12 mb-4">
                            <div class="alert alert-warning border mb-0">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="force_as_parent" name="force_as_parent" value="1"
                                        {{ old('force_as_parent') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="force_as_parent">
                                        Convertir este producto nuevamente en P
                                    </label>
                                </div>
                                <small class="d-block text-muted">
                                    Este producto está vinculado como variante de talla de {{ $currentParentLabel }}.
                                    Si activas este switch y guardas, se soltará de esa relación de talla y volverá a
                                    su propio ancla de color.
                                </small>
                            </div>
                        </div>
                    @endif

                    <!-- RELACIONAMENTO POR TALLA -->
                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="fas fa-link me-1"></i>Variantes de talla</label>
                        <div class="input-group mb-2">
                            <input type="text" id="parent_search" class="form-control"
                                placeholder="Buscar variante por talla..." autocomplete="off">
                            <button class="btn btn-primary" id="parent_search_btn" type="button"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <div id="parent_results" class="row g-2" style="display:none; z-index:1000;" data-noimage="{{ asset('storage/uploads/noimage.webp') }}" data-current-product-id="{{ $item->id }}"></div>
                        <div id="selected_parents" class="row g-2 mt-2">
                            @if (!empty($item->selected_size_children))
                                @php
                                    $parentIds = $item->selected_size_children;
                                @endphp
                                @foreach ($parentIds as $pid)
                                    @php $parentProduct = App\Models\Product::find($pid); @endphp
                                    @if ($parentProduct && (int) $parentProduct->id !== (int) $item->id)
                                        <div class="col-6 col-md-4 col-lg-2" data-id="{{ $parentProduct->id }}">
                                            <div class="card border-success h-100 position-relative">
                                                <img src="{{ $parentProduct->photo_url }}"
                                                    class="card-img-top" style="height:120px; object-fit:cover;">
                                                <div class="card-body p-2">
                                                    <p class="card-text m-0 fw-bold">
                                                        {{ $parentProduct->name ?? $parentProduct->external_name }}</p>
                                                    @if ($parentProduct->color)
                                                        <div class="d-flex align-items-center mt-1">
                                                            <span
                                                                style="display:inline-block;width:16px;height:16px;background:{{ $parentProduct->color }};border:1px solid #ccc;margin-right:5px;"></span>
                                                            <small>{{ $parentProduct->color }}</small>
                                                        </div>
                                                    @endif
                                                    @if ($parentProduct->size)
                                                        <div class="mt-1">
                                                            <small class="text-muted">Tamanho:
                                                                {{ $parentProduct->size }}</small>
                                                        </div>
                                                    @endif
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item"><i
                                                            class="fas fa-times"></i></button>
                                                    <input type="hidden" name="parent_id[]"
                                                        value="{{ $parentProduct->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Estas variantes comparten el ancla de talla y heredan los datos comunes del producto base.
                        </small>
                    </div>

                    <!-- FAMÍLIA DE COLOR -->
                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="fas fa-palette me-1"></i>Familia de color</label>
                        <div class="input-group mb-2">
                            <input type="text" id="color_search" class="form-control"
                                placeholder="Buscar producto para la familia de color..." autocomplete="off">
                            <button class="btn btn-primary" id="color_search_btn" type="button"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <div id="color_results" class="row g-2" style="display:none; z-index:1000;" data-noimage="{{ asset('storage/uploads/noimage.webp') }}" data-current-product-id="{{ $item->id }}"></div>
                        <div id="selected_colors" class="row g-2 mt-2">
                            @if (!empty($item->selected_color_family_members))
                                @php
                                    $colorIds = $item->selected_color_family_members;
                                @endphp
                                @foreach ($colorIds as $cid)
                                    @php $colorProduct = App\Models\Product::find($cid); @endphp
                                    @if ($colorProduct && (int) $colorProduct->id !== (int) $item->id)
                                        <div class="col-6 col-md-4 col-lg-2" data-id="{{ $colorProduct->id }}">
                                            <div class="card border-success h-100 position-relative">
                                                <img src="{{ $colorProduct->photo_url }}"
                                                    class="card-img-top" style="height:120px; object-fit:cover;">
                                                <div class="card-body p-2">
                                                    <p class="card-text m-0 fw-bold">
                                                        {{ $colorProduct->name ?? $colorProduct->external_name }}</p>
                                                    @if ($colorProduct->color)
                                                        <div class="d-flex align-items-center mt-1">
                                                            <span
                                                                style="display:inline-block;width:16px;height:16px;background:{{ $colorProduct->color }};border:1px solid #ccc;margin-right:5px;"></span>
                                                            <small>{{ $colorProduct->color }}</small>
                                                        </div>
                                                    @endif
                                                    @if ($colorProduct->size)
                                                        <div class="mt-1">
                                                            <small class="text-muted">Tamanho:
                                                                {{ $colorProduct->size }}</small>
                                                        </div>
                                                    @endif
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item"><i
                                                            class="fas fa-times"></i></button>
                                                    <input type="hidden" name="color_parent_id[]"
                                                        value="{{ $colorProduct->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        @error('color_parent_id')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted d-block mt-2">
                            Los miembros de la familia de color conservan sus propios datos; solo comparten el mismo referente.
                        </small>
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save me-2"></i>Salvar
                Alterações</button>
            <a href="{{ request('return_to') ?: route('admin.products.index') }}"
               class="btn btn-secondary mt-4">
                <i class="fas fa-times me-2"></i>Cancelar
            </a>
        </form>
    {{-- 1. FORMULÁRIOS DE APOIO (Apenas uma vez aqui) --}}
    @if ($item->photo)
        <form action="{{ route('admin.products.deletePhoto', $item->id) }}" method="POST" style="display:none;"
            id="deletePhotoForm">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @php
        $galleryImages = is_array($item->gallery) ? $item->gallery : json_decode($item->gallery, true);
        $galleryImages = is_array($galleryImages) ? array_values($galleryImages) : [];
    @endphp

    @foreach ($galleryImages as $galleryImage)
        <form action="{{ route('admin.products.gallery.delete', [$item->id, basename($galleryImage)]) }}" method="POST"
            style="display:none;" class="deleteGalleryForm">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    {{-- ESTE É O ÚNICO QUE DEVE EXISTIR --}}
    <form id="formMultiDeleteGallery" action="{{ route('admin.products.gallery.multiDelete', $item->id) }}"
        method="POST" style="display:none !important;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="image_names" id="inputImageNames">
    </form>

    <style>
        .product-edit-grid .form-label {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #4a4339;
            margin-bottom: 0.55rem;
        }

        .product-edit-grid .form-control,
        .product-edit-grid .form-select {
            min-height: 48px;
            border-radius: 10px;
            border: 1px solid #ddd6ca;
            background: #fff;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .product-edit-grid .form-control:focus,
        .product-edit-grid .form-select:focus {
            border-color: #b6a58c;
            box-shadow: 0 0 0 0.2rem rgba(182, 165, 140, 0.12);
        }

        .product-edit-grid input[readonly] {
            background: #f6f3ee;
            color: #5e564b;
        }

        .product-edit-grid .form-text {
            color: #7a7166 !important;
            font-size: 0.82rem;
            margin-top: 0.45rem;
        }

        input[type="color"].form-control-color {
            width: 58px;
            height: 42px;
            padding: 0.2rem;
            border: 1px solid #ddd6ca;
            border-radius: 10px;
            cursor: pointer;
        }

        /* Estilo do Label Superior */
        .sax-label {
            font-size: 0.7rem;
            font-weight: 800;
            color: #444;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.12em;
        }

        /* Remove arredondamento e ajusta borda do Editor */
        .tox-tinymce {
            border-radius: 0 !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        /* Ajuste na barra de ferramentas do editor */
        .tox .tox-toolbar__group {
            padding: 0 5px !important;
        }

        .editor-rich-wrapper {
            margin-top: 5px;
        }

        /* Estilo para os checkboxes da SAX */
        .sax-checkbox {
            border-radius: 0 !important;
            /* Bordas retas */
            border: 1px solid #000 !important;
            cursor: pointer;
            width: 1.2em;
            height: 1.2em;
        }

        .sax-checkbox:checked {
            background-color: #000 !important;
            /* Cor preta ao selecionar */
            border-color: #000 !important;
        }

        /* Ajuste para o container cinza claro no fundo */
        .bg-light {
            background-color: #f9f9f9 !important;
        }
    </style>
    </x-admin.card>

@endsection
