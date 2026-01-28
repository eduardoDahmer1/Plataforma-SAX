@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-credit-card"></i> Pagamento Seguro</h2>
        <p class="text-muted">Pedido #{{ $order->id }} - Finalize o pagamento abaixo.</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-center" style="min-height: 400px; background: #fdfdfd;">
            {{-- O Bancard injetará o formulário aqui --}}
            <div id="iframe-container" class="w-100"></div>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('checkout.index') }}" class="btn btn-link text-decoration-none text-muted">
            <i class="fa fa-arrow-left"></i> Voltar e alterar forma de pagamento
        </a>
    </div>
</div>

{{-- Carrega o script oficial do Bancard conforme o ambiente --}}
<script src="{{ env('BANCARD_MODE') === 'sandbox' ? 'https://vpos.infonet.com.py:8888/checkout/resources/js/vpos.js' : 'https://vpos.infonet.com.py/checkout/resources/js/vpos.js' }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const processId = "{{ $process_id }}";

    if(!processId) {
        console.error('Process ID ausente');
        return;
    }

    // Inicializa o Checkout
    try {
        Bancard.Checkout.createCheckout(processId, {
            onPaymentSuccess: function(data) {
                // Redireciona para o callback de sucesso
                window.location.href = "{{ route('bancard.callback') }}?process_id=" + processId + "&status=success";
            },
            onPaymentError: function(data) {
                // Redireciona informando a falha
                window.location.href = "{{ route('bancard.callback') }}?process_id=" + processId + "&status=failed";
            }
        });
    } catch(err) {
        console.error('Erro Bancard:', err);
    }
});
</script>
@endsection