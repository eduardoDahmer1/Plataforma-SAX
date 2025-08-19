@extends('layout.checkout')

@section('content')
<style>
/* Melhorando visual do modal Bancard */
#bancardModal .form-control {
    border-radius: 8px;
    padding: 10px;
    transition: 0.2s;
}

#bancardModal .form-control:focus {
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
    border-color: #007bff;
}

#bancardModal .modal-footer .btn i {
    margin-right: 5px;
}

/* ===================== STEPS ===================== */
.step {
    display: none;
}

.step.active {
    display: block;
}

/* ===================== CHECKOUT BOX ===================== */
.checkout-box {
    border: 1px solid #ddd;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    background-color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.checkout-box h4 {
    margin-bottom: 20px;
    font-weight: bold;
    color: #333;
}

/* ===================== CART ITEMS ===================== */
.cart-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.cart-item p {
    margin: 0;
}

.total-carrinho {
    margin-top: 15px;
    font-weight: bold;
    font-size: 1.2em;
    text-align: right;
}

/* ===================== FORM FIELDS ===================== */
.form-control {
    margin-bottom: 15px;
    border-radius: 8px;
    padding: 10px;
}

label {
    font-weight: 500;
}

/* ===================== BOTÕES ===================== */
.btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: bold;
}

.btn+.btn {
    margin-left: 10px;
}

/* ===================== RADIO & PAYMENT ===================== */
.payment-details .card {
    border: 1px solid #eee;
    border-radius: 10px;
    margin-bottom: 15px;
    transition: transform 0.2s;
}

.payment-details .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
}

.payment-details .card-body {
    padding: 15px;
}

.payment-details .card-title {
    font-weight: 600;
    font-size: 1.1em;
}

.payment-details .card-text {
    font-size: 0.95em;
    color: #555;
}

/* ===================== RESPONSIVO ===================== */
@media (max-width: 576px) {
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .total-carrinho {
        text-align: left;
    }
}
</style>

