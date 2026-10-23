// ============================
// PRODUCTS-ADMIN.JS
// Scripts de admin/products (edit, index, review).
// Se carga en las 3 rutas vía scripts-master.blade.php.
// ============================

document.addEventListener('DOMContentLoaded', function () {
    const aiButton = document.getElementById('completeProductWithAiBtn');
    const aiFeedback = document.getElementById('productEditFeedback');
    const aiResearch = document.getElementById('productAiResearch');
    const aiGeneratedAt = document.getElementById('productAiGeneratedAt');
    let currentProposal = null;

    if (!aiButton || !aiFeedback) return;

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    }

    function descriptionToHtml(value) {
        return String(value || '').split(/\n{2,}/).filter(Boolean).map(function (paragraph) {
            return '<p>' + escapeHtml(paragraph).replaceAll('\n', '<br>') + '</p>';
        }).join('');
    }

    function applyTaxonomyToForm(taxonomy) {
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        const childCategorySelect = document.getElementById('categoriasfilhas_id');
        const categoryId = taxonomy?.category_id;
        const subcategoryId = taxonomy?.subcategory_id;
        const childCategoryId = taxonomy?.childcategory_id;

        if (!categorySelect || !subcategorySelect || !childCategorySelect || !categoryId) return;

        const hasCategory = Array.from(categorySelect.options).some(function (option) {
            return String(option.value) === String(categoryId);
        });
        if (!hasCategory) return;

        // Reutiliza la cascada existente para mantener coherentes los tres niveles.
        categorySelect.value = String(categoryId);
        categorySelect.dispatchEvent(new Event('change', { bubbles: true }));
        subcategorySelect.dispatchEvent(new Event('change', { bubbles: true }));
        if (!subcategoryId) return;

        const hasSubcategory = Array.from(subcategorySelect.options).some(function (option) {
            return String(option.value) === String(subcategoryId);
        });
        if (!hasSubcategory) return;

        subcategorySelect.value = String(subcategoryId);
        subcategorySelect.dispatchEvent(new Event('change', { bubbles: true }));

        if (!childCategoryId) return;
        const hasChildCategory = Array.from(childCategorySelect.options).some(function (option) {
            return String(option.value) === String(childCategoryId);
        });
        if (hasChildCategory) childCategorySelect.value = String(childCategoryId);
    }

    function applyProposalToForm() {
        if (!currentProposal) return;

        const fieldMap = { pt_br: 'pt', es: 'es', en: 'en' };
        const optical = String(document.getElementById('category_id')?.value) === '149'
            || String(currentProposal.taxonomy_selection?.category_id) === '149';
        const brandSelect = document.getElementById('brand_id');
        const brand = brandSelect?.value ? brandSelect.selectedOptions?.[0]?.textContent?.trim() : '';
        Object.entries(fieldMap).forEach(function (entry) {
            const sourceLanguage = entry[0];
            const fieldLanguage = entry[1];
            const nameField = document.getElementById('real-name-' + fieldLanguage);
            const descriptionField = document.getElementById('real-desc-' + fieldLanguage);
            if (nameField && currentProposal.commercial_name?.[sourceLanguage]) {
                const name = currentProposal.commercial_name[sourceLanguage];
                // Normalize the identified commercial name, never reintroduce an unverified raw model.
                nameField.value = optical && typeof window.normalizeOpticalProductName === 'function'
                    ? window.normalizeOpticalProductName(brand, name).toLocaleUpperCase()
                    : name;
            }
            if (descriptionField && currentProposal.descriptions?.[sourceLanguage]) {
                descriptionField.value = descriptionToHtml(currentProposal.descriptions[sourceLanguage]);
            }
        });

        const visibleName = document.getElementById('visual-name-input');
        const activeNameLanguage = window.currentLangs?.name || 'pt';
        if (visibleName) visibleName.value = document.getElementById('real-name-' + activeNameLanguage)?.value || '';

        const editor = typeof tinymce !== 'undefined' ? tinymce.get('editor-product') : null;
        const activeDescriptionLanguage = window.currentLangs?.desc || 'pt';
        const description = document.getElementById('real-desc-' + activeDescriptionLanguage)?.value || '';
        if (editor) editor.setContent(description);
        else {
            const visible = document.getElementById('editor-product');
            if (visible) visible.value = description;
        }
        window.saxRefreshProductDescription?.();

        applyTaxonomyToForm(currentProposal.taxonomy_selection);

    }

    const suggestions = document.getElementById('aiTaxonomySuggestions');
    const taxonomyForm = document.getElementById('aiTaxonomyForm');
    const taxonomyAssessment = document.getElementById('aiTaxonomyAssessment');
    let selectedSuggestion = null;
    let suggestionButton = null;

    function assignAdditional(item) {
        const primary = { category_id: document.getElementById('category_id')?.value, subcategory_id: document.getElementById('subcategory_id')?.value, childcategory_id: document.getElementById('categoriasfilhas_id')?.value };
        if (['category_id', 'subcategory_id', 'childcategory_id'].every(key => String(primary[key] || '') === String(item[key] || ''))) return 'primary';
        const container = document.getElementById('additionalCategoriesContainer');
        const duplicate = Array.from(container?.children || []).some(row =>
            ['category', 'subcategory', 'childcategory'].every(level =>
                String(row.querySelector('.additional-' + level + '-select')?.value || '') === String(item[level + '_id'] || '')));
        if (!container) return 'unavailable';
        if (duplicate) return 'duplicate';
        document.getElementById('addAdditionalCategory')?.click();
        const row = container.lastElementChild;
        ['category', 'subcategory', 'childcategory'].forEach(level => {
            const select = row.querySelector('.additional-' + level + '-select');
            select.value = String(item[level + '_id'] || '');
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        return 'added';
    }

    function taxonomyPath(item, newName = '') {
        const category = document.querySelector('#category_id option[value="' + Number(item.category_id) + '"]')?.textContent.trim();
        const sub = window.saxProductTaxonomy?.subcategories.find(sub => String(sub.id) === String(item.subcategory_id));
        const child = window.saxProductTaxonomy?.childcategories.find(child => String(child.id) === String(item.childcategory_id));
        return [category, sub?.name, child?.name, newName].filter(Boolean).join(' / ');
    }

    function showTaxonomyMessage(message) {
        if (!suggestions) return;
        const text = document.createElement('p');
        text.className = 'product-taxonomy__empty mb-0';
        text.textContent = message;
        suggestions.replaceChildren(text);
    }

    function renderTaxonomyAssessment() {
        if (!taxonomyAssessment) return;
        taxonomyAssessment.replaceChildren();
        const assessment = currentProposal?.taxonomy_assessment || {};
        const list = document.createElement('dl');
        function addEvaluation(label, reason) {
            const heading = document.createElement('dt');
            heading.textContent = label;
            const detail = document.createElement('dd');
            detail.textContent = typeof reason === 'string' && reason.trim()
                ? reason.trim()
                : 'La IA no incluyó una explicación para este apartado. Volvé a generar la propuesta para revisarlo.';
            list.append(heading, detail);
        }
        addEvaluation('Categorías hijas', assessment.child_categories_reason);
        addEvaluation('Categorizaciones adicionales', assessment.additional_categories_reason);
        taxonomyAssessment.appendChild(list);
        const opportunities = Array.isArray(assessment.catalog_opportunities)
            ? assessment.catalog_opportunities.filter(item => typeof item === 'string' && item.trim()).slice(0, 2) : [];
        if (opportunities.length) {
            const heading = document.createElement('h6');
            heading.textContent = 'Posibles ampliaciones del catálogo';
            const hint = document.createElement('p');
            hint.className = 'product-taxonomy__assessment-hint';
            hint.textContent = 'Ideas para revisar; no se crean ni se asignan al producto.';
            const options = document.createElement('ul');
            opportunities.forEach(text => {
                const item = document.createElement('li');
                item.textContent = text;
                options.appendChild(item);
            });
            taxonomyAssessment.append(heading, hint, options);
        }
        taxonomyAssessment.classList.remove('d-none');
    }

    function renderTaxonomySuggestions() {
        if (!suggestions) return;
        suggestions.replaceChildren();
        function addSuggestion(label, reason, action, callback, badge = '') {
            const row = document.createElement('div');
            row.className = 'product-taxonomy__suggestion';
            const copy = document.createElement('div');
            copy.className = 'product-taxonomy__suggestion-copy';
            const title = document.createElement('div');
            title.className = 'product-taxonomy__suggestion-title';
            const path = document.createElement('span');
            path.textContent = label;
            title.appendChild(path);
            if (badge) {
                const tag = document.createElement('span');
                tag.className = 'product-taxonomy__badge';
                tag.textContent = badge;
                title.appendChild(tag);
            }
            const detail = document.createElement('small');
            detail.className = 'product-taxonomy__suggestion-reason';
            detail.textContent = reason || '';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = badge ? 'btn btn-outline-primary' : 'btn btn-primary';
            button.textContent = action;
            button.addEventListener('click', () => callback(button));
            copy.append(title, detail);
            row.append(copy, button);
            suggestions.appendChild(row);
        }
        (currentProposal?.additional_taxonomy_selections || []).forEach(item => {
            addSuggestion(taxonomyPath(item), item.reason, 'Agregar al producto', button => {
                const result = assignAdditional(item);
                button.disabled = true;
                button.textContent = result === 'primary' ? 'Ya es la principal' : result === 'duplicate' ? 'Ya está agregada' : 'Agregada al formulario';
            });
        });
        (currentProposal?.taxonomy_suggestions || []).forEach(item => {
            addSuggestion(taxonomyPath(item, item.name), item.reason, 'Revisar creación', button => {
                selectedSuggestion = item;
                suggestionButton = button;
                document.getElementById('aiTaxonomyType').value = item.type;
                document.getElementById('aiTaxonomyName').value = item.name;
                const category = document.getElementById('aiTaxonomyCategory');
                category.replaceChildren(...Array.from(document.getElementById('category_id').options).map(option => option.cloneNode(true)));
                category.value = String(item.category_id);
                updateModalParents(item.subcategory_id);
                document.getElementById('aiTaxonomyAssignment').value = 'none';
                document.getElementById('aiTaxonomyError').classList.add('d-none');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('aiTaxonomyModal')).show();
            }, item.type === 'subcategory' ? 'Nueva subcategoría' : 'Nueva categoría hija');
        });
        // An empty list is explained by the assessment, never by an invented reason.
        renderTaxonomyAssessment();
    }

    function updateModalParents(selectedId = '') {
        const child = selectedSuggestion?.type === 'childcategory';
        document.getElementById('aiTaxonomyTitle').textContent = child ? 'Crear categoría hija' : 'Crear subcategoría';
        const select = document.getElementById('aiTaxonomySubcategory');
        document.getElementById('aiTaxonomySubcategoryGroup').hidden = !child;
        select.required = child;
        select.replaceChildren(new Option('Seleccioná una subcategoría', ''));
        (window.saxProductTaxonomy?.subcategories || []).filter(item => String(item.category_id) === document.getElementById('aiTaxonomyCategory').value).forEach(item => select.add(new Option(item.name, item.id)));
        select.value = String(selectedId || '');
    }
    function openManualTaxonomy(type) {
        selectedSuggestion = { type };
        document.getElementById('aiTaxonomyType').value = type;
        suggestionButton = null;
        document.getElementById('aiTaxonomyName').value = '';
        const category = document.getElementById('aiTaxonomyCategory');
        const primary = document.getElementById('category_id');
        category.replaceChildren(...Array.from(primary.options).map(option => option.cloneNode(true)));
        category.value = primary.value;
        updateModalParents(document.getElementById('subcategory_id')?.value);
        document.getElementById('aiTaxonomyAssignment').value = 'none';
        document.getElementById('aiTaxonomyError').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('aiTaxonomyModal')).show();
    }
    document.getElementById('createProductTaxonomy')?.addEventListener('click', () => openManualTaxonomy(document.getElementById('subcategory_id')?.value ? 'childcategory' : 'subcategory'));
    document.getElementById('aiTaxonomyType')?.addEventListener('change', event => {
        if (!selectedSuggestion) return;
        selectedSuggestion = { ...selectedSuggestion, type: event.target.value };
        updateModalParents(document.getElementById('aiTaxonomySubcategory').value);
    });
    const additionalContainer = document.getElementById('additionalCategoriesContainer');
    const additionalEmpty = document.getElementById('additionalCategoriesEmpty');
    if (additionalContainer && additionalEmpty) {
        const updateEmpty = () => { additionalEmpty.hidden = !!additionalContainer.querySelector('.additional-category-row'); };
        new MutationObserver(updateEmpty).observe(additionalContainer, { childList: true });
        updateEmpty();
    }
    document.getElementById('aiTaxonomyCategory')?.addEventListener('change', () => updateModalParents());
    taxonomyForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!selectedSuggestion) return;
        const submit = taxonomyForm.querySelector('[type="submit"]');
        const errorBox = document.getElementById('aiTaxonomyError');
        submit.disabled = true;
        errorBox.classList.add('d-none');
        try {
            const response = await fetch(taxonomyForm.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ type: selectedSuggestion.type, name: document.getElementById('aiTaxonomyName').value, category_id: document.getElementById('aiTaxonomyCategory').value, subcategory_id: document.getElementById('aiTaxonomySubcategory').value || null })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'No fue posible crear la clasificación.');
            const list = data.type === 'subcategory' ? window.saxProductTaxonomy.subcategories : window.saxProductTaxonomy.childcategories;
            if (!list.some(item => String(item.id) === String(data.item.id))) list.push(data.item);
            const assignment = { category_id: data.item.category_id, subcategory_id: data.type === 'subcategory' ? data.item.id : data.item.subcategory_id, childcategory_id: data.type === 'childcategory' ? data.item.id : null };
            const mode = document.getElementById('aiTaxonomyAssignment').value;
            if (mode === 'primary') applyTaxonomyToForm(assignment);
            if (mode === 'additional') assignAdditional(assignment);
            bootstrap.Modal.getInstance(document.getElementById('aiTaxonomyModal')).hide();
            if (suggestionButton) {
                suggestionButton.disabled = true;
                suggestionButton.textContent = data.created ? 'Creada' : 'Ya existe';
            }
            aiFeedback.className = 'alert alert-success';
            aiFeedback.textContent = (data.created ? 'Clasificación creada.' : 'Se utilizó la clasificación existente.') + (mode === 'none' ? '' : ' Guardá el producto para confirmar su asignación.');
        } catch (error) {
            errorBox.textContent = error.message || 'No fue posible crear la clasificación.';
            errorBox.classList.remove('d-none');
        } finally {
            submit.disabled = false;
        }
    });

    function renderResearch(research) {
        if (!aiResearch) return;
        aiResearch.replaceChildren();
        const sources = Array.isArray(research?.sources) ? research.sources : [];
        const warning = research?.warning;
        if (!sources.length && !warning) {
            aiResearch.className = 'd-none mb-4';
            return;
        }

        aiResearch.className = 'alert alert-info mb-4';
        const heading = document.createElement('div');
        heading.className = 'fw-bold mb-1';
        heading.textContent = research?.status === 'matched' ? 'Fuentes consultadas' : 'Investigación web';
        aiResearch.appendChild(heading);

        if (warning) {
            const notice = document.createElement('div');
            notice.className = 'small mb-2';
            notice.textContent = warning;
            aiResearch.appendChild(notice);
        }

        if (sources.length) {
            const list = document.createElement('ul');
            list.className = 'small mb-0 ps-3';
            sources.forEach(function (source) {
                if (!source || typeof source.url !== 'string' || !/^https?:\/\//i.test(source.url)) return;
                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = source.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'text-break';
                link.title = source.url;
                const sourceTitle = typeof source.title === 'string' ? source.title.trim() : '';
                link.textContent = sourceTitle && sourceTitle !== source.url
                    ? sourceTitle
                    : new URL(source.url).hostname;
                item.appendChild(link);
                list.appendChild(item);
            });
            aiResearch.appendChild(list);
        }
    }

    aiButton.addEventListener('click', async function () {
        const originalHtml = aiButton.innerHTML;
        aiButton.disabled = true;
        aiButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando...';
        aiFeedback.className = 'alert d-none';
        if (aiResearch) aiResearch.className = 'd-none mb-4';
        if (aiGeneratedAt) aiGeneratedAt.value = '';
        currentProposal = null;
        taxonomyAssessment?.replaceChildren();
        taxonomyAssessment?.classList.add('d-none');
        showTaxonomyMessage('Buscando sugerencias de clasificación…');
        suggestions?.closest('.product-taxonomy__suggestions')?.classList.remove('d-none');
        suggestions?.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(aiButton.dataset.aiUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            let data = {};
            try {
                data = await response.json();
            } catch (parseError) {
                throw new Error('El servidor respondió con un formato inválido (HTTP ' + response.status + ').');
            }
            if (!response.ok) throw new Error(data.message || 'No fue posible generar la propuesta.');

            if (['commercial_name', 'descriptions'].some(field =>
                ['pt_br', 'es', 'en'].some(locale => typeof data.proposal?.[field]?.[locale] !== 'string'
                    || !data.proposal[field][locale].trim()))) {
                throw new Error('La IA no devolvió nombres y descripciones completos en los tres idiomas.');
            }
            currentProposal = data.proposal;
            window.dispatchEvent(new CustomEvent('sax:catalog-attribute-proposal', {detail: data}));
            if (aiGeneratedAt && data.preparation?.status === 'generated' && data.preparation?.generated_at) {
                aiGeneratedAt.value = data.preparation.generated_at;
            }
            applyProposalToForm();
            renderResearch(data.research);
            renderTaxonomySuggestions();
            const requiresReview = data.preparation?.status === 'not_found';
            aiFeedback.className = requiresReview ? 'alert alert-warning' : 'alert alert-success';
            aiFeedback.textContent = requiresReview
                ? 'La IA no encontró una coincidencia web confiable. Podés revisar la propuesta, pero el producto seguirá marcado para revisión.'
                : 'La IA completó el formulario. Revisá nombres, descripciones y clasificación antes de guardar.';
            window.saxToast
                ? saxToast(requiresReview ? 'warning' : 'success', requiresReview
                    ? 'Producto no encontrado; requiere revisión.'
                    : 'Formulario completado con IA; todavía no fue guardado.')
                : null;
        } catch (error) {
            showTaxonomyMessage('No se pudieron generar las sugerencias. Reintentá o creá una clasificación manualmente.');
            aiFeedback.className = 'alert alert-danger';
            aiFeedback.textContent = error instanceof TypeError
                ? 'No se pudo conectar con el servidor. Verificá tu conexión, el estado del servidor y los logs de Laravel.'
                : (error.message || 'No fue posible generar la propuesta.');
        } finally {
            suggestions?.setAttribute('aria-busy', 'false');
            aiButton.disabled = false;
            aiButton.innerHTML = originalHtml;
        }
    });

});

