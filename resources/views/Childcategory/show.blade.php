@extends('layout.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                @if($childcategory->banner && Storage::disk('public')->exists($childcategory->banner))
                    <img src="{{ Storage::url($childcategory->banner) }}" alt="{{ $childcategory->name }}" class="card-img-top">
                @endif

                <div class="card-body text-center">
                    <h2 class="card-title">{{ $childcategory->name ?? $childcategory->slug }}</h2>

                    @if($childcategory->photo && Storage::disk('public')->exists($childcategory->photo))
                        <img src="{{ Storage::url($childcategory->photo) }}" alt="{{ $childcategory->name }}" class="img-fluid rounded mb-3" width="150">
                    @endif

                    <p class="mb-1"><strong>Subcategoria:</strong> {{ $childcategory->subcategory->name ?? 'Sem Subcategoria' }}</p>
                    <p class="mb-1"><strong>Categoria:</strong> {{ $childcategory->subcategory->category->name ?? 'Sem Categoria' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
