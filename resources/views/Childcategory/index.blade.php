@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h1 class="mb-4 text-center fw-bold">Tipos / Modelos / Estilos / Linha</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-4 mx-auto" style="max-width: 500px;">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar sub-subcategoria..."
                   value="{{ request('search') }}">
            <button class="btn btn-primary">
                <i class="fas fa-search me-1"></i> Buscar
            </button>
        </div>
    </form>

    <p class="text-center text-muted mb-3">
        Exibindo {{ $childcategories->count() }} de {{ $childcategories->total() }} sub-subcategoria(s)
    </p>

    {{-- Listagem --}}
    <div class="row">
        @forelse ($childcategories as $child)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-img-top text-center p-3">
                    @if($child->photo && Storage::disk('public')->exists($child->photo))
                        <img src="{{ Storage::url($child->photo) }}" alt="{{ $child->name }}"
                             class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                             class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                    @endif
                </div>
                <div class="card-body text-center d-flex flex-column">

                    {{-- Nome --}}
                    <h5 class="fw-semibold">{{ $child->name ?? $child->slug }}</h5>

                    {{-- Produtos --}}
                    <p class="text-muted small mb-2">
                        <i class="fas fa-box me-1"></i>
                        {{ $child->products_count ?? $child->products->count() ?? 0 }} produto(s)
                    </p>

                    {{-- Subcategoria e categoria pai --}}
                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-sitemap me-1"></i>
                        Subcategoria: {{ $child->subcategory->name ?? 'N/A' }} <br>
                        Categoria: {{ $child->subcategory->category->name ?? 'N/A' }}
                    </small>

                    {{-- Ação --}}
                    <a href="{{ route('childcategories.show', $child->slug) }}"
                       class="btn btn-outline-primary btn-sm mt-auto">
                       <i class="fas fa-eye me-1"></i> Ver detalhes
                    </a>
                </div>
            </div>
        </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-1"></i> Nenhuma sub-subcategoria encontrada.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $childcategories->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
