document.addEventListener('DOMContentLoaded', function () {

    // =========================
    // ====== STEPS ===========
    // =========================
    const currentStepInput = document.getElementById('currentStep');
    let currentStep = Number(currentStepInput?.value || 1);
    const steps = document.querySelectorAll('.step');

    function showStep(step) {
        steps.forEach((s, i) => s.classList.toggle('active', i === step - 1));
        if (currentStepInput) currentStepInput.value = step;
        window.scrollTo(0, 0); // Sobe a página ao trocar de step
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

    // =========================
    // ====== ENDEREÇO & FRETE =
    // =========================
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    const shippingRegistered = document.getElementById('shipping_registered');
    const shippingAlternative = document.getElementById('shipping_alternative');
    const shippingStore = document.getElementById('shipping_store');
    
    const countrySelect = document.getElementById('country');
    const stateSelect = document.getElementById('state-select');
    const citySelect = document.getElementById('city-select');
    const postalInput = document.getElementById('postal_code');
    const labelPostal = document.getElementById('label-postal');
    const labelState = document.getElementById('label-state');

    const storeSelect = document.getElementById('storeSelect');
    const storeMaps = document.querySelectorAll('.store-map');
    const observationsInput = document.getElementById('observations');
    
    const infoBox = document.getElementById('shipping-info-box');
    const infoContent = document.getElementById('shipping-info-content');

    let paraguayData = null; 
    let observationsValues = { 1: '', 2: '', 3: '' };

    // --- Lógica de Mensagens ---
    function updateShippingMessage(method) {
        if (!infoBox || !infoContent) return;
        let message = "";
        infoBox.style.display = 'block';

        if (method == 3) {
            message = '<i class="fa fa-check-circle me-2"></i> <strong>Frete Grátis!</strong> Seu pedido estará pronto para retirada na loja selecionada em breve.';
        } else {
            const country = countrySelect?.value || 'brasil';
            if (country === 'brasil') {
                message = '<i class="fa fa-whatsapp me-2"></i> <strong>Envio para o Brasil:</strong> O frete será combinado via <strong>WhatsApp</strong> após a finalização.';
            } else {
                message = '<i class="fa fa-truck me-2"></i> <strong>Envio no Paraguai:</strong> Frete fixo de <strong>U$ 5</strong> (perto) ou <strong>U$ 10</strong> (outras cidades). Detalhes via WhatsApp.';
            }
        }
        infoContent.innerHTML = message;
    }

    // --- Carga de Dados: BRASIL (IBGE) ---
    function loadBrasilStates(callback = null) {
        stateSelect.innerHTML = '<option value="">Carregando estados...</option>';
        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
            .then(res => res.json())
            .then(data => {
                stateSelect.innerHTML = '<option value="">Selecione o Estado</option>';
                data.forEach(uf => {
                    stateSelect.innerHTML += `<option value="${uf.sigla}" data-id="${uf.id}">${uf.nome}</option>`;
                });
                if (callback) callback();
            });
    }

    // --- Carga de Dados: PARAGUAI (JSON LOCAL) ---
    function loadParaguayData() {
        stateSelect.innerHTML = '<option value="">Carregando...</option>';
        fetch('/data/py.json')
            .then(res => res.json())
            .then(data => {
                paraguayData = data;
                const depts = [...new Set(data.map(item => item.admin_name))].sort();
                stateSelect.innerHTML = '<option value="">Selecione o Departamento</option>';
                depts.forEach(d => {
                    stateSelect.innerHTML += `<option value="${d}">${d}</option>`;
                });
            });
    }

    // --- Evento: Troca de Estado -> Carrega Cidades ---
    stateSelect.addEventListener('change', function(e, cityToSelect = null) {
        const country = countrySelect.value;
        if (!this.value) return;

        citySelect.disabled = false;
        citySelect.innerHTML = '<option value="">Carregando cidades...</option>';

        if (country === 'brasil') {
            const stateId = this.options[this.selectedIndex].getAttribute('data-id');
            if(!stateId) return;

            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${stateId}/municipios`)
                .then(res => res.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Selecione a Cidade</option>';
                    data.forEach(c => {
                        const selected = (cityToSelect && c.nome === cityToSelect) ? 'selected' : '';
                        citySelect.innerHTML += `<option value="${c.nome}" ${selected}>${c.nome}</option>`;
                    });
                });
        } else if (paraguayData) {
            const dept = this.value;
            const cities = paraguayData.filter(item => item.admin_name === dept);
            citySelect.innerHTML = '<option value="">Selecione a Cidade</option>';
            cities.forEach(c => {
                const selected = (cityToSelect && c.city === cityToSelect) ? 'selected' : '';
                citySelect.innerHTML += `<option value="${c.city}" ${selected}>${c.city}</option>`;
            });
        }
    });

    // --- Troca de País ---
    window.toggleCountryFields = function() {
        const country = countrySelect.value;
        citySelect.innerHTML = '<option value="">Selecione o estado primeiro</option>';
        citySelect.disabled = true;

        if (country === 'brasil') {
            labelPostal.innerText = "CEP";
            postalInput.placeholder = "00000-000";
            labelState.innerText = "Estado";
            loadBrasilStates();
        } else {
            labelPostal.innerText = "Código da Casa (opcional)";
            postalInput.placeholder = "Ex: 1234";
            labelState.innerText = "Departamento";
            loadParaguayData();
        }
        updateShippingMessage(document.querySelector('input[name="shipping"]:checked')?.value);
    }

    // ==========================================
    // ====== AUTOCOMPLETE CEP (VIA CEP) ========
    // ==========================================
    if (postalInput) {
        postalInput.addEventListener('input', function(e) {
            if (countrySelect.value === 'brasil') {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) value = value.substring(0, 5) + '-' + value.substring(5, 8);
                e.target.value = value;
            }
        });

        postalInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (countrySelect.value === 'brasil' && cep.length === 8) {
                const originalLabel = labelPostal.innerText;
                labelPostal.innerHTML = 'CEP <i class="fa fa-spinner fa-spin"></i>';

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        labelPostal.innerText = originalLabel;
                        if (!data.erro) {
                            // Preenche Rua
                            const streetInp = document.querySelector('input[name="street"]');
                            if (streetInp) streetInp.value = data.logradouro;

                            // Seleciona Estado e dispara busca de cidades
                            stateSelect.value = data.uf;
                            const event = new Event('change');
                            stateSelect.dispatchEvent(event);

                            // Aguarda carregar cidades do IBGE e seleciona
                            setTimeout(() => {
                                // Forçamos a seleção da cidade após o fetch do IBGE completar
                                const stateId = stateSelect.options[stateSelect.selectedIndex].getAttribute('data-id');
                                fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${stateId}/municipios`)
                                    .then(r => r.json())
                                    .then(cities => {
                                        citySelect.innerHTML = '<option value="">Selecione a Cidade</option>';
                                        cities.forEach(c => {
                                            const sel = c.nome.toUpperCase() === data.localidade.toUpperCase() ? 'selected' : '';
                                            citySelect.innerHTML += `<option value="${c.nome}" ${sel}>${c.nome}</option>`;
                                        });
                                        citySelect.disabled = false;
                                    });
                            }, 500);
                        }
                    }).catch(() => labelPostal.innerText = originalLabel);
            }
        });
    }

    // --- Seleção de Método de Envio ---
    window.handleShippingSelection = function(value) {
        if(shippingRegistered) shippingRegistered.style.display = value == 1 ? 'block' : 'none';
        if(shippingAlternative) shippingAlternative.style.display = value == 2 ? 'block' : 'none';
        if(shippingStore) shippingStore.style.display = value == 3 ? 'block' : 'none';

        document.querySelectorAll('.sax-method-card').forEach(card => card.classList.remove('active'));
        document.getElementById('label-ship-' + value)?.classList.add('active');

        updateShippingMessage(value);

        if (value == 2) window.toggleCountryFields();
        if (observationsInput) observationsInput.value = observationsValues[value] || '';
    }

    // --- Mapas e Outros ---
    window.updateStoreMap = function() {
        if (!storeSelect) return;
        storeMaps.forEach(map => {
            map.style.display = map.id === 'map-' + storeSelect.value ? 'block' : 'none';
        });
    }

    shippingRadios.forEach(radio => radio.addEventListener('change', function() {
        window.handleShippingSelection(this.value);
    }));

    if (countrySelect) countrySelect.addEventListener('change', window.toggleCountryFields);
    if (storeSelect) storeSelect.addEventListener('change', window.updateStoreMap);

    // Inicialização
    const checkedRadio = document.querySelector('input[name="shipping"]:checked');
    if (checkedRadio) window.handleShippingSelection(checkedRadio.value);
    if (storeSelect) window.updateStoreMap();

    // =========================
    // ====== PAGAMENTO =======
    // =========================
    window.selectPayment = function(method) {
        const paymentInput = document.getElementById('payment_method');
        const instruction = document.getElementById('payment-instruction');
        if (paymentInput) paymentInput.value = method;

        document.querySelectorAll('.sax-payment-method').forEach((button) => {
            button.classList.toggle('active', button.dataset.paymentMethod === method);
        });

        if (instruction) {
            instruction.innerText = ['bancard', 'bancard_v2'].includes(method)
                ? "Finalize seu pagamento através do portal seguro Bancard."
                : "Após finalizar, você deverá enviar o comprovante do depósito/transferência.";
        }
    };

    document.querySelectorAll('.sax-payment-method[data-payment-method]').forEach((button) => {
        button.addEventListener('click', function() {
            window.selectPayment(this.dataset.paymentMethod);
        });
    });

    const initialPay = document.getElementById('payment_method')?.value || 'deposito';
    window.selectPayment(initialPay);
});