// ======== Index: filtros condicionais do Outlet e ações em massa ========
document.addEventListener('DOMContentLoaded', function () {
    const outletChannel = document.getElementById('outletChannelFilter');
    const outletFilters = document.getElementById('outletAdvancedFilters');
    if (outletChannel && outletFilters) {
        const updateOutletFilters = function () {
            const visible = outletChannel.value === 'outlet';
            outletFilters.hidden = !visible;
            outletFilters.querySelectorAll('input, select').forEach(function (control) {
                control.disabled = !visible;
            });
        };
        outletChannel.addEventListener('change', updateOutletFilters);
        updateOutletFilters();
    }

    const form = document.getElementById('productsBulkForm');
    if (!form) return;
    const selectAll = document.getElementById('productsSelectAll');
    const checkboxes = Array.from(document.querySelectorAll('[data-product-checkbox]'));
    const count = document.getElementById('productsSelectedCount');
    const action = document.getElementById('productsBulkAction');
    const submit = document.getElementById('productsBulkSubmit');

    function updateSelection() {
        const selected = checkboxes.filter(function (checkbox) { return checkbox.checked; });
        count.textContent = selected.length + (selected.length === 1 ? ' selecionado' : ' selecionados');
        submit.disabled = selected.length === 0 || !action.value;
        selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
        checkboxes.forEach(function (checkbox) {
            checkbox.closest('.sax-product-card')?.classList.toggle('is-selected', checkbox.checked);
        });
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
        updateSelection();
    });
    checkboxes.forEach(function (checkbox) { checkbox.addEventListener('change', updateSelection); });
    action.addEventListener('change', updateSelection);
    form.addEventListener('submit', function (event) {
        const total = checkboxes.filter(function (checkbox) { return checkbox.checked; }).length;
        const labels = {
            activate: 'ativar', deactivate: 'desativar', feature: 'adicionar aos destaques',
            unfeature: 'remover dos destaques', outlet: 'enviar para o Outlet',
            restore: 'restaurar do Outlet', delete: 'APAGAR PERMANENTEMENTE'
        };
        if (!total || !action.value) {
            event.preventDefault();
            return;
        }
        const destructive = action.value === 'delete';
        const message = destructive
            ? 'Tem certeza que deseja APAGAR PERMANENTEMENTE ' + total + ' produto(s)? Esta ação não pode ser desfeita.'
            : 'Confirma ' + labels[action.value] + ' em ' + total + ' produto(s)?';
        if (!window.confirm(message)) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        submit.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Aplicando…';
    });
    updateSelection();
});

