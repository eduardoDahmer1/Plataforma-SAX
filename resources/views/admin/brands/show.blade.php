@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Detalhes da Marca</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $brand->id }}</p>
            <p><strong>Nome:</strong> {{ $brand->name ?? 'Sem Nome' }}</p>

            {{-- LOGO --}}
            <p><strong>Logo:</strong></p>
            @if ($brand->image)
                <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem logo</p>
            @endif

            {{-- BANNER --}}
            <p><strong>Banner:</strong></p>
            @if ($brand->banner)
                <img src="{{ asset('storage/' . $brand->banner) }}" alt="Banner" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem banner</p>
            @endif

            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary mt-3">Voltar</a>
        </div>
    </div>
</div>
@endsection
