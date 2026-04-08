@extends('layout.admin')

@section('content')
<x-admin.card>
    <h3 class="mb-3">{{ $cupon->codigo }}</h3>

            <p><strong>Tipo:</strong> {{ ucfirst($cupon->tipo) }}</p>
            <p><strong>Montante:</strong> {{ $cupon->montante }}</p>
            <p><strong>Quantidade:</strong> {{ $cupon->quantidade ?? 'Ilimitado' }}</p>
            <p><strong>Modelo:</strong> {{ $cupon->modelo ?? 'Todos' }}</p>
            <p><strong>Categoria:</strong> {{ $cupon->category->name ?? 'Todas' }}</p>
            <p><strong>Marca:</strong> {{ $cupon->brand->name ?? 'Todas' }}</p>
            <p><strong>Valor mínimo:</strong> {{ $cupon->valor_minimo ?? '-' }}</p>
            <p><strong>Valor máximo:</strong> {{ $cupon->valor_maximo ?? '-' }}</p>
            <p><strong>Data:</strong> {{ $cupon->data_inicio->format('d/m/Y') }} -
                {{ $cupon->data_final->format('d/m/Y') }}</p>
            <p><strong>Usado:</strong> {{ $cupon->usado }}</p>

            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('admin.cupons.edit', $cupon) }}" class="btn btn-primary">Editar</a>
                <a href="{{ route('admin.cupons.index') }}" class="btn btn-secondary">Voltar</a>
    </div>
</x-admin.card>
@endsection