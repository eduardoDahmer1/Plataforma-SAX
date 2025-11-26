@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h1 class="mb-4 text-center fw-bold">Subcategorias / Refinar / Seções</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-4 mx-auto" style="max-width: 500px;">
        <div class="input-group">
            <input type="text" name="search" class="form-control"
                   placeholder="Buscar subcategoria..." value="{{ request('search') }}">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <button class="btn btn-primary">
                <i class="fas fa-search me-1"></i> Buscar
            </button>
        </div>
    </form>

    {{-- Lista --}}
    <div class="row">
        @forelse ($subcategories as $subcategory)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-img-top text-center p-3">
                    @if($subcategory->photo && Storage::disk('public')->exists($subcategory->photo))
                        <img src="{{ Storage::url($subcategory->photo) }}" alt="{{ $subcategory->name }}"
                             class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem"
                             class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                    @endif
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="fw-semibold">{{ $subcategory->name }}</h5>

                    {{-- Categoria Pai --}}
                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-layer-group me-1"></i>
                         {{ $subcategory->category->name ?? 'N/A' }}
                    </small>

                    {{-- Quantidade de Childcategories --}}
                    @if($subcategory->childcategories)
                        <small class="text-muted mb-2">
                            <i class="fas fa-list-ul me-1"></i>
                            {{ $subcategory->childcategories->count() }} itens
                        </small>
                    @endif

                    <a href="{{ route('subcategories.show', $subcategory->id) }}"
                       class="btn btn-outline-primary btn-sm mt-auto">
                       <i class="fas fa-eye me-1"></i> Ver detalhes
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-1"></i> Nenhuma subcategoria encontrada.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $subcategories->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
