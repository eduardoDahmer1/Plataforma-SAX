@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="d-flex justify-content-between mb-4"><div><h3 class="fw-bold mb-1">Carrinho abandonado #{{ $abandonedCart->id }}</h3><span class="text-muted">{{ $abandonedCart->user->name ?? 'Cliente removido' }} · {{ $abandonedCart->user->email ?? '' }}</span></div><a href="{{ route('admin.abandoned-carts.index') }}" class="btn btn-outline-dark">Voltar</a></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Produto</th><th>SKU</th><th>Quantidade</th><th>Preço unitário</th><th class="text-end">Subtotal</th></tr></thead><tbody>
        @foreach($abandonedCart->items as $item)<tr><td><strong>{{ $item->product_name }}</strong></td><td>{{ $item->sku ?? '—' }}</td><td>{{ $item->quantity }}</td><td>{{ $abandonedCart->currency_sign }} {{ number_format($item->unit_price * $abandonedCart->currency_value, 2, '.', ',') }}</td><td class="text-end fw-bold">{{ $abandonedCart->currency_sign }} {{ number_format($item->unit_price * $item->quantity * $abandonedCart->currency_value, 2, '.', ',') }}</td></tr>@endforeach
    </tbody></table></div>
    <div class="text-end border-top pt-3"><span class="text-muted me-3">Total</span><strong class="h4">{{ $abandonedCart->currency_sign }} {{ number_format($abandonedCart->total * $abandonedCart->currency_value, 2, '.', ',') }}</strong></div>
</x-admin.card>
@endsection
