const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function fixture(responses) {
    const calls = [], progress = [];
    const context = {
        window:{location:{href:'https://sax.example/admin/products/1/edit',origin:'https://sax.example'}},
        document:{querySelector:()=>({content:'csrf'})}, crypto:{randomUUID:()=> '12345678-1234-4234-8234-123456789abc'},
        AbortController, URL, Date,
        setTimeout(fn, ms) { if (ms < 15000) queueMicrotask(fn); return 1; }, clearTimeout() {},
        async fetch(url, options) {
            calls.push({url,options});
            const next = responses.shift();
            if (next instanceof Error) throw next;
            assert.ok(next, 'Unexpected extra request');
            return {ok:next.http === undefined || next.http < 400, status:next.http || 200, json:async()=>next.data};
        },
    };
    vm.runInNewContext(fs.readFileSync('public/js/product-ai-request.js','utf8'),context);
    return {calls,progress,run:()=>context.window.saxRequestProductAi('/start',{catalog_context:{name:'Current editor'}},text=>progress.push(text))};
}

test('one click starts one job and polls until the complete result is ready', async () => {
    const f = fixture([{data:{status_url:'/status'}},{data:{status:'queued'}},{data:{status:'processing'}},{data:{status:'completed',result:{proposal:'ready'}}}]);
    const result = await f.run();
    assert.equal(result.proposal,'ready');
    assert.equal(f.calls.filter(c=>c.options.method==='POST').length,1);
    assert.equal(JSON.parse(f.calls[0].options.body).catalog_context.name,'Current editor');
    assert.equal(JSON.parse(f.calls[0].options.body).async,true);
    assert.equal(f.progress.length,2);
});

test('lost start response reuses the request id and poll reconnection does not generate again', async () => {
    const f=fixture([new Error('network'),{data:{status_url:'/status'}},{http:504,data:{}},{data:{status:'completed',result:{done:true}}}]);
    assert.equal((await f.run()).done,true);
    const posts=f.calls.filter(c=>c.options.method==='POST');
    assert.equal(posts.length,2);
    assert.equal(posts[0].options.body,posts[1].options.body);
});

test('terminal generation error is displayed without repeatedly starting new jobs', async () => {
    const f=fixture([{data:{status_url:'/status'}},{data:{status:'failed',message:'Sin cuota'}}]);
    await assert.rejects(f.run(),/Sin cuota/);
    assert.equal(f.calls.filter(c=>c.options.method==='POST').length,1);
});

test('authorization errors stop immediately', async () => {
    const f=fixture([{http:403,data:{message:'Acceso denegado'}}]);
    await assert.rejects(f.run(),/Acceso denegado/);
    assert.equal(f.calls.length,1);
});
