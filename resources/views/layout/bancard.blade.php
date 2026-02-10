@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-credit-card"></i> Pagamento Seguro</h2>
        {{-- Adicionado o operador nulo ?? para garantir que a página carregue mesmo se o ID falhar --}}
        <p class="text-muted">Pedido #{{ $order->id ?? 'N/A' }} - Finalize o pagamento abaixo.</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-center" style="min-height: 450px; background: #fdfdfd;">
            {{-- O Bancard injetará o formulário aqui --}}
            <div id="iframe-container" class="w-100"></div>
        </div>
    </div>

    <div class="text-center">
        {{-- Rota de retorno caso o usuário desista --}}
        <a href="{{ route('checkout.index') }}" class="btn btn-link text-decoration-none text-muted">
            <i class="fa fa-arrow-left"></i> Voltar e alterar forma de pagamento
        </a>
    </div>
</div>

{{-- Seleção dinâmica do script baseada no ambiente --}}
@php
    $isSandbox = env('BANCARD_MODE') === 'sandbox';
    $scriptUrl = $isSandbox 
        ? 'https://vpos.infonet.com.py:8888/checkout/v2/lib/bancard-checkout.js' 
        : 'https://vpos.infonet.com.py/checkout/v2/lib/bancard-checkout.js';
@endphp

<script src="{{ $scriptUrl }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // O processId vem da sua BancardController
    const processId = "{{ $process_id }}";

    if (!processId || processId === "") {
        console.error('Bancard: Process ID ausente ou inválido.');
        alert('Erro ao carregar o checkout. Por favor, tente novamente.');
        return;
    }

    // Mantive suas configurações de estilo personalizadas
    const options = {
        styles: {
            'form-background-color': '#fdfdfd',
            'button-background-color': '#000000',
            'button-text-color': '#ffffff',
            'input-border-color': '#cccccc',
            'header-background-color': '#fdfdfd',
            'input-text-color': '#333333'
        }
    };

    try {
        /**
         * Inicializa o iframe oficial da Bancard.
         */
        Bancard.Checkout.createForm('iframe-container', processId, options);
        console.log('Bancard: Checkout iniciado com sucesso.');
    } catch (err) {
        console.error('Bancard: Erro crítico na inicialização do formulário:', err);
    }
});
</script>
@endsection