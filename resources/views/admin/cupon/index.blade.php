@extends('layout.admin')

@section('content')
    <div class="container py-4">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2>Cupons</h2>
            <a href="{{ route('admin.cupons.create') }}" class="btn btn-success">Adicionar Cupom</a>
        </div>

        <div class="row g-3">
            @forelse($cupons as $cupon)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">{{ $cupon->codigo }}</h5>
                            <p class="mb-1"><strong>Tipo:</strong> {{ ucfirst($cupon->tipo) }}</p>
                            <p class="mb-1"><strong>Montante:</strong> {{ $cupon->montante }}</p>
                            <p class="mb-1"><strong>Modelo:</strong> {{ $cupon->modelo ?? 'Todos' }}</p>
                            <p class="mb-1"><strong>Categoria:</strong> {{ $cupon->category->name ?? 'Todas' }}</p>
                            <p class="mb-1"><strong>Marca:</strong> {{ $cupon->brand->name ?? 'Todas' }}</p>

                            <div class="mt-3 d-flex gap-2">
                                <a href="{{ route('admin.cupons.edit', $cupon) }}" class="btn btn-primary btn-sm">Editar</a>
                                <form action="{{ route('admin.cupons.destroy', $cupon) }}" method="POST"
                                    onsubmit="return confirm('Tem certeza?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p>Nenhum cupom encontrado.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $cupons->links() }}
        </div>
    </div>
@endsection