// ======== Edit: Buscador de productos hijo (tamaño/color) ========
// Archivo: resources/views/admin/products/edit.blade.php
document.addEventListener('DOMContentLoaded', function () {
    function setupProductDropzone(zoneId, inputId, multiple) {
        var zone = document.getElementById(zoneId);
        var fileInput = document.getElementById(inputId);
        if (!zone || !fileInput) return;

        ['dragenter', 'dragover'].forEach(function (eventName) {
            zone.addEventListener(eventName, function (event) {
                event.preventDefault();
                zone.classList.add('is-dragging');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            zone.addEventListener(eventName, function () { zone.classList.remove('is-dragging'); });
        });

        zone.addEventListener('drop', function (event) {
            event.preventDefault();
            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            var imageFiles = Array.from(event.dataTransfer.files).filter(function (file) {
                return allowedTypes.includes(file.type) && file.size <= 10 * 1024 * 1024;
            });
            if (!imageFiles.length) {
                if (window.saxToast) saxToast('warning', 'Use imagens JPG, PNG ou WEBP com até 10 MB.');
                return;
            }

            var transfer = new DataTransfer();
            (multiple ? imageFiles : imageFiles.slice(0, 1)).forEach(function (file) { transfer.items.add(file); });
            fileInput.files = transfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    setupProductDropzone('productPhotoDropzone', 'photoInput', false);
    setupProductDropzone('productGalleryDropzone', 'galleryInput', true);
});

document.addEventListener('DOMContentLoaded', function () {
    var parentResults = document.getElementById('parent_results');
    if (!parentResults) return;

    // Imagen fallback leída del data-attribute del Blade (evita asset() en JS)
    var noImage = parentResults.dataset.noimage || '/storage/uploads/noimage.webp';

    function setupSearch(inputId, btnId, resultsId, selectedId, hiddenName, searchUrl, context) {
        const searchInput = document.getElementById(inputId);
        const searchBtn = document.getElementById(btnId);
        const resultsDiv = document.getElementById(resultsId);
        const selectedDiv = document.getElementById(selectedId);
        const resultsLabel = document.getElementById(resultsId + '_label');
        var activeRequest = null;

        if (!searchInput || !searchBtn || !resultsDiv || !selectedDiv) return;

        function showSearchMessage(message, type) {
            resultsDiv.innerHTML = '<div class="col-12"><div class="alert alert-' + type + ' m-0">' + message + '</div></div>';
            resultsDiv.style.display = 'flex';
            resultsDiv.style.flexWrap = 'wrap';
            if (resultsLabel) resultsLabel.classList.remove('d-none');
        }

        function updateSelectedCount() {
            const badge = document.querySelector('[data-count-for="' + selectedId + '"]');
            if (badge) badge.textContent = selectedDiv.querySelectorAll(':scope > [data-id]').length;
            resultsDiv.querySelectorAll('.relationship-suggestion').forEach(function (card) {
                const selected = !!selectedDiv.querySelector('div[data-id="' + card.dataset.id + '"]');
                card.classList.toggle('selected', selected);
                card.classList.toggle('border-success', selected);
                card.setAttribute('aria-checked', String(selected));
                card.querySelector('.relationship-choice').textContent = selected ? '✓ Seleccionada' : '+ Seleccionar';
            });
        }

        function selectResultCard(card) {
            if (!card) return;

            const id = card.getAttribute('data-id');
            if (!id || selectedDiv.querySelector('div[data-id="' + id + '"]')) return;

            const name = card.querySelector('.card-text')?.textContent || '';
            const imgSrc = card.querySelector('img')?.src || noImage;
            const color = card.getAttribute('data-color');
            const inferredColor = card.getAttribute('data-inferred-color');
            const size = card.getAttribute('data-size');
            const sku = card.getAttribute('data-sku');

            const newCard = document.createElement('div');
            newCard.className = 'col-6 col-md-4 col-lg-2';
            newCard.setAttribute('data-id', id);
            newCard.innerHTML = '<div class="card border-success h-100 position-relative">' +
                '<img src="' + imgSrc + '" class="card-img-top" style="height:120px; object-fit:cover;">' +
                '<div class="card-body p-2">' +
                '<span class="badge bg-success mb-1">✓ Seleccionada</span>' +
                '<p class="card-text m-0 fw-bold">' + name + '</p>' +
                (sku ? '<small class="text-muted d-block mt-1">SKU: ' + sku + '</small>' : '') +
                (color ? '<div class="d-flex align-items-center mt-1">' +
                    '<span style="display:inline-block;width:16px;height:16px;background:' + color + ';border:1px solid #ccc;margin-right:5px;"></span>' +
                    '<small>' + color + '</small></div>' : '') +
                (context === 'color' && inferredColor ?
                    '<small class="text-muted d-block mt-1">Código original: *' + inferredColor + '</small>' : '') +
                (size ? '<div class="mt-1"><small class="text-muted">Tamanho: ' + size + '</small></div>' : '') +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item">' +
                '<i class="fas fa-times"></i></button>' +
                '<input type="hidden" name="' + hiddenName + '[]" value="' + id + '">' +
                '</div></div>';
            selectedDiv.appendChild(newCard);
            updateSelectedCount();
        }

        function escapeRelationshipText(value) {
            const element = document.createElement('span');
            element.textContent = value;
            return element.innerHTML;
        }

        function searchProducts(autoSelectMatches = false) {
            const query = searchInput.value.trim();
            if (!query.length) {
                if (activeRequest) activeRequest.abort();
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
                if (resultsLabel) resultsLabel.classList.add('d-none');
                return;
            }

            if (activeRequest) activeRequest.abort();
            activeRequest = new AbortController();
            const request = activeRequest;
            showSearchMessage('Buscando produtos...', 'light');
            searchBtn.disabled = true;

            const excludeId = resultsDiv.dataset.currentProductId || '';
            const params = new URLSearchParams({ q: query });
            if (excludeId) params.append('exclude_id', excludeId);
            if (context) params.append('context', context);
            if ((context === 'size' || context === 'color') && resultsDiv.dataset.currentColorKey) {
                params.append('current_color_key', resultsDiv.dataset.currentColorKey);
            }
            if ((context === 'size' || context === 'color') && resultsDiv.dataset.currentReferenceKey) {
                params.append('current_reference_key', resultsDiv.dataset.currentReferenceKey);
            }
            if (context === 'size' && resultsDiv.dataset.currentSize) {
                params.append('current_size', resultsDiv.dataset.currentSize);
            }
            if (autoSelectMatches) params.append('strict_family', '1');

            fetch(searchUrl + '?' + params.toString(), { signal: request.signal })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    if (!Array.isArray(data)) throw new Error('Resposta inválida do servidor');
                    var html = '';
                    if (data.length) {
                        data.forEach(function (product) {
                            const alreadySelected = Array.from(
                                selectedDiv.querySelectorAll('input[name="' + hiddenName + '[]"]')
                            ).some(function (input) { return input.value == product.id; });

                            html += '<div class="col-6 col-md-4 col-lg-2">' +
                                '<div class="card h-100 relationship-suggestion ' + (alreadySelected ? 'border-success selected' : '') + '" ' +
                                'role="checkbox" tabindex="0" aria-checked="' + alreadySelected + '" data-id="' + product.id + '" ' +
                                'data-auto-select="' + (product.auto_select ? '1' : '0') + '" data-sku="' + (product.sku || '') + '" data-color="' + (product.color || '') + '" ' +
                                'data-inferred-color="' + (product.color_code || '') + '" data-size="' + (product.size || '') + '">' +
                                '<img src="' + (product.photo || noImage) + '" ' +
                                'class="img-fluid object-fit-cover" alt="' + (product.name || product.external_name) + '">' +
                                '<div class="card-body p-2">' +
                                '<span class="relationship-suggestion-badge">Sugerencia</span>' +
                                '<p class="card-text m-0 fw-bold">' + (product.external_name || product.name) + '</p>' +
                                (product.sku ? '<small class="text-muted d-block mt-1">SKU: ' + product.sku + '</small>' : '') +
                                (product.color_code ? '<small class="text-muted d-block mt-1">' + escapeRelationshipText(product.color_code) + '</small>' : '') +
                                (product.size ? '<small class="d-block mt-1">Talla: <strong>' + escapeRelationshipText(product.size) + '</strong></small>' : '') +
                                '<span class="relationship-choice">' + (alreadySelected ? '✓ Seleccionada' : '+ Seleccionar') + '</span>' +
                                '</div></div></div>';
                        });
                    } else {
                        html = '<div class="col-12"><div class="alert alert-info m-0">Nenhum produto encontrado</div></div>';
                    }
                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'flex';
                    resultsDiv.style.flexWrap = 'wrap';
                    if (resultsLabel) resultsLabel.classList.remove('d-none');

                    if (autoSelectMatches && resultsDiv.dataset.autoSelect === '1') {
                        resultsDiv.querySelectorAll('.card[data-id][data-auto-select="1"]').forEach(selectResultCard);
                    }
                    updateSelectedCount();
                })
                .catch(function (err) {
                    if (err.name === 'AbortError') return;
                    console.error('Falha na busca de produtos:', err);
                    showSearchMessage('Não foi possível buscar os produtos. Tente novamente.', 'danger');
                })
                .finally(function () {
                    if (activeRequest !== request) return;
                    activeRequest = null;
                    searchBtn.disabled = false;
                });
        }

        searchBtn.addEventListener('click', function () { searchProducts(false); });
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchProducts(false);
            }
        });

        if (searchInput.dataset.autoSearch === '1' && searchInput.value.trim().length >= 1) {
            window.setTimeout(function () { searchProducts(true); }, 80);
        }

        function toggleSuggestion(card) {
            if (!card) return;
            const selected = selectedDiv.querySelector('div[data-id="' + card.dataset.id + '"]');
            if (selected) {
                selected.remove();
                updateSelectedCount();
            } else {
                selectResultCard(card);
            }
        }

        resultsDiv.addEventListener('click', function (e) {
            toggleSuggestion(e.target.closest('.relationship-suggestion'));
        });
        resultsDiv.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const card = e.target.closest('.relationship-suggestion');
                if (!card) return;
                e.preventDefault();
                toggleSuggestion(card);
            }
        });

        selectedDiv.addEventListener('click', function (e) {
            if (e.target.closest('.remove-item')) {
                e.target.closest('[data-id]').remove();
                updateSelectedCount();
            }
        });

        updateSelectedCount();


    }

    setupSearch('parent_search', 'parent_search_btn', 'parent_results', 'selected_parents', 'parent_id', '/admin/products/search', 'size');
    setupSearch('color_search', 'color_search_btn', 'color_results', 'selected_colors', 'color_parent_id', '/admin/products/search', 'color');
});

