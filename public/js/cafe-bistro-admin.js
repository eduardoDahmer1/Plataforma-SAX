(() => {
    const form = document.getElementById('formCafeBistro');
    if (!form) return;

    form.querySelectorAll('.upload-zone').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        if (!input) return;
        const gallery = input.multiple;
        const prefix = input.name.startsWith('cardapio') ? 'cardapio' : 'eventos';
        const preview = gallery ? document.getElementById(prefix + 'GaleriaPreview') : null;
        const limit = gallery && prefix === 'cardapio' ? 8 : Infinity;
        const maxMB = ['hero_imagen', 'cardapio_pdf'].includes(input.name) ? 8 : 4;
        let files = [];
        const feedback = document.createElement('p');
        feedback.className = 'x-small mt-2 mb-0';
        feedback.setAttribute('role', 'status');
        zone.after(feedback);

        function sync() {
            const transfer = new DataTransfer();
            files.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
            const counter = document.getElementById(prefix + 'GaleriaCount');
            if (preview && counter) counter.textContent = preview.children.length;
        }

        function select(incoming) {
            const selected = Array.from(incoming);
            if (!selected.length) return;
            const valid = selected.every(file =>
                (input.name === 'cardapio_pdf' ? file.type === 'application/pdf' : /^image\//.test(file.type)) &&
                file.size <= maxMB * 1024 * 1024);
            const existing = preview ? preview.querySelectorAll('input[type="hidden"]').length : 0;
            if (!valid || (!gallery && selected.length > 1) || existing + files.length + selected.length > limit) {
                feedback.textContent = !valid ? `Seleccione archivos válidos de hasta ${maxMB} MB.` : `Máximo ${gallery ? limit : 1} archivo(s).`;
                feedback.classList.add('text-danger');
                sync();
                return;
            }
            feedback.classList.remove('text-danger');
            files = gallery ? files.concat(selected) : selected;
            if (preview) selected.forEach(file => {
                const item = document.createElement('div');
                item.className = 'gallery-preview-item shadow-sm border';
                const img = document.createElement('img');
                const url = URL.createObjectURL(file);
                img.src = url;
                img.alt = file.name;
                img.className = 'w-100 h-100 object-fit-cover';
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'gallery-remove-btn';
                remove.textContent = '×';
                remove.setAttribute('aria-label', 'Eliminar ' + file.name);
                remove.addEventListener('click', event => {
                    event.stopPropagation();
                    files = files.filter(candidate => candidate !== file);
                    URL.revokeObjectURL(url);
                    item.remove();
                    sync();
                    feedback.textContent = `${files.length} archivo(s) nuevo(s) seleccionado(s).`;
                });
                item.append(img, remove);
                preview.append(item);
            });
            sync();
            feedback.textContent = files.map(file => file.name).join(', ');
        }

        input.addEventListener('change', () => select(input.files));
        const targets = [zone];
        const imageBox = zone.previousElementSibling;
        if (imageBox?.classList.contains('img-preview-box')) targets.push(imageBox);
        targets.forEach(target => {
            target.addEventListener('dragover', event => {
                event.preventDefault();
                target.style.outline = '2px dashed #2d4a7a';
            });
            target.addEventListener('dragleave', () => { target.style.outline = ''; });
            target.addEventListener('drop', event => {
                event.preventDefault();
                target.style.outline = '';
                select(event.dataTransfer.files);
                if (!gallery && input.dataset.prev && files[0]) {
                    document.getElementById(input.dataset.prev).src = URL.createObjectURL(files[0]);
                }
            });
        });
    });
})();
