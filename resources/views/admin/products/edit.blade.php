@extends('layout.admin')

@section('content')
    @php
        $type = $type ?? 'product';
    @endphp

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fas fa-box-open me-2"></i>Editar Produto</h1>

        <form action="{{ isset($item) ? route('admin.products.update', $item->id) : route('admin.products.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
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

                    <!-- Categoria / Subcategoria / Childcategory -->
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
                        <select name="subcategory_id" id="subcategory_id" class="form-select">
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
                        <label for="childcategory_id" class="form-label"><i
                                class="fas fa-sitemap me-1"></i>Childcategory</label>
                        <select name="childcategory_id" id="childcategory_id" class="form-select">
                            <option value="">Selecione uma childcategory</option>
                            @foreach ($childcategories as $childcategory)
                                <option value="{{ $childcategory->id }}"
                                    {{ $item->childcategory_id === $childcategory->id ? 'selected' : '' }}>
                                    {{ $childcategory->name ?: $childcategory->slug }}
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

                    <!-- Foto Principal -->
                    <div class="col-md-6">
                        <label for="photo" class="form-label"><i class="fas fa-image me-1"></i>Foto Principal</label>
                        <input type="file" name="photo" class="form-control" id="photoInput">
                        @if ($item->photo)
                            <div class="mt-2 position-relative">
                                <img src="{{ Storage::url($item->photo) }}" class="slider-product">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                    onclick="document.getElementById('deletePhotoForm').submit()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Galeria -->
                    <div class="col-md-6">
                        <label for="gallery" class="form-label">
                            <i class="fas fa-images me-1"></i>Galeria
                        </label>
                        <input type="file" name="gallery[]" class="form-control" multiple id="galleryInput">

                        <div id="galleryCarousel" class="carousel slide mt-2" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @php
                                    $galleryImages = [];

                                    if ($item->gallery) {
                                        if (is_array($item->gallery)) {
                                            $galleryImages = $item->gallery;
                                        } elseif (is_string($item->gallery)) {
                                            $decoded = json_decode($item->gallery, true);
                                            $galleryImages = is_array($decoded) ? $decoded : [];
                                        }
                                    }
                                @endphp

                                @foreach ($galleryImages as $index => $galleryImage)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ Storage::url($galleryImage) }}"
                                            class="d-block w-100 rounded slider-product"
                                            alt="Imagem {{ $index + 1 }}">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="document.querySelectorAll('.deleteGalleryForm')[{{ $index }}].submit()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if (count($galleryImages) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
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
                    <div class="col-md-6">
                        <label for="size" class="form-label"><i
                                class="fas fa-ruler-combined me-1"></i>Tamanho</label>
                        <input type="text" id="size" name="size" class="form-control"
                            value="{{ old('size', $item->size ?? '') }}" placeholder="Digite o tamanho do produto">
                    </div>

                    <!-- Descrição -->
                    <div class="col-12">
                        <label for="description" class="form-label"><i
                                class="fas fa-align-left me-1"></i>Descrição</label>
                        <textarea name="description" id="editor" class="form-control" rows="5">{{ old('description', $item->description ?? '') }}</textarea>
                    </div>

                    <!-- RELACIONAMENTO POR TAMANHO -->
                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="fas fa-link me-1"></i>Produtos Pai (Tamanho)</label>
                        <div class="input-group mb-2">
                            <input type="text" id="parent_search" class="form-control"
                                placeholder="Buscar produto por tamanho..." autocomplete="off">
                            <button class="btn btn-primary" id="parent_search_btn" type="button"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <div id="parent_results" class="row g-2" style="display:none; z-index:1000;"></div>
                        <div id="selected_parents" class="row g-2 mt-2">
                            @if (!empty($item->parent_id))
                                @php
                                    $parentIds = is_array($item->parent_id)
                                        ? $item->parent_id
                                        : explode(',', $item->parent_id);
                                @endphp
                                @foreach ($parentIds as $pid)
                                    @php $parentProduct = App\Models\Product::find($pid); @endphp
                                    @if ($parentProduct)
                                        <div class="col-6 col-md-4 col-lg-2" data-id="{{ $parentProduct->id }}">
                                            <div class="card border-success h-100 position-relative">
                                                <img src="{{ $parentProduct->photo ? Storage::url($parentProduct->photo) : '/images/no-image.png' }}"
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
                    </div>

                    <!-- RELACIONAMENTO POR COR -->
                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="fas fa-palette me-1"></i>Produtos Pai (Cor)</label>
                        <div class="input-group mb-2">
                            <input type="text" id="color_search" class="form-control"
                                placeholder="Buscar produto por cor..." autocomplete="off">
                            <button class="btn btn-primary" id="color_search_btn" type="button"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <div id="color_results" class="row g-2" style="display:none; z-index:1000;"></div>
                        <div id="selected_colors" class="row g-2 mt-2">
                            @if (!empty($item->color_parent_id))
                                @php
                                    $colorIds = is_array($item->color_parent_id)
                                        ? $item->color_parent_id
                                        : explode(',', $item->color_parent_id);
                                @endphp
                                @foreach ($colorIds as $cid)
                                    @php $colorProduct = App\Models\Product::find($cid); @endphp
                                    @if ($colorProduct)
                                        <div class="col-6 col-md-4 col-lg-2" data-id="{{ $colorProduct->id }}">
                                            <div class="card border-success h-100 position-relative">
                                                <img src="{{ $colorProduct->photo ? Storage::url($colorProduct->photo) : '/images/no-image.png' }}"
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
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save me-2"></i>Salvar
                Alterações</button>
        </form>
    </div>

    {{-- Forms para deletar fotos --}}
    @if ($item->photo)
        <form
            action="{{ $type === 'product' ? route('admin.products.deletePhoto', $item->id) : route('admin.uploads.deletePhoto', $item->id) }}"
            method="POST" style="display:none;" id="deletePhotoForm">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @if (!empty($galleryImages))
        @foreach ($galleryImages as $galleryImage)
            <form
                action="{{ $type === 'product' ? route('admin.products.deleteGalleryImage', [$item->id, basename($galleryImage)]) : route('admin.uploads.deleteGalleryImage', [$item->id, basename($galleryImage)]) }}"
                method="POST" style="display:none;" class="deleteGalleryForm">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {

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
                        .then(res => res.json())
                        .then(data => {
                            let html = '';
                            if (data.length) {
                                data.forEach(product => {
                                    const alreadySelected = Array.from(selectedDiv.querySelectorAll(
                                            `input[name="${hiddenName}[]"]`))
                                        .some(input => input.value == product.id);

                                    html += `
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <div class="card h-100 card-hover ${alreadySelected ? 'border-success selected' : ''}" 
                                                style="cursor:pointer;" data-id="${product.id}"
                                                data-color="${product.color || ''}" data-size="${product.size || ''}">
                                                <img src="${product.photo || '{{ asset('storage/uploads/noimage.webp') }}'}" 
                                                    class="img-fluid object-fit-cover" alt="${product.name || product.external_name}">
                                                <div class="card-body p-2">
                                                    <p class="card-text m-0 fw-bold">${product.name || product.external_name}</p>
                                                    ${product.color ? `<div class="d-flex align-items-center mt-1">
                                                                <span style="display:inline-block;width:16px;height:16px;background:${product.color};border:1px solid #ccc;margin-right:5px;"></span>
                                                                <small>${product.color}</small>
                                                            </div>` : ''}
                                                    ${product.size ? `<div class="mt-1"><small class="text-muted">Tamanho: ${product.size}</small></div>` : ''}
                                                </div>
                                            </div>
                                        </div>`;
                                });
                            } else {
                                html =
                                    '<div class="col-12"><div class="alert alert-info m-0">Nenhum produto encontrado</div></div>';
                            }
                            resultsDiv.innerHTML = html;
                            resultsDiv.style.display = 'flex';
                            resultsDiv.style.flexWrap = 'wrap';
                        })
                        .catch(err => console.error('Falha na busca de produtos:', err));
                }

                searchBtn.addEventListener('click', searchProducts);
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchProducts();
                    }
                });

                resultsDiv.addEventListener('click', function(e) {
                    const card = e.target.closest('.card');
                    if (!card) return;
                    const id = card.getAttribute('data-id');
                    if (selectedDiv.querySelector(`div[data-id="${id}"]`)) return;

                    const name = card.querySelector('.card-text').textContent;
                    const imgSrc = card.querySelector('img').src;
                    const color = card.getAttribute('data-color');
                    const size = card.getAttribute('data-size');

                    const newCard = document.createElement('div');
                    newCard.className = 'col-6 col-md-4 col-lg-2';
                    newCard.setAttribute('data-id', id);
                    newCard.innerHTML = `
                        <div class="card border-success h-100 position-relative">
                            <img src="${imgSrc}" class="card-img-top" style="height:120px; object-fit:cover;">
                            <div class="card-body p-2">
                                <p class="card-text m-0 fw-bold">${name}</p>
                                ${color ? `<div class="d-flex align-items-center mt-1">
                                            <span style="display:inline-block;width:16px;height:16px;background:${color};border:1px solid #ccc;margin-right:5px;"></span>
                                            <small>${color}</small>
                                        </div>` : ''}
                                ${size ? `<div class="mt-1"><small class="text-muted">Tamanho: ${size}</small></div>` : ''}
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item">
                                    <i class="fas fa-times"></i>
                                </button>
                                <input type="hidden" name="${hiddenName}[]" value="${id}">
                            </div>
                        </div>`;
                    selectedDiv.appendChild(newCard);
                });

                selectedDiv.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-item')) {
                        const card = e.target.closest('[data-id]');
                        card.remove();
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!e.target.closest(`#${inputId}, #${resultsId}, #${btnId}`)) {
                        resultsDiv.style.display = 'none';
                    }
                });
            }

            // Configura busca para Tamanho
            setupSearch('parent_search', 'parent_search_btn', 'parent_results', 'selected_parents', 'parent_id',
                '/admin/products/search');

            // Configura busca para Cor
            setupSearch('color_search', 'color_search_btn', 'color_results', 'selected_colors', 'color_parent_id',
                '/admin/products/search');

        });
    </script>
    <style>
        input[type="color"].form-control-color {
            width: 50px;
            height: 35px;
            padding: 0;
            border: 1px solid #ccc;
            cursor: pointer;
        }
    </style>

@endsection
