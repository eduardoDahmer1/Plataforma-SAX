@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>Sub-Subcategorias</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar categoria filha..."
                value="{{ request('search') }}">
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <div class="row">
        @foreach ($childcategories as $childcategory)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-center">
                    @if($childcategory->photo && Storage::disk('public')->exists($childcategory->photo))
                        <img src="{{ Storage::url($childcategory->photo) }}" alt="{{ $childcategory->name }}" class="img-fluid rounded-3 shadow-sm">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5>{{ $childcategory->name ?? $childcategory->slug }}</h5>
                    <a href="{{ route('childcategories.show', $childcategory->slug) }}" class="btn btn-primary">Ver detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $childcategories->links() }}
</div>
@endsection
