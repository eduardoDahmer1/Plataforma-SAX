import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

const source = readFileSync(new URL('../../public/js/ai-batch-name.js', import.meta.url), 'utf8');
function editor() {
    class Element {
        constructor() { this.listeners = {}; this.dataset = {}; this.attributes = {}; this.hidden = false; }
        addEventListener(type, handler) { this.listeners[type] = handler; }
        async trigger(type, event = {}) { return this.listeners[type]?.call(this, { preventDefault() {}, ...event }); }
        focus() {}
        select() {}
        setAttribute(key, value) { this.attributes[key] = value; }
        removeAttribute(key) { delete this.attributes[key]; }
    }
    const root = new Element();
    root.dataset = { name: 'Anterior', url: '/admin/products/ai-batches/1/name' };
    const trigger = new Element();
    const form = new Element();
    form.hidden = true;
    const input = new Element();
    const cancel = new Element();
    const status = new Element();
    const label = new Element();
    label.textContent = 'Anterior';
    const submit = new Element();
    const token = { value: 'csrf-test' };
    root.querySelector = selector => ({
        '[data-rename-start]': trigger, '[data-rename-form]': form,
        '[data-rename-cancel]': cancel, '[data-rename-status]': status, '[data-rename-label]': label,
    })[selector];
    form.querySelector = selector => selector === '[name="name"]' ? input : token;
    form.querySelectorAll = () => [input, cancel, submit];
    const calls = [];
    const responses = [];
    vm.runInNewContext(source, {
        document: { addEventListener(type, callback) { callback(); }, querySelectorAll() { return [root]; } },
        async fetch(url, options) {
            calls.push({ url, options });
            return responses.shift() || { ok: true, async json() { return { name: 'Nuevo', display_name: 'Nuevo' }; } };
        },
    });
    return { root, trigger, form, input, cancel, status, label, calls, responses };
}

test('clicking the title edits its saved name and saves with CSRF without reloading', async () => {
    const app = editor();
    await app.trigger.trigger('click');
    assert.equal(app.input.value, 'Anterior');
    assert.equal(app.form.hidden, false);
    assert.equal(app.trigger.hidden, true);
    app.input.value = '  Nuevo  ';
    await app.form.trigger('submit');
    assert.equal(app.calls[0].options.method, 'PATCH');
    assert.equal(app.calls[0].options.headers['X-CSRF-TOKEN'], 'csrf-test');
    assert.equal(app.calls[0].options.body, JSON.stringify({ name: 'Nuevo' }));
    assert.equal(app.root.dataset.name, 'Nuevo');
    assert.equal(app.label.textContent, 'Nuevo');
    assert.equal(app.form.hidden, true);
    assert.equal(app.trigger.hidden, false);
});

test('cancel and Escape discard unsaved edits without making a request', async () => {
    const app = editor();
    for (const escape of [false, true]) {
        await app.trigger.trigger('click');
        app.input.value = 'No guardar';
        if (escape) await app.form.trigger('keydown', { key: 'Escape' });
        else await app.cancel.trigger('click');
        assert.equal(app.form.hidden, true);
        assert.equal(app.label.textContent, 'Anterior');
        assert.equal(app.root.dataset.name, 'Anterior');
    }
    assert.equal(app.calls.length, 0);
});

test('validation and connection errors preserve the input for retry', async () => {
    const app = editor();
    await app.trigger.trigger('click');
    app.input.value = 'Nombre nuevo';
    app.responses.push({ ok: false, async json() { return { errors: { name: ['Hasta 120 caracteres.'] } }; } });
    await app.form.trigger('submit');
    assert.equal(app.form.hidden, false);
    assert.equal(app.input.value, 'Nombre nuevo');
    assert.equal(app.input.disabled, false);
    assert.equal(app.status.textContent, 'Hasta 120 caracteres.');
    assert.equal(app.label.textContent, 'Anterior');
    app.responses.push({ async json() { throw new Error('Sin conexión'); } });
    await app.form.trigger('submit');
    assert.equal(app.form.hidden, false);
    assert.equal(app.input.value, 'Nombre nuevo');
    await app.form.trigger('submit');
    assert.equal(app.form.hidden, true);
});

test('clearing the name shows the server default title', async () => {
    const app = editor();
    await app.trigger.trigger('click');
    app.input.value = '';
    app.responses.push({ ok: true, async json() { return { name: null, display_name: 'Lote de IA #1' }; } });
    await app.form.trigger('submit');
    assert.equal(app.label.textContent, 'Lote de IA #1');
    assert.equal(app.root.dataset.name, '');
});
