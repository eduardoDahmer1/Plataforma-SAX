{{-- STEP 3: MÉTODO DE ENTREGA --}}
<div class="step" id="step3">
    <div class="sax-checkout-box">
        <h4 class="sax-step-title"><span class="step-number">03</span> Método de Entrega</h4>

        {{-- Opções de envio em Cards --}}
        <div class="sax-shipping-grid mb-4">
            <label class="sax-method-card active" id="label-ship-1">
                <input type="radio" name="shipping" value="1" checked onclick="handleShippingSelection(1)">
                <div class="method-icon"><i class="fa fa-home"></i></div>
                <div class="method-text">Endereço Cadastrado</div>
            </label>

            <label class="sax-method-card" id="label-ship-2">
                <input type="radio" name="shipping" value="2" onclick="handleShippingSelection(2)">
                <div class="method-icon"><i class="fa fa-map-marker-alt"></i></div>
                <div class="method-text">Novo Endereço</div>
            </label>

            <label class="sax-method-card" id="label-ship-3">
                <input type="radio" name="shipping" value="3" onclick="handleShippingSelection(3)">
                <div class="method-icon"><i class="fa fa-store"></i></div>
                <div class="method-text">Retirar na Loja</div>
            </label>
        </div>

        {{-- 1. Endereço Cadastrado --}}
        <div id="shipping_registered" class="sax-address-preview p-3 mb-3">
            <span class="d-block text-muted x-small mb-1">ENVIAR PARA:</span>
            <p class="mb-0 fw-bold">
                {{ auth()->user()->address ?? 'Nenhum endereço cadastrado' }}, 
                {{ auth()->user()->city ?? '' }} - {{ auth()->user()->state ?? '' }}
            </p>
        </div>

        {{-- 2. Endereço Alternativo --}}
        <div id="shipping_alternative" class="mt-4" style="display:none;">
            <div class="row g-3">
                <div class="col-12">
                    <label class="sax-label">Escolha o País</label>
                    <select name="country" id="country" class="sax-form-control" onchange="toggleCountryFields()">
                        <option value="brasil">Brasil</option>
                        <option value="paraguai">Paraguai</option>
                    </select>
                </div>

                <div id="brasil_address" class="row g-3 mx-0 px-0">
                    <div class="col-md-4">
                        <label class="sax-label">CEP</label>
                        <input type="text" name="cep" class="sax-form-control" placeholder="00000-000">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-label">Rua</label>
                        <input type="text" name="street" class="sax-form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="sax-label">Nº</label>
                        <input type="text" name="number" class="sax-form-control">
                    </div>
                </div>

                <div id="paraguai_address" style="display:none;" class="col-12">
                    <label class="sax-label">Código da Casa / Localização</label>
                    <input type="text" name="house_code" class="sax-form-control" placeholder="Ex: 1234">
                </div>
            </div>
        </div>

        {{-- 3. Retirar na Loja --}}
        <div id="shipping_store" class="mt-4" style="display:none;">
            <label class="sax-label">Escolha a Loja para Retirada</label>
            <select name="store" id="storeSelect" class="sax-form-control mb-3" onchange="updateStoreMap()">
                <option value="1">SAX Ciudad del Este</option>
                <option value="2">SAX Assunção</option>
            </select>

            <div id="storeMaps" class="sax-map-container rounded overflow-hidden">
                <div class="store-map" id="map-1">
                    <iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
                <div class="store-map" id="map-2" style="display:none;">
                    <iframe src="https://www.google.com/maps/embed?pb=..." width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
            </div>
        </div>

        {{-- Observações --}}
        <div class="mt-4">
            <label class="sax-label">Observações / Instruções de Entrega</label>
            <textarea name="observations" class="sax-form-control" rows="2">{{ old('observations') ?? auth()->user()->observations }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="sax-btn-prev" onclick="prevStep(2)">
            <i class="fa fa-arrow-left me-2"></i> Voltar
        </button>
        <button type="button" class="sax-btn-next" onclick="nextStep(3)">
            Continuar para Pagamento <i class="fa fa-arrow-right ms-2"></i>
        </button>
    </div>
</div>
<script>
function handleShippingSelection(value) {
    // 1. Alterna a classe 'active' nos cards visuais
    document.querySelectorAll('.sax-method-card').forEach(card => {
        card.classList.remove('active');
    });
    const selectedRef = document.getElementById('label-ship-' + value);
    if(selectedRef) selectedRef.classList.add('active');

    // 2. Executa a lógica de mostrar/esconder as seções (Antiga toggleShippingFields)
    
    // Esconde tudo primeiro
    document.getElementById('shipping_registered').style.display = 'none';
    document.getElementById('shipping_alternative').style.display = 'none';
    document.getElementById('shipping_store').style.display = 'none';

    // Mostra apenas o selecionado
    if (value === 1) {
        document.getElementById('shipping_registered').style.display = 'block';
    } else if (value === 2) {
        document.getElementById('shipping_alternative').style.display = 'block';
        toggleCountryFields(); // Garante que campos de país carreguem certo
    } else if (value === 3) {
        document.getElementById('shipping_store').style.display = 'block';
        updateStoreMap(); // Garante que o mapa carregue certo
    }
}

function toggleCountryFields() {
    const country = document.getElementById('country').value;
    const brasilDiv = document.getElementById('brasil_address');
    const paraguaiDiv = document.getElementById('paraguai_address');
    
    if(brasilDiv) brasilDiv.style.display = (country === 'brasil') ? 'flex' : 'none';
    if(paraguaiDiv) paraguaiDiv.style.display = (country === 'paraguai') ? 'block' : 'none';
}

function updateStoreMap() {
    const store = document.getElementById('storeSelect').value;
    const map1 = document.getElementById('map-1');
    const map2 = document.getElementById('map-2');
    
    if(map1) map1.style.display = (store == 1) ? 'block' : 'none';
    if(map2) map2.style.display = (store == 2) ? 'block' : 'none';
}

// Inicializa o estado correto ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    handleShippingSelection(1); 
});
</script>