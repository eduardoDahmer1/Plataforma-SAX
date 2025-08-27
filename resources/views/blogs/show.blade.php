@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Voltar --}}
    <a href="{{ route('blogs.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Ver Blogs
    </a>

    {{-- Cabeçalho do post --}}
    <div class="mb-4 text-center">
        <h1 class="fw-bold">{{ $blog->title }}</h1>
        <h5 class="text-muted">{{ $blog->subtitle }}</h5>

        @if($blog->category)
            <span class="badge bg-primary mt-2">
                <i class="fas fa-folder me-1"></i> {{ $blog->category->name }}
            </span>
        @endif
    </div>

    {{-- Imagem destacada --}}
    <div class="text-center mb-4">
        <div class="ratio ratio-16x9 mx-auto" style="max-width: 800px;">
            @if($blog->image && Storage::disk('public')->exists($blog->image))
                <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}" class="img-fluid rounded-3 shadow-sm object-fit-coverr">
            @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm object-fit-coverr">
            @else
                <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm object-fit-coverr">
            @endif
        </div>
    </div>

    {{-- Conteúdo --}}
    <article class="mb-4 px-2 px-md-5">
        <div class="fs-5 lh-lg">
            {!! $blog->content !!}
        </div>
    </article>

    {{-- Voltar --}}
    <div class="text-center">
        <a href="{{ route('blogs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

</div>
@endsection
