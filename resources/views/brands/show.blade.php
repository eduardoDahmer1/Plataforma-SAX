@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>{{ $brand->name }}</h1>

    <div class="text-center mb-3">
        @if($brand->image && Storage::disk('public')->exists($brand->image))
            <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <p><strong>ID:</strong> {{ $brand->id }}</p>
    <p><strong>Slug:</strong> {{ $brand->slug ?? 'N/A' }}</p>

    <a href="{{ route('brands.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
