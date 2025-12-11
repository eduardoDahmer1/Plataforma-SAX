@extends('layout.layout')

@section('content')
<style>
    .blog-header {
        animation: fadeIn 0.6s ease-in-out;
    }

    .blog-title {
        font-size: 2.6rem;
        font-weight: 800;
        letter-spacing: -1px;
    }

    .blog-subtitle {
        font-size: 1.15rem;
    }

    .blog-image img {
        transition: transform .4s ease, box-shadow .4s ease;
    }

    .blog-image img:hover {
        transform: scale(1.02);
        box-shadow: 0px 10px 28px rgba(0,0,0,0.25);
    }

    article {
        background: #fff;
        border-radius: 18px;
        padding: 35px;
        box-shadow: 0px 10px 25px rgba(0,0,0,0.06);
        animation: fadeUp .6s ease;
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-10px);}
        to {opacity: 1; transform: translateY(0);}
    }

    @keyframes fadeUp {
        from {opacity: 0; transform: translateY(15px);}
        to {opacity: 1; transform: translateY(0);}
    }

    .back-btn {
        transition: background .3s, color .3s, transform .2s;
    }

    .back-btn:hover {
        transform: translateX(-3px);
    }

</style>


<div class="container py-5">

    {{-- Voltar --}}
    <a href="{{ route('blogs.index') }}" class="btn btn-outline-secondary mb-4 back-btn">
        <i class="fas fa-arrow-left me-1"></i> Ver Blogs
    </a>

    {{-- Cabeçalho --}}
    <div class="mb-5 text-center blog-header">
        <h1 class="blog-title mb-2">{{ $blog->title }}</h1>

        @if($blog->subtitle)
            <h5 class="blog-subtitle text-muted mb-3">
                {{ $blog->subtitle }}
            </h5>
        @endif

        @if($blog->category)
            <span class="badge bg-gradient-primary px-3 py-2" 
                  style="background: linear-gradient(45deg, #007bff, #00a2ff); border-radius: 12px;">
                <i class="fas fa-folder me-1"></i> {{ $blog->category->name }}
            </span>
        @endif
    </div>

    {{-- Imagem --}}
    <div class="text-center mb-5 blog-image">
        <div class="ratio ratio-16x9 mx-auto" style="max-width: 900px;">
            @if($blog->image && Storage::disk('public')->exists($blog->image))
                <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}" 
                     class="img-fluid rounded-4 object-fit-coverr">
            @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
                <img src="{{ asset('storage/uploads/noimage.webp') }}" 
                     alt="Imagem padrão" class="img-fluid rounded-4 object-fit-coverr">
            @else
                <img src="{{ asset('storage/uploads/noimage.webp') }}" 
                     alt="Imagem padrão" class="img-fluid rounded-4 object-fit-coverr">
            @endif
        </div>
    </div>

    {{-- Conteúdo --}}
    <article class="mb-5">
        <div class="fs-5 lh-lg">
            {!! $blog->content !!}
        </div>

        {{-- Autor e data --}}
        <div class="mt-4 text-end text-muted">
            @if($blog->author)
                <small><i class="fas fa-user-edit me-1"></i> {{ $blog->author }}</small><br>
            @endif

            @if($blog->published_at)
                <small><i class="fas fa-calendar me-1"></i> 
                    {{ \Carbon\Carbon::parse($blog->published_at)->format('d/m/Y') }}
                </small>
            @endif
        </div>
    </article>

    {{-- Voltar --}}
    <div class="text-center">
        <a href="{{ route('blogs.index') }}" class="btn btn-outline-secondary back-btn">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

</div>
@endsection
