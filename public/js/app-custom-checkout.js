document.addEventListener('DOMContentLoaded', function () {
    const checkoutForm = document.getElementById('checkoutForm');
    const currentStepInput = document.getElementById('currentStep');
    let currentStep = Number(currentStepInput?.value || 1);
    const steps = document.querySelectorAll('.step');
    const progressSteps = document.querySelectorAll('.sax-checkout-progress-step');

    function showStep(step) {
        steps.forEach((s, i) => s.classList.toggle('active', i === step - 1));
        progressSteps.forEach((item, i) => item.classList.toggle('is-current', i <= step - 1));
        if (currentStepInput) currentStepInput.value = step;
        window.scrollTo(0, 0);
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const paymentMethodsAllowed = ['deposito', 'bancard_v2', 'whatsapp'];

    function getStepElement(stepNumber) {
        return document.getElementById(`step${stepNumber}`);
    }

    function clearStepAlert(stepNumber) {
        const stepEl = getStepElement(stepNumber);
        if (!stepEl) return;
        const alertEl = stepEl.querySelector('.sax-step-validation-alert');
        if (alertEl) alertEl.remove();
    }

    function showStepAlert(stepNumber, message) {
        const stepEl = getStepElement(stepNumber);
        if (!stepEl) return;

        let alertEl = stepEl.querySelector('.sax-step-validation-alert');
        if (!alertEl) {
            alertEl = document.createElement('div');
            alertEl.className = 'sax-step-validation-alert';
            const box = stepEl.querySelector('.sax-checkout-box') || stepEl;
            box.insertBefore(alertEl, box.firstChild);
        }

        alertEl.textContent = message;
    }

    function fieldValue(selector) {
        return (document.querySelector(selector)?.value || '').trim();
    }

    function markFieldInvalid(selector, shouldFocus = false) {
        const field = document.querySelector(selector);
        if (!field) return;
        field.classList.add('is-invalid');
        if (shouldFocus) field.focus();
    }

    function clearInvalid(selector) {
        const field = document.querySelector(selector);
        if (!field) return;
        field.classList.remove('is-invalid');
    }

    function validateStep1() {
        clearStepAlert(1);
        return true;
    }

    function validateStep2() {
        clearStepAlert(2);

        const name = fieldValue('#step2 input[name="name"]');
        const documentValue = fieldValue('#step2 input[name="document"]');
        const email = fieldValue('#step2 input[name="email"]');
        const phone = fieldValue('#step2 input[name="phone"]');
        const phoneCountry = fieldValue('#step2 select[name="phone_country"]');

        ['#step2 input[name="name"]', '#step2 input[name="document"]', '#step2 input[name="email"]', '#step2 input[name="phone"]', '#step2 select[name="phone_country"]']
            .forEach(clearInvalid);

        if (name.length < 2) {
            showStepAlert(2, 'Informe o nome completo para continuar.');
            markFieldInvalid('#step2 input[name="name"]', true);
            return false;
        }

        if (!documentValue) {
            showStepAlert(2, 'Informe o documento do cliente para continuar.');
            markFieldInvalid('#step2 input[name="document"]', true);
            return false;
        }

        if (!emailPattern.test(email)) {
            showStepAlert(2, 'Informe um e-mail valido para continuar.');
            markFieldInvalid('#step2 input[name="email"]', true);
            return false;
        }

        if (!phoneCountry) {
            const phoneCountryField = document.querySelector('#step2 select[name="phone_country"]');
            if (phoneCountryField) {
                phoneCountryField.value = '595';
            }
        }

        if (!phone) {
            showStepAlert(2, 'Informe o telefone para continuar.');
            markFieldInvalid('#step2 input[name="phone"]', true);
            return false;
        }

        return true;
    }

    function validateStep3() {
        clearStepAlert(3);

        const shipping = document.querySelector('input[name="shipping"]:checked')?.value;
        if (!shipping || !['1', '2', '3'].includes(shipping)) {
            showStepAlert(3, 'Selecione um metodo de entrega para continuar.');
            return false;
        }

        const selectorsToClear = [
            '#country', '#postal_code', 'input[name="street"]', 'input[name="number"]', 'input[name="district"]',
            '#state-select', '#city-select', '#storeSelect'
        ];
        selectorsToClear.forEach(clearInvalid);

        if (shipping === '1') {
            if (!fieldValue('#shipping_address_id')) {
                showStepAlert(3, 'Selecione um endereço salvo ou cadastre um novo endereço.');
                markFieldInvalid('#shipping_address_id', true);
                return false;
            }
            return true;
        }

        if (shipping === '2') {
            const country = fieldValue('#country');
            const cep = fieldValue('#postal_code');
            const street = fieldValue('input[name="street"]');
            const number = fieldValue('input[name="number"]');
            const district = fieldValue('input[name="district"]');
            const label = fieldValue('input[name="address_label"]');
            const state = fieldValue('#state-select');
            const city = fieldValue('#city-select');

            if (!label) {
                showStepAlert(3, 'Informe uma identificação para o endereço, como Casa ou Trabalho.');
                markFieldInvalid('input[name="address_label"]', true);
                return false;
            }

            if (!country) {
                showStepAlert(3, 'Selecione o pais de entrega para continuar.');
                markFieldInvalid('#country', true);
                return false;
            }

            if (!cep) {
                showStepAlert(3, 'Informe o CEP/Codigo postal para continuar.');
                markFieldInvalid('#postal_code', true);
                return false;
            }

            if (!street) {
                showStepAlert(3, 'Informe a rua para continuar.');
                markFieldInvalid('input[name="street"]', true);
                return false;
            }

            if (!state) {
                showStepAlert(3, 'Selecione o estado ou departamento para continuar.');
                markFieldInvalid('#state-select', true);
                return false;
            }

            if (!city) {
                showStepAlert(3, 'Selecione a cidade para continuar.');
                markFieldInvalid('#city-select', true);
                return false;
            }

            if (!number) {
                showStepAlert(3, 'Informe o numero do endereco para continuar.');
                markFieldInvalid('input[name="number"]', true);
                return false;
            }

            if (!district) {
                showStepAlert(3, 'Informe o bairro para continuar.');
                markFieldInvalid('input[name="district"]', true);
                return false;
            }

            return true;
        }

        if (shipping === '3') {
            const store = fieldValue('#storeSelect');
            if (!store) {
                showStepAlert(3, 'Selecione a loja para retirada.');
                markFieldInvalid('#storeSelect', true);
                return false;
            }
        }

        return true;
    }

    function validateStep4() {
        clearStepAlert(4);

        const paymentMethod = fieldValue('#payment_method');
        if (!paymentMethodsAllowed.includes(paymentMethod)) {
            showStepAlert(4, 'Selecione uma forma de pagamento valida para concluir.');
            return false;
        }

        const acceptTerms = document.getElementById('accept_terms');
        if (!acceptTerms?.checked) {
            const termsMessage = document.getElementById('terms-validation-message');
            termsMessage?.classList.remove('d-none');
            markFieldInvalid('#accept_terms', true);
            return false;
        }

        return true;
    }

    function validateStep(stepNumber) {
        if (stepNumber === 1) return validateStep1();
        if (stepNumber === 2) return validateStep2();
        if (stepNumber === 3) return validateStep3();
        if (stepNumber === 4) return validateStep4();
        return true;
    }

    window.nextStep = function(step) {
        if (!validateStep(step)) {
            showStep(step);
            return;
        }

        const selectedShipping = document.querySelector('input[name="shipping"]:checked')?.value;
        if (step === 3 && selectedShipping === '2' && window.bootstrap) {
            const modalElement = document.getElementById('saveAddressModal');
            if (modalElement) {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                return;
            }
        }

        currentStep = step + 1;
        showStep(currentStep);
    }

    window.prevStep = function(step) {
        currentStep = step;
        showStep(currentStep);
    }

    showStep(currentStep);

    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    const shippingRegistered = document.getElementById('shipping_registered');
    const shippingAlternative = document.getElementById('shipping_alternative');
    const shippingStore = document.getElementById('shipping_store');
    const addressSelect = document.getElementById('shipping_address_id');
    const addressPreview = document.getElementById('selected-address-preview');
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
    const shippingSummaryRow = document.getElementById('shipping-summary-row');
    const shippingSummaryLabel = document.getElementById('shipping-summary-label');
    const totalDisplay = document.getElementById('total-geral-display');
    const freteValorInput = document.getElementById('frete_valor');
    const subtotalDisplay = document.getElementById('subtotal-valor');
    const totalSemFrete = document.getElementById('total-sem-frete');
    const bancardCurrencyNotice = document.getElementById('bancard-currency-notice');
    const bancardPygTotal = document.getElementById('bancard-pyg-total');
    const bancardCountryWarning = document.getElementById('bancard-country-warning');
    const confirmSaveAddress = document.getElementById('confirmSaveAddress');

    let paraguayData = null;
    let observationsValues = { 1: '', 2: '', 3: '' };

    // Total do pedido já com o cupom abatido, antes do frete. Quando não há frete
    // (retirada na loja ou envio a combinar), é ele que vai para o total geral —
    // usar o subtotal aqui apagaria o desconto do cupom da tela.
    function textoTotalSemFrete() {
        return totalSemFrete?.textContent?.trim()
            || subtotalDisplay?.textContent?.trim()
            || '';
    }

    function calcularFrete() {
        const radioSelected = document.querySelector('input[name="shipping"]:checked')?.value;
        
        if (radioSelected === '3') {
            if (shippingSummaryRow) shippingSummaryRow.classList.remove('d-none');
            if (shippingSummaryLabel) shippingSummaryLabel.innerText = 'Retirada na loja';
            if (freteDisplay) freteDisplay.innerText = '';
            if (totalDisplay) totalDisplay.innerText = textoTotalSemFrete();
            if (freteValorInput) freteValorInput.value = '0.00';
            updateBancardCurrencyNotice();
            return; 
        }

        if (shippingSummaryRow) shippingSummaryRow.classList.remove('d-none');
        if (shippingSummaryLabel) shippingSummaryLabel.innerText = 'Envio';

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
            if (freteDisplay) freteDisplay.innerText = 'Frete via WhatsApp';
            
            if (totalDisplay) totalDisplay.innerText = textoTotalSemFrete();
            if (freteValorInput) freteValorInput.value = '0.00';
            updateBancardCurrencyNotice();
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
            updateBancardCurrencyNotice();
        })
        .catch(error => {
            console.error('Erro no cálculo:', error);
            if (freteDisplay) freteDisplay.innerText = 'Erro ao calcular';
        });
    }

    function resolveCheckoutCountry() {
        const shipping = document.querySelector('input[name="shipping"]:checked')?.value;
        if (shipping === '3') return 'paraguai';
        if (shipping === '1') return document.getElementById('user_country_data')?.value || '';
        if (shipping === '2') return countrySelect?.value || '';
        return '';
    }

    function updateBancardCurrencyNotice() {
        if (!bancardCurrencyNotice) return;

        const isBancard = document.getElementById('payment_method')?.value === 'bancard_v2';
        bancardCurrencyNotice.classList.toggle('d-none', !isBancard);
        if (!isBancard) return;

        const baseTotal = parseFloat(totalSemFrete?.dataset.valor || '0') || 0;
        const shippingValue = parseFloat(freteValorInput?.value || '0') || 0;
        const pygRate = parseFloat(bancardCurrencyNotice.dataset.pygRate || '1') || 1;
        const pygSign = bancardCurrencyNotice.dataset.pygSign || 'G$';
        const totalPyg = Math.round((baseTotal + shippingValue) * pygRate);

        if (bancardPygTotal) {
            bancardPygTotal.textContent = `${pygSign} ${new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 0 }).format(totalPyg)}`;
        }

        const checkoutCountry = resolveCheckoutCountry();
        if (!bancardCountryWarning) return;

        if (checkoutCountry === 'brasil') {
            bancardCountryWarning.innerHTML = '<strong>Aviso para compras do Brasil:</strong> esta é uma compra internacional processada em guaranis. O banco emissor e a bandeira do cartão podem aplicar sua própria cotação, IOF, spread cambial e outros encargos. Por isso, o valor final da fatura pode ser superior ao valor de referência exibido pela SAX. Esses encargos não são cobrados nem controlados pela SAX.';
        } else if (checkoutCountry === 'paraguai') {
            bancardCountryWarning.innerHTML = '<strong>Compra no Paraguai:</strong> o pagamento será processado diretamente em guaranis. A SAX não aplica taxa de conversão internacional. Caso o cartão ou a conta estejam em outra moeda, o banco emissor ainda poderá realizar sua própria conversão.';
        } else {
            bancardCountryWarning.innerHTML = '<strong>Aviso para cartões internacionais:</strong> o pagamento é processado em guaranis. Seu banco ou bandeira poderá aplicar conversão cambial, impostos e encargos próprios, fazendo o valor da fatura diferir da referência apresentada no checkout.';
        }
    }

    function updateSelectedAddress() {
        if (!addressSelect) return;
        const option = addressSelect.options[addressSelect.selectedIndex];
        const country = option?.dataset.country || '';
        const city = option?.dataset.city || '';
        const fields = {
            country: country === 'paraguai' ? 'Paraguai' : 'Brasil',
            state: option?.dataset.state || 'Não informado',
            city: city || 'Não informada',
            street: option?.dataset.street || 'Não informada',
            number: option?.dataset.number || 'Não informado',
            district: option?.dataset.district || 'Não informado',
            complement: option?.dataset.complement || 'Não informado',
            postalCode: option?.dataset.postalCode || 'Não informado'
        };

        const countryData = document.getElementById('user_country_data');
        const cityData = document.getElementById('user_city_data');
        if (countryData) countryData.value = country;
        if (cityData) cityData.value = city;
        if (addressPreview) {
            Object.entries(fields).forEach(function ([field, value]) {
                const element = addressPreview.querySelector(`[data-address-field="${field}"]`);
                if (element) element.textContent = value;
            });
        }
        const selectedLabel = document.querySelector('[data-address-selected-label]');
        if (selectedLabel) selectedLabel.textContent = option?.dataset.label || 'Endereço selecionado';

        updateShippingMessage('1');
        calcularFrete();
    }

    function updateShippingMessage(method) {
        if (!infoBox || !infoContent) return;
        infoBox.style.display = 'block';

        if (method == 3) {
            infoContent.innerHTML = '<i class="fa fa-shopping-bag me-2"></i> <strong>Retirada na Loja:</strong> Selecione a unidade de preferência no mapa. <strong>Frete Grátis.</strong>';
            if (shippingSummaryRow) shippingSummaryRow.classList.remove('d-none');
            if (shippingSummaryLabel) shippingSummaryLabel.innerText = 'Retirada na loja';
            if (freteDisplay) freteDisplay.innerText = '';
            if (totalDisplay) totalDisplay.innerText = textoTotalSemFrete();
            if (freteValorInput) freteValorInput.value = '0.00';

        } else {
            if (shippingSummaryRow) shippingSummaryRow.classList.remove('d-none');
            if (shippingSummaryLabel) shippingSummaryLabel.innerText = 'Envio';
            let country = (method == 1) 
                ? (document.getElementById('user_country_data')?.value || 'brasil') 
                : (countrySelect?.value || 'brasil');

            if (country.toLowerCase() === 'brasil') {
                infoContent.innerHTML = '<i class="fab fa-whatsapp me-2"></i> <strong>Entrega no Brasil:</strong> Após finalizar o pedido, nossa equipe entrará em contato pelo WhatsApp para confirmar a melhor opção de envio e o valor do frete. <strong>O frete não está incluído no total abaixo.</strong>';
                if (freteDisplay) freteDisplay.innerText = 'Frete via WhatsApp';
                if (totalDisplay) totalDisplay.innerText = textoTotalSemFrete();
                if (freteValorInput) freteValorInput.value = '0.00';

            } else {
                infoContent.innerHTML = '<i class="fa fa-truck me-2"></i> <strong>Envio Nacional (Paraguai):</strong> O custo do frete será calculado com base na sua cidade e adicionado ao total abaixo.';
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
                if (stateSelect.dataset.selected) {
                    stateSelect.value = stateSelect.dataset.selected;
                    stateSelect.dispatchEvent(new Event('change'));
                }
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
                if (stateSelect.dataset.selected) {
                    stateSelect.value = stateSelect.dataset.selected;
                    stateSelect.dispatchEvent(new Event('change'));
                }
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
                    if (citySelect.dataset.selected) citySelect.value = citySelect.dataset.selected;
                });
        } else if (paraguayData) {
            const cities = paraguayData.filter(item => item.admin_name === this.value);
            citySelect.innerHTML = '<option value="">Selecione a Cidade</option>';
            cities.forEach(c => citySelect.innerHTML += `<option value="${c.city}">${c.city}</option>`);
            if (citySelect.dataset.selected) citySelect.value = citySelect.dataset.selected;
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
    if (addressSelect) addressSelect.addEventListener('change', updateSelectedAddress);
    if (confirmSaveAddress) {
        confirmSaveAddress.addEventListener('click', function () {
            const modalElement = document.getElementById('saveAddressModal');
            if (modalElement && window.bootstrap) {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }
            currentStep = 4;
            showStep(currentStep);
        });
    }

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

    window.selectPayment = function(method) {
        const paymentInput = document.getElementById('payment_method');
        const instruction = document.getElementById('payment-instruction');
        
        if (paymentInput) paymentInput.value = method;
        
        document.querySelectorAll('.sax-payment-method').forEach((button) => {
            button.classList.toggle('active', button.dataset.paymentMethod === method);
        });
        
        if (instruction) {
            instruction.innerText = method === 'bancard_v2'
                ? "Cartão disponível para Brasil, Paraguai e outros países. QR Bancard somente para o Paraguai; PIX/QR Brasil em breve."
                : "Após finalizar, você deverá enviar o comprovante do depósito/transferência.";
        }
        updateBancardCurrencyNotice();
    };

    document.querySelectorAll('.sax-payment-method[data-payment-method]').forEach((button) => {
        button.addEventListener('click', function() {
            window.selectPayment(this.dataset.paymentMethod);
        });
    });

    const initialPay = document.getElementById('payment_method')?.value || 'deposito';
    window.selectPayment(initialPay);

    const acceptTerms = document.getElementById('accept_terms');
    const syncTermsAcceptance = function () {
        if (acceptTerms?.checked) {
            clearInvalid('#accept_terms');
            document.getElementById('terms-validation-message')?.classList.add('d-none');
        }
    };
    acceptTerms?.addEventListener('change', syncTermsAcceptance);
    syncTermsAcceptance();

    if (countrySelect && countrySelect.value) {
        countrySelect.dispatchEvent(new Event('change'));
    }
    
    if (typeof window.toggleCountryFields === 'function') {
        window.toggleCountryFields();
    }

    /* ----------------------------------------------------------------------
     * Cupom no checkout
     * O código fica na sessão do servidor; o formulário do pedido não envia
     * desconto algum. Aqui só atualizamos a tela com o que o servidor calculou.
     * -------------------------------------------------------------------- */
    const cuponBox = document.getElementById('cupon-box');

    if (cuponBox) {
        const textos = window.cuponTexts || {};
        const descontoRow = document.getElementById('desconto-row');
        const descontoDisplay = document.getElementById('desconto-display');
        const subtotalDisplayFinal = document.getElementById('subtotal-display');

        function feedbackEl() {
            return document.getElementById('cupon-feedback');
        }

        function mostrarFeedback(mensagem, ok) {
            const el = feedbackEl();
            if (!el) return;
            el.textContent = mensagem || '';
            el.classList.toggle('is-error', !ok);
            el.classList.toggle('is-success', !!ok);
        }

        function escapar(texto) {
            const div = document.createElement('div');
            div.textContent = texto ?? '';
            return div.innerHTML;
        }

        // Redesenha só o bloco do cupom, preservando o que o cliente já digitou
        // nos outros passos (por isso não recarregamos a página).
        function renderCuponBox(cupon) {
            const corpo = cupon
                ? `<div class="sax-cupon-applied">
                       <div>
                           <span class="sax-cupon-applied-label">${escapar(textos.aplicado)}</span>
                           <strong class="sax-cupon-applied-code">${escapar(cupon.codigo)}</strong>
                           <span class="sax-cupon-applied-rule">${escapar(cupon.rotulo)} · ${escapar(cupon.escopo)}</span>
                       </div>
                       <button type="button" class="sax-cupon-remove" id="cupon-remove-btn" aria-label="${escapar(textos.remover)}">
                           <i class="fas fa-times"></i>
                       </button>
                   </div>`
                : `<label for="cupon-codigo-checkout" class="sax-cupon-label">
                       <i class="fas fa-tag me-1"></i>${escapar(textos.tem_codigo)}
                   </label>
                   <div class="sax-cupon-input-group">
                       <input type="text" id="cupon-codigo-checkout" maxlength="60"
                              class="sax-cupon-input text-uppercase" placeholder="${escapar(textos.placeholder)}">
                       <button type="button" class="sax-cupon-btn" id="cupon-apply-btn">${escapar(textos.aplicar)}</button>
                   </div>`;

            cuponBox.innerHTML = corpo + '<div class="sax-cupon-feedback" id="cupon-feedback" role="status"></div>';
        }

        function aplicarResumo(data) {
            if (subtotalDisplayFinal) subtotalDisplayFinal.innerText = data.subtotal_formatado;
            if (subtotalDisplay) {
                subtotalDisplay.innerText = data.subtotal_formatado;
                subtotalDisplay.dataset.valor = data.subtotal;
            }
            if (descontoDisplay) descontoDisplay.innerText = '- ' + data.desconto_formatado;
            if (descontoRow) descontoRow.classList.toggle('d-none', !(data.desconto > 0));
            if (totalSemFrete) {
                totalSemFrete.innerText = data.total_formatado;
                totalSemFrete.dataset.valor = data.total;
            }

            // Com entrega já escolhida, o servidor devolve o total somando o frete;
            // sem entrega, o total é o do pedido sem frete.
            if (document.querySelector('input[name="shipping"]:checked')) {
                calcularFrete();
            } else if (totalDisplay) {
                totalDisplay.innerText = data.total_formatado;
            }
        }

        function enviarCupon(url, corpo, botao) {
            if (botao) botao.disabled = true;
            mostrarFeedback('', true);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(corpo)
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || !data.success) {
                    if (botao) botao.disabled = false;
                    mostrarFeedback(data.message || textos.erro_generico, false);
                    return;
                }

                aplicarResumo(data);
                renderCuponBox(data.codigo ? { codigo: data.codigo, rotulo: data.rotulo, escopo: data.escopo } : null);
                mostrarFeedback(data.message, true);
            })
            .catch(() => {
                if (botao) botao.disabled = false;
                mostrarFeedback(textos.erro_conexao, false);
            });
        }

        // Delegação: o conteúdo do bloco é redesenhado a cada aplicação/remoção.
        cuponBox.addEventListener('click', function(e) {
            const aplicar = e.target.closest('#cupon-apply-btn');
            const remover = e.target.closest('#cupon-remove-btn');

            if (aplicar) {
                const input = document.getElementById('cupon-codigo-checkout');
                const codigo = (input?.value || '').trim();

                if (!codigo) {
                    mostrarFeedback(textos.digite_codigo, false);
                    return;
                }

                enviarCupon(cuponBox.dataset.applyUrl, { codigo }, aplicar);
            }

            if (remover) {
                enviarCupon(cuponBox.dataset.removeUrl, {}, remover);
            }
        });

        cuponBox.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.id === 'cupon-codigo-checkout') {
                e.preventDefault();
                document.getElementById('cupon-apply-btn')?.click();
            }
        });
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            clearStepAlert(4);

            if (!validateStep1()) {
                e.preventDefault();
                showStep(1);
                return;
            }

            if (!validateStep2()) {
                e.preventDefault();
                showStep(2);
                return;
            }

            if (!validateStep3()) {
                e.preventDefault();
                showStep(3);
                return;
            }

            if (!validateStep4()) {
                e.preventDefault();
                showStep(4);
            }
        });
    }
});
