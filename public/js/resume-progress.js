(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const panel = document.getElementById('resumeProgress');
        if (!panel) return;

        const ids = panel.dataset.ids;
        if (!/^[1-9][0-9]*(,[1-9][0-9]*)*$/.test(ids || '')) return;

        const count = document.getElementById('resumeProgressCount');
        const detail = document.getElementById('resumeProgressDetail');
        const fill = document.getElementById('resumeProgressFill');
        const bar = panel.querySelector('[role="progressbar"]');
        const startedAt = Date.now();
        let lastCount = '';

        function schedule(delay) {
            if (Date.now() - startedAt < 15 * 60 * 1000) {
                window.setTimeout(poll, delay);
            } else {
                detail.textContent = 'Acompanhamento pausado. Atualize a página para conferir os registros.';
            }
        }

        function updateRows(statuses) {
            Object.entries(statuses).forEach(function ([id, status]) {
                const row = document.querySelector('[data-row][data-id="' + id + '"]');
                const label = row?.querySelector('[data-hr-status]');
                if (!label) return;
                label.textContent = status === 'sent' ? 'Enviado pro RH'
                    : status === 'failed' ? 'Falha no envio ao RH'
                    : 'Aguardando envio ao RH';
            });
        }

        async function poll() {
            try {
                const response = await fetch(panel.dataset.url + '?ids=' + encodeURIComponent(ids), {
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Falha ao consultar o progresso');
                const progress = await response.json();
                if (!Number.isInteger(progress.total) || progress.total < 1) throw new Error('Progresso inválido');

                const summary = progress.sent + ' de ' + progress.total + ' enviado' + (progress.total === 1 ? '' : 's');
                if (summary !== lastCount) {
                    count.textContent = summary;
                    lastCount = summary;
                }
                fill.style.width = Math.min(100, (progress.sent / progress.total) * 100) + '%';
                bar.setAttribute('aria-valuenow', String(progress.sent));
                detail.textContent = progress.pending > 0
                    ? progress.pending + ' aguardando · ' + progress.failed + ' falha' + (progress.failed === 1 ? '' : 's')
                    : 'Concluído · ' + progress.sent + ' enviado' + (progress.sent === 1 ? '' : 's')
                        + ' · ' + progress.failed + ' falha' + (progress.failed === 1 ? '' : 's');
                panel.classList.toggle('has-failures', progress.failed > 0);
                updateRows(progress.statuses || {});

                if (progress.pending > 0) schedule(document.hidden ? 5000 : 2000);
            } catch (error) {
                detail.textContent = 'A conexão com o painel falhou. Tentando atualizar novamente…';
                schedule(5000);
            }
        }

        poll();
    });
})();
