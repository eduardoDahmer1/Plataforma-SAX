@extends('layout.admin')

@section('content')

@php
$type = $type ?? 'product';
@endphp
<div class="container">
    <h1>Editar Produto</h1>

    <form action="{{ isset($item) ? route('admin.products.update', ['product' => $item->id]) : '#' }}" method="POST" enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="row">
            {{-- SKU e Nome Externo --}}
            @if($type === 'product')
            <div class="col-md-6 mb-3">
                <label for="sku">SKU</label>
                <input readonly type="text" id="sku" name="sku" class="form-control" value="{{ old('sku', $item->sku ?? '') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="external_name">Nome Externo</label>
                <input readonly type="text" id="external_name" name="external_name" class="form-control" value="{{ old('external_name', $item->external_name ?? '') }}">
            </div>

            {{-- Nome --}}
            <div class="col-md-6 mb-3">
                <label for="name">Nome</label>
                @php
                    $nameValue = old('name') ?? ($item->name ?? ($item->external_name ?? ''));
                @endphp
                <input type="text" id="name" name="name" class="form-control" value="{{ $nameValue }}">
            </div>

            {{-- Marca --}}
            <div class="col-md-6 mb-3">
                <label for="brand_id">Marca</label>
                <select name="brand_id" id="brand_id" class="form-control">
                    <option value="">Selecione uma marca</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (int)old('brand_id', $item->brand_id ?? 0) === (int)$brand->id ? 'selected' : '' }}>
                            {{ $brand->name ?: $brand->slug }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Categoria --}}
            <div class="col-md-6 mb-3">
                <select name="category_id" id="category_id" class="form-control">
                    <option value="">Selecione uma categoria</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $item->category_id === $category->id ? 'selected' : '' }}>
                            {{ $category->name ?: $category->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Subcategoria --}}
            <div class="col-md-6 mb-3">
                <select name="subcategory_id" id="subcategory_id" class="form-control">
                    <option value="">Selecione uma subcategoria</option>
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}" {{ $item->subcategory_id === $subcategory->id ? 'selected' : '' }}>
                            {{ $subcategory->name ?: $subcategory->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Childcategory --}}
            <div class="col-md-6 mb-3">
                <select name="childcategory_id" id="childcategory_id" class="form-control">
                    <option value="">Selecione uma childcategory</option>
                    @foreach($childcategories as $childcategory)
                        <option value="{{ $childcategory->id }}" {{ $item->childcategory_id === $childcategory->id ? 'selected' : '' }}>
                            {{ $childcategory->name ?: $childcategory->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Preço / Estoque --}}
            <div class="col-md-6 mb-3">
                <label for="price">Preço</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control"
                    value="{{ old('price', $item->price ?? '') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label for="stock">Estoque</label>
                <input readonly type="number" id="stock" name="stock" class="form-control"
                    value="{{ old('stock', $item->stock ?? '') }}">
            </div>

            {{-- Foto Principal --}}
            <div class="col-md-6 mb-3">
                <label for="photo">Foto Principal</label>
                <input type="file" name="photo" class="form-control" id="photoInput">
                @if($item->photo)
                    <div class="mt-2" style="position:relative; max-width:220px;">
                        <img src="{{ Storage::url($item->photo) }}" style="width:100%;height:auto;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="document.getElementById('deletePhotoForm').submit()">X</button>
                    </div>
                @endif
            </div>

            {{-- Galeria --}}
            <div class="col-md-6 mb-3">
                <label for="gallery">Galeria</label>
                <input type="file" name="gallery[]" class="form-control" multiple id="galleryInput">
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @php $galleryImages = $item->gallery ? json_decode($item->gallery, true) : []; @endphp
                    @foreach($galleryImages as $index => $galleryImage)
                        <div style="position:relative;">
                            <img src="{{ Storage::url($galleryImage) }}" style="max-width:100px;height:auto;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="document.querySelectorAll('.deleteGalleryForm')[{{ $index }}].submit()">X</button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Descrição --}}
            <div class="col-12 mb-3">
                <label for="description">Descrição</label>
                <textarea name="description" id="editor">{{ old('description', $item->description ?? '') }}</textarea>
            </div>

            @endif
        </div>

        <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
    </form>
</div>

{{-- Form para deletar foto --}}
@if($item->photo)
<form action="{{ $type === 'product'
    ? route('admin.products.deletePhoto', $item->id)
    : route('admin.uploads.deletePhoto', $item->id) }}" method="POST" style="display:none;" id="deletePhotoForm">
    @csrf
    @method('DELETE')
</form>
@endif

{{-- Forms para deletar imagens da galeria --}}
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
@endsection