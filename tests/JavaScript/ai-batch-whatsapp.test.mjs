import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const source = readFileSync(new URL('../../public/js/ai-batch-whatsapp.js', import.meta.url), 'utf8');

// Exercise the actual event handlers without a browser or additional dependencies.
const guideContacts = [
    { id: 'dept1', building: 'department', label: 'Bolsas · SAX Department Store · Piso 1', phone: '+595 981 123456' },
    { id: 'dubai1', building: 'dubai', label: 'Lentes · Shopping Dubai · PB', phone: '+595 982 123456' },
    { id: 'guembe1', building: 'distrito', label: 'Lentes · Guembe · Piso 1', phone: '+595 983 123456' },
    { id: 'casona1', building: 'distrito', label: 'Lentes · Casona · Piso 1', phone: '+595 984 123456' },
    { id: 'casona2', building: 'distrito', label: 'Bolsas · Casona · PB', phone: '' },
];
function consultation(families, contacts = guideContacts) {
    const elements = new Map();
    const opened = [];
    const copied = [];
    const confirmations = [];
    const decisions = [];
    class Element {
        constructor() {
            this.value = '';
            this.dataset = {};
            this.children = [];
            this.listeners = {};
            this.style = { removeProperty() {}, setProperty() {} };
            this.classList = { add() {}, remove() {} };
            this.hidden = false;
        }
        addEventListener(event, handler) { (this.listeners[event] ||= []).push(handler); }
        async trigger(event) { for (const handler of this.listeners[event] || []) await handler.call(this); }
        appendChild(child) {
            this.children.push(child);
            if (!this.value) this.value = String(child.value);
        }
        replaceChildren() { this.children = []; this.value = ''; }
        focus() {}
        select() {}
    }
    const get = id => {
        if (!elements.has(id)) elements.set(id, new Element());
        return elements.get(id);
    };
    const items = families.map(variants => {
        const element = new Element();
        element.checked = true;
        element.dataset.variants = JSON.stringify(variants);
        return element;
    });
    get('waConsultation').querySelectorAll = selector => selector === '.wa-item' ? items : [];
    const reasons = ['photos', 'confirm', 'dimensions', 'weight'].map(value => {
        const input = new Element();
        input.value = value;
        input.checked = value === 'photos' || value === 'confirm';
        return input;
    });
    get('waReasons').querySelectorAll = () => reasons;
    const buildings = ['department', 'dubai', 'distrito'].map(value => {
        const input = new Element();
        input.value = value;
        input.checked = value === 'department';
        return input;
    });
    get('waBuildings').querySelectorAll = () => buildings;
    get('waContacts').textContent = JSON.stringify(contacts);
    vm.runInNewContext(source, {
        document: {
            addEventListener(event, handler) { if (event === 'DOMContentLoaded') handler(); },
            getElementById: get,
            createElement: () => new Element(),
        },
        bootstrap: { Modal: class { show() {} } },
        navigator: { clipboard: { async writeText(value) { copied.push(value); } } },
        window: { isSecureContext: true, open(...args) { opened.push(args); }, confirm(text) { confirmations.push(text); return decisions.shift() ?? true; } },
    });
    return { get, items, opened, copied, reasons, confirmations, decisions, buildings };
}

const reasonKeys = ['photos', 'confirm', 'dimensions', 'weight'];
const combinations = Array.from({ length: 15 }, (_, index) => reasonKeys.filter((key, bit) => (index + 1) & (1 << bit)));
async function setReasons(app, keys) {
    app.reasons.forEach(input => input.checked = keys.includes(input.value));
    await app.reasons[0].trigger('change');
}

const product = (id, length = 20) => ({ sku: 'SKU-' + id, name: ('Producto ' + id + ' ').padEnd(length, 'ó') });
const codes = messages => messages.flatMap(message => Array.from(message.matchAll(/Código: ([^\n]+)/g), match => match[1]));

async function messages(app) {
    const values = [];
    for (const option of app.get('waGroup').children) {
        app.get('waGroup').value = String(option.value);
        await app.get('waGroup').trigger('change');
        values.push(app.get('waMessage').value);
    }
    return values;
}

for (const count of [1, 10, 20, 50, 100]) {
    for (const length of [20, 260]) {
        test(`${count} products, name length ${length}: complete, unique and within 2,000`, async () => {
            const products = Array.from({ length: count }, (_, index) => product(index, length));
            const app = consultation(products.map(item => [item]));
            await app.get('waContinue').trigger('click');
            for (const reasons of combinations) {
                await setReasons(app, reasons);
                const generated = await messages(app);
                assert.deepEqual(codes(generated), products.map(item => item.sku));
                assert.ok(generated.every(text => text.length <= 2000));
                assert.ok(generated.every(text => text.endsWith('\n\nGracias!')));
            }
        });
    }
}

test('more than ten short products share a message and Unicode is counted before encoding', async () => {
    const products = Array.from({ length: 20 }, (_, index) => product(index));
    const app = consultation(products.map(item => [item]));
    await app.get('waContinue').trigger('click');
    const generated = await messages(app);
    assert.equal(generated.length, 1);
    assert.deepEqual(codes(generated), products.map(item => item.sku));
    assert.ok(generated[0].length < 2000);
    assert.ok(encodeURIComponent(generated[0]).length > 2000);
});

test('a fitting family moves intact to the next message', async () => {
    const first = [product('A', 1250)];
    const family = [product('B1', 350), product('B2', 350)];
    const app = consultation([first, family]);
    await app.get('waContinue').trigger('click');
    const generated = await messages(app);
    assert.equal(generated.length, 2);
    assert.deepEqual(codes([generated[0]]), ['SKU-A']);
    assert.deepEqual(codes([generated[1]]), ['SKU-B1', 'SKU-B2']);
});

test('an oversized family splits in order, preserving each whole variant once', async () => {
    const family = Array.from({ length: 50 }, (_, index) => product(index, 220));
    const app = consultation([family]);
    await app.get('waContinue').trigger('click');
    const generated = await messages(app);
    assert.ok(generated.length > 1);
    assert.ok(generated.every(text => text.length <= 2000));
    assert.deepEqual(codes(generated), family.map(item => item.sku));
    for (const variant of family) assert.equal(generated.filter(text => text.includes(variant.name)).length, 1);
});

test('duplicate codes across selected families are included only once', async () => {
    const app = consultation([[product('A'), product('B')], [product('B'), product('C')]]);
    await app.get('waContinue').trigger('click');
    assert.deepEqual(codes(await messages(app)), ['SKU-A', 'SKU-B', 'SKU-C']);
});

test('exactly 2,000 characters fit, including the full introduction, line breaks and farewell', async () => {
    const base = product('EXACT', 20);
    const probe = consultation([[base]]);
    await probe.get('waContinue').trigger('click');
    const exact = { ...base, name: base.name + 'x'.repeat(2000 - probe.get('waMessage').value.length) };
    const app = consultation([[exact], [product('NEXT')]]);
    await app.get('waContinue').trigger('click');
    const generated = await messages(app);
    assert.equal(generated[0].length, 2000);
    assert.equal(generated.length, 2);
    assert.deepEqual(codes(generated), ['SKU-EXACT', 'SKU-NEXT']);
});

test('an oversized single product is preserved alone and warns without truncation', async () => {
    const large = product('LONG', 2300);
    const app = consultation([[product('BEFORE')], [large], [product('AFTER')]]);
    await app.get('waContinue').trigger('click');
    const generated = await messages(app);
    assert.equal(generated.length, 3);
    assert.ok(generated[1].includes(large.name));
    assert.deepEqual(codes(generated), ['SKU-BEFORE', 'SKU-LONG', 'SKU-AFTER']);
    app.get('waGroup').value = '1';
    await app.get('waGroup').trigger('change');
    assert.match(app.get('waMessageLength').textContent, /supera los 2.000/);
});

test('changing reason recalculates the character budget and restores edits and position per reason', async () => {
    const app = consultation([[product('A', 935)], [product('B', 935)]]);
    await app.get('waContinue').trigger('click');
    assert.equal(app.get('waGroup').children.length, 2);
    app.get('waGroup').value = '1';
    await app.get('waGroup').trigger('change');
    app.get('waMessage').value = 'Texto editado libremente';
    await setReasons(app, ['photos']);
    assert.equal(app.get('waGroup').children.length, 1);
    assert.ok(app.get('waMessage').value.length <= 2000);
    await setReasons(app, ['photos', 'confirm']);
    assert.equal(app.get('waGroup').value, '1');
    assert.equal(app.get('waMessage').value, 'Texto editado libremente');
    await app.get('waContinue').trigger('click');
    assert.equal(app.get('waMessage').value, 'Texto editado libremente');
    assert.ok(app.items.every(item => item.checked));
});

test('navigation keeps manual drafts, clipboard needs no phone and wa.me encodes the edited text', async () => {
    const app = consultation([[product('A', 1500)], [product('B', 1500)]]);
    await app.get('waContinue').trigger('click');
    const edited = 'Hola compa, ñ & fotos?\n' + 'x'.repeat(2000);
    app.get('waMessage').value = edited;
    await app.get('waMessage').trigger('input');
    assert.match(app.get('waMessageLength').textContent, /supera los 2.000/);
    app.get('waGroup').value = '1';
    await app.get('waGroup').trigger('change');
    app.get('waGroup').value = '0';
    await app.get('waGroup').trigger('change');
    assert.equal(app.get('waMessage').value, edited);
    await app.get('waCopy').trigger('click');
    assert.deepEqual(app.copied, [edited]);
    app.get('waSector').value = 'dept1';
    await app.get('waSector').trigger('change');
    assert.equal(app.get('waPhone').value, '+595 981 123456');
    app.get('waPhone').value = '+595 982 654321';
    await app.get('waOpen').trigger('click');
    assert.equal(app.opened[0][0], 'https://wa.me/595982654321?text=' + encodeURIComponent(edited));
});

for (const reasons of combinations) {
    test(`requests only the selected reasons: ${reasons.join(', ')}`, async () => {
        const app = consultation([[product('A')]]);
        await app.get('waContinue').trigger('click');
        await setReasons(app, reasons);
        const intro = app.get('waMessage').value.split('\n\n')[0];
        const terms = { photos: /fotos/, confirm: /modelo y las variantes/, dimensions: /medidas/, weight: /peso/ };
        for (const [key, term] of Object.entries(terms)) {
            assert.equal(term.test(intro), reasons.includes(key), `${key}: ${intro}`);
        }
        assert.deepEqual(codes([app.get('waMessage').value]), ['SKU-A']);
        assert.equal(app.confirmations.length, 0, 'Generated proposals do not require confirmation');
    });
}

test('photos and model confirmation are selected by default', async () => {
    const app = consultation([[product('A')]]);
    assert.deepEqual(app.reasons.filter(input => input.checked).map(input => input.value), ['photos', 'confirm']);
    await app.get('waContinue').trigger('click');
    assert.match(app.get('waMessage').value, /las fotos y confirmar el modelo y las variantes/);
    assert.doesNotMatch(app.get('waMessage').value, /medidas|peso/);
});

test('no reasons blocks copy and WhatsApp without deleting the draft, and selection recovers', async () => {
    const app = consultation([[product('A')]]);
    await app.get('waContinue').trigger('click');
    app.get('waMessage').value = 'Consulta editada';
    await setReasons(app, []);
    assert.equal(app.get('waReasonsError').hidden, false);
    for (const id of ['waCopy', 'waOpen', 'waMessage', 'waGroup']) assert.equal(app.get(id).disabled, true);
    await app.get('waCopy').trigger('click');
    app.get('waPhone').value = '+595 981 123456';
    await app.get('waOpen').trigger('click');
    assert.equal(app.copied.length, 0);
    assert.equal(app.opened.length, 0);
    assert.equal(app.get('waMessage').value, 'Consulta editada');
    await setReasons(app, ['photos', 'confirm']);
    assert.equal(app.get('waMessage').value, 'Consulta editada');
    assert.equal(app.get('waReasonsError').hidden, true);
    for (const id of ['waCopy', 'waOpen', 'waMessage', 'waGroup']) assert.equal(app.get(id).disabled, false);
    assert.equal(app.confirmations.length, 0);
});

test('declining regeneration restores the checkboxes and leaves the edited message intact', async () => {
    const app = consultation([[product('A')]]);
    await app.get('waContinue').trigger('click');
    app.get('waMessage').value = 'Texto propio';
    app.decisions.push(false);
    await setReasons(app, ['weight']);
    assert.equal(app.confirmations.length, 1);
    assert.equal(app.get('waMessage').value, 'Texto propio');
    assert.deepEqual(app.reasons.filter(input => input.checked).map(input => input.value), ['photos', 'confirm']);
    assert.equal(app.get('waCopy').disabled, false);
    await app.get('waCopy').trigger('click');
    assert.deepEqual(app.copied, ['Texto propio']);
});

test('edits in another message also require confirmation and are restored for each combination', async () => {
    const app = consultation([[product('A', 1500)], [product('B', 1500)]]);
    await app.get('waContinue').trigger('click');
    app.get('waMessage').value = 'Primer mensaje editado';
    app.get('waGroup').value = '1';
    await app.get('waGroup').trigger('change');
    await setReasons(app, ['dimensions', 'weight']);
    assert.equal(app.confirmations.length, 1);
    assert.match(app.get('waMessage').value, /las medidas y el peso del producto/);
    assert.doesNotMatch(app.get('waMessage').value, /fotos|modelo/);
    app.get('waMessage').value = 'Medidas y peso editados';
    await setReasons(app, ['photos', 'confirm']);
    assert.equal(app.confirmations.length, 2);
    app.get('waGroup').value = '0';
    await app.get('waGroup').trigger('change');
    assert.equal(app.get('waMessage').value, 'Primer mensaje editado');
    await setReasons(app, ['dimensions', 'weight']);
    assert.equal(app.get('waMessage').value, 'Medidas y peso editados');
    assert.ok(app.items.every(item => item.checked));
});

async function setBuilding(app, key) {
    app.buildings.forEach(input => input.checked = input.value === key);
    await app.buildings.find(input => input.checked).trigger('change');
}

test('building filters sectors and Distrito groups Guembe and Casona without displaying phone numbers', async () => {
    const app = consultation([[product('A')]]);
    assert.deepEqual(app.get('waSector').children.map(option => option.value), ['', 'dept1']);
    await setBuilding(app, 'dubai');
    assert.deepEqual(app.get('waSector').children.map(option => option.value), ['', 'dubai1']);
    await setBuilding(app, 'distrito');
    const options = app.get('waSector').children;
    assert.deepEqual(options.map(option => option.value), ['', 'guembe1', 'casona1', 'casona2']);
    assert.match(options[1].textContent, /Lentes · Guembe/);
    assert.match(options[2].textContent, /Lentes · Casona/);
    assert.ok(options.every(option => !/595|123456/.test(option.textContent)));
    assert.equal(app.buildings.filter(input => input.checked).length, 1);
});

test('switching buildings resets sector and phone while preserving messages, reasons and products', async () => {
    const app = consultation([[product('A')]]);
    await app.get('waContinue').trigger('click');
    app.get('waSector').value = 'dept1';
    await app.get('waSector').trigger('change');
    assert.equal(app.get('waPhone').value, '+595 981 123456');
    app.get('waMessage').value = 'Mensaje editado';
    await setBuilding(app, 'distrito');
    assert.equal(app.get('waSector').value, '');
    assert.equal(app.get('waPhone').value, '');
    assert.equal(app.get('waMessage').value, 'Mensaje editado');
    assert.deepEqual(app.reasons.filter(input => input.checked).map(input => input.value), ['photos', 'confirm']);
    assert.ok(app.items.every(item => item.checked));
    app.get('waSector').value = 'guembe1';
    await app.get('waSector').trigger('change');
    assert.equal(app.get('waPhone').value, '+595 983 123456');
    app.get('waSector').value = 'casona1';
    await app.get('waSector').trigger('change');
    assert.equal(app.get('waPhone').value, '+595 984 123456');
    app.get('waPhone').value = '+595 985 654321';
    await app.get('waOpen').trigger('click');
    assert.equal(app.opened[0][0], 'https://wa.me/595985654321?text=' + encodeURIComponent('Mensaje editado'));
});

test('a building without contacts and a sector without a phone allow manual entry', async () => {
    const app = consultation([[product('A')]], guideContacts.filter(contact => contact.building !== 'dubai'));
    await app.get('waContinue').trigger('click');
    await setBuilding(app, 'dubai');
    assert.equal(app.get('waSector').children.length, 1);
    assert.match(app.get('waSector').children[0].textContent, /Sin sectores disponibles/);
    app.get('waPhone').value = '+595 981 654321';
    await app.get('waOpen').trigger('click');
    assert.match(app.opened[0][0], /^https:\/\/wa.me\/595981654321\?text=/);
    await setBuilding(app, 'distrito');
    app.get('waSector').value = 'casona2';
    await app.get('waSector').trigger('change');
    assert.equal(app.get('waPhone').value, '');
    await app.get('waCopy').trigger('click');
    assert.equal(app.copied.length, 1);
});
