@props([
    'product',
    'variante' => 'card', // 'card' (etiqueta sobre a foto) ou 'produto' (detalhe do produto)
])

@php
    // O melhor cupom vigente para este produto. A lista de cupons vigentes é
    // carregada uma única vez por request dentro do serviço.
    $cupon = null;
    $desconto = 0;

    if ($product instanceof \App\Models\Product) {
        $servico = app(\App\Services\CuponService::class);
        $cupon = $servico->melhorCuponParaProduto($product);
        $desconto = $cupon ? $servico->descontoNoProduto($cupon, $product) : 0;
    }

    $precoFinal = $cupon ? max(0, ($product->price ?? 0) - $desconto) : 0;

    // Quando o teto de desconto do cupom corta o percentual, anunciar "-5%" enganaria:
    // nesse caso mostramos o valor que o cliente realmente abate neste produto.
    $rotulo = '';

    if ($cupon) {
        $descontoCheio = $cupon->ehPercentual()
            ? ($product->price ?? 0) * ($cupon->montante / 100)
            : (float) $cupon->montante;

        $rotulo = ($descontoCheio - $desconto) > 0.005
            ? currency_format($desconto)
            : $cupon->rotuloDesconto();
    }
@endphp

@if ($cupon && $desconto > 0)
    @if ($variante === 'card')
        <span class="sax-cupon-selo-card" title="{{ $cupon->codigo }} · {{ $cupon->rotuloEscopo() }}">
            <span class="sax-cupon-selo-card__valor">-{{ $rotulo }}</span>
            <span class="sax-cupon-selo-card__label">{{ __('messages.cupon_selo_card_label') }}</span>
        </span>
    @else
        <div class="sax-cupon-produto">
            <span class="sax-cupon-produto__preco">{{ currency_format($precoFinal) }}</span>
            <span class="sax-cupon-produto__texto">{{ __('messages.cupon_selo_produto_texto') }}</span>
            <span class="sax-cupon-produto__codigo">{{ $cupon->codigo }}</span>
            <span class="sax-cupon-produto__regra">
                - {{ $rotulo }} · {{ $cupon->rotuloEscopo() }}
            </span>
        </div>
    @endif
@endif