// ======== Edit: Selector asistido de tamaño ========
document.addEventListener('DOMContentLoaded', function () {
    var hiddenSize = document.getElementById('size');
    var sizeSelect = document.querySelector('[data-size-select]');
    var manualInput = document.querySelector('[data-size-manual]');

    if (!hiddenSize || !sizeSelect || !manualInput) return;

    function syncSizeValue() {
        if (sizeSelect.value === '__manual__') {
            manualInput.classList.remove('d-none');
            hiddenSize.value = manualInput.value.trim();
            return;
        }

        manualInput.classList.add('d-none');
        manualInput.value = '';
        hiddenSize.value = sizeSelect.value;
    }

    sizeSelect.addEventListener('change', syncSizeValue);
    manualInput.addEventListener('input', syncSizeValue);
    syncSizeValue();
});


// ======== Edit: Galería multi-delete ========
// Archivo: resources/views/admin/products/edit.blade.php — modal de gestión de galería
function deleteSelectedImages() {
    const checkboxes = document.querySelectorAll('.gallery-checkbox:checked');
    const selectedNames = Array.from(checkboxes).map(cb => cb.value);
    if (selectedNames.length === 0) {
        alert('Selecione pelo menos uma imagem.');
        return;
    }
    if (confirm('Excluir ' + selectedNames.length + ' imagens selecionadas?')) {
        const form = document.getElementById('formMultiDeleteGallery');
        const input = document.getElementById('inputImageNames');
        if (form && input) {
            input.value = selectedNames.join(',');
            form.submit();
        }
    }
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.gallery-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}


// ======== Index: Auto-submit de filtros ========
// Archivo: resources/views/admin/products/index.blade.php
document.addEventListener('DOMContentLoaded', function () {
    var filterForm = document.getElementById('filterForm');
    if (!filterForm) return;

    // Adicionado select[name="per_page"]
    var selects = filterForm.querySelectorAll(
        'select[name="status_filter"], select[name="sort_by"], select[name="highlight_filter"], select[name="brand_id"], select[name="category_id"], select[name="product_type"], select[name="ai_preparation_filter"], select[name="per_page"]'
    );

    selects.forEach(function (select) {
        select.addEventListener('change', function () {
            filterForm.submit();
        });
    });
});


// ======== Index: Destaques AJAX ========
// Archivo: resources/views/admin/products/index.blade.php — modal de destaques
document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('.form-highlights');
    if (!forms.length) return;

    forms.forEach(function (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            var button = form.querySelector('button[type="submit"]');
            var originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Salvando...';

            try {
                var formData = new FormData(form);
                var response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (response.ok) {
                    var modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    if (modal) modal.hide();

                    if (window.saxToast) saxToast('success', 'Destaques atualizados com sucesso!');
                } else {
                    throw new Error('Erro ao salvar');
                }
            } catch (error) {
                if (window.saxToast) {
                    saxToast('error', 'Erro ao atualizar destaques. Tente novamente.');
                } else {
                    alert('Erro ao atualizar destaques. Tente novamente.');
                }
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        });
    });
});


