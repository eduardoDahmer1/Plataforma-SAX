@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/admin-commerce.css') }}?v={{ file_exists(public_path('css/admin-commerce.css')) ? filemtime(public_path('css/admin-commerce.css')) : time() }}" rel="stylesheet">
@endpush

@section('content')
<div class="commerce-admin commerce-abandoned-show">
<x-admin.card>
    @php
        $cartTotal = $abandonedCart->currency_sign.' '.number_format($abandonedCart->total * $abandonedCart->currency_value, 2, '.', ',');
        $customerName = $abandonedCart->user->name ?? 'Cliente removido';
        $customerEmail = $abandonedCart->user->email ?? '';
        $feedbackLabel = [
            'later' => 'Vai comprar depois',
            'payment' => 'Não conseguiu pagar',
            'shipping_price' => 'Preço ou frete',
            'help' => 'Precisa de ajuda',
            'no_answer' => 'Preferiu não responder',
            'gave_up' => 'Não quer mais os produtos',
            'other' => 'Outro motivo',
        ][$abandonedCart->feedback_reason] ?? $abandonedCart->feedback_reason;
        $feedbackTone = $abandonedCart->feedback_reason === 'payment' ? 'is-danger' : ($abandonedCart->feedback_at ? 'is-warning' : 'is-neutral');
    @endphp

    <a href="{{ route('admin.abandoned-carts.index') }}" class="commerce-back-link">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Voltar aos carrinhos
    </a>

    <header class="commerce-cart-hero">
        <div class="commerce-cart-hero__identity">
            <span class="commerce-eyebrow">Carrinho abandonado</span>
            <h1>#{{ $abandonedCart->id }}</h1>
            <p><i class="fa-regular fa-calendar me-1"></i>{{ $abandonedCart->abandoned_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="commerce-cart-hero__customer">
            <span class="commerce-avatar">{{ mb_strtoupper(mb_substr($customerName, 0, 1)) }}</span>
            <div><span>Cliente</span><strong>{{ $customerName }}</strong><small>{{ $customerEmail }}</small></div>
        </div>
        <div class="commerce-cart-hero__total">
            <span>Total potencial</span>
            <strong>{{ $cartTotal }}</strong>
            <small>{{ $abandonedCart->items->sum('quantity') }} itens no carrinho</small>
        </div>
        <div class="commerce-cart-hero__status">
            <span>Status</span>
            <strong class="commerce-status {{ $abandonedCart->status === 'restored' ? 'is-success' : 'is-warning' }}">
                <i class="fa-solid fa-circle" aria-hidden="true"></i>{{ $abandonedCart->status === 'restored' ? 'Restaurado' : 'Abandonado' }}
            </strong>
        </div>
    </header>

    <section class="commerce-panel commerce-cart-products">
        <div class="commerce-section-heading">
            <span><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
            <div><h2>Produtos do carrinho</h2><p>Itens que o cliente deixou antes de finalizar.</p></div>
        </div>
        <div class="table-responsive">
            <table class="table commerce-table align-middle mb-0">
                <thead><tr><th>Produto</th><th>SKU</th><th>Quantidade</th><th>Preço unitário</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach($abandonedCart->items as $item)
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td>{{ $item->sku ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $abandonedCart->currency_sign }} {{ number_format($item->unit_price * $abandonedCart->currency_value, 2, '.', ',') }}</td>
                            <td class="text-end fw-bold">{{ $abandonedCart->currency_sign }} {{ number_format($item->unit_price * $item->quantity * $abandonedCart->currency_value, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="commerce-cart-products__total"><span>Total</span><strong>{{ $cartTotal }}</strong></div>
    </section>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <section class="commerce-insight-card {{ $abandonedCart->help_email_sent_at ? 'is-success' : 'is-warning' }} h-100">
                <span class="commerce-insight-card__icon"><i class="fa-regular fa-envelope" aria-hidden="true"></i></span>
                <div><span>Contato de ajuda</span><strong>{{ $abandonedCart->help_email_sent_at ? 'E-mail enviado' : 'Contato pendente' }}</strong><p>{{ $abandonedCart->help_email_sent_at ? 'Enviado em '.$abandonedCart->help_email_sent_at->format('d/m/Y H:i') : 'E-mail ainda não enviado' }}</p></div>
            </section>
        </div>
        <div class="col-md-6">
            <section class="commerce-insight-card {{ $feedbackTone }} h-100">
                <span class="commerce-insight-card__icon"><i class="fa-regular fa-message" aria-hidden="true"></i></span>
                <div>
                    <span>Resposta do cliente</span>
                    @if($abandonedCart->feedback_at)
                        <strong>{{ $feedbackLabel }}</strong>
                        <p>{{ $abandonedCart->feedback_message ?: 'Sem comentário adicional.' }}</p>
                        <small>{{ $abandonedCart->feedback_at->format('d/m/Y H:i') }}</small>
                    @else
                        <strong>Aguardando resposta</strong><p>O cliente ainda não respondeu.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-admin.card>
</div>
@endsection
