@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>{{ $subcategory->name }}</h1>
    
    @if($subcategory->banner && Storage::disk('public')->exists($subcategory->banner))
        <img src="{{ Storage::url($subcategory->banner) }}" alt="Banner" class="img-fluid mb-3">
    @endif

    <p><strong>Categoria Pai:</strong> {{ $subcategory->category->name ?? 'N/A' }}</p>

    @if($subcategory->photo && Storage::disk('public')->exists($subcategory->photo))
        <img src="{{ Storage::url($subcategory->photo) }}" alt="Imagem" class="img-fluid mb-3">
    @endif

    <a href="{{ route('subcategories.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