// ======== Review: filtros e modal de detalhes ========
// Archivo: resources/views/admin/products/review.blade.php
var productReviewState = {
    products: {},
    filtered: {},
    activeDay: null
};

function productReviewEscape(value) {
    return String(value == null ? '' : value).replace(/[&<>'"]/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char];
    });
}

function productReviewNormalize(value) {
    return String(value == null ? '' : value)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function productReviewMatches(product) {
    var search = productReviewNormalize(document.getElementById('product-review-search')?.value);
    var editor = document.getElementById('product-review-editor')?.value || '';
    var source = document.getElementById('product-review-source')?.value || '';
    var status = document.getElementById('product-review-status')?.value || '';
    var image = document.getElementById('product-review-image')?.value || '';
    var front = document.getElementById('product-review-front')?.value || '';
    var haystack = productReviewNormalize([product.name, product.sku, product.ref_code].join(' '));

    if (search && !haystack.includes(search)) return false;
    if (editor && product.editor_label !== editor) return false;
    if (source && product.source !== source) return false;
    if (status === 'unknown' && product.status !== null) return false;
    if ((status === '0' || status === '1') && String(product.status) !== status) return false;
    if (image === 'with' && !product.has_image) return false;
    if (image === 'without' && product.has_image) return false;
    if (front === 'available' && !product.front_available) return false;
    if (front === 'unavailable' && product.front_available) return false;

    return true;
}

function productReviewSyncPdfFilters() {
    var fields = [
        ['product-review-search', 'report-filter-search'],
        ['product-review-editor', 'report-filter-editor'],
        ['product-review-source', 'report-filter-source'],
        ['product-review-status', 'report-filter-status'],
        ['product-review-image', 'report-filter-image'],
        ['product-review-front', 'report-filter-front']
    ];
    var activeLabels = [];

    fields.forEach(function (ids) {
        var source = document.getElementById(ids[0]);
        var target = document.getElementById(ids[1]);
        if (!source || !target) return;

        target.value = source.value;
        if (!source.value) return;

        activeLabels.push(source.tagName === 'SELECT'
            ? source.options[source.selectedIndex].text
            : 'Busca: ' + source.value);
    });

    var summary = document.getElementById('product-review-pdf-filter-summary');
    if (summary) {
        summary.textContent = activeLabels.length
            ? activeLabels.length + (activeLabels.length === 1 ? ' filtro aplicado' : ' filtros aplicados')
            : 'Sem filtros adicionais';
        summary.title = activeLabels.join(' · ');
    }
}

function productReviewApplyFilters() {
    var visibleTotal = 0;
    var visibleDays = 0;
    productReviewState.filtered = {};

    Object.keys(productReviewState.products).forEach(function (day) {
        productReviewState.filtered[day] = (productReviewState.products[day] || []).filter(productReviewMatches);
    });

    document.querySelectorAll('[data-review-day]').forEach(function (dayCard) {
        var day = dayCard.dataset.reviewDay;
        var count = (productReviewState.filtered[day] || []).length;
        var countElement = dayCard.querySelector('.product-review-day-count');

        if (countElement) countElement.textContent = count;
        dayCard.classList.toggle('d-none', count === 0);
        visibleTotal += count;
        if (count > 0) visibleDays += 1;
    });

    var countLabel = document.getElementById('product-review-result-count');
    if (countLabel) {
        countLabel.textContent = visibleTotal + (visibleTotal === 1 ? ' produto em ' : ' produtos em ') +
            visibleDays + (visibleDays === 1 ? ' dia' : ' dias');
    }

    document.getElementById('product-review-empty')?.classList.toggle('d-none', visibleTotal !== 0);

    var visibleProducts = Object.values(productReviewState.filtered).flat();
    var quickCounts = {
        'product-review-inactive-count': visibleProducts.filter(function (product) {
            return Number(product.status) === 0;
        }).length,
        'product-review-without-image-count': visibleProducts.filter(function (product) {
            return !product.has_image;
        }).length,
        'product-review-without-front-count': visibleProducts.filter(function (product) {
            return !product.front_available;
        }).length
    };
    Object.keys(quickCounts).forEach(function (id) {
        var element = document.getElementById(id);
        if (element) element.textContent = quickCounts[id];
    });

    document.querySelectorAll('[data-review-quick-filter]').forEach(function (button) {
        var isActive = (button.dataset.reviewQuickFilter === 'inactive'
                && document.getElementById('product-review-status')?.value === '0')
            || (button.dataset.reviewQuickFilter === 'without-image'
                && document.getElementById('product-review-image')?.value === 'without')
            || (button.dataset.reviewQuickFilter === 'without-front'
                && document.getElementById('product-review-front')?.value === 'unavailable');
        button.classList.toggle('active', isActive);
    });

    productReviewSyncPdfFilters();

    if (productReviewState.activeDay) {
        productReviewRenderDay(productReviewState.activeDay);
    }
}

function productReviewAction(url, icon, label, styleClass, unavailableTitle) {
    if (!url) {
        return '<span class="btn btn-sm btn-light disabled" aria-disabled="true" title="' +
            productReviewEscape(unavailableTitle || 'Ação indisponível') + '">' +
            '<i class="' + icon + '"></i><span class="d-none d-sm-inline ms-1">' + label + '</span></span>';
    }

    return '<a class="btn btn-sm ' + styleClass + '" href="' + productReviewEscape(url) +
        '" target="_blank" rel="noopener"><i class="' + icon + '"></i>' +
        '<span class="d-none d-sm-inline ms-1">' + label + '</span></a>';
}

function productReviewRenderDay(data) {
    var corpo = document.getElementById('corpoTabelaLocal');
    var titulo = document.getElementById('tituloModal');
    var modalCount = document.getElementById('product-review-modal-count');
    if (!corpo || !titulo) return;

    var produtosDoDia = productReviewState.filtered[data] || [];
    var dateParts = data.split('-');
    var displayDate = dateParts.length === 3 ? dateParts.reverse().join('/') : data;

    titulo.innerText = 'Editados em ' + displayDate;
    if (modalCount) {
        modalCount.textContent = produtosDoDia.length +
            (produtosDoDia.length === 1 ? ' produto com os filtros atuais' : ' produtos com os filtros atuais');
    }
    corpo.innerHTML = '';

    if (produtosDoDia.length === 0) {
        corpo.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">Nenhum produto corresponde aos filtros atuais.</td></tr>';
        return;
    }

    produtosDoDia.forEach(function (product) {
        var status = product.status === null
            ? '<span class="badge text-bg-secondary">Sem correspondente</span>'
            : (Number(product.status) === 1
                ? '<span class="badge text-bg-success">Ativo</span>'
                : '<span class="badge text-bg-light border text-dark">Inativo</span>');

        var image = product.image_url
            ? '<img class="product-review-thumb" src="' + productReviewEscape(product.image_url) +
                '" alt="" loading="lazy">'
            : '<div class="product-review-thumb product-review-thumb-empty" title="Este produto não possui imagem cadastrada">' +
                '<i class="fa-regular fa-image"></i><span>Sem imagem</span></div>';

        corpo.insertAdjacentHTML('beforeend',
            '<tr class="product-review-row">' +
                '<td class="ps-lg-4" data-label="Produto">' +
                    '<div class="product-review-product">' +
                        image +
                        '<div class="min-w-0"><strong class="product-review-name">' +
                            productReviewEscape(product.name || 'Produto sem nome') + '</strong>' +
                            '<div class="d-flex flex-wrap gap-1 mt-1">' + status +
                            '<span class="badge bg-light text-dark border">' + productReviewEscape(product.source_label || 'SAX') +
                            '</span></div></div>' +
                    '</div>' +
                '</td>' +
                '<td data-label="Identificação">' +
                    '<div><span class="text-muted small">SKU</span> <code>' + productReviewEscape(product.sku || '-') + '</code></div>' +
                    '<div><span class="text-muted small">Ref.</span> <span class="small fw-semibold">' +
                        productReviewEscape(product.ref_code || '-') + '</span></div>' +
                '</td>' +
                '<td data-label="Edição">' +
                    '<div class="fw-semibold small">' + productReviewEscape(product.editor_label || 'Usuário removido') + '</div>' +
                    '<div class="text-muted small"><i class="fa-regular fa-clock me-1"></i>' +
                        productReviewEscape(product.edited_at || '-') + '</div>' +
                '</td>' +
                '<td class="pe-lg-4" data-label="Ações"><div class="product-review-actions">' +
                    productReviewAction(
                        product.admin_url,
                        'fa-regular fa-pen-to-square',
                        'Admin',
                        'btn-dark',
                        'Produto sem correspondente nesta loja'
                    ) +
                    productReviewAction(
                        product.front_url,
                        'fa-solid fa-arrow-up-right-from-square',
                        'Loja',
                        'btn-outline-dark',
                        'Produto indisponível no front; você permanece nesta revisão'
                    ) +
                '</div></td>' +
            '</tr>'
        );
    });
}

function abrirModalLocal(data) {
    productReviewState.activeDay = data;
    productReviewRenderDay(data);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalhesLocal')).show();
}

function inicializarFiltrosRevisaoProdutos() {
    var jsonElement = document.getElementById('product-review-json');
    if (!jsonElement) return;

    try {
        productReviewState.products = JSON.parse(jsonElement.textContent || '{}');
    } catch (error) {
        productReviewState.products = {};
    }

    var editorSelect = document.getElementById('product-review-editor');
    var editors = [];
    Object.keys(productReviewState.products).forEach(function (day) {
        (productReviewState.products[day] || []).forEach(function (product) {
            if (product.editor_label && !editors.includes(product.editor_label)) editors.push(product.editor_label);
        });
    });
    editors.sort(function (left, right) {
        return left.localeCompare(right, 'pt', { sensitivity: 'base' });
    }).forEach(function (editor) {
        var option = document.createElement('option');
        option.value = editor;
        option.textContent = editor;
        editorSelect?.appendChild(option);
    });

    ['product-review-search', 'product-review-editor', 'product-review-source', 'product-review-status', 'product-review-image', 'product-review-front'].forEach(function (id) {
        var element = document.getElementById(id);
        element?.addEventListener(element.tagName === 'INPUT' ? 'input' : 'change', productReviewApplyFilters);
    });

    document.querySelectorAll('[data-review-quick-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.dataset.reviewQuickFilter === 'inactive') {
                document.getElementById('product-review-status').value = '0';
            } else if (button.dataset.reviewQuickFilter === 'without-image') {
                document.getElementById('product-review-image').value = 'without';
            } else if (button.dataset.reviewQuickFilter === 'without-front') {
                document.getElementById('product-review-front').value = 'unavailable';
            }
            productReviewApplyFilters();
        });
    });

    document.getElementById('product-review-clear')?.addEventListener('click', function () {
        ['product-review-search', 'product-review-editor', 'product-review-source', 'product-review-status', 'product-review-image', 'product-review-front'].forEach(function (id) {
            var element = document.getElementById(id);
            if (element) element.value = '';
        });
        productReviewApplyFilters();
        document.getElementById('product-review-search')?.focus();
    });

    document.getElementById('modalDetalhesLocal')?.addEventListener('hidden.bs.modal', function () {
        productReviewState.activeDay = null;
    });

    productReviewApplyFilters();
}

