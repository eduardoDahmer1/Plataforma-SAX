@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4"><i class="fas fa-blog me-2"></i> Blog</h1>

    {{-- Busca --}}
    <form method="GET" class="mb-4">
        <div class="input-group shadow-sm">
            <input type="text" name="search" class="form-control" placeholder="🔎 Buscar artigos..." value="{{ request('search') }}">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <button class="btn btn-primary"><i class="fas fa-search"></i></button>
        </div>
    </form>

    {{-- Filtro de categorias --}}
    <div class="mb-4">
        <a href="{{ route('blogs.index') }}" 
           class="btn btn-outline-secondary btn-sm rounded-pill {{ request('category') == '' ? 'active' : '' }}">
            <i class="fas fa-list me-1"></i> Todas
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('blogs.index', array_merge(request()->all(), ['category' => $cat->id])) }}" 
               class="btn btn-outline-secondary btn-sm rounded-pill {{ request('category') == $cat->id ? 'active' : '' }}">
               {{ $cat->name }}
            </a>
        @endforeach
    </div>

    {{-- Banner da categoria selecionada --}}
    @if(request('category'))
        @php
            $selectedCategory = $categories->firstWhere('id', request('category'));
        @endphp
        @if($selectedCategory && $selectedCategory->banner && Storage::disk('public')->exists($selectedCategory->banner))
            <div class="mb-4">
                <img src="{{ Storage::url($selectedCategory->banner) }}" alt="{{ $selectedCategory->name }}" class="img-fluid rounded shadow-sm">
            </div>
        @endif
    @endif

    {{-- Lista de blogs --}}
    <div class="row">
        @forelse ($blogs as $blog)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                
                {{-- Imagem --}}
                <div class="ratio ratio-16x9 bg-light">
                    @if($blog->image && Storage::disk('public')->exists($blog->image))
                        <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}" class="img-fluid object-fit-coverr">
                    @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid object-fit-coverr">
                    @else
                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid object-fit-coverr">
                    @endif
                </div>

                {{-- Conteúdo --}}
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $blog->title }}</h5>
                    <p class="card-text text-muted flex-grow-1">{{ Str::limit($blog->subtitle, 100) }}</p>

                    <div class="mt-2">
                        <small class="badge bg-secondary">{{ $blog->category->name ?? 'Sem categoria' }}</small>
                    </div>

                    <a href="{{ route('blogs.show', $blog->slug) }}" class="btn btn-sm btn-primary mt-3 w-100">
                        <i class="fas fa-book-open me-1"></i> Leia mais
                    </a>
                </div>
            </div>
        </div>
        @empty
            <p class="text-muted text-center">Nenhum artigo encontrado.</p>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
