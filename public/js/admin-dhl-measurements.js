document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.getElementById('dhl-product-bulk-form');

    if (bulkForm) {
        const selectPage = document.querySelector('[data-select-page]');
        const productChecks = Array.from(document.querySelectorAll('[data-product-select]'));
        const editButtons = Array.from(document.querySelectorAll('[data-edit-measurements]'));
        const counter = bulkForm.querySelector('[data-selection-count]');
        const reviewedCounter = bulkForm.querySelector('[data-reviewed-count]');
        const submit = bulkForm.querySelector('[data-bulk-submit]');
        const suggestSelected = bulkForm.querySelector('[data-bulk-suggest]');
        const modalElement = document.getElementById('dhlMeasurementModal');
        const modal = modalElement && window.bootstrap
            ? window.bootstrap.Modal.getOrCreateInstance(modalElement)
            : null;
        const modalFields = modalElement
            ? Array.from(modalElement.querySelectorAll('[data-modal-field]'))
            : [];
        const measurementSelects = modalFields.filter((field) => field.dataset.measurementSelect);
        const measurementOptionsElement = document.getElementById('dhl-measurement-options');
        let measurementOptions = {};
        try {
            measurementOptions = JSON.parse(measurementOptionsElement?.textContent || '{}');
        } catch (error) {
            measurementOptions = {};
        }
        let activeButton = null;

        const formatNumber = function (value, decimals) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(Number(value) || 0);
        };

        const updateSelection = function () {
            const selected = productChecks.filter((checkbox) => checkbox.checked).length;
            const reviewed = document.querySelectorAll('[data-product-row].is-staged').length;

            counter.textContent = selected + (selected === 1 ? ' selecionado' : ' selecionados');
            reviewedCounter.textContent = reviewed + (reviewed === 1 ? ' preparado' : ' preparados');
            submit.disabled = selected === 0;
            suggestSelected.disabled = selected === 0;

            if (selectPage) {
                selectPage.checked = selected > 0 && selected === productChecks.length;
                selectPage.indeterminate = selected > 0 && selected < productChecks.length;
            }
        };

        const rowForButton = function (button) {
            return button?.closest('[data-product-row]') || null;
        };

        const hiddenField = function (row, fieldName) {
            return row?.querySelector('[data-measurement-value="' + fieldName + '"]') || null;
        };

        const setModalField = function (fieldName, value) {
            const field = modalFields.find((candidate) => candidate.dataset.modalField === fieldName);
            if (!field) return;

            if (field.type === 'checkbox') {
                field.checked = String(value) === '1' || value === true;
                return;
            }

            const normalizedValue = value ?? '';
            if (field.tagName === 'SELECT' && normalizedValue !== '' && !Array.from(field.options).some((option) => option.value === String(normalizedValue))) {
                field.add(new Option(String(normalizedValue), String(normalizedValue)));
            }
            field.value = normalizedValue;
        };

        const measurementLabel = function (kind, value) {
            const decimals = kind === 'weight' ? 3 : (Number(value) % 1 === 0 ? 0 : 1);
            return formatNumber(value, decimals) + (kind === 'weight' ? ' kg' : ' cm');
        };

        const refreshMeasurementSelects = function (profileCode, selectedValues) {
            const fallbackProfile = activeButton?.dataset.suggestedProfile || 'accessory';
            const optionProfile = measurementOptions[profileCode] ? profileCode : fallbackProfile;
            const profileOptions = measurementOptions[optionProfile] || measurementOptions.accessory || {};

            measurementSelects.forEach(function (select) {
                const kind = select.dataset.measurementSelect;
                const selected = String(selectedValues?.[select.dataset.modalField] ?? '');
                const values = Array.from(profileOptions[kind] || []).map(Number).filter((value) => value > 0);
                const selectedNumber = Number(selected);
                if (selectedNumber > 0 && !values.some((value) => Math.abs(value - selectedNumber) < 0.0001)) {
                    values.push(selectedNumber);
                }
                values.sort((left, right) => left - right);

                select.replaceChildren();
                values.forEach(function (value) {
                    const normalized = kind === 'weight' ? Number(value).toFixed(3) : String(Number(value));
                    select.add(new Option(measurementLabel(kind, value), normalized));
                });

                if (selectedNumber > 0) {
                    select.value = kind === 'weight' ? selectedNumber.toFixed(3) : String(selectedNumber);
                }
            });
        };

        const updateCustomsDescriptionState = function () {
            const description = modalFields.find((field) => field.dataset.modalField === 'shipping_customs_description');
            const missing = modalElement?.querySelector('[data-customs-description-missing]');
            const editLink = modalElement?.querySelector('[data-edit-product-description]');
            const hasDescription = Boolean(description?.value.trim());

            if (missing) missing.hidden = hasDescription;
            if (editLink) editLink.href = activeButton?.dataset.productEditUrl || '#';
        };

        const openProduct = function (button) {
            if (!button || !modalElement || !modal) return;

            activeButton = button;
            const row = rowForButton(button);
            const position = editButtons.indexOf(button) + 1;
            const image = modalElement.querySelector('[data-modal-image]');

            modalElement.querySelector('[data-modal-product-id]').value = button.dataset.productId;
            modalElement.querySelector('[data-modal-name]').textContent = button.dataset.productName || 'Produto';
            modalElement.querySelector('[data-modal-meta]').textContent = button.dataset.productMeta || '';
            modalElement.querySelector('[data-modal-suggestion]').textContent = [
                button.dataset.suggestedLabel,
                formatNumber(button.dataset.suggestedWeight, 3) + ' kg',
                [
                    formatNumber(button.dataset.suggestedLength, 1),
                    formatNumber(button.dataset.suggestedWidth, 1),
                    formatNumber(button.dataset.suggestedHeight, 1),
                ].join(' × ') + ' cm',
            ].filter(Boolean).join(' · ');
            modalElement.querySelector('[data-modal-progress]').textContent = 'Produto ' + position + ' de ' + editButtons.length + ' nesta página';

            image.src = button.dataset.productImage || '';
            image.alt = button.dataset.productName || '';

            const profileValue = hiddenField(row, 'shipping_profile')?.value || '';
            setModalField('shipping_profile', profileValue);
            const selectedMeasurements = {};
            measurementSelects.forEach(function (field) {
                selectedMeasurements[field.dataset.modalField] = hiddenField(row, field.dataset.modalField)?.value || '';
            });
            refreshMeasurementSelects(profileValue, selectedMeasurements);
            modalFields
                .filter((field) => field.dataset.modalField !== 'shipping_profile' && !field.dataset.measurementSelect)
                .forEach(function (field) {
                    const source = hiddenField(row, field.dataset.modalField);
                    setModalField(field.dataset.modalField, source?.value ?? '');
                });
            updateCustomsDescriptionState();

            modal.show();
        };

        const applySuggestedMeasurements = function () {
            if (!activeButton) return;

            const suggestedProfile = activeButton.dataset.suggestedProfile || '';
            const suggestedMeasurements = {
                shipping_weight_kg: activeButton.dataset.suggestedWeight || '',
                shipping_length_cm: activeButton.dataset.suggestedLength || '',
                shipping_width_cm: activeButton.dataset.suggestedWidth || '',
                shipping_height_cm: activeButton.dataset.suggestedHeight || '',
            };
            setModalField('shipping_profile', suggestedProfile);
            refreshMeasurementSelects(suggestedProfile, suggestedMeasurements);
        };

        const validateModal = function () {
            const customsDescription = modalFields.find((field) => field.dataset.modalField === 'shipping_customs_description');
            if (!customsDescription?.value.trim()) {
                updateCustomsDescriptionState();
                window.alert('Preencha a descrição em inglês no cadastro do produto antes de concluir a revisão DHL.');
                modalElement?.querySelector('[data-edit-product-description]')?.focus();
                return false;
            }

            for (const field of modalFields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    field.focus();
                    return false;
                }
            }

            return true;
        };

        const saveCurrent = function (openNext) {
            if (!activeButton || !validateModal()) return;

            const row = rowForButton(activeButton);
            modalFields.forEach(function (field) {
                const destination = hiddenField(row, field.dataset.modalField);
                if (!destination) return;
                destination.value = field.type === 'checkbox' ? (field.checked ? '1' : '0') : field.value.trim();
            });

            const profileSelect = modalFields.find((field) => field.dataset.modalField === 'shipping_profile');
            const profileLabel = profileSelect?.selectedOptions[0]?.textContent?.split(' — ')[0] || 'Sugestão não revisada';
            row.querySelector('[data-row-profile]').textContent = profileLabel;
            row.querySelector('[data-row-weight]').textContent = formatNumber(hiddenField(row, 'shipping_weight_kg').value, 3) + ' kg';
            row.querySelector('[data-row-dimensions]').textContent = [
                formatNumber(hiddenField(row, 'shipping_length_cm').value, 1),
                formatNumber(hiddenField(row, 'shipping_width_cm').value, 1),
                formatNumber(hiddenField(row, 'shipping_height_cm').value, 1),
            ].join(' × ') + ' cm';
            row.classList.add('is-staged');
            row.querySelector('[data-staged-badge]').hidden = false;

            const checkbox = row.querySelector('[data-product-select]');
            checkbox.checked = true;
            updateSelection();

            if (openNext) {
                const currentIndex = editButtons.indexOf(activeButton);
                const nextButton = editButtons[currentIndex + 1];
                if (nextButton) {
                    openProduct(nextButton);
                    return;
                }
            }

            modal.hide();
        };

        const stageSuggestion = function (row) {
            const button = row?.querySelector('[data-edit-measurements]');
            if (!row || !button) return;

            const suggested = {
                shipping_profile: button.dataset.suggestedProfile || '',
                shipping_weight_kg: button.dataset.suggestedWeight || '',
                shipping_length_cm: button.dataset.suggestedLength || '',
                shipping_width_cm: button.dataset.suggestedWidth || '',
                shipping_height_cm: button.dataset.suggestedHeight || '',
            };

            Object.entries(suggested).forEach(function ([fieldName, value]) {
                const destination = hiddenField(row, fieldName);
                if (destination) destination.value = value;
            });

            row.querySelector('[data-row-profile]').textContent = button.dataset.suggestedLabel || 'Sugestão não revisada';
            row.querySelector('[data-row-weight]').textContent = formatNumber(suggested.shipping_weight_kg, 3) + ' kg';
            row.querySelector('[data-row-dimensions]').textContent = [
                formatNumber(suggested.shipping_length_cm, 1),
                formatNumber(suggested.shipping_width_cm, 1),
                formatNumber(suggested.shipping_height_cm, 1),
            ].join(' × ') + ' cm';
            row.classList.add('is-staged');
            row.querySelector('[data-staged-badge]').hidden = false;
        };

        selectPage?.addEventListener('change', function () {
            productChecks.forEach((checkbox) => { checkbox.checked = selectPage.checked; });
            updateSelection();
        });
        productChecks.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
        editButtons.forEach((button) => button.addEventListener('click', function () { openProduct(button); }));

        modalElement?.querySelector('[data-use-suggestion]')?.addEventListener('click', applySuggestedMeasurements);
        modalElement?.querySelector('[data-modal-save]')?.addEventListener('click', function () { saveCurrent(false); });
        modalElement?.querySelector('[data-modal-save-next]')?.addEventListener('click', function () { saveCurrent(true); });
        suggestSelected?.addEventListener('click', function () {
            const selectedRows = productChecks
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.closest('[data-product-row]'));
            selectedRows.forEach(stageSuggestion);
            updateSelection();
        });

        const profileSelect = modalFields.find((field) => field.dataset.modalField === 'shipping_profile');
        profileSelect?.addEventListener('change', function () {
            const option = profileSelect.selectedOptions[0];
            const defaults = option?.dataset.weight ? {
                shipping_weight_kg: option.dataset.weight,
                shipping_length_cm: option.dataset.length,
                shipping_width_cm: option.dataset.width,
                shipping_height_cm: option.dataset.height,
            } : {
                shipping_weight_kg: activeButton?.dataset.suggestedWeight || '',
                shipping_length_cm: activeButton?.dataset.suggestedLength || '',
                shipping_width_cm: activeButton?.dataset.suggestedWidth || '',
                shipping_height_cm: activeButton?.dataset.suggestedHeight || '',
            };
            refreshMeasurementSelects(profileSelect.value, defaults);
        });

        bulkForm.addEventListener('submit', function (event) {
            const selectedChecks = productChecks.filter((checkbox) => checkbox.checked);
            const selected = selectedChecks.length;
            const pendingReview = selectedChecks.filter((checkbox) => !checkbox.closest('[data-product-row]')?.classList.contains('is-staged')).length;

            if (pendingReview > 0) {
                event.preventDefault();
                window.alert('Revise os produtos selecionados no modal ou clique em “Fixar sugestões” antes de salvar.');
                return;
            }

            if (selected === 0 || !window.confirm('Salvar somente as medidas DHL de ' + selected + ' produto(s)?')) {
                event.preventDefault();
                return;
            }

            submit.disabled = true;
        });

        updateSelection();
    }

    const category = document.getElementById('dhl-category-filter');
    const subcategory = document.getElementById('dhl-subcategory-filter');
    const child = document.getElementById('dhl-childcategory-filter');

    const filterOptions = function (select, predicate) {
        if (!select) return;
        Array.from(select.options).forEach(function (option, index) {
            if (index === 0) return;
            const visible = predicate(option);
            option.hidden = !visible;
            option.disabled = !visible;
        });
    };

    const updateTaxonomy = function () {
        const categoryId = category?.value || '';
        const subcategoryId = subcategory?.value || '';

        filterOptions(subcategory, (option) => !categoryId || option.dataset.category === categoryId);
        filterOptions(child, (option) => {
            if (subcategoryId) return option.dataset.subcategory === subcategoryId;
            return !categoryId || option.dataset.category === categoryId;
        });
    };

    category?.addEventListener('change', function () {
        if (subcategory) subcategory.value = '';
        if (child) child.value = '';
        updateTaxonomy();
    });
    subcategory?.addEventListener('change', function () {
        if (child) child.value = '';
        updateTaxonomy();
    });
    updateTaxonomy();
});
