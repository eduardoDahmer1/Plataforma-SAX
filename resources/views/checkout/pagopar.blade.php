@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-credit-card"></i> Pagamento com PagoPAR</h2>
        <p class="text-muted">Você será redirecionado para o pagamento seguro.</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px;">
            <div id="iframe-container" class="w-100" style="max-width: 500px;"></div>
        </div>
    </div>

    <div class="text-center">
        <p class="text-muted">Não feche esta página até concluir o pagamento.</p>
    </div>
</div>

<script src="{{ asset('js/pagopar-checkout.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const processId = "{{ $processId }}";
    const publicKey = "{{ $publicKey }}";

    const options = {
        publicKey: publicKey,
        processId: processId,
        styles: {
            'input-background-color': '#FFFFFF',
            'input-text-color': '#555555',
            'input-border-color': '#CCCCCC',
            'button-background-color': '#007BFF',
            'button-text-color': '#FFFFFF',
            'form-background-color': '#FFFFFF',
            'form-border-color': '#DDDDDD'
        }
    };

    // Inicializa o iframe do PagoPAR (assumindo que seu JS já sabe lidar com `options`)
    PagoPar.Checkout.createForm('iframe-container', options);
});
</script>
@endsection
