@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>Categorias</h1>
    <div class="row">
        @foreach ($categories as $category)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-center">
                    @if($category->photo && Storage::disk('public')->exists($category->photo))
                        <!-- Imagem da categoria se existir -->
                        <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}" class="img-fluid rounded-3 shadow-sm">
                    @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                        <!-- Imagem padrão caso a categoria não tenha imagem -->
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @else
                        <!-- Caso não tenha imagem e não exista a imagem padrão, você pode definir uma imagem genérica -->
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5>{{ $category->name ?? $category->slug }}</h5>
                    <a href="{{ route('categories.show', $category->id) }}" class="btn btn-primary">Ver detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $categories->links() }}
</div>
@endsection