function inicializarFiltroRelatorioProdutos() {
    var form = document.getElementById('product-review-report-form');
    var periodSelect = document.getElementById('report-period');
    if (!form || !periodSelect) return;

    var fieldGroups = form.querySelectorAll('[data-report-field]');

    function atualizarCamposRelatorio() {
        var selectedPeriod = periodSelect.value;

        fieldGroups.forEach(function (group) {
            var isActive = group.dataset.reportField === selectedPeriod;
            group.classList.toggle('d-none', !isActive);

            group.querySelectorAll('input').forEach(function (input) {
                input.disabled = !isActive;
                input.required = isActive;
            });
        });
    }

    periodSelect.addEventListener('change', atualizarCamposRelatorio);
    atualizarCamposRelatorio();
}

document.addEventListener('DOMContentLoaded', function () {
    inicializarFiltroRelatorioProdutos();
    inicializarFiltrosRevisaoProdutos();

    var input = document.getElementById('photoInput');
    if (!input) return;

    input.addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        var box = document.getElementById('photoPreviewBox');
        var img = document.getElementById('photoPreviewImg');

        // libera el objectURL anterior (evita fuga de memoria al cambiar varias veces)
        if (img.dataset.objectUrl) URL.revokeObjectURL(img.dataset.objectUrl);

        var url = URL.createObjectURL(file);
        img.src = url;
        img.dataset.objectUrl = url;
        box.style.display = '';
        document.getElementById('productPhotoDropzone')?.classList.add('mt-3');
        var status = document.getElementById('photoSelectionStatus');
        if (status) status.textContent = file.name + ' · ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
    });
});



// Acumula las imágenes elegidas con DataTransfer y reescribe input.files,
// para que al guardar se manden TODAS. No toca backend (update ya acumula sobre lo guardado).
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('galleryInput');
    if (!input) return;

    var preview = document.getElementById('galleryPreview');
    var countLabel = document.getElementById('gallerySelectionCount');
    var store = new DataTransfer(); // acumula TODOS los archivos elegidos
    var urls = [];                  // objectURLs activos, para revocarlos en cada render

    function render() {
        urls.forEach(function (u) { URL.revokeObjectURL(u); });
        urls = [];
        preview.innerHTML = '';

        var files = Array.from(store.files);
        preview.style.display = files.length ? 'grid' : 'none';
        if (countLabel) countLabel.textContent = files.length
            ? files.length + (files.length === 1 ? ' nova imagem selecionada' : ' novas imagens selecionadas')
            : 'Nenhuma nova imagem selecionada';

        files.forEach(function (file, index) {
            var url = URL.createObjectURL(file);
            urls.push(url);

            var col = document.createElement('div');
            col.className = 'product-gallery-pending';
            col.innerHTML =
                '<img src="' + url + '" alt="Prévia da imagem">' +
                '<button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 m-1" data-index="' + index + '" aria-label="Remover imagem">' +
                '<i class="fas fa-times"></i></button>';
            preview.appendChild(col);
        });
    }

    input.addEventListener('change', function () {
        Array.from(input.files).forEach(function (file) {
            var dup = Array.from(store.files).some(function (f) {
                return f.name === file.name && f.size === file.size; // evita duplicados
            });
            if (!dup) store.items.add(file);
        });
        input.files = store.files; // reescribe el input → al guardar va TODO lo acumulado
        render();
    });

    preview.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-index]');
        if (!btn) return;
        store.items.remove(parseInt(btn.dataset.index, 10)); // quita esa foto del set
        input.files = store.files;
        render();
    });
});


// ======== Index: aplica visualmente o status (ativo/inativo) num card ========
// Archivo: resources/views/admin/products/index.blade.php
// Usado tanto pelo toggle individual quanto pela revalidação em massa.
function applyProductStatusUI(productId, isActive) {
    var card = document.getElementById('product-card-' + productId);
    if (!card) return;

    var pill = card.querySelector('.product-status-pill');
    var btn = card.querySelector('.btn-toggle-status');

    if (pill && btn) {
        pill.classList.toggle('is-on', isActive);
        pill.classList.toggle('is-off', !isActive);
        pill.textContent = isActive ? btn.dataset.labelActive : btn.dataset.labelInactive;
    }

    var img = card.querySelector('.sax-product-img-box img');
    if (img) img.classList.toggle('is-inactive', !isActive);

    if (!btn) return;

    btn.classList.toggle('is-on', isActive);

    var icon = btn.querySelector('.icon-toggle');
    if (icon) {
        icon.classList.toggle('fa-toggle-on', isActive);
        icon.classList.toggle('fa-toggle-off', !isActive);
    }
}

// ======== Index: Ativar/Desativar via AJAX (sem reload) ========
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-toggle-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled) return;
            btn.disabled = true;

            fetch(btn.dataset.url, {
                method: 'POST',
                headers: Object.assign({ 'Accept': 'application/json' }, headers),
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok || !result.data.success) {
                        throw new Error(result.data.message || 'Erro ao atualizar status.');
                    }
                    var card = btn.closest('[id^="product-card-"]');
                    if (card) applyProductStatusUI(card.id.replace('product-card-', ''), !!result.data.status);
                    if (window.saxToast) saxToast('success', result.data.message || 'Status atualizado!');
                })
                .catch(function (err) {
                    if (window.saxToast) saxToast('error', err.message || 'Erro ao atualizar status.');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    });
});

// ======== Index: Excluir produto via AJAX (com modal de confirmação) ========
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = document.getElementById('product-card-' + btn.dataset.productId);

            function doDelete() {
                btn.disabled = true;

                fetch(btn.dataset.url, {
                    method: 'DELETE',
                    headers: Object.assign({ 'Accept': 'application/json' }, headers),
                })
                    .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                    .then(function (result) {
                        if (!result.ok || !result.data.success) {
                            throw new Error(result.data.message || 'Erro ao excluir produto.');
                        }
                        if (window.saxToast) saxToast('success', result.data.message || 'Produto excluído com sucesso!');
                        if (card) {
                            card.style.transition = 'opacity .25s';
                            card.style.opacity = '0';
                            setTimeout(function () { card.remove(); }, 250);
                        }
                    })
                    .catch(function (err) {
                        if (window.saxToast) saxToast('error', err.message || 'Erro ao excluir produto.');
                        btn.disabled = false;
                    });
            }

            var confirmModalEl = document.getElementById('saxConfirmModal');
            if (confirmModalEl && typeof bootstrap !== 'undefined') {
                document.getElementById('saxConfirmMessage').textContent = 'Tem certeza que deseja excluir este produto? Essa ação não pode ser desfeita.';
                document.getElementById('saxConfirmAccept').onclick = function () {
                    bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
                    doDelete();
                };
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            } else if (confirm('Tem certeza que deseja excluir este produto?')) {
                doDelete();
            }
        });
    });
});

