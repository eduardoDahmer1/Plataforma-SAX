@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>{{ $brand->name ?? $brand->slug ?? 'N/A' }}</h1>

    <div class="text-center mb-3">
        @if($brand->image && Storage::disk('public')->exists($brand->image))
            <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name ?? $brand->slug }}" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <p><strong>ID:</strong> {{ $brand->id }}</p>
    <p><strong>Slug:</strong> {{ $brand->slug ?? 'N/A' }}</p>

    {{-- Se quiser mostrar banner igual childcategory --}}
    @if($brand->banner && Storage::disk('public')->exists($brand->banner))
    <div class="text-center mt-4">
        <img src="{{ Storage::url($brand->banner) }}" alt="Banner da Marca" class="img-fluid rounded-3 shadow-sm">
    </div>
    @endif

    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
