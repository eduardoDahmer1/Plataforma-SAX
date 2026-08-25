(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const testForm = document.querySelector('[data-dhl-test-form]');
        const testSubmit = testForm?.querySelector('[data-dhl-test-submit]');
        const testFieldNames = [
            'test_package_id',
            'test_weight_kg',
            'test_declared_value',
            'rate_markup_enabled',
            'rate_markup_percent',
            'test_destination_country_code',
            'test_destination_province_code',
            'test_destination_province_name',
            'test_destination_city',
            'test_destination_postal_code'
        ];

        const packageSelect = document.getElementById('test_package_id');
        const packageWeight = document.getElementById('test_weight_kg');
        const packageHint = document.getElementById('dhl-test-package-hint');
        const markupEnabled = document.getElementById('rate_markup_enabled');
        const markupInput = document.querySelector('[data-rate-markup-input]');

        function syncMarkupState() {
            if (!markupInput) return;
            const active = Boolean(markupEnabled?.checked);
            markupInput.readOnly = !active;
            markupInput.classList.toggle('bg-light', !active);
            markupInput.setAttribute('aria-disabled', active ? 'false' : 'true');
        }

        markupEnabled?.addEventListener('change', syncMarkupState);
        syncMarkupState();

        function syncTestPackage(useRecommendedWeight) {
            const option = packageSelect?.options[packageSelect.selectedIndex];
            if (!option) return;

            const values = {
                test_length_cm: option.dataset.length || '',
                test_width_cm: option.dataset.width || '',
                test_height_cm: option.dataset.height || ''
            };
            Object.entries(values).forEach(function ([name, value]) {
                const field = document.querySelector(`form[action$="/admin/dhl"] [name="${name}"]`);
                if (field) field.value = value;
            });

            const maxWeight = Number(option.dataset.maxWeight || 0);
            if (packageWeight && maxWeight > 0) {
                packageWeight.max = String(maxWeight);
                if (useRecommendedWeight || Number(packageWeight.value || 0) > maxWeight) {
                    packageWeight.value = String(maxWeight);
                }
            }
            if (packageHint) {
                packageHint.textContent = `Código ${option.dataset.code || 'DHL'} · ${option.dataset.length} × ${option.dataset.width} × ${option.dataset.height} cm · limite ${maxWeight.toLocaleString('pt-BR')} kg.`;
            }
        }

        packageSelect?.addEventListener('change', function () { syncTestPackage(true); });
        syncTestPackage(false);

        testForm?.addEventListener('submit', function (event) {
            if (!window.confirm('Executar uma cotação no sandbox com os dados atuais da tela?')) {
                event.preventDefault();
                return;
            }

            testFieldNames.forEach(function (name) {
                const sources = document.querySelectorAll(`form[action$="/admin/dhl"] [name="${name}"]`);
                const source = Array.from(sources).find(function (field) { return field.type === 'checkbox'; }) || sources[0];
                const target = testForm.querySelector(`[data-dhl-test-value="${name}"]`);
                if (source && target) {
                    target.value = source.type === 'checkbox' ? (source.checked ? '1' : '0') : source.value;
                }
            });

            if (testSubmit) {
                testSubmit.disabled = true;
                testSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Consultando DHL...';
            }
        });

        const root = document.getElementById('dhl-test-destination');
        if (!root) return;

        const country = document.getElementById('test_destination_country_code');
        const province = document.getElementById('test_destination_province_code');
        const provinceName = document.getElementById('test_destination_province_name');
        const city = document.getElementById('test_destination_city');
        const cityOptions = document.getElementById('dhl-test-city-options');
        const postalCode = document.getElementById('test_destination_postal_code');
        const postalOptions = document.getElementById('dhl-test-postal-options');
        const postalHint = document.getElementById('dhl-test-postal-hint');
        const status = document.getElementById('dhl-location-status');
        const countriesUrl = root.dataset.countriesUrl;
        const subdivisionsUrl = root.dataset.subdivisionsUrl;
        const citiesUrl = root.dataset.citiesUrl;
        const postalCodesUrl = root.dataset.postalCodesUrl;

        if (!country || !province || !provinceName || !city || !cityOptions) return;

        let selectedProvinceCode = String(province.dataset.selectedCode || '');
        let selectedProvinceName = String(province.dataset.selectedName || '');
        let selectedCity = String(city.value || '');
        let requestSequence = 0;
        let postalRequestSequence = 0;
        let postalTimer = null;

        function updatePostalGuide(countryCode) {
            if (!postalCode || !postalHint || !window.SaxWorldLocations?.postalGuide) return;

            postalHint.textContent = 'Consultando o formato postal do país...';
            window.SaxWorldLocations.postalGuide(countryCode, countriesUrl).then(function (guide) {
                if (country.value !== countryCode) return;
                if (guide.example) postalCode.placeholder = `Ex.: ${guide.example}`;
                else postalCode.placeholder = 'Código postal do endereço';

                postalHint.textContent = guide.format
                    ? `Formato orientativo: ${guide.format}. Informe o código postal exato do endereço.`
                    : 'Informe o código postal oficial do endereço. A DHL confirmará o formato na cotação.';
            });
        }

        function message(text, isError) {
            if (!status) return;
            status.textContent = text || '';
            status.classList.toggle('text-danger', Boolean(isError));
            status.classList.toggle('text-muted', !isError);
        }

        function json(url) {
            return fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            }).then(function (response) {
                if (!response.ok) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        throw new Error(payload.message || 'Não foi possível carregar as localidades.');
                    });
                }
                return response.json();
            });
        }

        function fillCities(items, sequence) {
            if (sequence !== requestSequence) return;
            cityOptions.innerHTML = '';
            (items || []).forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.name;
                if (item.region) option.label = item.region;
                cityOptions.appendChild(option);
            });
            if (selectedCity) {
                city.value = selectedCity;
                loadPostalCodes();
            }
            message(items.length
                ? `${items.length} cidades carregadas pelo GeoNames.`
                : 'O GeoNames não encontrou cidades para essa seleção; você pode digitar a cidade manualmente.', false);
        }

        function loadPostalCodes() {
            if (!postalCodesUrl || !postalCode || !postalOptions) return;
            const countryCode = country.value;
            const cityName = city.value.trim();
            const sequence = ++postalRequestSequence;
            postalOptions.innerHTML = '';

            if (!countryCode || !cityName) return;

            postalHint.textContent = 'Buscando códigos postais da cidade no GeoNames...';
            const query = new URLSearchParams({ country: countryCode, city: cityName });
            if (province.value) query.set('admin_code', province.value);

            json(`${postalCodesUrl}?${query.toString()}`)
                .then(function (payload) {
                    if (sequence !== postalRequestSequence) return;
                    const items = Array.isArray(payload.data) ? payload.data : [];
                    items.forEach(function (item) {
                        const option = document.createElement('option');
                        option.value = item.postal_code;
                        option.label = [item.place_name, item.admin_name].filter(Boolean).join(' — ');
                        postalOptions.appendChild(option);
                    });

                    const limitation = ['BR', 'CA', 'IE', 'MT'].includes(countryCode)
                        ? ' O GeoNames pode retornar apenas códigos parciais ou principais nesse país; confirme o código completo do endereço.'
                        : '';
                    postalHint.textContent = items.length
                        ? `${items.length} sugestão(ões) encontrada(s). Selecione uma e confirme com o destinatário.${limitation}`
                        : 'Nenhuma sugestão encontrada. O cliente deve informar o código postal oficial do endereço.';
                })
                .catch(function () {
                    if (sequence !== postalRequestSequence) return;
                    postalHint.textContent = 'Não foi possível sugerir o código postal. Informe o código oficial do endereço.';
                });
        }

        function loadCities(adminCode, sequence) {
            const countryCode = country.value;
            if (!countryCode || sequence !== requestSequence) return;

            cityOptions.innerHTML = '';
            message('Carregando cidades pelo GeoNames...', false);
            const query = new URLSearchParams({ country: countryCode });
            if (adminCode) query.set('admin_code', adminCode);

            json(`${citiesUrl}?${query.toString()}`)
                .then(function (payload) {
                    if (sequence !== requestSequence || country.value !== countryCode) return;
                    fillCities(Array.isArray(payload.data) ? payload.data : [], sequence);
                })
                .catch(function (error) {
                    if (sequence !== requestSequence) return;
                    cityOptions.innerHTML = '';
                    message(`${error.message} Você ainda pode digitar a cidade manualmente.`, true);
                });
        }

        function loadSubdivisions(preserveSelection) {
            const countryCode = country.value;
            const sequence = ++requestSequence;

            province.innerHTML = '';
            provinceName.value = '';
            cityOptions.innerHTML = '';

            if (!preserveSelection) {
                selectedProvinceCode = '';
                selectedProvinceName = '';
                selectedCity = '';
                city.value = '';
                if (postalCode) postalCode.value = '';
                if (postalOptions) postalOptions.innerHTML = '';
            }

            if (!countryCode) {
                province.disabled = true;
                province.add(new Option('Selecione primeiro o país', ''));
                message('', false);
                return;
            }

            updatePostalGuide(countryCode);

            province.disabled = true;
            province.add(new Option('Carregando regiões...', ''));
            message('Carregando estados, províncias ou regiões pelo GeoNames...', false);

            json(`${subdivisionsUrl}?${new URLSearchParams({ country: countryCode }).toString()}`)
                .then(function (payload) {
                    if (sequence !== requestSequence || country.value !== countryCode) return;
                    const items = Array.isArray(payload.data) ? payload.data : [];
                    province.innerHTML = '';

                    if (!items.length) {
                        province.add(new Option('Sem subdivisão cadastrada — cidades do país', ''));
                        province.disabled = true;
                        loadCities('', sequence);
                        return;
                    }

                    province.add(new Option('Selecione o estado / província / região', ''));
                    items.forEach(function (item) {
                        const option = new Option(item.name, item.code);
                        province.add(option);
                    });
                    province.disabled = false;

                    let matched = false;
                    if (selectedProvinceCode) {
                        matched = Array.from(province.options).some(function (option) {
                            return option.value === selectedProvinceCode;
                        });
                        if (matched) province.value = selectedProvinceCode;
                    }
                    if (!matched && selectedProvinceName) {
                        const option = Array.from(province.options).find(function (item) {
                            return item.text.toLocaleLowerCase() === selectedProvinceName.toLocaleLowerCase();
                        });
                        if (option) {
                            province.value = option.value;
                            matched = true;
                        }
                    }

                    if (matched) {
                        const option = province.options[province.selectedIndex];
                        provinceName.value = String(option?.text || selectedProvinceName);
                        loadCities(province.value, sequence);
                    } else {
                        message(`${items.length} estados, províncias ou regiões carregados pelo GeoNames.`, false);
                    }
                })
                .catch(function (error) {
                    if (sequence !== requestSequence) return;
                    province.innerHTML = '';
                    province.add(new Option('Não foi possível carregar as regiões', ''));
                    province.disabled = true;
                    message(`${error.message} A cidade pode ser digitada manualmente.`, true);
                });
        }

        province.addEventListener('change', function () {
            const option = province.options[province.selectedIndex];
            provinceName.value = province.value ? String(option?.text || '') : '';
            selectedProvinceCode = province.value;
            selectedProvinceName = provinceName.value;
            selectedCity = '';
            city.value = '';
            cityOptions.innerHTML = '';
            if (postalOptions) postalOptions.innerHTML = '';

            if (province.value) loadCities(province.value, requestSequence);
        });

        country.addEventListener('change', function () {
            loadSubdivisions(false);
        });

        city.addEventListener('input', function () {
            selectedCity = city.value;
            clearTimeout(postalTimer);
            postalTimer = setTimeout(loadPostalCodes, 450);
        });
        city.addEventListener('change', loadPostalCodes);

        loadSubdivisions(true);
    });
}());
