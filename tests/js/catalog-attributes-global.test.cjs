const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/js/catalog-attributes-admin.js', 'utf8');

function picker(query = '') {
    const items = {children: [], replaceChildren() { this.children = []; }, append(...children) { this.children.push(...children); }};
    const empty = {};
    const added = [];
    const context = {
        product: true, search: {value: query}, chosen: new Map([[3, {}]]),
        dictionary: [
            {id: 1, code: 'puente', name: 'Puente', active: true, translations: {es: 'Puente', pt: 'Ponte', en: 'Bridge'}},
            {id: 2, code: 'material', name: 'Material', active: true, translations: {es: 'Material'}},
            {id: 3, code: 'color', name: 'Color', active: true, translations: {es: 'Color'}},
            {id: 4, code: 'inactive', name: 'Inactivo', active: false, translations: {es: 'Inactivo'}},
        ],
        root: {querySelector(selector) {
            if (selector === '[data-attribute-picker-items]') return items;
            if (selector === '[data-attribute-picker-empty]') return empty;
            if (selector === '#catalogAttributePicker') return {};
            throw new Error('Unexpected DOM dependency: ' + selector);
        }},
        rows: {querySelector() { return null; }},
        node: (tag, text) => ({tag, text}), button: (text, action) => ({text, action}),
        normalize: value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim(),
        refreshSummary() {}, addRow(attribute) { added.push(attribute.id); context.chosen.set(attribute.id, {}); },
        bootstrap: {Dropdown: {getOrCreateInstance() { return {hide() {}}; }}},
    };
    vm.createContext(context);
    vm.runInContext(source.slice(source.indexOf('    function matchingAttributes('), source.indexOf("    search.addEventListener('input',renderDictionary);")) + '\nrenderDictionary();', context);
    return {context, items, empty, added};
}

test('global dropdown lists every active unselected attribute alphabetically without category DOM', () => {
    const {items, added} = picker();
    assert.deepEqual(items.children.map(c => c.text), ['Diccionario de atributos', 'Material', 'Puente']);
    items.children[1].action();
    assert.deepEqual(added, [2]);
    assert.deepEqual(items.children.map(c => c.text), ['Diccionario de atributos', 'Puente']);
});

test('dropdown searches translated labels and shows an empty state', () => {
    assert.deepEqual(picker('BRIDGE').items.children.map(c => c.text), ['Diccionario de atributos', 'Puente']);
    assert.equal(picker('unknown').empty.hidden, false);
});


function reviewFixture(items, protectedIds = [], dirtyIds = []) {
    const box = {textContent:'', children:[], append(child) {this.children.push(child);}};
    const added = [];
    const context = {
        dictionary:[{id:1,code:'polarizado',name:'Polarizado',active:true}],
        effective:{}, chosen:new Map(dirtyIds.map(id => [id,{dirty:true}])),
        read() {return protectedIds;},
        root:{open:false, querySelector() {return box;}, dispatchEvent() {}},
        feedback(text) {box.textContent=text;},
        node(tag,text) {return {tag,text,children:[],append(child) {this.children.push(child);}};},
        addRow(a,state) {added.push(state);context.chosen.set(a.id,state);}, Event:class {},
    };
    vm.createContext(context);
    vm.runInContext(source.slice(source.indexOf('    function showAttributeReview('), source.indexOf('    function matchingAttributes(')), context);
    context.showAttributeReview({attribute_review:{items}});
    return {context,box,added};
}
const accepted = {code:'polarizado',status:'accepted',value:{attribute_id:1,mode:'value',boolean:true,options:[],ai_proposal:'server-signed'}};

test('review opens the section, counts accepted/rejected values and shows reasons', () => {
    const {context,box,added} = reviewFixture([accepted,{code:'unknown',status:'rejected',reason:'Falta evidencia textual.'}]);
    assert.equal(context.root.open,true);
    assert.match(box.textContent,/2 sugeridos, 1 aceptados, 1 rechazados/);
    assert.match(box.children[0].children[0].text,/Falta evidencia/);
    assert.equal(added[0].ai_proposal,'server-signed');
    assert.equal(added[0].dirty,true);
});

test('review never overwrites protected or locally edited attributes', () => {
    for (const [protectedIds,dirtyIds] of [[[1],[]],[[],[1]]]) {
        const {box,added}=reviewFixture([accepted],protectedIds,dirtyIds);
        assert.equal(added.length,0);
        assert.match(box.textContent,/1 sugeridos, 0 aceptados, 1 rechazados/);
        assert.match(box.children[0].children[0].text,/protegida|cambios pendientes/);
    }
});

test('empty review explicitly reports zero attributes without inventing values', () => {
    const {box,added}=reviewFixture([]);
    assert.match(box.textContent,/0 sugeridos, 0 aceptados, 0 rechazados/);
    assert.equal(added.length,0);
    assert.match(box.children[0].text,/No se encontraron/);
});

test('unsigned proposals cannot be accepted by the editor', () => {
    const {box,added}=reviewFixture([{...accepted,value:{attribute_id:1,mode:'value',boolean:true}}]);
    assert.equal(added.length,0);
    assert.match(box.textContent,/0 aceptados, 1 rechazados/);
});
