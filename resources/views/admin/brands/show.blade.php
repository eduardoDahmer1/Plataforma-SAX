@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-industry me-2"></i>Detalhes da Marca</h2>

    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body">
            <p><strong><i class="fas fa-hashtag me-1"></i>ID:</strong> {{ $brand->id }}</p>
            <p><strong><i class="fas fa-tag me-1"></i>Nome:</strong> {{ $brand->name ?? 'Sem Nome' }}</p>

            {{-- LOGO --}}
            <p><strong><i class="fas fa-image me-1"></i>Logo:</strong></p>
            @if ($brand->image)
                <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem logo</p>
            @endif

            {{-- BANNER --}}
            <p><strong><i class="fas fa-images me-1"></i>Banner:</strong></p>
            @if ($brand->banner)
                <img src="{{ asset('storage/' . $brand->banner) }}" alt="Banner" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem banner</p>
            @endif

            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>
</div>
@endsection