<div class="container mt-5">
    <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" id="currentStep">

        {{-- STEP 1: CARRINHO --}}
        <div class="step active" id="step1">
            <div class="checkout-box">
                <h4><i class="fa fa-shopping-cart"></i> Itens no Carrinho</h4>
                @php $totalCarrinho = 0; @endphp
                @foreach ($cart as $item)
                <div class="cart-item">
                    <div>
                        <p><strong>{{ $item->product->external_name ?? 'Produto' }}</strong></p>
                        <p><i class="fa fa-dollar-sign"></i> R$
                            {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p><i class="fa fa-sort-numeric-up"></i> Quantidade: {{ $item->quantity }}</p>
                        <p><i class="fa fa-calculator"></i> Total: R$
                            {{ number_format(($item->product->price ?? 0) * $item->quantity, 2, ',', '.') }}</p>
                    </div>
                </div>
                @php $totalCarrinho += ($item->product->price ?? 0) * $item->quantity; @endphp
                @endforeach
                <div class="total-carrinho">
                    <h5>Total do Carrinho: R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</h5>
                </div>
                <button type="button" class="btn btn-primary mt-3" onclick="nextStep(1)"><i
                        class="fa fa-arrow-right"></i> Seguir</button>
            </div>
        </div>

        {{-- STEP 2: DADOS PESSOAIS --}}
        <div class="step" id="step2">
            <div class="checkout-box">
                <h4><i class="fa fa-user"></i> Dados Pessoais</h4>
                <label>Nome Completo *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') ?? auth()->user()->name }}">

                <label>Documento *</label>
                <input type="text" name="document" class="form-control"
                    value="{{ old('document') ?? auth()->user()->document }}">

                <label>Email *</label>
                <input type="email" name="email" class="form-control"
                    value="{{ old('email') ?? auth()->user()->email }}">

                <label>Telefone *</label>
                <input type="text" name="phone" class="form-control"
                    value="{{ old('phone') ?? auth()->user()->phone_number }}">
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fa fa-arrow-left"></i>
                Voltar</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(2)"><i class="fa fa-arrow-right"></i>
                Seguir</button>
        </div>

        {{-- STEP 3: ENDEREÇO --}}
        <div class="step" id="step3">
            <div class="checkout-box">
                <h4><i class="fa fa-map-marker-alt"></i> Endereço de Envio</h4>
                <label><input type="radio" name="shipping" value="1" checked> Enviar para o Endereço
                    Cadastrado</label><br>
                <label><input type="radio" name="shipping" value="2"> Endereço Alternativo</label><br>
                <label><input type="radio" name="shipping" value="3"> Retirar na Loja</label><br>

                {{-- Endereço Cadastrado --}}
                <p><i class="fa fa-home"></i> Endereço: <strong>{{ auth()->user()->street }},
                        {{ auth()->user()->number }}, {{ auth()->user()->city }}, {{ auth()->user()->state }}</strong>
                </p>

                {{-- Endereço Alternativo --}}
                <div id="nc_addressShipping" style="display:none;">
                    <label>Escolha o País</label>
                    <select name="country" id="country" class="form-control">
                        <option value="brasil">Brasil</option>
                        <option value="paraguai">Paraguai</option>
                    </select>

                    <div id="brasil_address" style="display:none;">
                        <label>CEP</label>
                        <input type="text" name="cep" class="form-control" placeholder="CEP">
                        <label>Rua</label>
                        <input type="text" name="street" class="form-control" placeholder="Rua">
                        <label>Número</label>
                        <input type="text" name="number" class="form-control" placeholder="Número da casa">
                        <label>Observações</label>
                        <input type="text" name="observations" class="form-control" placeholder="Observações">
                    </div>

                    <div id="paraguai_address" style="display:none;">
                        <label>Código da Casa</label>
                        <input type="text" name="house_code" class="form-control" placeholder="Código da casa">
                        <label>Observações</label>
                        <input type="text" name="observations" class="form-control" placeholder="Observações">
                    </div>
                </div>

                {{-- Retirar na Loja --}}
                <div id="storePickUp" style="display:none;">
                    <p><i class="fa fa-store"></i> Escolha a Loja:</p>
                    <select name="store" class="form-control">
                        <option value="1">SAX Ciudad del Este</option>
                        <option value="2">SAX Assunção</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i class="fa fa-arrow-left"></i>
                Voltar</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)"><i class="fa fa-arrow-right"></i>
                Seguir</button>
        </div>

        {{-- STEP 4: PAGAMENTO --}}
        <div class="step" id="step4">
            <div class="checkout-box">
                <h4><i class="fa fa-credit-card"></i> Método de Pagamento</h4>
                <label><input type="radio" name="payment_method" value="deposito" checked
                        onclick="togglePaymentMethod('deposito')"> Depósito bancário</label><br>
                <label><input type="radio" name="payment_method" value="bancard"
                        onclick="togglePaymentMethod('bancard')"> Bancard</label>
            </div>

            <div id="bankDetails" class="payment-details">
                <h5><i class="fa fa-university"></i> Escolha o Banco</h5>
                <div class="row">
                    @foreach ($paymentMethods as $method)
                    @if ($method->type === 'bank')
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fa fa-building"></i> {{ $method->name }}</h6>
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
            <div class="modal fade" id="bancardModal" tabindex="-1" aria-labelledby="bancardModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.2);">
                        <div class="modal-header bg-primary text-white"
                            style="border-top-left-radius:12px; border-top-right-radius:12px;">
                            <h5 class="modal-title" id="bancardModalLabel"><i class="fa fa-credit-card"></i> Pagamento
                                Bancard</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3"><i class="fa fa-info-circle"></i> Preencha os dados do cartão
                                para continuar com o pagamento.</p>

                            <div class="mb-3">
                                <label><i class="fa fa-credit-card"></i> Número do Cartão</label>
                                <input type="text" class="form-control" name="card_number"
                                    placeholder="XXXX XXXX XXXX XXXX">
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label><i class="fa fa-calendar-alt"></i> Validade</label>
                                    <input type="text" class="form-control" name="card_expiry" placeholder="MM/AA">
                                </div>
                                <div class="col-6 mb-3">
                                    <label><i class="fa fa-lock"></i> CVV</label>
                                    <input type="text" class="form-control" name="card_cvv" placeholder="XXX">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i
                                    class="fa fa-times"></i> Cancelar</button>
                            <button type="submit" form="checkoutForm" class="btn btn-primary"><i
                                    class="fa fa-check-circle"></i> Pagar com Bancard</button>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Resumo do Pedido --}}
            @php $totalPedido = 0; @endphp
            <div class="checkout-box">
                <h4><i class="fa fa-receipt"></i> Resumo do Pedido</h4>
                @foreach ($cart as $item)
                <p><strong>{{ $item->product->slug ?? 'Produto' }}</strong></p>
                <p><i class="fa fa-dollar-sign"></i> Preço: R$
                    {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</p>
                <p><i class="fa fa-sort-numeric-up"></i> Quantidade: {{ $item->quantity }}</p>
                <p><i class="fa fa-calculator"></i> Total: R$
                    {{ number_format(($item->product->price ?? 0) * $item->quantity, 2, ',', '.') }}</p>
                @php $totalPedido += ($item->product->price ?? 0) * $item->quantity; @endphp
                @endforeach
                <hr>
                <h5><i class="fa fa-money-bill-wave"></i> Total do Pedido: R$
                    {{ number_format($totalPedido, 2, ',', '.') }}</h5>
            </div>

            <button type="button" class="btn btn-secondary" onclick="prevStep(3)"><i class="fa fa-arrow-left"></i>
                Voltar</button>
            <button type="submit" class="btn btn-success" id="checkoutSubmit"><i class="fa fa-check"></i> Finalizar
                Compra</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ====== STEPS ======
    let currentStep = 1;
    const steps = document.querySelectorAll('.step');

    function showStep(step) {
        steps.forEach((s, i) => s.classList.toggle('active', i === step - 1));
        document.getElementById('currentStep').value = step;
    }
    window.nextStep = function(step) {
        currentStep = step + 1;
        showStep(currentStep);
    }
    window.prevStep = function(step) {
        currentStep = step;
        showStep(currentStep);
    }
    showStep(currentStep);

    // ====== ENDEREÇO ======
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    const ncAddressShipping = document.getElementById('nc_addressShipping');
    const storePickUp = document.getElementById('storePickUp');
    const countrySelect = document.getElementById('country');
    const brasilAddress = document.getElementById('brasil_address');
    const paraguaiAddress = document.getElementById('paraguai_address');

    function updateCountryFields() {
        if (!countrySelect) return;
        brasilAddress.style.display = countrySelect.value === 'brasil' ? 'block' : 'none';
        paraguaiAddress.style.display = countrySelect.value === 'paraguai' ? 'block' : 'none';
    }

    function updateShippingDisplay() {
        const checkedRadio = document.querySelector('input[name="shipping"]:checked');
        if (!checkedRadio) return;
        const value = checkedRadio.value;
        ncAddressShipping.style.display = value === '2' ? 'block' : 'none';
        storePickUp.style.display = value === '3' ? 'block' : 'none';
        if (value === '2') updateCountryFields();
        else {
            brasilAddress.style.display = 'none';
            paraguaiAddress.style.display = 'none';
        }
    }

    shippingRadios.forEach(radio => radio.addEventListener('change', updateShippingDisplay));
    if (countrySelect) countrySelect.addEventListener('change', updateCountryFields);
    updateShippingDisplay();

    // ====== PAGAMENTO ======
    const bancardModal = new bootstrap.Modal(document.getElementById('bancardModal'));
    window.togglePaymentMethod = function(method) {
        const bankDetails = document.getElementById('bankDetails');

        if (method === 'bancard') {
            bankDetails.style.display = 'none';
            bancardModal.show(); // abrir modal
        } else if (method === 'deposito') {
            bankDetails.style.display = 'block';
            bancardModal.hide(); // fechar modal
        }
    }

    // ====== VALIDAÇÃO SIMPLES NO FRONT ======
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedPayment) {
            e.preventDefault();
            alert('Escolha um método de pagamento');
            return false;
        }
    });
});
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection