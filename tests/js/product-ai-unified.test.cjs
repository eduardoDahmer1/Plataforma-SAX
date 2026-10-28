const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/js/products-admin.js', 'utf8');
const admin = fs.readFileSync('public/js/admin.js', 'utf8');

function form(proposal, category = '140', brand = 'MARCA', responseOk = true) {
    const elements = {};
    const element = id => elements[id] ||= {
        value: 'original', innerHTML: '', className: '', classList: {add() {}, remove() {}},
        options: [], dispatchEvent() {}, replaceChildren() {}, setAttribute() {},
        closest() { return null; }, addEventListener(type, fn) { this[type] = fn; }
    };
    ['pt', 'es', 'en'].forEach(lang => {element('real-name-' + lang); element('real-desc-' + lang);});
    element('category_id').value = category;
    element('category_id').options = [{value: category}];
    element('brand_id').value = '1';
    element('brand_id').selectedOptions = [{textContent: brand}];
    const button = element('button'); button.dataset = {aiUrl: '/complete'};
    const calls = [], events = [];
    const context = {
        console, Event: class {}, CustomEvent: class {constructor(type, args) {this.type = type; this.detail = args.detail;}},
        currentProposal: null, aiButton: button, aiFeedback: element('feedback'), aiResearch: null,
        aiGeneratedAt: element('generated'), taxonomyAssessment: null, suggestions: null,
        document: {getElementById: element, querySelector: () => null, createElement: () => ({
            set textContent(value) {this.innerHTML = String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');}
        })},
        window: {currentLangs: {name: 'es', desc: 'en'}, dispatchEvent(event) {events.push(event);}},
        showTaxonomyMessage() {}, renderResearch() {}, renderTaxonomySuggestions() {},
        async fetch(url, options) {calls.push({url, options}); return {ok: responseOk, json: async () => ({proposal, research: {status: 'matched'}, preparation: {status: 'generated', generated_at: '2026-10-23'}})};}
    };
    vm.createContext(context);
    vm.runInContext(admin.slice(admin.indexOf('function normalizeOpticalProductName('), admin.indexOf('async function translateProductField')), context);
    context.window.normalizeOpticalProductName = context.normalizeOpticalProductName;
    vm.runInContext(source.slice(source.indexOf('    function escapeHtml('), source.indexOf("    const suggestions =")), context);
    const start = source.indexOf("    aiButton.addEventListener('click'");
    const end = source.indexOf('\n});', start);
    vm.runInContext(source.slice(start, end), context);
    return {elements, button, calls, events, context};
}

const cases = [
    ['Óptica', '149', 'RAY BAN', ['RAY BAN RAY BAN RB2140 #50 *901', 'RAY BAN RB2140', 'RAY BAN RB2140'], 'RAY BAN RB2140'],
    ['ropa', '140', 'MARCA', ['MARCA CAMISETA REF-123', 'MARCA CAMISETA REF-123', 'MARCA T-SHIRT REF-123']],
    ['calzado', '140', 'MARCA', ['MARCA TÊNIS JONAH', 'MARCA ZAPATILLAS JONAH', 'MARCA SNEAKERS JONAH']],
    ['perfumería', '144', 'MARCA', ['MARCA AMBREE EAU DE PARFUM', 'MARCA AMBREE EAU DE PARFUM', 'MARCA AMBREE EAU DE PARFUM']],
    ['Óptica sin modelo', '149', 'MARCA', ['MARCA ÓCULOS DE SOL', 'MARCA GAFAS DE SOL', 'MARCA SUNGLASSES']],
];
for (const [label, category, brand, names, opticalName] of cases) {
    test(`${label}: one request fills all locales and active editor without saving`, async () => {
        const proposal = {commercial_name: Object.fromEntries(['pt_br', 'es', 'en'].map((key, index) => [key, names[index]])),
            descriptions: {pt_br: 'Descrição factual.', es: 'Descripción factual.', en: 'Factual description.\n\nSecond paragraph.'},
            taxonomy_selection: {category_id: Number(category), subcategory_id: null}, catalog_attributes: []};
        const {elements, button, calls, events} = form(proposal, category, brand);
        await button.click();
        assert.equal(calls.length, 1);
        assert.equal(calls[0].url, '/complete');
        assert.equal(calls[0].options.method, 'POST');
        ['pt', 'es', 'en'].forEach((lang, index) => {
            assert.equal(elements['real-name-' + lang].value, index === 0 && opticalName ? opticalName : names[index]);
            assert.match(elements['real-desc-' + lang].value, /^<p>/);
        });
        assert.equal(elements['visual-name-input'].value, names[1]);
        assert.equal(elements['editor-product'].value, '<p>Factual description.</p><p>Second paragraph.</p>');
        assert.equal(events[0].type, 'sax:catalog-attribute-proposal');
        assert.equal(button.disabled, false);
        assert.match(elements.feedback.textContent, /antes de guardar/);
    });
}

test('missing locale leaves existing names, descriptions and attributes untouched', async () => {
    const {button, elements, events} = form({commercial_name: {pt_br: 'A', es: 'A', en: 'A'}, descriptions: {pt_br: 'A', es: 'A'}});
    await button.click();
    assert.equal(elements['real-name-pt'].value, 'original');
    assert.equal(elements['real-desc-es'].value, 'original');
    assert.equal(events.length, 0);
    assert.match(elements.feedback.textContent, /tres idiomas/);
    assert.equal(button.disabled, false);
});

test('failed request does not change form values', async () => {
    const {button, elements, events} = form({}, '149', 'MARCA', false);
    await button.click();
    assert.equal(elements['real-name-pt'].value, 'original');
    assert.equal(events.length, 0);
    assert.equal(button.disabled, false);
});

test('description text is escaped before reaching the editor', async () => {
    const {button, elements} = form({commercial_name: {pt_br: 'A', es: 'A', en: 'A'}, descriptions: {pt_br: '<script>alert(1)</script>', es: 'A', en: 'A'}});
    await button.click();
    assert.equal(elements['real-desc-pt'].value, '<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>');
});


test('unsaved changes do not block research and current editor values are sent', async () => {
    const proposal = {commercial_name:{pt_br:'A',es:'A',en:'A'},descriptions:{pt_br:'A',es:'A',en:'A'}};
    const {button, calls, events, elements, context} = form(proposal);
    context.window.saxProductHasUnsavedChanges = () => true;
    context.window.saxProductAiContext = () => ({brand_id:'2',size:'L',description:'Texto todavía sin guardar'});
    await button.click();
    assert.equal(calls.length, 1);
    assert.equal(events.length, 1);
    assert.deepEqual(JSON.parse(calls[0].options.body).catalog_context,{brand_id:'2',size:'L',description:'Texto todavía sin guardar'});
    assert.equal(elements['real-name-pt'].value,'A');
});

test('changes during research discard the response without overwriting the form', async () => {
    const proposal = {commercial_name: {pt_br:'A',es:'A',en:'A'}, descriptions:{pt_br:'A',es:'A',en:'A'}};
    const {button, calls, events, elements, context} = form(proposal);
    let snapshot = 0;
    context.window.saxProductAiContext = () => ({size:String(++snapshot)});
    await button.click();
    assert.equal(calls.length, 1);
    assert.equal(events.length, 0);
    assert.equal(elements['real-name-pt'].value, 'original');
    assert.match(elements.feedback.textContent, /Cambiaste los datos investigados/);
    assert.equal(button.disabled, false);
});
