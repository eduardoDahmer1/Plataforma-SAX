@extends('layout.admin')

@section('content')
<div class="container">

    <h1>Editar Produto</h1>

    <form action="{{ $type === 'product' 
        ? route('admin.products.update', $item->id) 
        : route('admin.uploads.update', $item->id) }}" method="POST" enctype="multipart/form-data" id="editForm">

        @csrf
        @method('PUT')

        @if($type === 'product')
        <div class="form-group">
            <label for="sku">SKU</label>
            <input readonly type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku ?? '') }}">
        </div>

        <div class="form-group">
            <label for="external_name">Nome Externo</label>
            <input type="text" name="external_name" class="form-control" value="{{ $item->external_name ?? '' }}"
                readonly>
        </div>

        <div class="form-group">
            <label for="name">Nome</label>
            @php
            $nameValue = old('name') ?? (!empty($item->name) ? $item->name : ($item->external_name ?? ''));
            @endphp
            <input type="text" name="name" class="form-control" value="{{ $nameValue }}">
        </div>

        {{-- Marca --}}
        <div class="form-group">
            <label for="brand_id">Marca</label>
            <select name="brand_id" class="form-control">
                <option value="">Selecione uma marca</option>
                @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ $item->brand_id == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name ?: $brand->slug }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Categoria --}}
        <div class="form-group">
            <label for="category_id">Categoria</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">Selecione uma categoria</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $item->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name ?: $category->slug }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Subcategoria --}}
        <div class="form-group">
            <label for="subcategory_id">Subcategoria</label>
            <select name="subcategory_id" id="subcategory_id" class="form-control">
                <option value="">Selecione uma subcategoria</option>
                @foreach($subcategories as $subcategory)
                @if($subcategory->category_id == $item->category_id)
                <option value="{{ $subcategory->id }}"
                    {{ $item->subcategory_id == $subcategory->id ? 'selected' : '' }}>
                    {{ $subcategory->name ?: $subcategory->slug }}
                </option>
                @endif
                @endforeach
            </select>
        </div>

        {{-- Childcategory --}}
        <div class="form-group">
            <label for="childcategory_id">Childcategory</label>
            <select name="childcategory_id" id="childcategory_id" class="form-control">
                <option value="">Selecione uma childcategory</option>
                @foreach($childcategories as $childcategory)
                @if($childcategory->subcategory_id == $item->subcategory_id)
                <option value="{{ $childcategory->id }}"
                    {{ $item->childcategory_id == $childcategory->id ? 'selected' : '' }}>
                    {{ $childcategory->name ?: $childcategory->slug }}
                </option>
                @endif
                @endforeach
            </select>
        </div>

        @endif

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="editor">{{ old('description', $item->description ?? '') }}</textarea>
        </div>


        @if($type === 'product')
        <div class="form-group">
            <label for="price">Preço</label>
            <input type="number" step="0.01" name="price" class="form-control"
                value="{{ old('price', $item->price ?? '') }}">
        </div>

        <div class="form-group">
            <label for="stock">Estoque</label>
            <input readonly type="number" name="stock" class="form-control"
                value="{{ old('stock', $item->stock ?? '') }}">
        </div>
        @else
        <div class="form-group">
            <label for="file">Arquivo (se desejar substituir)</label>
            <input type="file" name="file" class="form-control">
        </div>
        @endif

        <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
    </form>
</div>

@endsection
