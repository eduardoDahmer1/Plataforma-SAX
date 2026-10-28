/* Catalog attributes: independent of taxonomy and of AI availability. */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('catalogProductAttributes') || document.getElementById('catalogAttributeAdmin');
    if (!root) return;
    const read = selector => JSON.parse(root.querySelector(selector)?.textContent || '{}');
    let dictionary = read('[data-attribute-dictionary]');
    const effective = read('[data-attribute-effective]');
    const old = read('[data-attribute-old]');
    const product = root.id === 'catalogProductAttributes';
    const form = document.getElementById('catalogAttributeDefinitionForm');
    const modalElement = document.getElementById('catalogAttributeModal');
    const rows = root.querySelector('[data-product-attribute-rows]');
    const chosen = new Map();
    let editing = null;
    let optionAttribute = null;
    let optionEditing = null;
    let managerMode = false;
    let returnToManager = false;
    let managerQuery = '';
    modalElement.addEventListener('hidden.bs.modal', () => { managerMode = false; returnToManager = false; });
    const search = root.querySelector(product ? '[data-attribute-selector-search]' : '[data-dictionary-search]');
    const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    const node = (tag, text, cls) => { const el = document.createElement(tag); if (text != null) el.textContent = text; if (cls) el.className = cls; return el; };
    const button = (text, action, cls = 'btn btn-sm btn-outline-primary') => { const b = node('button', text, cls); b.type = 'button'; b.addEventListener('click', action); return b; };
    async function request(url, data, method = 'POST') {
        const response = await fetch(url, {method, headers: {'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || ''}, body:JSON.stringify(data)});
        let result;
        try { result = await response.json(); } catch (_) { throw new Error('Respuesta inválida del servidor (HTTP ' + response.status + ').'); }
        if (!response.ok) throw new Error(Object.values(result.errors || {}).flat().join(' ') || result.message || 'No se pudo guardar.');
        return result;
    }
    function feedback(text, error = false) {
        const box = root.querySelector('[data-attribute-feedback]');
        if (box) { box.textContent = text; box.className = 'alert ' + (error ? 'alert-danger' : 'alert-info'); }
    }
    function modalError(text) { const box = form.querySelector('[data-attribute-modal-error]'); box.textContent = text; box.classList.toggle('d-none', !text); }
    function fillTranslations(translations = {}) {
        for (const [locale, column] of Object.entries({'pt-br':'pt',es:'es',en:'en'})) form.querySelector(`[data-lang-real="${locale}"]`).value = translations[column] || '';
        const field = form.querySelector('[data-lang-field]');
        field.querySelector('[data-lang-visual]').value = translations[{'pt-br':'pt',es:'es',en:'en'}[field.dataset.currentLang]] || '';
        field.querySelectorAll('[data-lang-state]').forEach(el => el.classList.toggle('is-complete', !!form.querySelector(`[data-lang-real="${el.dataset.langState}"]`).value.trim()));
    }
    function openDefinition(attribute = null, parent = null, option = null) {
        if (managerMode) returnToManager = true;
        managerMode = false;
        editing = attribute; optionAttribute = parent; optionEditing = option;
        form.reset(); modalError('');
        form.querySelector('[data-attribute-manager]').classList.add('d-none');
        form.querySelector('[data-attribute-manager-back]').classList.toggle('d-none', !returnToManager);
        const definition = form.querySelector('[data-attribute-definition-fields]');
        definition.hidden = false;
        definition.querySelectorAll('input, textarea, select, button').forEach(el => { el.disabled = false; });
        form.querySelector('[type="submit"]').hidden = false;
        form.querySelector('[data-attribute-save-hint]').hidden = false;
        form.querySelector('.modal-footer [data-bs-dismiss]').textContent = 'Cancelar';
        const isOption = !!parent;
        document.getElementById('catalogAttributeModalTitle').textContent = isOption ? (option ? 'Editar opción' : 'Crear opción') : (attribute ? 'Editar atributo' : 'Crear atributo');
        fillTranslations((isOption ? option : attribute)?.translations);
        form.querySelector('[name="is_active"][type="checkbox"]').checked = (isOption ? option : attribute)?.active ?? true;
        for (const name of ['data_type', 'unit', 'inherit_from_parent']) {
            form.querySelectorAll(`[name="${name}"]`).forEach(el => { el.disabled = isOption; el.closest('label')?.classList.toggle('d-none', isOption); });
        }
        form.querySelector('[name="data_type"]').value = attribute?.type || 'single';
        form.querySelector('[name="unit"]').value = attribute?.unit || '';
        form.querySelector('[name="inherit_from_parent"][type="checkbox"]').checked = attribute?.inherit || false;
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
    function openManager() {
        managerMode = true; returnToManager = true; modalError('');
        document.getElementById('catalogAttributeModalTitle').textContent = 'Diccionario de atributos';
        form.querySelector('[data-attribute-manager]').classList.remove('d-none');
        form.querySelector('[data-attribute-manager-back]').classList.add('d-none');
        const definition = form.querySelector('[data-attribute-definition-fields]');
        definition.hidden = true;
        definition.querySelectorAll('input, textarea, select, button').forEach(el => { el.disabled = true; });
        form.querySelector('[type="submit"]').hidden = true;
        form.querySelector('[data-attribute-save-hint]').hidden = true;
        form.querySelector('.modal-footer [data-bs-dismiss]').textContent = 'Cerrar';
        form.querySelector('[data-manager-search]').value = managerQuery;
        renderDictionaryCards(form.querySelector('[data-manager-list]'), managerQuery);
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
    root.querySelector('[data-create-attribute]').addEventListener('click', () => { returnToManager = false; openDefinition(); });
    root.querySelector('[data-manage-attributes]')?.addEventListener('click', () => { managerQuery = ''; openManager(); });
    form.querySelector('[data-attribute-manager-back]').addEventListener('click', openManager);
    form.querySelector('[data-manager-create]').addEventListener('click', () => openDefinition());
    form.querySelector('[data-manager-search]').addEventListener('input', event => {
        managerQuery = event.target.value;
        renderDictionaryCards(form.querySelector('[data-manager-list]'), managerQuery);
    });
    form.querySelector('[data-manager-search]').addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (managerMode) return;
        const submit = form.querySelector('[type="submit"]'); submit.disabled = true; modalError('');
        try {
            const payload = {translate:{}, is_active:form.querySelector('[name="is_active"][type="checkbox"]').checked ? 1 : 0};
            for (const locale of ['pt-br','es','en']) payload.translate[locale] = {name:form.querySelector(`[data-lang-real="${locale}"]`).value};
            let url = root.dataset.baseUrl, method = 'POST';
            if (optionAttribute) { url += '/' + optionAttribute.id + '/options'; if (optionEditing) { url += '/' + optionEditing.id; method = 'PUT'; } }
            else {
                if (editing) { url += '/' + editing.id; method = 'PUT'; }
                Object.assign(payload, {data_type:form.querySelector('[name="data_type"]').value, unit:form.querySelector('[name="unit"]').value || null, inherit_from_parent:form.querySelector('[name="inherit_from_parent"][type="checkbox"]').checked ? 1 : 0});
            }
            const data = await request(url, payload, method);
            dictionary = dictionary.filter(a => a.id !== data.item.id).concat(data.item);
            if (product) {
                if (!editing && !optionAttribute) addRow(data.item);
                if (editing && chosen.has(data.item.id)) renderRow(data.item, chosen.get(data.item.id));
                if (optionAttribute && chosen.has(data.item.id)) {
                    const state = chosen.get(data.item.id);
                    if (data.option_id && !optionEditing) {
                        state.options = data.item.type === 'single' ? [data.option_id] : [...new Set([...state.options, data.option_id])];
                        state.mode = 'value'; state.dirty = true;
                    }
                    renderRow(data.item, state);
                }
                feedback('Diccionario guardado. Guardá el producto para conservar sus asignaciones.');
            }
            renderDictionary();
            if (returnToManager) openManager();
            else bootstrap.Modal.getInstance(modalElement).hide();
        } catch (error) { modalError(error.message); } finally { submit.disabled = false; }
    });
    form.querySelector('[data-attribute-translate]').addEventListener('click', async event => {
        if (['es','pt-br','en'].every(l => form.querySelector(`[data-lang-real="${l}"]`).value.trim())) { modalError('Los tres idiomas ya están completos.'); return; }
        const name = ['es','pt-br','en'].map(l => form.querySelector(`[data-lang-real="${l}"]`).value).find(v => v.trim());
        if (!name) { modalError('Escribí primero un nombre.'); return; }
        event.target.disabled = true; modalError('');
        try {
            const data = await request(root.dataset.translateUrl, {name, context:optionAttribute?.name || 'Atributo de producto'});
            const translations = {};
            for (const [locale,column] of Object.entries({'pt-br':'pt',es:'es',en:'en'})) translations[column] = form.querySelector(`[data-lang-real="${locale}"]`).value || data[column];
            fillTranslations(translations);
        } catch (error) { modalError(error.message); } finally { event.target.disabled = false; }
    });
    function valueLabel(attribute, value) {
        if (attribute.type === 'boolean') return value.boolean == null || value.boolean === '' ? '' : (value.boolean ? 'Sí' : 'No');
        if (attribute.type === 'number') return value.number == null || value.number === '' ? '' : `${Number(value.number)}${attribute.unit ? ' ' + attribute.unit : ''}`;
        return (value.options || []).map(id => attribute.options.find(o => o.id === Number(id))?.name).filter(Boolean).join(', ');
    }
    function refreshSummary() {
        if (!product) return;
        let assigned = 0, exceptions = 0;
        const labels = [];
        chosen.forEach((state, id) => {
            const attribute = dictionary.find(a => a.id === id);
            if (!attribute) return;
            if (state.mode === 'block') { exceptions++; labels.push(`${attribute.name}: herencia bloqueada`); return; }
            const value = state.mode === 'inherit' ? (effective[id]?.inherited ? effective[id] : null) : state;
            const text = value ? valueLabel(attribute, value) : '';
            if (text) { assigned++; labels.push(`${attribute.name}: ${text}${state.mode === 'inherit' ? ' (heredado)' : ''}`); }
            else if (state.mode === 'inherit' && state.dirty) labels.push(`${attribute.name}: heredar del padre`);
        });
        root.querySelector('[data-attribute-count]').textContent = (assigned ? `${assigned} ${assigned === 1 ? 'asignado' : 'asignados'}` : 'Sin atributos asignados') + (exceptions ? ` · ${exceptions} ${exceptions === 1 ? 'excepción' : 'excepciones'}` : '');
        const preview = root.querySelector('[data-attribute-preview]');
        preview.textContent = labels.slice(0, 3).join(' · ') + (labels.length > 3 ? ` · y ${labels.length - 3} más` : '');
        preview.hidden = labels.length === 0;
    }
    function serialize(attribute, state, wrap) {
        const hidden = wrap.querySelector('[data-value-inputs]'); hidden.replaceChildren();
        const put = (key,value,array = false) => { const i = node('input'); i.type = 'hidden'; i.name = `catalog_values[${attribute.id}][${key}]` + (array ? '[]' : ''); i.value = value; hidden.append(i); };
        put('attribute_id',attribute.id); put('mode',state.mode);
        if (state.ai_proposal) put('ai_proposal',state.ai_proposal);
        if (state.mode === 'value') {
            if (attribute.type === 'boolean') put('boolean',state.boolean == null ? '' : Number(state.boolean));
            else if (attribute.type === 'number') put('number',state.number ?? '');
            else (state.options || []).forEach(id => put('options',id,true));
        }
        hidden.dispatchEvent(new Event('change', {bubbles:true})); state.dirty = true;
        refreshSummary();
    }
    function addRow(attribute, initial = null) {
        if (chosen.has(attribute.id)) return;
        const value = initial || effective[attribute.id];
        const state = value ? {...value, mode:value.inherited ? 'inherit' : value.mode, options:[...(value.options || [])], dirty:!!value.dirty} : {mode:'value',options:[],boolean:null,number:null,dirty:false};
        chosen.set(attribute.id,state); renderRow(attribute,state);
    }
    // Keep the native select as the value source; only its multiple-selection UI changes.
    function multipleValuePicker(attribute, input) {
        const picker = node('div', null, 'dropdown product-attributes__multiple');
        const toggle = button('', () => {}, 'btn btn-outline-secondary dropdown-toggle product-attributes__value-input');
        toggle.id = `catalogAttributeValue${attribute.id}`;
        toggle.dataset.bsToggle = 'dropdown'; toggle.dataset.bsAutoClose = 'outside';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', attribute.name);
        toggle.setAttribute('aria-describedby', `catalogAttributeNotice${attribute.id}`);
        const menu = node('div', null, 'dropdown-menu product-attributes__picker');
        menu.setAttribute('aria-labelledby', toggle.id);
        const searchLabel = node('label', 'Buscar opciones de ' + attribute.name, 'visually-hidden');
        const searchInput = node('input', null, 'form-control form-control-sm');
        searchInput.type = 'search'; searchInput.placeholder = 'Buscar opción…'; searchInput.autocomplete = 'off';
        searchInput.id = `catalogAttributeOptionSearch${attribute.id}`; searchLabel.htmlFor = searchInput.id;
        const list = node('div', null, 'product-attributes__choices');
        const empty = node('p', 'No hay coincidencias.', 'small text-muted p-2 mb-0'); empty.hidden = true;
        const choices = Array.from(input.options).map(option => {
            const label = node('label', null, 'product-attributes__option');
            const check = node('input', null, 'form-check-input'); check.type = 'checkbox'; check.checked = option.selected;
            check.addEventListener('change', () => {
                option.selected = check.checked;
                input.dispatchEvent(new Event('change', {bubbles:true}));
            });
            label.append(check, node('span', option.text)); list.append(label);
            return {label, check, option};
        });
        const filter = () => {
            let visible = 0;
            choices.forEach(({label, option}) => { label.hidden = !normalize(option.text).includes(normalize(searchInput.value)); if (!label.hidden) visible++; });
            empty.hidden = visible > 0;
        };
        searchInput.addEventListener('input', filter);
        // Enter inside a search must not submit the product form.
        searchInput.addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
        toggle.addEventListener('shown.bs.dropdown', () => { searchInput.value = ''; filter(); searchInput.focus(); });
        const done = button('Listo', () => { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); toggle.focus(); }, 'btn btn-sm btn-outline-secondary w-100 mt-2');
        menu.append(searchLabel, searchInput, list, empty, done); picker.append(toggle, menu);
        const refresh = () => {
            const text = Array.from(input.selectedOptions).map(option => option.text).join(', ');
            toggle.textContent = text || 'Seleccionar…'; toggle.title = text;
            toggle.classList.toggle('product-attributes__placeholder', !text);
            toggle.disabled = input.disabled; picker.hidden = input.hidden;
            choices.forEach(({check, option}) => { check.checked = option.selected; });
        };
        input.addEventListener('change', refresh);
        input.classList.remove('product-attributes__value-input'); input.classList.add('d-none');
        return {element:picker, refresh};
    }
    function renderRow(attribute,state) {
        const previous = rows.querySelector(`[data-attribute-row="${attribute.id}"]`);
        previous?.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => bootstrap.Dropdown.getInstance(el)?.dispose());
        const wrap = node('div',null,'product-attributes__row'); wrap.dataset.attributeRow = attribute.id;
        const heading = node('div',null,'product-attributes__row-heading');
        const name = node('label',attribute.name + (attribute.unit ? ' (' + attribute.unit + ')' : '') + (!attribute.active ? ' · desactivado' : ''),'product-attributes__name');
        name.htmlFor = `catalogAttributeValue${attribute.id}`;
        heading.append(name);
        const provenance = node('span', 'IA', 'product-attributes__ai');
        provenance.title = 'Origen: IA'; provenance.setAttribute('aria-label', 'Origen: IA');
        provenance.hidden = !(state.ai_proposal || state.source === 'ai'); heading.append(provenance);
        const mode = node('select'); mode.hidden = true; mode.setAttribute('aria-label','Origen de '+attribute.name);
        [['value','Valor propio'],['inherit','Heredar del padre'],['block','Bloquear herencia / sin valor']].forEach(([v,t])=>mode.add(new Option(t,v)));
        mode.value=state.mode;
        const actions = node('div',null,'dropdown product-attributes__actions');
        const actionToggle = button('⋯',()=>{},'btn product-attributes__action-toggle');
        actionToggle.id = `catalogAttributeActions${attribute.id}`;
        actionToggle.dataset.bsToggle = 'dropdown'; actionToggle.setAttribute('aria-expanded','false');
        actionToggle.setAttribute('aria-label','Acciones de '+attribute.name);
        const actionMenu = node('div',null,'dropdown-menu dropdown-menu-end product-attributes__action-menu');
        actionMenu.setAttribute('aria-labelledby', actionToggle.id);
        const editTitle = node('div','Diccionario','dropdown-header');
        const editAttribute = button('Editar atributo',()=>{ returnToManager = false; openDefinition(attribute); },'dropdown-item');
        actionMenu.append(editTitle, editAttribute);
        if (['single','multiple'].includes(attribute.type) && attribute.active) actionMenu.append(button('Crear opción',()=>{ returnToManager = false; openDefinition(null,attribute); },'dropdown-item'));
        const optionActions = node('div'); actionMenu.append(optionActions);
        actionMenu.append(node('hr',null,'dropdown-divider'),node('div','Valor del producto','dropdown-header'));
        const modeActions = [];
        [['value','Usar valor propio'],['inherit','Heredar del padre'],['block','Bloquear herencia / sin valor']].forEach(([value,label])=>{
            const action = button(label,()=>{ mode.value=value; mode.dispatchEvent(new Event('change')); },'dropdown-item');
            modeActions.push({value, action}); actionMenu.append(action);
        });
        actions.append(actionToggle,actionMenu); heading.append(actions);
        const controls=node('div',null,'product-attributes__fields');
        const input=node(attribute.type==='number'?'input':'select',null,(attribute.type==='number'?'form-control':'form-select')+' form-control-sm product-attributes__value-input');
        input.id = `catalogAttributeValue${attribute.id}`; input.setAttribute('aria-label',attribute.name);
        if (attribute.type==='number') {input.type='number';input.step='any';input.value=state.number??'';}
        else if(attribute.type==='boolean'){input.add(new Option('Seleccionar…',''));input.add(new Option('Sí','1'));input.add(new Option('No','0'));input.value=state.boolean==null?'':String(Number(state.boolean));}
        else {input.multiple=attribute.type==='multiple';if(!input.multiple)input.add(new Option('Seleccionar…',''));attribute.options.filter(o=>o.active||state.options.includes(o.id)).forEach(o=>{const option=new Option(o.name+(o.active?'':' · desactivada'),o.id);option.selected=state.options.includes(o.id);input.add(option);});}
        const hidden=node('div');hidden.dataset.valueInputs='';
        const inherited=effective[attribute.id];
        const notice = node('p', null, 'product-attributes__notice');
        notice.id = `catalogAttributeNotice${attribute.id}`; input.setAttribute('aria-describedby',notice.id);
        let multiple;
        if (input.multiple) {
            input.id += 'Source';
            multiple = multipleValuePicker(attribute,input); controls.append(multiple.element);
        }
        controls.append(input,notice);
        wrap.append(heading,controls,mode,hidden);
        if (previous) previous.replaceWith(wrap); else rows.append(wrap);
        const renderOptions = () => {
            optionActions.replaceChildren();
            if (!['single','multiple'].includes(attribute.type)) return;
            Array.from(input.selectedOptions).filter(o=>o.value).forEach(o=>{
                const option = attribute.options.find(item=>item.id===Number(o.value));
                if (option) optionActions.append(button('Editar opción: '+option.name,()=>{ returnToManager = false; openDefinition(null,attribute,option); },'dropdown-item'));
            });
        };
        renderOptions(); input.addEventListener('change',renderOptions);
        const present = () => {
            input.disabled = state.mode !== 'value';
            input.hidden = state.mode === 'block' || (state.mode === 'inherit' && !inherited?.inherited);
            notice.hidden = state.mode === 'value';
            notice.textContent = state.mode === 'block' ? 'Herencia bloqueada · sin valor' : inherited?.inherited ? `Heredado del producto #${inherited.from_product_id}` : 'El valor del padre se resolverá al guardar.';
            optionActions.hidden = state.mode !== 'value';
            modeActions.forEach(({value, action}) => { action.classList.toggle('active',state.mode===value); action.setAttribute('aria-current',state.mode===value?'true':'false'); });
            multiple?.refresh();
        };
        const update=()=>{delete state.ai_proposal;state.source='manual';provenance.hidden=true;state.mode=mode.value;if(attribute.type==='number')state.number=input.value;else if(attribute.type==='boolean')state.boolean=input.value===''?null:input.value==='1';else state.options=Array.from(input.selectedOptions).map(o=>Number(o.value)).filter(Boolean);present();serialize(attribute,state,wrap);};
        present(); input.addEventListener('change',update);mode.addEventListener('change',update);
        if(state.dirty)serialize(attribute,state,wrap);
        else refreshSummary();
    }
    function showAttributeReview(result) {
        const review = result.attribute_review;
        const items = Array.isArray(review?.items) ? review.items : [];
        let accepted = 0;
        const rejected = [];
        items.forEach(item => {
            const attribute = dictionary.find(a => a.active && a.code === item.code);
            let reason = item.status === 'accepted' ? null : (item.reason || 'La propuesta no superó la validación.');
            if (!reason && (!attribute || !item.value?.ai_proposal)) reason = 'El atributo o su propuesta validada ya no están disponibles.';
            if (!reason && (effective[attribute.id] || read('[data-attribute-protected]').includes(attribute.id))) reason = 'Existe un valor propio, heredado o una decisión protegida.';
            if (!reason && chosen.get(attribute.id)?.dirty) reason = 'El atributo tiene cambios pendientes en el formulario.';
            if (reason) { rejected.push((attribute?.name || item.code || 'Atributo') + ': ' + reason); return; }
            chosen.delete(attribute.id);
            addRow(attribute, {...item.value, dirty:true});
            accepted++;
        });
        const suggested = items.length;
        feedback(`Atributos: ${suggested} sugeridos, ${accepted} aceptados, ${rejected.length} rechazados. ` + (accepted ? 'Revisá los valores antes de guardar.' : 'No se agregaron valores.'));
        const box = root.querySelector('[data-attribute-feedback]');
        if (rejected.length) {
            const list = node('ul', null, 'mb-0 mt-2');
            rejected.forEach(reason => list.append(node('li', reason)));
            box.append(list);
        }
        if (!suggested) box.append(node('p', result.message || 'No se encontraron nuevos valores respaldados para los atributos disponibles.', 'mb-0 mt-2'));
        root.open = true;
        root.dispatchEvent(new Event('sax:attribute-review', {bubbles:true}));
    }

    function matchingAttributes(query) {
        return dictionary.filter(a => normalize([a.code,...Object.values(a.translations)].join(' ')).includes(normalize(query)))
            .sort((a,b) => a.name.localeCompare(b.name, undefined, {sensitivity:'base'}));
    }
    function renderDictionaryCards(list, query) {
        list.replaceChildren();
        const matches = matchingAttributes(query);
        if (!matches.length) list.append(node('p','No hay coincidencias.','small text-muted'));
        matches.forEach(a=>{const card=node('div',null,'border rounded p-3 mb-3');card.append(node('strong',a.name+(a.active?'':' · desactivado')),node('p',a.code+' · '+a.type+(a.unit?' · '+a.unit:''),'small text-muted'),button('Editar atributo',()=>openDefinition(a),'btn btn-sm btn-outline-secondary'));
            if(['single','multiple'].includes(a.type))card.append(button('Crear opción',()=>openDefinition(null,a),'btn btn-sm btn-outline-secondary ms-2'));
            const options=node('div',null,'d-flex flex-wrap gap-2 mt-2');a.options.forEach(o=>options.append(button(o.name+(o.active?'':' · desactivada'),()=>openDefinition(null,a,o),'btn btn-sm btn-light')));card.append(options);list.append(card);});
    }
    function renderDictionary() {
        if (!product) { renderDictionaryCards(root.querySelector('[data-dictionary-list]'), search.value); return; }
        const matches = matchingAttributes(search.value).filter(a => a.active && !chosen.has(a.id));
        const group = (selector, title, items) => {
            const target = root.querySelector(selector); target.replaceChildren(); target.hidden = !items.length;
            if (!items.length) return;
            target.append(node('div', title, 'dropdown-header'));
            items.forEach(attribute => target.append(button(attribute.name, () => {
                addRow(attribute); renderDictionary();
                bootstrap.Dropdown.getOrCreateInstance(root.querySelector('#catalogAttributePicker')).hide();
                rows.querySelector(`[data-attribute-row="${attribute.id}"] .product-attributes__value-input`)?.focus();
            }, 'dropdown-item')));
        };
        group('[data-attribute-picker-items]', 'Diccionario de atributos', matches);
        root.querySelector('[data-attribute-picker-empty]').hidden = matches.length > 0;
        refreshSummary();
    }
    search.addEventListener('input',renderDictionary);
    if(product){
        root.querySelector('#catalogAttributePicker').addEventListener('shown.bs.dropdown', () => search.focus());
        root.querySelector('[data-attribute-picker]').addEventListener('keydown', event => {
            if (!['ArrowDown','ArrowUp'].includes(event.key)) return;
            const choices = Array.from(root.querySelectorAll('[data-attribute-picker] .dropdown-item'));
            if (!choices.length) return;
            event.preventDefault(); event.stopPropagation();
            const current = choices.indexOf(document.activeElement);
            const next = current < 0 ? (event.key === 'ArrowDown' ? 0 : choices.length - 1) : (current + (event.key === 'ArrowDown' ? 1 : -1) + choices.length) % choices.length;
            choices[next].focus();
        });
        Object.keys(effective).forEach(id=>{const a=dictionary.find(a=>a.id===Number(id));if(a)addRow(a);});
        Object.values(old).forEach(row=>{const a=dictionary.find(a=>a.id===Number(row.attribute_id));if(!a)return;chosen.delete(a.id);addRow(a,{...row,boolean:row.boolean==null?null:String(row.boolean)==='1',options:(row.options||[]).map(Number),dirty:true});const state=chosen.get(a.id);state.dirty=true;state.ai_proposal=row.ai_proposal;renderRow(a,state);});
        root.querySelector('[data-complete-attributes]').addEventListener('click',async event=>{
            const aiButton = event.currentTarget;
            const requestedContext = window.saxProductAiContext?.() || {};
            aiButton.disabled=true;feedback('Buscando información respaldada…');
            try {
                const result=await request(root.dataset.aiUrl,{catalog_context:requestedContext});
                if (JSON.stringify(requestedContext) !== JSON.stringify(window.saxProductAiContext?.() || {})) throw new Error('Cambiaste los datos investigados durante la consulta. Volvé a generar con los datos actuales.');
                window.dispatchEvent(new CustomEvent('sax:catalog-attribute-proposal',{detail:result}));
            }
            catch(error){feedback(error.message,true);}finally{aiButton.disabled=false;}
        });
        window.addEventListener('sax:catalog-attribute-proposal', event => {
            showAttributeReview(event.detail);
        });
    }
    renderDictionary();
});
