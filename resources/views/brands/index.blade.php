@extends('layout.layout')

@section('content')
    <div class="container py-4">

        <h1 class="mb-4 text-center fw-bold">Marcas</h1>

        {{-- Busca --}}
        <form method="GET" class="mb-4 mx-auto" style="max-width: 500px;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar marcas..."
                    value="{{ request('search') }}">
                <button class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </form>

        {{-- Listagem --}}
        <div class="row">
            @forelse ($brands as $brand)
                @if (($brand->products_count ?? 0) > 0)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-img-top text-center p-3">
                                @if ($brand->image && Storage::disk('public')->exists($brand->image))
                                    <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                                @endif
                            </div>
                            <div class="card-body text-center d-flex flex-column">

                                {{-- Nome --}}
                                <h5 class="fw-semibold">{{ $brand->name ?? $brand->slug }}</h5>

                                {{-- Contador de produtos --}}
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-box me-1"></i>
                                    {{ $brand->products_count }} produto(s)
                                </p>

                                {{-- Botão --}}
                                <a href="{{ route('brands.show', $brand->slug) }}"
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
                        <i class="fas fa-info-circle me-1"></i> Nenhuma marca encontrada.
                    </div>
                </div>
            @endforelse

        </div>

        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $brands->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
