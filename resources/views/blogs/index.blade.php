@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>Blog</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar blog..." value="{{ request('search') }}">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <button class="btn btn-primary">Buscar</button>
        </div>
    </form>

    {{-- Filtro de categorias --}}
    <div class="mb-4">
        <a href="{{ route('blogs.index') }}" class="btn btn-outline-secondary btn-sm {{ request('category') == '' ? 'active' : '' }}">Todas</a>
        @foreach($categories as $cat)
            <a href="{{ route('blogs.index', array_merge(request()->all(), ['category' => $cat->id])) }}" 
               class="btn btn-outline-secondary btn-sm {{ request('category') == $cat->id ? 'active' : '' }}">
               {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <div class="row">
        @foreach ($blogs as $blog)
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-center">
                    @if($blog->image && Storage::disk('public')->exists($blog->image))
                        <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}" class="img-fluid rounded-3 shadow-sm">
                    @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
                    @endif
                </div>
                <div class="card-body text-center">
                    <h5>{{ $blog->title }}</h5>
                    <p>{{ $blog->subtitle }}</p>
                    <small class="text-muted">{{ $blog->category->name ?? 'Sem categoria' }}</small>
                    <br>
                    <a href="{{ route('blogs.show', $blog->slug) }}" class="btn btn-primary mt-2">Leia mais</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $blogs->links() }}
</div>
@endsection