// ======== Index: Verificar produtos (revalida em massa, sem reload) ========
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnRevalidateProducts');
    if (!btn) return;

    btn.addEventListener('click', function () {
        if (btn.disabled) return;
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Verificando...';

        fetch(btn.dataset.url, {
            method: 'POST',
            headers: Object.assign({ 'Accept': 'application/json' }, headers),
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok || !result.data.success) {
                    throw new Error(result.data.message || 'Erro ao verificar produtos.');
                }
                (result.data.activated_ids || []).forEach(function (id) { applyProductStatusUI(id, true); });
                (result.data.deactivated_ids || []).forEach(function (id) { applyProductStatusUI(id, false); });
                if (window.saxToast) saxToast('success', result.data.message || 'Verificação concluída!');
            })
            .catch(function (err) {
                if (window.saxToast) saxToast('error', err.message || 'Erro ao verificar produtos.');
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    });
});


(function() {
                    if (!document.getElementById('color-section')) return;
                    let colorEntries = [];
                    let showAll = false;

                    async function initPalette() {
                        try {
                            const response = await fetch('/data/color.json');
                            const data = await response.json();

                            colorEntries = Object.entries(data).map(([hex, name]) => ({
                                hex: String(hex).toUpperCase(),
                                name: String(name),
                            }));

                            renderColors();
                            detectDominantImageColor();
                        } catch (error) {
                            console.error("Erro ao carregar cores:", error);
                        }
                    }

                    function normalizeHex(value) {
                        if (!value) return null;
                        let hex = String(value).trim().replace('#', '').toUpperCase();

                        if (/^[0-9A-F]{3}$/.test(hex)) {
                            hex = hex.split('').map(ch => ch + ch).join('');
                        }

                        if (!/^[0-9A-F]{6}$/.test(hex)) {
                            return null;
                        }

                        return `#${hex}`;
                    }

                    function hexToRgb(hex) {
                        return {
                            r: parseInt(hex.slice(1, 3), 16),
                            g: parseInt(hex.slice(3, 5), 16),
                            b: parseInt(hex.slice(5, 7), 16),
                        };
                    }

                    function colorDistance(a, b) {
                        const dr = a.r - b.r;
                        const dg = a.g - b.g;
                        const db = a.b - b.b;
                        return dr * dr + dg * dg + db * db;
                    }

                    function closestByHex(hex, limit = 6) {
                        const target = hexToRgb(hex);

                        return colorEntries
                            .map(entry => ({
                                ...entry,
                                dist: colorDistance(target, hexToRgb(entry.hex))
                            }))
                            .sort((a, b) => a.dist - b.dist)
                            .slice(0, limit);
                    }

                    function closestByName(term, limit = 6) {
                        const query = term.toLowerCase().trim();
                        if (!query) return [];

                        const words = query.split(/\s+/).filter(Boolean);

                        return colorEntries
                            .map(entry => {
                                const name = entry.name.toLowerCase();
                                const score = words.reduce((acc, word) => {
                                    if (name === word) return acc + 120;
                                    if (name.startsWith(word)) return acc + 50;
                                    if (name.includes(word)) return acc + 25;
                                    return acc;
                                }, 0);

                                return { ...entry, score };
                            })
                            .filter(entry => entry.score > 0)
                            .sort((a, b) => b.score - a.score)
                            .slice(0, limit);
                    }

                    function applyColor(hex) {
                        const colorInput = document.getElementById('color-input');
                        const colorSearch = document.getElementById('color-search');
                        const noColor = document.getElementById('no_color');

                        if (colorInput) colorInput.value = hex;
                        if (colorSearch) colorSearch.value = hex;
                        if (noColor) noColor.checked = false;
                        addSelectedColor(hex);
                    }

                    function addSelectedColor(hex) {
                        const normalized = normalizeHex(hex);
                        const container = document.getElementById('selected-colors');
                        if (!normalized || !container || container.querySelector(`[data-color="${normalized}"]`)) return;
                        if (container.querySelectorAll('.selected-color').length >= 8) return;

                        const chip = document.createElement('span');
                        chip.className = 'selected-color badge bg-light text-dark border d-inline-flex align-items-center gap-2 p-2';
                        chip.dataset.color = normalized;
                        chip.innerHTML = `<i style="width:18px;height:18px;border-radius:50%;background:${normalized};border:1px solid #bbb"></i>${normalized}<button type="button" class="btn-close" style="font-size:8px" aria-label="Remover cor"></button><input type="hidden" name="colors_values[]" value="${normalized}">`;
                        container.appendChild(chip);
                    }

                    function removeSelectedColor(event) {
                        const removeButton = event.target.closest('.btn-close');
                        if (removeButton) removeButton.closest('.selected-color')?.remove();
                    }

                    function detectDominantImageColor() {
                        const section = document.getElementById('color-section');
                        const image = document.getElementById('photoPreviewImg');
                        if (!section || section.dataset.detectColorFromImage !== '1' || !image) return;
                        if (!image.getAttribute('src')) {
                            image.addEventListener('load', detectDominantImageColor, { once: true });
                            return;
                        }

                        const analyze = () => {
                            try {
                                const canvas = document.createElement('canvas');
                                const context = canvas.getContext('2d', { willReadFrequently: true });
                                const size = 72;
                                canvas.width = size;
                                canvas.height = size;
                                context.drawImage(image, 0, 0, size, size);

                                const pixels = context.getImageData(0, 0, size, size).data;
                                const buckets = new Map();
                                for (let index = 0; index < pixels.length; index += 4) {
                                    const r = pixels[index];
                                    const g = pixels[index + 1];
                                    const b = pixels[index + 2];
                                    if (pixels[index + 3] < 180 || (r > 235 && g > 235 && b > 235)) continue;

                                    const saturation = Math.max(r, g, b) - Math.min(r, g, b);
                                    const key = `${Math.round(r / 24)},${Math.round(g / 24)},${Math.round(b / 24)}`;
                                    const bucket = buckets.get(key) || { score: 0, r: 0, g: 0, b: 0, count: 0 };
                                    bucket.score += 1 + saturation / 80;
                                    bucket.r += r;
                                    bucket.g += g;
                                    bucket.b += b;
                                    bucket.count += 1;
                                    buckets.set(key, bucket);
                                }

                                const dominant = Array.from(buckets.values()).sort((a, b) => b.score - a.score)[0];
                                if (!dominant || dominant.count < 10) return;

                                const toHex = value => Math.round(value).toString(16).padStart(2, '0').toUpperCase();
                                const hex = `#${toHex(dominant.r / dominant.count)}${toHex(dominant.g / dominant.count)}${toHex(dominant.b / dominant.count)}`;
                                applyColor(hex);
                                renderColors(hex);

                                const message = document.getElementById('image-color-suggestion');
                                if (message) {
                                    message.classList.remove('d-none');
                                    message.innerHTML = `<i class="fas fa-image me-1"></i>Cor sugerida pela foto: <strong>${hex}</strong>. Confira antes de salvar.`;
                                }
                                section.dataset.detectColorFromImage = '0';
                            } catch (error) {
                                console.warn('Não foi possível identificar a cor pela imagem:', error);
                            }
                        };

                        if (image.complete && image.naturalWidth) analyze();
                        else image.addEventListener('load', analyze, { once: true });
                    }

                    function renderSuggestions(filter, filteredCount) {
                        const suggestionsContainer = document.getElementById('color-suggestions');
                        if (!suggestionsContainer) return;

                        suggestionsContainer.innerHTML = '';

                        const normalizedHex = normalizeHex(filter);
                        let suggestions = [];

                        if (normalizedHex) {
                            const exact = colorEntries.some(entry => entry.hex === normalizedHex);
                            if (!exact) {
                                suggestions = closestByHex(normalizedHex, 8);
                            }
                        } else if (String(filter).trim() && filteredCount === 0) {
                            suggestions = closestByName(filter, 8);
                        }

                        if (!suggestions.length) {
                            return;
                        }

                        const wrap = document.createElement('div');
                        wrap.className = 'd-flex flex-wrap align-items-center gap-2';

                        const title = document.createElement('span');
                        title.className = 'text-muted';
                        title.textContent = 'Nomes parecidos:';
                        wrap.appendChild(title);

                        suggestions.forEach(entry => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'btn btn-outline-secondary btn-sm py-1 px-2';
                            btn.innerHTML = `<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${entry.hex};margin-right:6px;"></span>${entry.name}`;
                            btn.onclick = () => applyColor(entry.hex);
                            wrap.appendChild(btn);
                        });

                        suggestionsContainer.appendChild(wrap);
                    }

                    window.renderColors = function(filter = '') {
                        const container = document.getElementById('color-palette');
                        if (!container) return;

                        container.innerHTML = '';
                        const normalizedFilter = String(filter).toLowerCase();
                        const filtered = colorEntries.filter(entry =>
                            entry.name.toLowerCase().includes(normalizedFilter) || entry.hex.toLowerCase().includes(normalizedFilter)
                        );

                        const limit = showAll ? filtered.length : 20;

                        filtered.slice(0, limit).forEach(entry => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'btn btn-sm border shadow-sm';
                            btn.style.backgroundColor = entry.hex;
                            btn.style.width = '32px';
                            btn.style.height = '32px';
                            btn.title = `${entry.name} (${entry.hex})`;
                            btn.onclick = () => applyColor(entry.hex);
                            container.appendChild(btn);
                        });

                        renderSuggestions(filter, filtered.length);
                    };

                    window.toggleColors = function() {
                        showAll = !showAll;
                        document.getElementById('btn-toggle').innerText = showAll ? 'Mostrar menos...' : 'Mostrar mais cores...';
                        renderColors(document.getElementById('color-search') ? document.getElementById('color-search').value : '');
                    };

                    document.addEventListener('DOMContentLoaded', () => {
                        initPalette();

                        const colorSearch = document.getElementById('color-search');
                        const colorInput = document.getElementById('color-input');
                        const selectedColors = document.getElementById('selected-colors');
                        const addColor = document.getElementById('add-color');
                        const noColor = document.getElementById('no_color');

                        selectedColors?.addEventListener('click', removeSelectedColor);
                        addColor?.addEventListener('click', () => applyColor(colorInput?.value));
                        noColor?.addEventListener('change', function () {
                            if (this.checked && selectedColors) selectedColors.innerHTML = '';
                        });

                        if (colorSearch) {
                            colorSearch.addEventListener('input', function() {
                                renderColors(this.value);
                            });
                        }

                        if (colorInput) {
                            colorInput.addEventListener('input', function() {
                                const colorSearchField = document.getElementById('color-search');
                                if (colorSearchField) {
                                    colorSearchField.value = this.value;
                                    renderColors(this.value);
                                }
                            });
                        }
                    });
                })();

