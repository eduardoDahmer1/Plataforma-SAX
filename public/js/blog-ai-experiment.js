(function () {
    'use strict';

    const box = document.getElementById('blogAiExperiment');
    const form = document.getElementById('blogAiExperimentForm');
    if (!box || !form) return;

    const button = document.getElementById('blogAiGenerateButton');
    const progress = document.getElementById('blogAiProgress');
    const result = document.getElementById('blogAiResult');

    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = value == null ? '' : String(value);
        return node.innerHTML;
    }

    button.addEventListener('click', async function () {
        const topic = document.getElementById('blogAiTopic');
        if (!topic.value.trim()) {
            topic.setCustomValidity('Ingresá un tema para generar el artículo.');
            topic.reportValidity();
            topic.addEventListener('input', () => topic.setCustomValidity(''), { once: true });
            return;
        }
        button.disabled = true;
        progress.classList.remove('d-none');
        result.className = 'mt-4 d-none';
        result.innerHTML = '';

        try {
            const response = await fetch(box.dataset.generateUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    topic: topic.value.trim(),
                    focus: document.getElementById('blogAiFocus').value,
                    instructions: document.getElementById('blogAiInstructions').value,
                }),
            });
            const data = await response.json();
            if (!response.ok) {
                const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(validationMessage || data.message || 'No fue posible generar el artículo.');
            }

            ['title', 'subtitle', 'author', 'meta_description'].forEach(function (name) {
                const field = document.getElementById(name);
                field.value = data.blog[name] || '';
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
            const content = document.getElementById('editor-blog');
            content.value = data.blog.content || '';
            const editor = typeof tinymce !== 'undefined' ? tinymce.get('editor-blog') : null;
            if (editor) {
                editor.setContent(content.value);
                editor.save();
                editor.fire('change');
            }
            content.dispatchEvent(new Event('input', { bubbles: true }));

            const sources = (data.sources || []).map(function (source) {
                return '<li><a href="' + escapeHtml(source.url) + '" target="_blank" rel="noopener noreferrer">' +
                    escapeHtml(source.title || source.url) + '</a></li>';
            }).join('');
            const suggestions = (data.image_suggestions || []).map(function (item) {
                return '<li><strong>' + escapeHtml(item.description) + '</strong><br><span class="text-muted">' +
                    escapeHtml(item.search_query) + '</span></li>';
            }).join('');

            result.className = 'mt-4 alert alert-success';
            result.innerHTML = '<h6 class="fw-bold">Formulario completado</h6>' +
                '<p class="mb-3">Revisá el contenido, agregá las imágenes y guardá el artículo.</p>' +
                (sources ? '<details class="mb-2"><summary>Fuentes utilizadas</summary><ul class="mt-2">' + sources + '</ul></details>' : '') +
                (suggestions ? '<details><summary>Sugerencias de imágenes</summary><ul class="mt-2">' + suggestions + '</ul></details>' : '');
        } catch (error) {
            result.className = 'mt-4 alert alert-danger';
            result.textContent = error.message || 'No fue posible generar el artículo.';
        } finally {
            button.disabled = false;
            progress.classList.add('d-none');
        }
    });
})();
