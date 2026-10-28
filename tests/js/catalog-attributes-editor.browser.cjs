/* Render the STAGE product editor to a temporary HTML file first, then run with
 * SAX_ATTRIBUTE_EDITOR_HTML=/tmp/sax-attributes-product.html node this-file.cjs.
 * All requests are intercepted: no product, dictionary or translation is saved.
 */
const {chromium} = require(process.env.SAX_PLAYWRIGHT_MODULE || 'playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const project = path.resolve(__dirname, '../..');
const fixture = process.env.SAX_ATTRIBUTE_EDITOR_HTML;
assert(fixture, 'Set SAX_ATTRIBUTE_EDITOR_HTML to a rendered STAGE product editor.');
const attribute = (id, name, type, extra = {}) => ({id, name, code:'qa_'+id, type, active:true, inherit:true, unit:null, translations:{es:name, pt:name, en:name}, options:[], ...extra});
const option = (id, name, active = true) => ({id, name, code:'option_'+id, active, translations:{es:name, pt:name, en:name}});
let dictionary = [
    attribute(901, 'Material de montura', 'single', {options:[option(1,'Acetato'), option(2,'Metal'), option(3,'Histórica',false)]}),
    attribute(902, 'Protección de lentes', 'multiple', {options:[option(4,'UV 400'), option(5,'Antirreflejo'), option(6,'Fotocromático')]}),
    attribute(903, 'Ancho del puente', 'number', {unit:'mm'}),
    attribute(904, 'Polarizado', 'boolean'),
    attribute(905, 'Forma heredada', 'single', {options:[option(7,'Ovalada')]}),
    attribute(906, 'Tratamiento bloqueado', 'boolean'),
    attribute(907, 'Atributo adicional', 'number'),
    attribute(908, 'Atributo desactivado', 'boolean', {active:false}),
    attribute(909, 'Nuevo valor IA', 'boolean'),
];
const effective = {
    901:{mode:'value', options:[1], source:'manual'},
    902:{mode:'value', options:[4,5], source:'ai'},
    903:{mode:'value', number:'18.5', options:[], source:'manual'},
    904:{mode:'value', boolean:false, options:[], source:'manual'},
    905:{mode:'value', options:[7], inherited:true, from_product_id:45, source:'manual'},
    906:{mode:'block', options:[], source:'manual'},
    908:{mode:'value', boolean:true, options:[], source:'manual'},
};
let html = fs.readFileSync(fixture,'utf8');
for (const [key, data] of Object.entries({dictionary, effective, protected:[], old:[]})) {
    html = html.replace(new RegExp(`(data-attribute-${key}>)[\\s\\S]*?(</script>)`), (_, start, end) => start+JSON.stringify(data)+end);
}
const baseline = process.env.SAX_ATTRIBUTE_BASELINE;
const screenshotDir = process.env.SAX_ATTRIBUTE_SCREENSHOTS || '/tmp';
const rowSelector = id => `[data-attribute-row="${id}"]`;
(async () => {
    const browser = await chromium.launch({headless:true, ...(process.env.SAX_CHROMIUM_PATH ? {executablePath:process.env.SAX_CHROMIUM_PATH} : {}), args:['--no-sandbox','--disable-dev-shm-usage']});
    try {
        const page = await browser.newPage({viewport:{width:1440,height:1050}, reducedMotion:'reduce'});
        page.setDefaultTimeout(10000);
        page.setDefaultNavigationTimeout(15000);
        const errors = [], mutations = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', async route => {
            const req = route.request(), url = new URL(req.url());
            if (req.method() !== 'GET') {
                mutations.push(url.pathname);
                const data = req.postDataJSON() || {};
                if (url.pathname.startsWith('/admin/catalog-attributes/')) {
                    if (data.translate?.es?.name === 'Duplicado') return route.fulfill({status:422,json:{errors:{code:['El atributo ya existe.']}}});
                    const segments = url.pathname.split('/');
                    const item = dictionary.find(a => a.id === Number(segments[3]));
                    const translations = {es:data.translate.es.name, pt:data.translate['pt-br'].name, en:data.translate.en.name};
                    if (segments[4] === 'options') {
                        const existing = item.options.find(o => o.id === Number(segments[5]));
                        const updated = existing || option(1001,'');
                        Object.assign(updated, {name:translations.es, translations});
                        if (!existing) item.options.push(updated);
                        return route.fulfill({json:{item, option_id:updated.id}});
                    }
                    Object.assign(item, {name:translations.es, translations});
                    return route.fulfill({json:{item}});
                }
                throw new Error('Unexpected mutation: '+url.pathname);
            }
            if (url.pathname === '/admin/qa-product') return route.fulfill({contentType:'text/html',body:html});
            let file = path.join(project,'public',url.pathname);
            if (url.pathname.endsWith('/bootstrap.bundle.min.js')) file = path.join(project,'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js');
            if (url.pathname.endsWith('/bootstrap.min.css')) file = path.join(project,'node_modules/bootstrap/dist/css/bootstrap.min.css');
            if (url.pathname.includes('jquery')) file = '/usr/share/javascript/jquery/jquery.min.js';
            if (baseline && /\/(admin.css|catalog-attributes-admin.js)$/.test(url.pathname)) file = path.join(baseline,path.basename(url.pathname));
            return fs.existsSync(file) && fs.statSync(file).isFile() ? route.fulfill({path:file}) : route.fulfill({body:'',contentType:url.pathname.endsWith('.js')?'application/javascript':'text/plain'});
        });
        console.log('Loading rendered STAGE editor…');
        await page.goto('http://sax.test/admin/qa-product', {waitUntil:'domcontentloaded'});
        const root = page.locator('#catalogProductAttributes');
        await root.locator('summary').click();
        await page.locator(rowSelector(901)).waitFor();
        console.log('Editor initialized.');
        const payload = () => page.locator('#productEditForm').evaluate(form => Array.from(new FormData(form).entries()).filter(([key]) => key.startsWith('catalog_values')));
        const row = id => page.locator(rowSelector(id));
        // Exclude unrelated fixed admin navigation from section-only captures.
        const capture = async filename => {
            await page.evaluate(() => document.querySelectorAll('body *').forEach(el => {
                if (!el.closest('#catalogProductAttributes') && getComputedStyle(el).position === 'fixed') el.classList.add('attribute-qa-fixed');
            }));
            await root.screenshot({path:path.join(screenshotDir,filename), style:'.attribute-qa-fixed { visibility: hidden !important; }'});
        };
        if (baseline) {
            for (const width of [1440,390]) {
                await page.setViewportSize({width,height:1050});
                await capture(`sax-attributes-before-${width}.png`);
            }
            console.log('Baseline captured.'); return;
        }
        assert.deepEqual(await payload(), [], 'untouched values must not be rewritten');
        assert.equal(await root.locator('.dropdown-menu:visible').count(),0);
        assert.equal(await root.locator('select[multiple]:visible').count(),0);
        assert.equal(await row(902).locator('.product-attributes__ai').isVisible(),true);
        assert.equal(await row(904).locator('select.product-attributes__value-input').inputValue(),'0');
        assert.equal(await row(905).locator('select.product-attributes__value-input').isDisabled(),true);
        assert.match(await row(905).textContent(),/Heredado del producto #45/);
        assert.match(await row(906).textContent(),/Herencia bloqueada/);
        assert.equal(await row(908).count(),1);
        const desktopPositions = await root.locator('[data-attribute-row]').evaluateAll(nodes => nodes.map(el => ({x:el.getBoundingClientRect().x,y:el.getBoundingClientRect().y})));
        assert(desktopPositions[0].x < desktopPositions[1].x && desktopPositions[0].y === desktopPositions[1].y, 'desktop cards must share a row');
        await capture('sax-attributes-after-1440.png');
        console.log('Initial values and desktop layout passed.');
        const multi = row(902).locator('.product-attributes__multiple');
        const multiToggle = multi.locator('button').first();
        await multiToggle.click();
        await multi.locator('input[type=search]').fill('foto');
        assert.equal(await multi.locator('.product-attributes__option:visible').count(),1);
        await multi.locator('input[type=search]').press('Enter');
        assert.equal(mutations.length,0,'search Enter must not submit');
        await multi.getByLabel('Fotocromático',{exact:true}).check();
        assert.equal(await multi.locator('.dropdown-menu').isVisible(),true);
        assert.equal(await row(902).locator('.product-attributes__ai').isHidden(),true);
        await multi.getByRole('button',{name:'Listo',exact:true}).click();
        assert.equal(await multiToggle.getAttribute('aria-expanded'),'false');
        assert.equal(await multiToggle.evaluate(el => el === document.activeElement),true);
        assert((await payload()).some(([key,value])=>key==='catalog_values[902][options][]'&&value==='6'));
        await multiToggle.click(); await multi.locator('input[type=search]').press('Escape');
        assert.equal(await multi.locator('.dropdown-menu').isHidden(),true);
        await multiToggle.click(); await root.locator('.product-attributes__hint').click();
        assert.equal(await multi.locator('.dropdown-menu').isHidden(),true);
        // Clear every selection, then choose with the keyboard, preserving the same payload contract.
        await multiToggle.click();
        for (const check of await multi.locator('input[type=checkbox]').all()) await check.uncheck();
        assert.equal(await row(902).locator('input[name$="[options][]"]').count(),0);
        assert.equal(await multiToggle.textContent(),'Seleccionar…');
        await multi.getByLabel('UV 400',{exact:true}).focus(); await page.keyboard.press('Space');
        await multi.getByRole('button',{name:'Listo',exact:true}).click();
        assert.equal(await row(902).locator('input[name$="[options][]"]').inputValue(),'4');
        async function action(id, text) {
            await row(id).getByRole('button',{name:'Acciones de '+dictionary.find(a=>a.id===id).name,exact:true}).click();
            await row(id).getByRole('button',{name:text,exact:true}).click();
        }
        await action(903,'Bloquear herencia / sin valor');
        assert.equal(await row(903).locator('input[name$="[mode]"]').inputValue(),'block');
        assert.equal(await row(903).locator('input[name$="[number]"]').count(),0);
        await action(903,'Heredar del padre');
        assert.equal(await row(903).locator('input[name$="[mode]"]').inputValue(),'inherit');
        await action(903,'Usar valor propio');
        await row(903).locator('input[type=number]').fill('19.25');
        await row(903).locator('input[type=number]').dispatchEvent('change');
        assert.equal(await row(903).locator('input[name$="[number]"]').inputValue(),'19.25');
        await row(901).locator('select.product-attributes__value-input').selectOption('2');
        await row(904).locator('select.product-attributes__value-input').selectOption('1');
        console.log('Selection, menus and inheritance passed.');
        const modal = page.locator('#catalogAttributeModal'), definition = page.locator('#catalogAttributeDefinitionForm');
        async function labels(text) {
            for (const lang of ['pt-br','es','en']) {
                await definition.locator(`[data-lang-field-btn="${lang}"]`).click();
                await definition.locator('[data-lang-visual]').fill(text);
            }
        }
        await action(901,'Editar atributo'); await labels('Duplicado');
        await definition.locator('[type=submit]').click();
        await definition.getByText('El atributo ya existe.',{exact:true}).waitFor();
        await labels('Material actualizado'); await definition.locator('[type=submit]').click();
        await modal.waitFor({state:'hidden'});
        assert.equal(await row(901).locator('select.product-attributes__value-input').inputValue(),'2');
        assert.deepEqual(await root.locator('[data-attribute-row]').evaluateAll(nodes=>nodes.map(el=>Number(el.dataset.attributeRow))),[901,902,903,904,905,906,908], 'dictionary edits must preserve card order');
        await action(901,'Crear opción'); await labels('Titanio');
        await definition.locator('[type=submit]').click(); await modal.waitFor({state:'hidden'});
        assert.equal(await row(901).locator('input[name$="[options][]"]').inputValue(),'1001');
        await action(901,'Editar opción: Titanio'); await labels('Titanio actualizado');
        await definition.locator('[type=submit]').click(); await modal.waitFor({state:'hidden'});
        assert.equal(await row(901).locator('select.product-attributes__value-input option:checked').textContent(),'Titanio actualizado');
        await root.locator('[data-manage-attributes]').click();
        await definition.locator('[data-manager-search]').fill('Material actualizado');
        assert.equal(await definition.locator('[data-manager-list] > div').count(),1);
        await modal.locator('[data-bs-dismiss]').first().click(); await modal.waitFor({state:'hidden'});
        await page.locator('#catalogAttributePicker').click();
        await root.locator('[data-attribute-selector-search]').fill('adicional');
        await root.locator('[data-attribute-selector-search]').press('ArrowDown'); await page.keyboard.press('Enter');
        assert.equal(await root.locator('[data-attribute-picker]').isHidden(),true);
        assert.equal(await row(907).locator('input[type=number]').evaluate(el=>el===document.activeElement),true);
        await page.evaluate(() => window.dispatchEvent(new CustomEvent('sax:catalog-attribute-proposal',{detail:{attribute_review:{items:[{code:'qa_909',status:'accepted',value:{attribute_id:909,mode:'value',boolean:true,options:[],ai_proposal:'signed-proof'}}]}}})));
        assert.equal(await row(909).locator('.product-attributes__ai').isVisible(),true);
        assert.equal(await row(909).locator('input[name$="[ai_proposal]"]').inputValue(),'signed-proof');
        const before = await payload();
        await root.locator('summary').click(); await root.locator('summary').click();
        assert.deepEqual(await payload(),before,'collapsing must preserve AI proof and all values');
        await row(909).locator('select.product-attributes__value-input').selectOption('0');
        assert.equal(await row(909).locator('input[name$="[ai_proposal]"]').count(),0);
        for (const width of [768,390,320]) {
            await page.setViewportSize({width,height:1050});
            const bounds = await root.boundingBox();
            assert(bounds.x>=0 && bounds.x+bounds.width<=width, 'section overflow at '+width);
            if (width<576) {
                const positions = await root.locator('[data-attribute-row]').evaluateAll(nodes=>nodes.map(el=>el.getBoundingClientRect().x));
                assert(positions.every(x=>x===positions[0]),'mobile must use one column');
            }
            await multiToggle.evaluate(el=>el.scrollIntoView({block:'center',behavior:'instant'}));
            await multiToggle.click();
            const menuBounds = await multi.locator('.dropdown-menu').boundingBox();
            assert(menuBounds.x>=0 && menuBounds.x+menuBounds.width<=width,'multiple menu overflow at '+width);
            await multi.locator('input[type=search]').press('Escape');
            await row(903).getByRole('button',{name:'Acciones de Ancho del puente',exact:true}).click();
            const actionBounds = await row(903).locator('.product-attributes__action-menu').boundingBox();
            assert(actionBounds.x>=0 && actionBounds.x+actionBounds.width<=width,'actions overflow at '+width);
            await page.keyboard.press('Escape');
            if (width===390) {
                await capture('sax-attributes-after-390.png');
                await multiToggle.evaluate(el=>el.scrollIntoView({block:'center',behavior:'instant'}));
                await multiToggle.click();
                await page.screenshot({path:path.join(screenshotDir,'sax-attributes-multiple-390.png'),style:'.attribute-qa-fixed { visibility: hidden !important; }'});
                await multi.locator('input[type=search]').press('Escape');
            }
        }
        assert.deepEqual(errors,[],'browser exceptions');
        console.log(JSON.stringify({result:'PASS', viewports:[1440,768,390,320], interceptedDictionaryRequests:mutations.length, checks:'all value types, untouched payload, multiple search/selection/closing, inheritance/block, dictionary validation/edit/options, picker keyboard, signed AI provenance, responsive bounds', errors}));
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });
