@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Categorias de Blog"
        description="Organização hierárquica do conteúdo editorial"
        actionUrl="{{ route('admin.blog-categories.create') }}"
        actionLabel="Nova Categoria" />

    <x-admin.alert />

    @if($categories->count())
    <div class="row g-4">
        @foreach($categories as $cat)
            <div class="col-sm-6 col-lg-3">
                <div class="sax-category-card border-0 bg-transparent h-100">

                    {{-- Imagem em Aspect Ratio 1:1 ou 4:3 --}}
                    <div class="category-img-container mb-3 position-relative overflow-hidden">
                        @if($cat->banner && Storage::disk('public')->exists($cat->banner))
                            <img src="{{ Storage::url($cat->banner) }}" alt="{{ $cat->name }}" class="grayscale-hover">
                        @else
                            <div class="no-image-placeholder x-small text-uppercase fw-bold text-muted">
                                No Image
                            </div>
                        @endif

                        {{-- Overlay de Ações (Aparece no hover) --}}
                        <div class="category-overlay">
                            <a href="{{ route('admin.blog-categories.edit', $cat) }}" class="btn btn-light btn-sm rounded-0 x-small fw-bold border">EDITAR</a>
                        </div>
                    </div>

                    <div class="category-info">
                        <h6 class="text-uppercase tracking-tighter fw-bold mb-1 fs-7">{{ $cat->name }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="x-small text-secondary text-uppercase tracking-wider">ID #{{ $cat->id }}</span>

                            <div class="d-flex gap-3 align-items-center">
                                <a href="{{ route('admin.blog-categories.show', $cat) }}" class="text-dark x-small fw-bold text-decoration-none hover-underline">VER</a>

                                <form action="{{ route('admin.blog-categories.destroy', $cat) }}" method="POST" class="m-0" onsubmit="return confirm('¿Eliminar categoría?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">ELIMINAR</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @else
        <div class="py-5 text-center border">
            <p class="text-muted small italic mb-0">Nenhuma categoria registrada.</p>
        </div>
    @endif
</x-admin.card>
@endsection
