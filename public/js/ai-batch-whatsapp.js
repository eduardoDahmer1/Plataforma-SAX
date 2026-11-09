document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('waConsultation');
    if (!root) return;
    const byId = id => document.getElementById(id);
    const items = Array.from(root.querySelectorAll('.wa-item'));
    const actions = byId('waSelectionActions');
    const selectAll = byId('waSelectAll');
    const modal = new bootstrap.Modal(byId('waConsultationModal'));
    const message = byId('waMessage');
    const buildingInputs = Array.from(byId('waBuildings').querySelectorAll('input[type="radio"]'));
    const contacts = JSON.parse(byId('waContacts').textContent);
    const sector = byId('waSector');
    const suggestionBuilding = byId('waSuggestionBuilding');
    const selectedBuilding = () => buildingInputs.find(input => input.checked)?.value;
    const variantsByItem = new WeakMap(items.map(item => [item, JSON.parse(item.dataset.variants)]));
    let preferredContactId = '';

    function matchContact(contact, sourceItems) {
        const matchedProducts = sourceItems.map(item => ({
            item,
            products: variantsByItem.get(item).filter(product => product.matches && product.matches[contact.id]),
        })).filter(match => match.products.length > 0);
        const reasons = Array.from(new Set(matchedProducts.flatMap(match =>
            match.products.map(product => product.matches[contact.id]))));
        const labels = Array.from(new Set(matchedProducts.flatMap(match => match.products.flatMap(product =>
            product.matches[contact.id] === 'brand' ? [product.brand] : (product.taxonomy || []))))).filter(Boolean);
        return { contact, items: matchedProducts.map(match => match.item), reasons, labels };
    }
    function contactMatches(sourceItems, building = selectedBuilding()) {
        return contacts
            .filter(contact => contact.building === building && contact.phone)
            .map(contact => matchContact(contact, sourceItems))
            .filter(match => match.items.length > 0)
            .sort((left, right) => right.items.length - left.items.length);
    }
    function appendSuggestionButton(container, match, options = {}) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-dark wa-sector-suggestion';
        if (options.recommended) button.classList.add('is-recommended');
        button.dataset.contactId = match.contact.id;

        const label = document.createElement('span');
        label.textContent = match.contact.label;
        const details = document.createElement('small');
        const labelSummary = match.labels.slice(0, 3).join(', ') + (match.labels.length > 3 ? '…' : '');
        details.textContent = match.items.length + (match.items.length === 1 ? ' producto' : ' productos')
            + (labelSummary ? ' · ' + labelSummary : '');
        button.append(label, details);
        button.addEventListener('click', options.onClick);
        container.appendChild(button);
    }

    function renderQuickSuggestions() {
        const list = byId('waQuickSuggestions');
        const help = byId('waQuickSelectionHelp');
        list.replaceChildren();
        const matches = contactMatches(items, suggestionBuilding.value);
        if (!matches.length) {
            help.textContent = 'No encontramos sectores por marca o categoría en este edificio. Podés seleccionar los productos manualmente.';
            return;
        }
        matches.forEach(match => appendSuggestionButton(list, match, {
            recommended: preferredContactId === match.contact.id,
            onClick: function () {
                preferredContactId = match.contact.id;
                items.forEach(item => item.checked = match.items.includes(item));
                updateSelection();
                renderQuickSuggestions();
            },
        }));
        const matched = new Set(matches.flatMap(match => match.items));
        const withoutSuggestion = items.length - matched.size;
        help.textContent = withoutSuggestion > 0
            ? withoutSuggestion + (withoutSuggestion === 1
                ? ' producto no tiene un destino sugerido y sigue disponible para selección manual.'
                : ' productos no tienen un destino sugerido y siguen disponibles para selección manual.')
            : 'Elegí un sector para marcar solamente sus productos.';
    }

    function populateSectors() {
        sector.replaceChildren();
        const available = contacts.filter(contact => contact.building === selectedBuilding());
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = available.length
            ? 'Elegir sector o ingresar número manualmente'
            : 'Sin sectores disponibles. Ingresá un número manualmente';
        sector.appendChild(placeholder);
        available.forEach(contact => {
            const option = document.createElement('option');
            option.value = contact.id;
            option.textContent = contact.label;
            sector.appendChild(option);
        });
        sector.value = '';
    }
    function markSelectedDestination() {
        byId('waSuggestedDestinationList').querySelectorAll('[data-contact-id]').forEach(button => {
            button.classList.toggle('is-recommended', button.dataset.contactId === sector.value);
        });
    }
    function chooseContact(contact) {
        if (!contact || contact.building !== selectedBuilding()) return;
        sector.value = contact.id;
        byId('waPhone').value = contact.phone || '';
        byId('waPhone').classList.remove('is-invalid');
        markSelectedDestination();
    }
    function renderDestinationSuggestions() {
        const container = byId('waSuggestedDestinations');
        const list = byId('waSuggestedDestinationList');
        const selectedItems = items.filter(item => item.checked);
        const matches = contactMatches(selectedItems);
        list.replaceChildren();
        container.hidden = !matches.length;
        if (!matches.length) return;

        const recognizedLabels = Array.from(new Set(matches.flatMap(match => match.labels)));
        byId('waSuggestedDestinationsSummary').textContent = recognizedLabels.length
            ? 'Coincidencias reconocidas: ' + recognizedLabels.join(', ') + '.'
            : 'Encontramos coincidencias con la guía de sectores.';
        const bestCount = matches[0].items.length;
        matches.forEach(match => appendSuggestionButton(list, match, {
            recommended: match.items.length === bestCount,
            onClick: () => chooseContact(match.contact),
        }));
        markSelectedDestination();
    }
    populateSectors();
    buildingInputs.forEach(input => input.addEventListener('change', function () {
        if (!this.checked) return;
        suggestionBuilding.value = this.value;
        preferredContactId = '';
        populateSectors();
        byId('waPhone').value = '';
        byId('waPhone').classList.remove('is-invalid');
        renderQuickSuggestions();
        renderDestinationSuggestions();
    }));
    suggestionBuilding.addEventListener('change', function () {
        preferredContactId = '';
        const modalBuilding = buildingInputs.find(input => input.value === this.value);
        if (modalBuilding) modalBuilding.checked = true;
        renderQuickSuggestions();
    });
    const reasonInputs = Array.from(byId('waReasons').querySelectorAll('input[type="checkbox"]'));
    const requests = {
        photos: 'las fotos',
        confirm: 'confirmar el modelo y las variantes',
        dimensions: 'las medidas',
        weight: 'el peso del producto',
    };
    const selectedReasonKey = () => Object.keys(requests)
        .filter(key => reasonInputs.some(input => input.value === key && input.checked)).join('|');
    let activeReasons = selectedReasonKey();
    const group = byId('waGroup');
    const maxMessageLength = 2000; // Usability target for the plain text, before URL encoding.
    let families = [];
    let groups = [];
    let groupPositions = {};
    let drafts = {};
    let currentKey = '';

    function validateReasons() {
        const valid = Boolean(selectedReasonKey());
        byId('waReasonsError').hidden = valid;
        byId('waCopy').disabled = !valid;
        byId('waOpen').disabled = !valid;
        message.disabled = !valid;
        group.disabled = !valid;
        return valid;
    }

    function updateSelection() {
        const count = items.filter(item => item.checked).length;
        byId('waCount').textContent = count + (count === 1 ? ' seleccionado' : ' seleccionados');
        byId('waContinue').disabled = !count;
        selectAll.checked = count > 0 && count === items.length;
        selectAll.indeterminate = count > 0 && count < items.length;
    }
    byId('waStart').addEventListener('click', function () {
        this.hidden = true;
        actions.hidden = false;
        actions.style.removeProperty('display');
        root.querySelectorAll('.wa-select-cell').forEach(cell => cell.hidden = false);
        renderQuickSuggestions();
        updateSelection();
    });
    byId('waCancel').addEventListener('click', function () {
        items.forEach(item => item.checked = false);
        root.querySelectorAll('.wa-select-cell').forEach(cell => cell.hidden = true);
        actions.hidden = true;
        actions.style.setProperty('display', 'none', 'important');
        byId('waStart').hidden = false;
        drafts = {};
        currentKey = '';
        delete root.dataset.selection;
        preferredContactId = '';
        updateSelection();
    });
    selectAll.addEventListener('change', function () {
        preferredContactId = '';
        items.forEach(item => item.checked = this.checked);
        updateSelection();
        renderQuickSuggestions();
    });
    items.forEach(item => item.addEventListener('change', function () {
        preferredContactId = '';
        updateSelection();
        renderQuickSuggestions();
    }));

    function generate(products, selectedReason) {
        const needs = selectedReason.split('|').map(key => requests[key]);
        const list = needs.length === 1 ? needs[0] : needs.slice(0, -1).join(', ') + ' y ' + needs[needs.length - 1];
        const intro = selectedReason === 'photos'
            ? 'Hola compa, ¿me podés enviar fotos de estos productos?'
            : 'Hola compa, ¿me podés ayudar con estos productos? Necesito ' + list + ':';
        return intro + '\n\n' + products.map((product, index) =>
            (index + 1) + '. ' + (product.name ? product.name + '\n   ' : '') + 'Código: ' + product.sku
        ).join('\n\n') + '\n\nGracias!';
    }
    function splitMessages(selectedFamilies, selectedReason) {
        const messages = [];
        let current = [];
        function flush() {
            if (current.length) messages.push(current);
            current = [];
        }
        selectedFamilies.forEach(family => {
            // Keep a family together whenever its complete message fits on its own.
            if (generate(family, selectedReason).length <= maxMessageLength) {
                if (generate(current.concat(family), selectedReason).length > maxMessageLength) flush();
                current.push(...family);
                return;
            }
            // Oversized families are split only between whole variants, in source order.
            family.forEach(product => {
                if (generate(current.concat(product), selectedReason).length > maxMessageLength) flush();
                current.push(product);
                // An oversized product gets its own message without truncating any information.
                if (generate(current, selectedReason).length > maxMessageLength) flush();
            });
        });
        flush();
        return messages;
    }
    function populateGroups() {
        groups = splitMessages(families, activeReasons);
        group.replaceChildren();
        groups.forEach((productsInGroup, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = 'Grupo ' + (index + 1) + ' (' + productsInGroup.length + ' productos)';
            group.appendChild(option);
        });
        group.value = groupPositions[activeReasons] || '0';
        byId('waGroupContainer').hidden = groups.length <= 1;
    }
    function updateMessageLength() {
        const length = message.value.length;
        const warning = byId('waMessageLength');
        warning.className = length > maxMessageLength ? 'form-text text-danger' : 'form-text';
        warning.textContent = length + ' / 2.000 caracteres.' + (length > maxMessageLength
            ? ' Este mensaje supera los 2.000 caracteres. Se conserva el texto completo; podés acortarlo antes de enviar.' : '');
    }
    function showDraft() {
        if (currentKey) drafts[currentKey] = message.value;
        currentKey = activeReasons + ':' + group.value;
        groupPositions[activeReasons] = group.value;
        message.value = Object.prototype.hasOwnProperty.call(drafts, currentKey)
            ? drafts[currentKey] : generate(groups[Number(group.value)], activeReasons);
        updateMessageLength();
        byId('waFeedback').textContent = '';
    }
    byId('waContinue').addEventListener('click', function () {
        const unique = new Set();
        const selectedFamilies = [];
        items.filter(item => item.checked).forEach(item => {
            const family = variantsByItem.get(item).filter(product => {
                const key = product.sku.trim().toLocaleLowerCase();
                if (!key || unique.has(key)) return false;
                unique.add(key);
                return true;
            });
            if (family.length) selectedFamilies.push(family);
        });
        if (!selectedFamilies.length) return;
        const signature = JSON.stringify(selectedFamilies);
        if (root.dataset.selection !== signature) {
            root.dataset.selection = signature;
            drafts = {};
            currentKey = '';
            families = selectedFamilies;
            groupPositions = {};
            populateGroups();
        }
        const modalBuilding = buildingInputs.find(input => input.value === suggestionBuilding.value);
        if (modalBuilding) modalBuilding.checked = true;
        populateSectors();
        renderDestinationSuggestions();
        if (preferredContactId) {
            chooseContact(contacts.find(contact => contact.id === preferredContactId));
        } else {
            byId('waPhone').value = '';
        }
        showDraft();
        validateReasons();
        modal.show();
    });
    reasonInputs.forEach(input => input.addEventListener('change', function () {
        if (!validateReasons()) return;
        const nextReasons = selectedReasonKey();
        if (nextReasons === activeReasons) return;
        if (currentKey) drafts[currentKey] = message.value;
        const hasEdits = groups.some((products, index) => {
            const key = activeReasons + ':' + index;
            return Object.prototype.hasOwnProperty.call(drafts, key) && drafts[key] !== generate(products, activeReasons);
        });
        if (hasEdits && !window.confirm('Tenés mensajes editados. ¿Querés generar una propuesta para los nuevos motivos? Tus ediciones se conservan al volver a los motivos anteriores.')) {
            reasonInputs.forEach(input => input.checked = activeReasons.split('|').includes(input.value));
            return;
        }
        activeReasons = nextReasons;
        currentKey = '';
        if (!families.length) return;
        populateGroups();
        showDraft();
    }));
    group.addEventListener('change', showDraft);
    message.addEventListener('input', updateMessageLength);
    sector.addEventListener('change', function () {
        const contact = contacts.find(contact => contact.id === this.value && contact.building === selectedBuilding());
        byId('waPhone').value = contact?.phone || '';
        byId('waPhone').classList.remove('is-invalid');
        markSelectedDestination();
    });
    byId('waPhone').addEventListener('input', function () { this.classList.remove('is-invalid'); });
    byId('waOpen').addEventListener('click', function () {
        if (!validateReasons()) return;
        const phone = byId('waPhone');
        const raw = phone.value.trim();
        let digits = raw.replace(/[\s()+.\-]/g, '');
        if (digits.startsWith('00')) digits = digits.slice(2);
        if (!/^\d{8,15}$/.test(digits) || digits.startsWith('0')) {
            phone.classList.add('is-invalid');
            phone.focus();
            return;
        }
        window.open('https://wa.me/' + digits + '?text=' + encodeURIComponent(message.value), '_blank', 'noopener,noreferrer');
    });
    byId('waCopy').addEventListener('click', async function () {
        if (!validateReasons()) return;
        const feedback = byId('waFeedback');
        try {
            if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard unavailable');
            await navigator.clipboard.writeText(message.value);
            feedback.className = 'small text-success';
            feedback.textContent = 'Mensaje copiado.';
        } catch (error) {
            message.focus();
            message.select();
            feedback.className = 'small text-danger';
            feedback.textContent = 'No se pudo copiar automáticamente. El texto está seleccionado para copiarlo.';
        }
    });
});
