@extends('layout.admin')

@section('content')
<div class="container">

    <h1>Editar {{ $type === 'product' ? 'Produto' : 'Upload' }}</h1>

    <form action="{{ $type === 'product' 
        ? route('admin.products.update', $item->id) 
        : route('admin.uploads.update', $item->id) }}" 
        method="POST" enctype="multipart/form-data" id="editForm">
        
        @csrf
        @method('PUT')

        @if($type === 'product')
            <div class="form-group">
                <label for="sku">SKU</label>
                <input readonly type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku ?? '') }}">
            </div>

            <div class="form-group">
                <label for="external_name">Nome Externo</label>
                <input type="text" name="external_name" class="form-control" 
                       value="{{ $item->external_name ?? '' }}" readonly>
            </div>

            <div class="form-group">
                <label for="name">Nome</label>
                @php
                    $nameValue = old('name') ?? (!empty($item->name) ? $item->name : ($item->external_name ?? ''));
                @endphp
                <input type="text" name="name" class="form-control" value="{{ $nameValue }}">
            </div>
        @endif

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $item->description ?? '') }}</textarea>
        </div>

        @if($type === 'product')
            <div class="form-group">
                <label for="price">Preço</label>
                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $item->price ?? '') }}">
            </div>

            <div class="form-group">
                <label for="stock">Estoque</label>
                <input readonly type="number" name="stock" class="form-control" value="{{ old('stock', $item->stock ?? '') }}">
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

<script>
    document.getElementById('editForm').addEventListener('submit', function() {
        if (typeof tinymce !== 'undefined') {
            var content = tinymce.get('description')?.getContent() || '';
            document.querySelector('textarea[name="description"]').value = content;
        }
    });
</script>
@endsection
