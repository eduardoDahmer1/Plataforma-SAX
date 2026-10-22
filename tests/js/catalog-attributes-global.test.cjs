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
