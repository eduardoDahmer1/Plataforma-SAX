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
        const state = value ? {...value, mode:value.inherited ? 'inherit' : value.mode, options:[...(value.options || [])], dirty:false} : {mode:'value',options:[],boolean:null,number:null,dirty:false};
        chosen.set(attribute.id,state); renderRow(attribute,state);
    }
    function renderRow(attribute,state) {
        rows.querySelector(`[data-attribute-row="${attribute.id}"]`)?.remove();
        const wrap = node('div',null,'product-attributes__row'); wrap.dataset.attributeRow = attribute.id;
        const heading = node('div',null,'d-flex flex-wrap justify-content-between align-items-center gap-2');
        heading.append(node('strong',attribute.name + (attribute.unit ? ' (' + attribute.unit + ')' : '') + (!attribute.active ? ' · desactivado' : '')));
        const actions = node('div',null,'d-flex flex-wrap gap-2');
        actions.append(button('Editar atributo',()=>{ returnToManager = false; openDefinition(attribute); },'btn btn-sm btn-link p-0'));
        if (['single','multiple'].includes(attribute.type) && attribute.active) actions.append(button('Crear opción',()=>{ returnToManager = false; openDefinition(null,attribute); },'btn btn-sm btn-link p-0'));
        heading.append(actions);
        const mode = node('select',null,'form-select form-select-sm'); mode.setAttribute('aria-label','Origen de '+attribute.name);
        [['value','Valor propio'],['inherit','Heredar del padre'],['block','Bloquear herencia / sin valor']].forEach(([v,t])=>mode.add(new Option(t,v)));
        mode.value=state.mode;
        const controls=node('div'); const input=node(attribute.type==='number'?'input':'select',null,'form-control form-control-sm product-attributes__value-input'); input.setAttribute('aria-label',attribute.name);
        if (attribute.type==='number') {input.type='number';input.step='any';input.value=state.number??'';}
        else if(attribute.type==='boolean'){input.add(new Option('Seleccionar…',''));input.add(new Option('Sí','1'));input.add(new Option('No','0'));input.value=state.boolean==null?'':String(Number(state.boolean));}
        else {input.multiple=attribute.type==='multiple';if(!input.multiple)input.add(new Option('Seleccionar…',''));attribute.options.filter(o=>o.active||state.options.includes(o.id)).forEach(o=>{const option=new Option(o.name+(o.active?'':' · desactivada'),o.id);option.selected=state.options.includes(o.id);input.add(option);});}
        const hidden=node('div');hidden.dataset.valueInputs='';
        const inherited=effective[attribute.id];
        const notice = node('p', null, 'product-attributes__notice');
        controls.append(notice, input);
        const fields = node('div',null,'product-attributes__fields'); fields.append(mode,controls);
        wrap.append(heading,fields,hidden);rows.append(wrap);
        let optionActions;
        if (['single','multiple'].includes(attribute.type)) {
            optionActions = node('div',null,'d-flex flex-wrap gap-2 mt-2');
            const renderOptions = () => {
                optionActions.replaceChildren();
                Array.from(input.selectedOptions).filter(o=>o.value).forEach(o=>{
                    const option = attribute.options.find(item=>item.id===Number(o.value));
                    if (option) optionActions.append(button('Editar '+option.name,()=>{ returnToManager = false; openDefinition(null,attribute,option); },'btn btn-sm btn-link p-0'));
                });
            };
            controls.append(optionActions); renderOptions(); input.addEventListener('change',renderOptions);
        }
        const present = () => {
            input.disabled = state.mode !== 'value';
            input.hidden = state.mode === 'block' || (state.mode === 'inherit' && !inherited?.inherited);
            notice.hidden = state.mode === 'value';
            notice.textContent = state.mode === 'block' ? 'No se heredará ni se asignará un valor.' : inherited?.inherited ? `Heredado del producto #${inherited.from_product_id}` : 'El valor del padre se resolverá al guardar.';
            if (optionActions) optionActions.hidden = state.mode !== 'value';
        };
        const update=()=>{state.mode=mode.value;if(attribute.type==='number')state.number=input.value;else if(attribute.type==='boolean')state.boolean=input.value===''?null:input.value==='1';else state.options=Array.from(input.selectedOptions).map(o=>Number(o.value)).filter(Boolean);present();serialize(attribute,state,wrap);};
        present(); input.addEventListener('change',update);mode.addEventListener('change',update);
        if(state.dirty)serialize(attribute,state,wrap);
        else refreshSummary();
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
        Object.values(old).forEach(row=>{const a=dictionary.find(a=>a.id===Number(row.attribute_id));if(!a)return;chosen.delete(a.id);addRow(a,{...row,boolean:row.boolean==null?null:String(row.boolean)==='1',options:(row.options||[]).map(Number),dirty:true});const state=chosen.get(a.id);state.dirty=true;renderRow(a,state);});
        root.querySelector('[data-complete-attributes]').addEventListener('click',async event=>{
            const aiButton = event.currentTarget;
            aiButton.disabled=true;feedback('Buscando información respaldada…');
            try{const result=await request(root.dataset.aiUrl,{});window.dispatchEvent(new CustomEvent('sax:catalog-attribute-proposal',{detail:result}));feedback(result.message||'Investigación finalizada. Los valores respaldados se guardarán con el producto.');}
            catch(error){feedback(error.message,true);}finally{aiButton.disabled=false;}
        });
        window.addEventListener('sax:catalog-attribute-proposal',event=>{
            const result=event.detail;if(result.research?.status!=='matched')return;
            const urls=(result.research.sources||[]).map(s=>s.url);
            (result.proposal?.catalog_attributes||[]).forEach(v=>{
                const a=dictionary.find(a=>a.active&&a.code===v.code);if(!a||effective[a.id]||read('[data-attribute-protected]').includes(a.id)||chosen.get(a.id)?.dirty||v.confidence!=='high'||!v.evidence||!urls.includes(v.source_url)||v.product_sku!==root.dataset.productSku||v.variant_match!==true)return;
                const opts=(v.option_codes||[]).map(code=>a.options.find(o=>o.active&&o.code===code)?.id);if(opts.some(id=>!id))return;
                chosen.delete(a.id);addRow(a,{mode:'value',number:v.number_value,boolean:v.boolean_value,options:opts});const state=chosen.get(a.id);state.dirty=true;renderRow(a,state);
            });
        });
    }
    renderDictionary();
});
