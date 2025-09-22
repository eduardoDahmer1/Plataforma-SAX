@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-credit-card"></i> Pagamento com Bancard</h2>
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

<script src="{{ asset('js/bancard-checkout-5.0.1.js') }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const processId = "{{ $process_id }}"; // enviado do backend

    if(!processId) {
        alert('Erro: process_id não gerado. Contate o suporte.');
        return;
    }

    const styles = {
        'input-background-color': '#FFFFFF',
        'input-text-color': '#555555',
        'input-border-color': '#CCCCCC',
        'input-placeholder-color': '#999999',
        'button-background-color': '#5CB85C',
        'button-text-color': '#FFFFFF',
        'button-border-color': '#4CAE4C',
        'form-background-color': '#FFFFFF',
        'form-border-color': '#DDDDDD',
        'header-background-color': '#F5F5F5',
        'header-text-color': '#333333',
        'hr-border-color': '#EEEEEE'
    };

    const options = { styles: styles };

    // Inicializa o iFrame do Bancard
    try {
        Bancard.Checkout.createForm('iframe-container', processId, options);
    } catch(err) {
        console.error('Erro ao inicializar o iFrame do Bancard:', err);
        alert('Falha ao carregar o formulário de pagamento. Atualize a página e tente novamente.');
    }
});
</script>
@endsection
