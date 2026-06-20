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
        window.scrollTo(0, 0);
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
    const freteDisplay = document.getElementById('frete-display');
    const totalDisplay = document.getElementById('total-geral-display');
    const freteValorInput = document.getElementById('frete_valor');
    const subtotalDisplay = document.getElementById('subtotal-valor');

    let paraguayData = null;
    let observationsValues = { 1: '', 2: '', 3: '' };

    function calcularFrete() {
        const radioSelected = document.querySelector('input[name="shipping"]:checked')?.value;
        
        if (radioSelected === '3') {
            if (freteDisplay) freteDisplay.innerText = 'Gratis';
            if (totalDisplay) totalDisplay.innerText = subtotalDisplay?.textContent?.trim() ?? '';
            if (freteValorInput) freteValorInput.value = '0.00';
            return; 
        }

        let cidade, pais;
        if (radioSelected === '1') {
            cidade = document.getElementById('user_city_data')?.value;
            pais = document.getElementById('user_country_data')?.value;
        } else {
            cidade = citySelect?.value;
            pais = countrySelect?.value;
        }

        if (!cidade || !pais) return;

        if (pais === 'brasil') {
            if (freteDisplay) freteDisplay.innerText = 'A combinar';
            
            if (totalDisplay) totalDisplay.innerText = subtotalDisplay?.textContent?.trim() ?? '';
            if (freteValorInput) freteValorInput.value = '0.00';
            return;
        }

        if (freteDisplay) freteDisplay.innerText = 'Calculando...';

        fetch("/checkout/calcular-frete", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ city: cidade, country: pais })
        })
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(data => {
            if (freteDisplay) freteDisplay.innerText = data.frete_formatado;
            if (totalDisplay) totalDisplay.innerText = data.total_formatado;
            if (freteValorInput) freteValorInput.value = (parseFloat(data.frete) || 0).toFixed(2);
        })
        .catch(error => {
            console.error('Erro no cálculo:', error);
            if (freteDisplay) freteDisplay.innerText = 'Erro ao calcular';
        });
    }

    function updateShippingMessage(method) {
        if (!infoBox || !infoContent) return;
        infoBox.style.display = 'block';

        if (method == 3) {
            // Retirada na Loja
            infoContent.innerHTML = '<i class="fa fa-shopping-bag me-2"></i> <strong>Retirada na Loja:</strong> Selecione a unidade de preferência no mapa. <strong>Frete Grátis.</strong>';
            if (freteDisplay) freteDisplay.innerText = 'Gratis';
            if (totalDisplay) totalDisplay.innerText = subtotalDisplay?.textContent?.trim() ?? '';
            if (freteValorInput) freteValorInput.value = '0.00';

        } else {
            let country = (method == 1) 
                ? (document.getElementById('user_country_data')?.value || 'brasil') 
                : (countrySelect?.value || 'brasil');

            if (country.toLowerCase() === 'brasil') {
                // Envio para o Brasil
                infoContent.innerHTML = '<i class="fa fa-whatsapp me-2"></i> <strong>Envio Internacional (Brasil):</strong> O valor do frete não está incluso no total. <strong>Será combinado via WhatsApp</strong> após a finalização do pedido.';
                if (freteDisplay) freteDisplay.innerText = 'A combinar';
                if (totalDisplay) totalDisplay.innerText = subtotalDisplay?.textContent?.trim() ?? '';
                if (freteValorInput) freteValorInput.value = '0.00';

            } else {
                // Envio no Paraguai
                infoContent.innerHTML = '<i class="fa fa-truck me-2"></i> <strong>Envio Nacional (Paraguai):</strong> O custo do frete será calculado com base na sua cidade e adicionado ao total abaixo.';
                // O calcularFrete() será chamado em seguida para preencher os valores reais
                if (freteDisplay) freteDisplay.innerText = 'Calculando...';
            }
        }
    }

    function loadBrasilStates(callback = null) {
        stateSelect.innerHTML = '<option value="">Carregando estados...</option>';
        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
            .then(res => res.json())
            .then(data => {
                stateSelect.innerHTML = '<option value="">Selecione o Estado</option>';
                data.forEach(uf => stateSelect.innerHTML += `<option value="${uf.sigla}" data-id="${uf.id}">${uf.nome}</option>`);
                if (callback) callback();
            });
    }

    function loadParaguayData() {
        stateSelect.innerHTML = '<option value="">Carregando...</option>';
        fetch('/data/py.json')
            .then(res => res.json())
            .then(data => {
                paraguayData = data;
                const depts = [...new Set(data.map(item => item.admin_name))].sort();
                stateSelect.innerHTML = '<option value="">Selecione o Departamento</option>';
                depts.forEach(d => stateSelect.innerHTML += `<option value="${d}">${d}</option>`);
            });
    }

    stateSelect.addEventListener('change', function() {
        const country = countrySelect.value;
        if (!this.value) return;
        citySelect.disabled = false;
        citySelect.innerHTML = '<option value="">Carregando cidades...</option>';

        if (country === 'brasil') {
            const stateId = this.options[this.selectedIndex].getAttribute('data-id');
            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${stateId}/municipios`)
                .then(res => res.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Selecione a Cidade</option>';
                    data.forEach(c => citySelect.innerHTML += `<option value="${c.nome}">${c.nome}</option>`);
                });
        } else if (paraguayData) {
            const cities = paraguayData.filter(item => item.admin_name === this.value);
            citySelect.innerHTML = '<option value="">Selecione a Cidade</option>';
            cities.forEach(c => citySelect.innerHTML += `<option value="${c.city}">${c.city}</option>`);
        }
    });

    citySelect.addEventListener('change', calcularFrete);

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
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.erro) {
                            document.querySelector('input[name="street"]').value = data.logradouro;
                            stateSelect.value = data.uf;
                            stateSelect.dispatchEvent(new Event('change'));
                            setTimeout(() => {
                                const cityOp = Array.from(citySelect.options).find(o => o.text.toUpperCase() === data.localidade.toUpperCase());
                                if (cityOp) {
                                    citySelect.value = cityOp.value;
                                    citySelect.dispatchEvent(new Event('change'));
                                }
                            }, 1000);
                        }
                    });
            }
        });
    }

    window.handleShippingSelection = function(value) {
        if(shippingRegistered) shippingRegistered.style.display = value == 1 ? 'block' : 'none';
        if(shippingAlternative) shippingAlternative.style.display = value == 2 ? 'block' : 'none';
        if(shippingStore) shippingStore.style.display = value == 3 ? 'block' : 'none';

        document.querySelectorAll('.sax-method-card').forEach(card => card.classList.remove('active'));
        document.getElementById('label-ship-' + value)?.classList.add('active');
        updateShippingMessage(value);

        calcularFrete();

        if (observationsInput) observationsInput.value = observationsValues[value] || '';
    }

    window.updateStoreMap = function() {
        if (!storeSelect) return;
        storeMaps.forEach(map => map.style.display = map.id === 'map-' + storeSelect.value ? 'block' : 'none');
    }

    shippingRadios.forEach(radio => radio.addEventListener('change', function() {
        window.handleShippingSelection(this.value);
    }));

    if (countrySelect) countrySelect.addEventListener('change', window.toggleCountryFields);
    if (storeSelect) storeSelect.addEventListener('change', window.updateStoreMap);

    const checkedRadio = document.querySelector('input[name="shipping"]:checked');
    
    if (checkedRadio) {
        window.handleShippingSelection(checkedRadio.value);
    }
    
    if (storeSelect) {
        window.updateStoreMap();
    }

    if (checkedRadio && checkedRadio.value !== '3') {
        calcularFrete();
    }
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

    if (countrySelect && countrySelect.value) {
        countrySelect.dispatchEvent(new Event('change'));
    }
    
    if (typeof window.toggleCountryFields === 'function') {
        window.toggleCountryFields();
    }
});