@extends('layout.admin')

@section('content')
<div class="container">

    <h1>Editar Produto</h1>

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
                <select name="category_id" id="category_id" class="form-control" required>
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
                <select name="subcategory_id" id="subcategory_id" class="form-control" required>
                    <option value="">Selecione uma subcategoria</option>
                    @foreach($subcategories as $subcategory)
                        @if($subcategory->category_id == $item->category_id)
                            <option value="{{ $subcategory->id }}" {{ $item->subcategory_id == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->name ?: $subcategory->slug }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Childcategory --}}
            <div class="form-group">
                <label for="childcategory_id">Childcategory</label>
                <select name="childcategory_id" id="childcategory_id" class="form-control" required>
                    <option value="">Selecione uma childcategory</option>
                    @foreach($childcategories as $childcategory)
                        @if($childcategory->subcategory_id == $item->subcategory_id)
                            <option value="{{ $childcategory->id }}" {{ $item->childcategory_id == $childcategory->id ? 'selected' : '' }}>
                                {{ $childcategory->name ?: $childcategory->slug }}
                            </option>
                        @endif
                    @endforeach
                </select>
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


<script>
document.addEventListener('DOMContentLoaded', function () {
    const categories = @json($categories);
    const subcategories = @json($subcategories);
    const childcategories = @json($childcategories);

    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const childcategorySelect = document.getElementById('childcategory_id');

    function clearOptions(select) {
        select.innerHTML = '<option value="">Selecione uma opção</option>';
    }

    function populateSubcategories(categoryId) {
        clearOptions(subcategorySelect);
        clearOptions(childcategorySelect);
        if (!categoryId) return;

        const filteredSubs = subcategories.filter(s => s.category_id == categoryId);
        filteredSubs.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.text = sub.name || sub.slug;
            subcategorySelect.appendChild(option);
        });
    }

    function populateChildcategories(subcategoryId) {
        clearOptions(childcategorySelect);
        if (!subcategoryId) return;

        const filteredChilds = childcategories.filter(c => c.subcategory_id == subcategoryId);
        filteredChilds.forEach(child => {
            const option = document.createElement('option');
            option.value = child.id;
            option.text = child.name || child.slug;
            childcategorySelect.appendChild(option);
        });
    }

    categorySelect.addEventListener('change', function () {
        populateSubcategories(this.value);
    });

    subcategorySelect.addEventListener('change', function () {
        populateChildcategories(this.value);
    });

    // Se quiser setar os selects já com valores selecionados no carregamento:
    if (categorySelect.value) {
        populateSubcategories(categorySelect.value);
        if (subcategorySelect.value) {
            populateChildcategories(subcategorySelect.value);

            // Opcionalmente setar o subcategorySelect para o valor correto
            subcategorySelect.value = '{{ $item->subcategory_id }}';
            childcategorySelect.value = '{{ $item->childcategory_id }}';
        }
    }
});
</script>
