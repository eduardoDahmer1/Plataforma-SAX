@extends('layout.admin')

@section('content')

@php
$type = $type ?? 'product';
@endphp
<div class="container mt-5">
    <h1 class="mb-4"><i class="fas fa-box-open me-2"></i>Editar Produto</h1>

    <form action="{{ isset($item) ? route('admin.products.update', ['product' => $item->id]) : '#' }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            @if($type === 'product')
            <!-- SKU e Nome Externo -->
            <div class="col-md-6">
                <label for="sku" class="form-label"><i class="fas fa-barcode me-1"></i>SKU</label>
                <input readonly type="text" id="sku" name="sku" class="form-control" value="{{ old('sku', $item->sku ?? '') }}">
            </div>

            <div class="col-md-6">
                <label for="external_name" class="form-label"><i class="fas fa-tag me-1"></i>Nome Externo</label>
                <input readonly type="text" id="external_name" name="external_name" class="form-control" value="{{ old('external_name', $item->external_name ?? '') }}">
            </div>

            <!-- Nome -->
            <div class="col-md-6">
                <label for="name" class="form-label"><i class="fas fa-pencil-alt me-1"></i>Nome</label>
                @php $nameValue = old('name') ?? ($item->name ?? ($item->external_name ?? '')); @endphp
                <input type="text" id="name" name="name" class="form-control" value="{{ $nameValue }}">
            </div>

            <!-- Marca -->
            <div class="col-md-6">
                <label for="brand_id" class="form-label"><i class="fas fa-industry me-1"></i>Marca</label>
                <select name="brand_id" id="brand_id" class="form-select">
                    <option value="">Selecione uma marca</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (int)old('brand_id', $item->brand_id ?? 0) === (int)$brand->id ? 'selected' : '' }}>
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
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $item->category_id === $category->id ? 'selected' : '' }}>
                            {{ $category->name ?: $category->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="subcategory_id" class="form-label"><i class="fas fa-folder-open me-1"></i>Subcategoria</label>
                <select name="subcategory_id" id="subcategory_id" class="form-select">
                    <option value="">Selecione uma subcategoria</option>
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}" {{ $item->subcategory_id === $subcategory->id ? 'selected' : '' }}>
                            {{ $subcategory->name ?: $subcategory->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="childcategory_id" class="form-label"><i class="fas fa-sitemap me-1"></i>Childcategory</label>
                <select name="childcategory_id" id="childcategory_id" class="form-select">
                    <option value="">Selecione uma childcategory</option>
                    @foreach($childcategories as $childcategory)
                        <option value="{{ $childcategory->id }}" {{ $item->childcategory_id === $childcategory->id ? 'selected' : '' }}>
                            {{ $childcategory->name ?: $childcategory->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Preço / Estoque -->
            <div class="col-md-6">
                <label for="price" class="form-label"><i class="fas fa-dollar-sign me-1"></i>Preço</label>
                <input type="text" id="price" name="price" class="form-control" value="{{ currency_format(old('price', $item->price ?? 0)) }}" readonly>
            </div>

            <div class="col-md-6">
                <label for="stock" class="form-label"><i class="fas fa-boxes me-1"></i>Estoque</label>
                <input readonly type="number" id="stock" name="stock" class="form-control" value="{{ old('stock', $item->stock ?? '') }}">
            </div>

            <!-- Foto Principal -->
            <div class="col-md-6">
                <label for="photo" class="form-label"><i class="fas fa-image me-1"></i>Foto Principal</label>
                <input type="file" name="photo" class="form-control" id="photoInput">
                @if($item->photo)
                <div class="mt-2 position-relative" style="max-width:220px;">
                    <img src="{{ Storage::url($item->photo) }}" class="img-thumbnail">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="document.getElementById('deletePhotoForm').submit()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @endif
            </div>

            <!-- Galeria -->
            <div class="col-md-6">
                <label for="gallery" class="form-label"><i class="fas fa-images me-1"></i>Galeria</label>
                <input type="file" name="gallery[]" class="form-control" multiple id="galleryInput">
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @php $galleryImages = $item->gallery ? json_decode($item->gallery, true) : []; @endphp
                    @foreach($galleryImages as $index => $galleryImage)
                        <div class="position-relative" style="max-width:100px;">
                            <img src="{{ Storage::url($galleryImage) }}" class="img-thumbnail">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="document.querySelectorAll('.deleteGalleryForm')[{{ $index }}].submit()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Descrição -->
            <div class="col-12">
                <label for="description" class="form-label"><i class="fas fa-align-left me-1"></i>Descrição</label>
                <textarea name="description" id="editor" class="form-control" rows="5">{{ old('description', $item->description ?? '') }}</textarea>
            </div>

            @endif
        </div>

        <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save me-2"></i>Salvar Alterações</button>
    </form>
</div>

{{-- Forms para deletar fotos --}}
@if($item->photo)
<form action="{{ $type === 'product'
    ? route('admin.products.deletePhoto', $item->id)
    : route('admin.uploads.deletePhoto', $item->id) }}" method="POST" style="display:none;" id="deletePhotoForm">
    @csrf
    @method('DELETE')
</form>
@endif

@if(!empty($galleryImages))
    @foreach($galleryImages as $galleryImage)
    <form action="{{ $type === 'product'
        ? route('admin.products.deleteGalleryImage', [$item->id, basename($galleryImage)])
        : route('admin.uploads.deleteGalleryImage', [$item->id, basename($galleryImage)]) }}" 
          method="POST" style="display:none;" class="deleteGalleryForm">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const childSelect = document.getElementById('childcategory_id');

    categorySelect?.addEventListener('change', function() {
        fetch(`/admin/subcategories/${this.value}`)
            .then(res => res.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Selecione uma subcategoria</option>';
                childSelect.innerHTML = '<option value="">Selecione uma childcategory</option>'; // limpa child
                data.forEach(sc => {
                    subcategorySelect.innerHTML += `<option value="${sc.id}">${sc.name}</option>`;
                });
            });
    });

    subcategorySelect?.addEventListener('change', function() {
        fetch(`/admin/childcategories/${this.value}`)
            .then(res => res.json())
            .then(data => {
                childSelect.innerHTML = '<option value="">Selecione uma childcategory</option>';
                data.forEach(cc => {
                    childSelect.innerHTML += `<option value="${cc.id}">${cc.name}</option>`;
                });
            });
    });
});
</script>

@endsection
