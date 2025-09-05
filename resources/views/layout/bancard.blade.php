@extends('layout.checkout') {{-- ou seu layout principal --}}

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-credit-card"></i> Pagamento com Bancard</h2>
        <p class="text-muted">Você será redirecionado para o pagamento seguro.</p>
    </div>

    {{-- Container do iframe do Bancard --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-center align-items-center" style="min-height: 250px;">
            <div id="bancardContainer" class="w-100" style="max-width: 500px;"></div>
        </div>
    </div>

    {{-- Mensagem extra --}}
    <div class="text-center">
        <p class="text-muted">Não feche esta página até concluir o pagamento.</p>
    </div>
</div>

{{-- Script Bancard --}}
<script src="https://vpos.infonet.com.py/bancard-checkout-5.0.1.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const processId = "{{ $process_id }}";
        const options = {
            process_id: processId,
            container: "#bancardContainer",
            callback: "{{ route('bancard.callback') }}",
        };
        BancardCheckout.initialize(options);
    });
</script>
@endsection
