@extends('layout.layout')

@section('content')
<div class="container py-4">
    <a href="{{ route('blogs.index') }}" class="btn btn-secondary mb-3">Ver Blogs</a>

    <h1>{{ $blog->title }}</h1>
    <h6 class="text-muted">{{ $blog->subtitle }}</h6>
    @if($blog->category)
        <p><strong>Categoria:</strong> {{ $blog->category->name }}</p>
    @endif

    <div class="text-center mb-3">
        @if($blog->image && Storage::disk('public')->exists($blog->image))
            <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <div>{!! $blog->content !!}</div>

    <a href="{{ route('blogs.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
