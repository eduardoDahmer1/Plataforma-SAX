@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>Subcategorias</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar a subcategoria..."
                value="{{ request('search') }}">
            @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>
    <div class="row">
        @foreach ($subcategories as $subcategory)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-center">
                    @if($subcategory->photo && Storage::disk('public')->exists($subcategory->photo))
                    <img src="{{ Storage::url($subcategory->photo) }}" alt="{{ $subcategory->name }}"
                        class="img-fluid rounded-3 shadow-sm">
                    @else
                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem"
                        class="img-fluid rounded-3 shadow-sm">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5>{{ $subcategory->name }}</h5>
                    <small class="text-muted">Categoria Pai: {{ $subcategory->category->name ?? 'N/A' }}</small><br>
                    <a href="{{ route('subcategories.show', $subcategory->id) }}" class="btn btn-primary mt-2">Ver
                        detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $subcategories->links() }}
</div>
@endsection