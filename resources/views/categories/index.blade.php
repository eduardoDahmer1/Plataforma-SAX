@extends('layout.layout')

@section('content')
    <div class="container py-4">

        <h1 class="mb-4 text-center fw-bold">Categorias</h1>

        {{-- Busca --}}
        <form method="GET" class="mb-4 mx-auto" style="max-width: 500px;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar categoria..."
                    value="{{ request('search') }}">
                <button class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </form>

        {{-- Listagem --}}
        <div class="row">
            @forelse ($categories as $category)
                @if (($category->products_count ?? 0) > 0)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-img-top text-center p-3">
                                @if ($category->photo && Storage::disk('public')->exists($category->photo))
                                    <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                                @endif
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="fw-semibold">{{ $category->name ?? $category->slug }}</h5>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-box me-1"></i>
                                    {{ $category->products_count }} produto(s)
                                </p>

                                <a href="{{ route('categories.show', $category->slug) }}"
                                    class="btn btn-outline-primary btn-sm mt-auto">
                                    <i class="fas fa-eye me-1"></i> Ver detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-1"></i> Nenhuma categoria encontrada.
                    </div>
                </div>
            @endforelse

        </div>

        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $categories->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
