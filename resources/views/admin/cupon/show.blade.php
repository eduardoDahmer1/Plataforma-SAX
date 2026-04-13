@extends('layout.admin')

@section('content')
<x-admin.card>
    <h3 class="mb-3">{{ $cupon->codigo }}</h3>

    <div class="sax-details-view">
        <p><strong>{{ __('messages.show_tipo_label') }}</strong> {{ ucfirst($cupon->tipo) }}</p>
        <p><strong>{{ __('messages.show_montante_label') }}</strong> {{ $cupon->montante }}</p>
        <p><strong>{{ __('messages.show_quantidade_label') }}</strong> {{ $cupon->quantidade ?? __('messages.ilimitado_text') }}</p>
        <p><strong>{{ __('messages.show_modelo_label') }}</strong> {{ $cupon->modelo ?? __('messages.todos_opt') }}</p>
        <p><strong>{{ __('messages.show_categoria_label') }}</strong> {{ $cupon->category->name ?? __('messages.todas_opt') }}</p>
        <p><strong>{{ __('messages.show_marca_label') }}</strong> {{ $cupon->brand->name ?? __('messages.todas_opt') }}</p>
        <p><strong>{{ __('messages.show_valor_min_label') }}</strong> {{ $cupon->valor_minimo ?? '-' }}</p>
        <p><strong>{{ __('messages.show_valor_max_label') }}</strong> {{ $cupon->valor_maximo ?? '-' }}</p>
        <p><strong>{{ __('messages.show_data_label') }}</strong> {{ $cupon->data_inicio->format('d/m/Y') }} - {{ $cupon->data_final->format('d/m/Y') }}</p>
        <p><strong>{{ __('messages.show_usado_label') }}</strong> {{ $cupon->usado }}</p>
    </div>

    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('admin.cupons.edit', $cupon) }}" class="btn btn-dark rounded-0 px-4 text-uppercase fw-bold x-small">
            {{ __('messages.editar_btn_mini') }}
        </a>
        <a href="{{ route('admin.cupons.index') }}" class="btn btn-outline-secondary rounded-0 px-4 text-uppercase fw-bold x-small">
            {{ __('messages.voltar_btn') }}
        </a>
    </div>
</x-admin.card>
@endsection