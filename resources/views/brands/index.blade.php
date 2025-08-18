@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>Marcas</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar marcas..."
                value="{{ request('search') }}">
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>
    <div class="row">
        @foreach ($brands as $brand)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-center">
                    @if($brand->image && Storage::disk('public')->exists($brand->image))
                        <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" class="img-fluid rounded-3 shadow-sm">
                    @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5>{{ $brand->name ?? $brand->slug }}</h5>
                    <a href="{{ route('brands.show', $brand->slug) }}" class="btn btn-primary">Ver detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $brands->links() }}
</div>
@endsection
