/* Short start/status requests keep AI generation independent of proxy timeouts. */
(function () {
    const pause = ms => new Promise(resolve => setTimeout(resolve, ms));

    async function jsonRequest(url, options = {}) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 15000);
        try {
            const response = await fetch(url, {...options, signal: controller.signal, cache: 'no-store'});
            let data;
            try { data = await response.json(); } catch (_) {
                const error = new Error('El servidor no devolvió el estado de la generación.');
                error.status = response.redirected ? 401 : (response.status >= 400 ? response.status : 502);
                throw error;
            }
            if (!response.ok) {
                const error = new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || `No se pudo consultar la generación (HTTP ${response.status}).`);
                error.status = response.status;
                throw error;
            }
            return data;
        } finally {
            clearTimeout(timer);
        }
    }

    window.saxRequestProductAi = async function (url, payload, progress = () => {}) {
        const requestId = crypto.randomUUID();
        const headers = {'Accept':'application/json', 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest',
            'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || ''};
        let receipt;
        for (let attempt = 0; attempt < 3; attempt++) {
            try {
                receipt = await jsonRequest(url, {method:'POST', headers, body:JSON.stringify({...payload, async:true, request_id:requestId})});
                break;
            } catch (error) {
                if ((error.status && error.status < 500) || attempt === 2) throw error;
                progress('Reconectando con la generación…');
                await pause(1500);
            }
        }
        // Supports a server rollout before every application instance has restarted.
        if (!receipt.status_url) return receipt;
        const statusUrl = new URL(receipt.status_url, window.location.href);
        if (statusUrl.origin !== window.location.origin) throw new Error('La dirección de consulta de la generación no es válida.');
        const deadline = Date.now() + 12 * 60 * 1000;
        while (Date.now() < deadline) {
            let state;
            try {
                state = await jsonRequest(statusUrl.href, {headers});
            } catch (error) {
                if (error.status && error.status < 500) throw error;
                progress('Reconectando; la generación continúa…');
                await pause(3000);
                continue;
            }
            if (state.status === 'completed') return state.result;
            if (state.status === 'failed') throw new Error(state.message || 'No se pudo completar la generación.');
            progress(state.status === 'queued' ? 'Esperando turno para generar…' : 'Investigando y generando el contenido…');
            await pause(2000);
        }
        throw new Error('La generación sigue pendiente. Revisá el estado del servicio de IA.');
    };
})();
