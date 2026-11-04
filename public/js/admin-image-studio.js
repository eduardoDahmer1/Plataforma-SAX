(function () {
    'use strict';

    var studio = document.querySelector('[data-image-studio]');
    if (!studio || !window.SaxImageEditor) return;

    var input = studio.querySelector('[data-studio-input]');
    var preview = studio.querySelector('[data-studio-preview]');
    var empty = studio.querySelector('[data-studio-empty]');
    var widthInput = studio.querySelector('[data-studio-width]');
    var heightInput = studio.querySelector('[data-studio-height]');
    var modeInput = studio.querySelector('[data-studio-mode]');
    var fileLabel = studio.querySelector('[data-studio-file-label]');
    var outputInfo = studio.querySelector('[data-studio-output-info]');
    var editButton = studio.querySelector('[data-studio-edit]');
    var autoButton = studio.querySelector('[data-studio-auto]');
    var downloadButton = studio.querySelector('[data-studio-download]');
    var currentFile = null;
    var previewUrl = null;

    function clamp(value) {
        return Math.max(240, Math.min(2000, parseInt(value || 1080, 10)));
    }

    function settings() {
        return {
            width: clamp(widthInput.value),
            height: clamp(heightInput.value),
            mode: modeInput.value === 'cover' ? 'cover' : 'contain',
            smartSubject: modeInput.value !== 'cover'
        };
    }

    function setPreview(file) {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl; preview.hidden = false; empty.hidden = true;
        currentFile = file;
        fileLabel.textContent = file.name + ' · ' + (file.size / 1048576).toFixed(2) + ' MB';
        editButton.disabled = false; autoButton.disabled = false; downloadButton.disabled = false;
    }

    function open(runAutomatic) {
        if (!currentFile) return;
        var options = settings();
        window.SaxImageEditor.open(Object.assign({}, options, {
            file: currentFile,
            name: currentFile.name,
            runAutomatic: runAutomatic,
            onApply: function (file, metadata) {
                setPreview(file);
                widthInput.value = metadata.width; heightInput.value = metadata.height;
                outputInfo.textContent = metadata.width + ' × ' + metadata.height + ' px · WebP · ' + Math.round(metadata.quality * 100) + '%';
            }
        }));
    }

    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            if (window.saxToast) window.saxToast('error', 'Selecione um arquivo de imagem válido.');
            input.value = ''; return;
        }
        setPreview(file); open(false);
    });

    studio.querySelectorAll('[data-studio-preset]').forEach(function (button) {
        button.addEventListener('click', function () {
            var values = button.dataset.studioPreset.split(',');
            widthInput.value = values[0]; heightInput.value = values[1]; modeInput.value = values[2];
            studio.querySelectorAll('[data-studio-preset]').forEach(function (preset) { preset.classList.toggle('is-active', preset === button); });
            outputInfo.textContent = values[0] + ' × ' + values[1] + ' px · WebP';
        });
    });

    [widthInput, heightInput, modeInput].forEach(function (field) {
        field.addEventListener('change', function () {
            widthInput.value = clamp(widthInput.value); heightInput.value = clamp(heightInput.value);
            studio.querySelectorAll('[data-studio-preset]').forEach(function (preset) { preset.classList.remove('is-active'); });
            outputInfo.textContent = widthInput.value + ' × ' + heightInput.value + ' px · WebP';
        });
    });

    editButton.addEventListener('click', function () { open(false); });
    autoButton.addEventListener('click', function () { open(true); });
    downloadButton.addEventListener('click', function () {
        if (!currentFile) return;
        var link = document.createElement('a');
        link.href = URL.createObjectURL(currentFile);
        link.download = currentFile.name || 'imagem-editada.webp';
        document.body.appendChild(link); link.click(); link.remove();
        setTimeout(function () { URL.revokeObjectURL(link.href); }, 1000);
    });

    studio.querySelector('[data-studio-reset]').addEventListener('click', function () {
        input.value = ''; currentFile = null;
        if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = null;
        preview.removeAttribute('src'); preview.hidden = true; empty.hidden = false;
        fileLabel.textContent = 'Nenhum arquivo selecionado';
        editButton.disabled = true; autoButton.disabled = true; downloadButton.disabled = true;
        outputInfo.textContent = widthInput.value + ' × ' + heightInput.value + ' px · WebP';
    });
})();
