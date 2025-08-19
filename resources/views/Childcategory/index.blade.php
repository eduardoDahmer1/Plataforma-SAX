@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h1 class="mb-4 text-center fw-bold">Categorias Filhas</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-4 mx-auto" style="max-width: 500px;">
        <div class="input-group">
            <input type="text" name="search" class="form-control"
                   placeholder="Buscar categoria filha..." value="{{ request('search') }}">
            <button class="btn btn-primary">
                <i class="fas fa-search me-1"></i> Buscar
            </button>
        </div>
    </form>

    {{-- Listagem --}}
    <div class="row">
        @foreach ($childcategories as $childcategory)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-img-top text-center p-3">
                    @if($childcategory->photo && Storage::disk('public')->exists($childcategory->photo))
                        <img src="{{ Storage::url($childcategory->photo) }}" alt="{{ $childcategory->name }}"
                             class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                             class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5 class="fw-semibold">{{ $childcategory->name ?? $childcategory->slug }}</h5>
                    <a href="{{ route('childcategories.show', $childcategory->slug) }}"
                       class="btn btn-outline-primary btn-sm mt-2">
                       <i class="fas fa-eye me-1"></i> Ver detalhes
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $childcategories->links() }}
    </div>
</div>
@endsection
