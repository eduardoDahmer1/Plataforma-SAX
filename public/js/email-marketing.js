(function () {
    'use strict';

    const editorElement = document.getElementById('editor-email');
    if (editorElement && typeof tinymce !== 'undefined') {
        const uploadUrl = editorElement.dataset.uploadUrl;
        tinymce.init({
            selector: '#editor-email',
            height: 480,
            menubar: false,
            branding: false,
            statusbar: true,
            plugins: ['advlist autolink lists link image charmap preview anchor', 'searchreplace visualblocks code fullscreen', 'insertdatetime table paste code help wordcount', 'quickbars'],
            toolbar: 'formatselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | blockquote hr | table | link image | removeformat | preview code fullscreen',
            quickbars_selection_toolbar: 'bold italic | quicklink blockquote',
            automatic_uploads: true,
            paste_data_images: true,
            convert_urls: false,
            content_style: 'body{font-family:Arial,sans-serif;font-size:16px;line-height:1.65;color:#252525}img{max-width:100%;height:auto}',
            setup: function (editor) {
                editor.on('change keyup', function () { editor.save(); });
            },
            images_upload_handler: uploadUrl ? function (blobInfo, success, failure) {
                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                fetch(uploadUrl, { method: 'POST', headers: window.headers || headers, body: formData })
                    .then(function (response) { return response.json().then(function (data) { return { ok: response.ok, data: data }; }); })
                    .then(function (result) { result.ok && result.data.location ? success(result.data.location) : failure(result.data.message || 'Falha ao enviar imagem.'); })
                    .catch(function () { failure('Falha ao enviar imagem.'); });
            } : undefined
        });
    }

    const audience = document.getElementById('emailAudience');
    const specificField = document.getElementById('specificEmailsField');
    function syncAudience() {
        if (specificField && audience) specificField.hidden = audience.value !== 'specific';
    }
    if (audience) {
        audience.addEventListener('change', syncAudience);
        syncAudience();
    }

    const saveTemplate = document.getElementById('saveAsTemplate');
    const templateNameField = document.getElementById('templateNameField');
    function syncSaveTemplate() {
        if (templateNameField && saveTemplate) templateNameField.hidden = !saveTemplate.checked;
    }
    if (saveTemplate) {
        saveTemplate.addEventListener('change', syncSaveTemplate);
        syncSaveTemplate();
    }

    const templateSelect = document.getElementById('emailTemplateSelect');
    const templateDataElement = document.getElementById('emailTemplatesData');
    const subject = document.getElementById('emailSubject');
    if (templateSelect && templateDataElement) {
        const templates = JSON.parse(templateDataElement.textContent || '{}');
        templateSelect.addEventListener('change', function () {
            if (!this.value || !templates[this.value]) return;
            if (subject) subject.value = templates[this.value].subject || '';
            const setBody = function () {
                const editor = typeof tinymce !== 'undefined' ? tinymce.get('editor-email') : null;
                if (editor) editor.setContent(templates[templateSelect.value].body || '');
                else if (editorElement) editorElement.value = templates[templateSelect.value].body || '';
            };
            setBody();
        });
    }

    const campaignForm = document.getElementById('emailCampaignForm');
    if (campaignForm) {
        campaignForm.addEventListener('submit', function (event) {
            if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            const selected = audience ? audience.options[audience.selectedIndex].text : 'os destinatários selecionados';
            if (!window.confirm('Confirmar o envio para ' + selected + '?')) {
                event.preventDefault();
                return;
            }
            const button = document.getElementById('sendCampaignButton');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Preparando...';
            }
        });
    }
})();
