@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Título --}}
    <h1 class="mb-4 fw-bold d-flex align-items-center">
        <i class="fas fa-blog me-2"></i> Blog
    </h1>

    {{-- Busca --}}
    <form method="GET" class="mb-4">
        <div class="input-group shadow-sm">
            <input type="text" name="search" class="form-control"
                   placeholder="🔎 Buscar artigos..."
                   value="{{ request('search') }}">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <button class="btn btn-primary px-4">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    {{-- Filtro de Categorias --}}
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="{{ route('blogs.index') }}"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3 {{ request('category') == '' ? 'active' : '' }}">
            <i class="fas fa-list me-1"></i> Todas
        </a>

        @foreach($categories as $cat)
            <a href="{{ route('blogs.index', array_merge(request()->all(), ['category' => $cat->id])) }}"
               class="btn btn-outline-secondary btn-sm rounded-pill px-3 {{ request('category') == $cat->id ? 'active' : '' }}">
               {{ $cat->name }}
            </a>
        @endforeach
    </div>

    {{-- Banner da categoria --}}
    @if(request('category'))
        @php
            $selectedCategory = $categories->firstWhere('id', request('category'));
        @endphp

        @if($selectedCategory && $selectedCategory->banner && Storage::disk('public')->exists($selectedCategory->banner))
            <div class="mb-4">
                <img src="{{ Storage::url($selectedCategory->banner) }}"
                     alt="{{ $selectedCategory->name }}"
                     class="img-fluid rounded shadow-sm w-100 object-fit-cover"
                     style="max-height: 260px;">
            </div>
        @endif
    @endif

    {{-- Lista de Blogs --}}
    <div class="row g-4">
        @forelse ($blogs as $blog)
        <div class="col-md-4 col-sm-6">

            {{-- CARD BONITÃO --}}
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden blog-card">

                {{-- Imagem --}}
                <div class="blog-img-wrapper position-relative">
                    @php
                        $imagePath =
                            ($blog->image && Storage::disk('public')->exists($blog->image))
                            ? Storage::url($blog->image)
                            : asset('storage/uploads/noimage.webp');
                    @endphp

                    <img src="{{ $imagePath }}"
                         class="w-100 object-fit-cover"
                         style="height: 180px;"
                         alt="{{ $blog->title }}">

                    {{-- Badge de categoria --}}
                    <span class="position-absolute top-0 start-0 m-2 badge bg-dark px-3 py-2 rounded-pill">
                        {{ $blog->category->name ?? 'Sem categoria' }}
                    </span>
                </div>

                {{-- Conteúdo --}}
                <div class="card-body d-flex flex-column">

                    <h5 class="fw-bold">{{ $blog->title }}</h5>

                    <p class="text-muted small flex-grow-1">
                        {{ Str::limit($blog->subtitle, 110) }}
                    </p>

                    {{-- Leia mais --}}
                    <a href="{{ route('blogs.show', $blog->slug) }}"
                       class="btn btn-primary w-100 mt-3 py-2 fw-semibold rounded-3"
                       style="font-size: 0.9rem;">
                        <i class="fas fa-book-open me-1"></i> Ler artigo
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

{{-- CSS extra pro layout ficar top --}}
<style>
    .blog-card:hover {
        transform: translateY(-4px);
        transition: .2s ease;
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }

    .blog-img-wrapper img {
        transition: .3s ease;
    }

    .blog-card:hover .blog-img-wrapper img {
        filter: brightness(0.9);
    }
</style>
@endsection