(async function() {
                    const sizeContainer = document.getElementById('size-container');
                    if (!sizeContainer) return;
                    const typeSelector = document.getElementById('type_selector');
                    const sizeSelect = document.getElementById('size_select');
                    const inputManual = document.getElementById('size_manual');
                    const detectionMsg = document.getElementById('detection-msg');

                    const currentSize = sizeContainer.dataset.currentSize;
                    const detectedSize = sizeContainer.dataset.detectedSize;

                    // Reutiliza inferredSize() recebido pela vista, sem substituir edição manual.
                    if (!inputManual.value.trim() && detectedSize) {
                        inputManual.value = detectedSize;
                    }

                    let sizeGroups = {};
                    typeSelector.value = 'manual';
                    populateSizes('manual');

                    const response = await fetch('/data/tamanho.json');
                    sizeGroups = await response.json();

                    Object.keys(sizeGroups).forEach(key => {
                        const opt = document.createElement('option');
                        opt.value = key; opt.text = key.charAt(0).toUpperCase() + key.slice(1);
                        typeSelector.appendChild(opt);
                    });

                    function populateSizes(group) {
                        sizeSelect.innerHTML = '<option value="">Selecione o tamanho</option>';
                        const valToSet = currentSize || detectedSize;

                        if (group && sizeGroups[group]) {
                            sizeGroups[group].forEach(val => {
                                const opt = document.createElement('option');
                                opt.value = val; opt.text = val;
                                if(val === valToSet) opt.selected = true;
                                sizeSelect.appendChild(opt);
                            });
                            sizeSelect.classList.remove('d-none');
                            sizeSelect.setAttribute('name', 'size');
                            inputManual.removeAttribute('name');
                            inputManual.classList.add('d-none');
                        } else if (group === 'manual') {
                            sizeSelect.classList.add('d-none');
                            sizeSelect.removeAttribute('name');
                            inputManual.classList.remove('d-none');
                            inputManual.setAttribute('name', 'size');
                        } else {
                            sizeSelect.classList.add('d-none');
                            inputManual.classList.add('d-none');
                        }
                    }

                    typeSelector.addEventListener('change', (e) => {
                        detectionMsg.classList.add('d-none');
                        populateSizes(e.target.value);
                    });

                    typeSelector.value = 'manual';
                    populateSizes('manual');
                })();

// A barra usa o submit nativo do mesmo formulário, inclusive a sincronização existente do editor.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('productEditForm');
    const status = document.getElementById('productUnsavedStatus');
    if (!form || !status) return;

    const savebar = form.querySelector('.product-edit-savebar');
    const topbar = document.querySelector('.sax-admin-topbar');
    if (savebar && topbar) {
        const updateOffset = () => savebar.style.setProperty(
            '--product-savebar-header-height', `${topbar.getBoundingClientRect().height}px`
        );
        updateOffset();
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(updateOffset).observe(topbar);
        }
    }

    // Observa os valores enviados, incluindo alterações programáticas (IA e variantes),
    // sem interferir na troca de idiomas nem na sincronização dos campos.
    function snapshot() {
        return JSON.stringify(Array.from(new FormData(form), ([name, value]) => [
            name, value instanceof File ? [value.name, value.size, value.size ? value.lastModified : 0] : value
        ]));
    }
    const initial = snapshot();
    function updateStatus() {
        const dirty = snapshot() !== initial;
        status.textContent = dirty ? 'Alterações não salvas' : 'Sem alterações pendentes';
        savebar?.classList.toggle('has-unsaved-changes', dirty);
        status.classList.toggle('text-warning', dirty);
        status.classList.toggle('text-muted', !dirty);
    }
    form.addEventListener('input', () => queueMicrotask(updateStatus));
    form.addEventListener('change', () => queueMicrotask(updateStatus));
    const timer = window.setInterval(updateStatus, 750);
    window.addEventListener('pagehide', () => window.clearInterval(timer), { once: true });

    // Permite focar um campo DHL inválido mesmo com o bloco recolhido.
    form.addEventListener('invalid', function (event) {
        const details = event.target.closest('details');
        if (details) details.open = true;
    }, true);
});

// Description UI: language presence and keyboard navigation, scoped to products.
document.addEventListener('DOMContentLoaded', function () {
    const section = document.querySelector('.product-description');
    if (!section) return;
    const tabs = Array.from(section.querySelectorAll('.desc-lang-btn'));
    window.saxRefreshProductDescription = function () {
        section.querySelectorAll('[data-description-presence]').forEach(status => {
            const html = document.getElementById('real-desc-' + status.dataset.descriptionPresence)?.value || '';
            const content = document.createElement('template');
            content.innerHTML = html;
            status.textContent = content.content.textContent.replace(/\u00a0/g, ' ').trim() ? '' : 'Sin texto';
        });
    };
    window.saxRefreshProductDescription();
    tabs.forEach(tab => tab.addEventListener('keydown', function (event) {
        const index = tabs.indexOf(tab);
        let next;
        if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
        else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
        else if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = tabs.length - 1;
        else return;
        event.preventDefault();
        tabs[next].click();
        tabs[next].focus();
    }));
    const visible = document.getElementById('editor-product');
    if (visible && (typeof tinymce === 'undefined' || !tinymce.get('editor-product'))) {
        visible.value = document.getElementById('real-desc-' + (window.currentLangs?.desc || 'pt'))?.value || '';
    }
    visible?.addEventListener('input', () => {
        if (typeof tinymce !== 'undefined' && tinymce.get('editor-product')) return;
        const target = document.getElementById('real-desc-' + (window.currentLangs?.desc || 'pt'));
        if (target) target.value = visible.value;
        window.saxRefreshProductDescription();
    });
});

// Product-name language tabs use the same keyboard behavior as description tabs.
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('.product-identity .name-lang-btn'));
    tabs.forEach(tab => tab.addEventListener('keydown', function (event) {
        const index = tabs.indexOf(tab);
        let next;
        if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
        else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
        else if (event.key === 'Home') next = 0;
        else if (event.key === 'End') next = tabs.length - 1;
        else return;
        event.preventDefault();
        tabs[next].click();
        tabs[next].focus();
    }));
});
