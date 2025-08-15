@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>Categorias</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar categoria..."
                value="{{ request('search') }}">
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>


    <div class="row">
        @foreach ($categories as $category)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-center">
                    @if($category->photo && Storage::disk('public')->exists($category->photo))
                    <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}"
                        class="img-fluid rounded-3 shadow-sm">
                    @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                        class="img-fluid rounded-3 shadow-sm">
                    @else
                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                        class="img-fluid rounded-3 shadow-sm">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5>{{ $category->name ?? $category->slug }}</h5>

                    @if($category->subcategories && $category->subcategories->count())
                    <div class="mt-3 text-start">
                        <strong>Subcategorias:</strong>
                        <ul class="list-unstyled mb-0">
                            @foreach($category->subcategories as $subcategory)
                            <li>
                                {{ $subcategory->name ?? $subcategory->slug }}

                                @if($subcategory->childcategories && $subcategory->childcategories->count())
                                <ul>
                                    @foreach($subcategory->childcategories as $childcategory)
                                    <li>{{ $childcategory->name ?? $childcategory->slug }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-primary mt-3">Ver
                        detalhes</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $categories->links() }}
</div>
@endsection