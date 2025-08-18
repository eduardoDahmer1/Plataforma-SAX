@extends('layout.checkout')

@section('content')
<style>
.step {
    display: none;
}
.step.active {
    display: block;
}
.checkout-box {
    border: 1px solid #ccc;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.payment-details .card {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.payment-details .card .card-body {
    padding: 15px;
}
.payment-details .card .card-title {
    font-size: 1.2em;
    margin-bottom: 10px;
}
.payment-details .card .card-text {
    font-size: 1em;
    margin-bottom: 10px;
}
.payment-details .card .btn {
    width: 100%;
}
.form-control {
    margin-bottom: 15px;
}
.upload-btn {
    display: flex;
    flex-direction: column;
}
.upload-btn input[type="file"] {
    margin-top: 10px;
}
</style>

<div class="container mt-5">
    <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" id="currentStep">

        {{-- STEP 1 --}}
        <div class="step active" id="step1">
            <div class="checkout-box">
                <h4>Itens no Carrinho</h4>
                @php $totalCarrinho = 0; @endphp
                @foreach ($cart as $item)
                    <div class="cart-item">
                        <p><strong>{{ $item->product->external_name ?? 'Produto' }}</strong></p>
                        <p>Preço: R$ {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</p>
                        <p>Quantidade: {{ $item->quantity }}</p>
                        <p>Total: R$ {{ number_format(($item->product->price ?? 0) * $item->quantity, 2, ',', '.') }}</p>
                    </div>
                    @php $totalCarrinho += ($item->product->price ?? 0) * $item->quantity; @endphp
                @endforeach
                <div class="total-carrinho">
                    <h4>Total do Carrinho: R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</h4>
                </div>
                <button type="button" class="btn btn-primary" onclick="nextStep(1)">Seguir</button>
            </div>
        </div>

        {{-- STEP 2 --}}
        <div class="step" id="step2">
            <div class="checkout-box">
                <h4>Dados Pessoais</h4>
                <label>Nome Completo *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') ?? auth()->user()->name }}"><br>

                <label>Documento *</label>
                <input type="text" name="document" class="form-control" value="{{ old('document') ?? auth()->user()->document }}"><br>

                <label>Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') ?? auth()->user()->email }}"><br>

                <label>Telefone *</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') ?? auth()->user()->phone_number }}"><br>
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Voltar</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Seguir</button>
        </div>

        {{-- STEP 3 --}}
        <div class="step" id="step3">
            <div class="checkout-box">
                <h4>Endereço de Envio</h4>
                <label><input type="radio" name="shipping" value="1" checked> Enviar para o Endereço Cadastrado</label><br>
                <label><input type="radio" name="shipping" value="2"> Endereço Alternativo</label><br>
                <label><input type="radio" name="shipping" value="3"> Retirar na Loja</label><br>

                {{-- Endereço Cadastrado --}}
                <p>Endereço Cadastrado:
                    <strong>{{ auth()->user()->street }}, {{ auth()->user()->number }}, {{ auth()->user()->city }}, {{ auth()->user()->state }}</strong>
                </p>

                {{-- Endereço Alternativo --}}
                <div id="nc_addressShipping" style="display:none;">
                    <label>Escolha o País</label>
                    <select name="country" id="country" class="form-control">
                        <option value="brasil">Brasil</option>
                        <option value="paraguai">Paraguai</option>
                    </select><br>

                    <div id="brasil_address" style="display:none;">
                        <label>CEP</label>
                        <input type="text" name="cep" class="form-control" placeholder="CEP"><br>
                        <label>Rua</label>
                        <input type="text" name="street" class="form-control" placeholder="Rua"><br>
                        <label>Número</label>
                        <input type="text" name="number" class="form-control" placeholder="Número da casa"><br>
                        <label>Observações</label>
                        <input type="text" name="observations" class="form-control" placeholder="Observações"><br>
                    </div>

                    <div id="paraguai_address" style="display:none;">
                        <label>Código da Casa</label>
                        <input type="text" name="house_code" class="form-control" placeholder="Código da casa"><br>
                        <label>Observações</label>
                        <input type="text" name="observations" class="form-control" placeholder="Observações"><br>
                    </div>
                </div>

                {{-- Retirar na Loja --}}
                <div id="storePickUp" style="display:none;">
                    <p>Escolha a Loja para Retirar:</p>
                    <select name="store" class="form-control">
                        <option value="1">SAX Ciudad del Este</option>
                        <option value="2">SAX Assunção</option>
                    </select>
                </div>

            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Voltar</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)">Seguir</button>
        </div>

        {{-- STEP 4 --}}
        <div class="step" id="step4">
            <div class="checkout-box">
                <h4>Método de Pagamento</h4>
                <label>
                    <input type="radio" name="payment_method" value="deposito" checked onclick="togglePaymentMethod('deposito')"> Depósito bancário
                </label><br>
                <label>
                    <input type="radio" name="payment_method" value="bancard" onclick="togglePaymentMethod('bancard')"> Bancard
                </label>
            </div>

            {{-- Depósito Bancário --}}
            <div id="bankDetails" class="payment-details">
                <h5>Escolha o Banco para Depósito</h5>
                <div class="row">
                    @foreach ($paymentMethods as $method)
                        @if ($method->type === 'bank')
                        <div class="col-12 col-sm-6 col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $method->name }}</h6>
                                    <p class="card-text">{{ $method->bank_details }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="upload-btn">
                    <label>Envie o Comprovante de Depósito (opcional)</label>
                    <input type="file" name="deposit_receipt" class="form-control"><br>
                </div>
            </div>

            {{-- Modal Bancard --}}
            <div class="modal fade" id="bancardModal" tabindex="-1" aria-labelledby="bancardModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bancardModalLabel">Pagamento Bancard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Preencha os dados do cartão e confirme para continuar com o pagamento.</p>
                    <label>Número do Cartão</label>
                    <input type="text" class="form-control" name="card_number"><br>
                    <label>Validade</label>
                    <input type="text" class="form-control" name="card_expiry"><br>
                    <label>CVV</label>
                    <input type="text" class="form-control" name="card_cvv"><br>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="checkoutForm" class="btn btn-primary">Pagar com Bancard</button>
                </div>
                </div>
            </div>
            </div>

            {{-- Resumo do Pedido --}}
            @php $totalPedido = 0; @endphp
            <div class="checkout-box">
                <h4>Resumo do Pedido</h4>
                @foreach ($cart as $item)
                    <p><strong>{{ $item->product->slug ?? 'Produto' }}</strong></p>
                    <p>Preço: R$ {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</p>
                    <p>Quantidade: {{ $item->quantity }}</p>
                    <p>Total: R$ {{ number_format(($item->product->price ?? 0) * $item->quantity, 2, ',', '.') }}</p>
                    @php $totalPedido += ($item->product->price ?? 0) * $item->quantity; @endphp
                @endforeach
                <hr>
                <h5>Total do Pedido: R$ {{ number_format($totalPedido, 2, ',', '.') }}</h5>
            </div>

            <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Voltar</button>
            <button type="submit" class="btn btn-success" id="checkoutSubmit">Finalizar Compra</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ====== STEPS ======
    let currentStep = 1;
    const steps = document.querySelectorAll('.step');
    function showStep(step){
        steps.forEach((s,i)=> s.classList.toggle('active', i===step-1));
        document.getElementById('currentStep').value = step;
    }
    window.nextStep = function(step){ currentStep = step+1; showStep(currentStep);}
    window.prevStep = function(step){ currentStep = step; showStep(currentStep);}
    showStep(currentStep);

    // ====== ENDEREÇO ======
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    const ncAddressShipping = document.getElementById('nc_addressShipping');
    const storePickUp = document.getElementById('storePickUp');
    const countrySelect = document.getElementById('country');
    const brasilAddress = document.getElementById('brasil_address');
    const paraguaiAddress = document.getElementById('paraguai_address');

    function updateCountryFields(){
        if(!countrySelect) return;
        brasilAddress.style.display = countrySelect.value==='brasil'?'block':'none';
        paraguaiAddress.style.display = countrySelect.value==='paraguai'?'block':'none';
    }

    function updateShippingDisplay(){
        const checkedRadio = document.querySelector('input[name="shipping"]:checked');
        if(!checkedRadio) return;
        const value = checkedRadio.value;
        ncAddressShipping.style.display = value==='2'?'block':'none';
        storePickUp.style.display = value==='3'?'block':'none';
        if(value==='2') updateCountryFields();
        else { brasilAddress.style.display='none'; paraguaiAddress.style.display='none'; }
    }

    shippingRadios.forEach(radio => radio.addEventListener('change', updateShippingDisplay));
    if(countrySelect) countrySelect.addEventListener('change', updateCountryFields);
    updateShippingDisplay();

    // ====== PAGAMENTO ======
    const bancardModal = new bootstrap.Modal(document.getElementById('bancardModal'));
    window.togglePaymentMethod = function(method){
        const bankDetails = document.getElementById('bankDetails');

        if(method === 'bancard'){
            bankDetails.style.display = 'none';
            bancardModal.show(); // abrir modal
        } else if(method === 'deposito'){
            bankDetails.style.display = 'block';
            bancardModal.hide(); // fechar modal
        }
    }

    // ====== VALIDAÇÃO SIMPLES NO FRONT ======
    document.getElementById('checkoutForm').addEventListener('submit', function(e){
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        if(!selectedPayment){
            e.preventDefault();
            alert('Escolha um método de pagamento');
            return false;
        }
    });
});
</script>

@endsection
