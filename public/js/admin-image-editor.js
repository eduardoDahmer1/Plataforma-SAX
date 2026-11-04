(function () {
    'use strict';

    var modalElement = document.getElementById('saxImageEditorModal');
    if (!modalElement || !window.bootstrap) return;

    var canvas = modalElement.querySelector('[data-image-canvas]');
    var ctx = canvas.getContext('2d', { alpha: true });
    var shell = modalElement.querySelector('[data-image-canvas-shell]');
    var grid = modalElement.querySelector('.sax-image-editor__grid');
    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    var editor = null;
    var image = null;
    var imageUrl = null;
    var state = null;
    var initialState = null;
    var history = [];
    var future = [];
    var ratioLocked = true;
    var dragging = false;
    var dragPoint = null;
    var estimateTimer = null;
    var maskCanvas = document.createElement('canvas');
    var maskCtx = maskCanvas.getContext('2d', { willReadFrequently: true });
    var paintCanvas = document.createElement('canvas');
    var paintCtx = paintCanvas.getContext('2d');
    var compositeCanvas = document.createElement('canvas');
    var compositeCtx = compositeCanvas.getContext('2d');
    var activeTool = 'move';
    var painting = false;
    var lassoPoints = [];

    var widthInput = modalElement.querySelector('[data-image-width]');
    var heightInput = modalElement.querySelector('[data-image-height]');
    var zoomInput = modalElement.querySelector('[data-image-zoom]');
    var qualityInput = modalElement.querySelector('[data-image-quality]');
    var applyButton = modalElement.querySelector('[data-image-apply]');

    function number(value, fallback) {
        var parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function copyState(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function fileBaseName(name) {
        return (name || 'imagem').replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_-]+/g, '-').replace(/^-+|-+$/g, '') || 'imagem';
    }

    function formatBytes(bytes) {
        if (!Number.isFinite(bytes)) return 'Calculando…';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function toast(type, message) {
        if (window.saxToast) window.saxToast(type, message);
        else if (type === 'error') window.alert(message);
    }

    function loadImage(source) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = function () { reject(new Error('Não foi possível carregar a imagem.')); };
            if (typeof source === 'string' && !source.startsWith('blob:') && !source.startsWith('data:')) {
                img.crossOrigin = 'anonymous';
            }
            img.src = source;
        });
    }

    function sourceFor(file, url) {
        if (file) {
            imageUrl = URL.createObjectURL(file);
            return imageUrl;
        }
        imageUrl = null;
        return url;
    }

    function rotatedSize() {
        var turn = Math.abs(state.rotation % 180) === 90;
        return { width: turn ? image.naturalHeight : image.naturalWidth, height: turn ? image.naturalWidth : image.naturalHeight };
    }

    function baseScale(mode) {
        var size = rotatedSize();
        var sx = state.width / size.width;
        var sy = state.height / size.height;
        return mode === 'cover' ? Math.max(sx, sy) : Math.min(sx, sy);
    }

    function syncGrid() {
        requestAnimationFrame(function () {
            grid.style.width = canvas.clientWidth + 'px';
            grid.style.height = canvas.clientHeight + 'px';
            grid.style.left = canvas.offsetLeft + 'px';
            grid.style.top = canvas.offsetTop + 'px';
        });
    }

    function ensurePaintSize(width, height) {
        if (paintCanvas.width === width && paintCanvas.height === height) return;
        var previous = document.createElement('canvas');
        previous.width = paintCanvas.width || width;
        previous.height = paintCanvas.height || height;
        if (paintCanvas.width && paintCanvas.height) previous.getContext('2d').drawImage(paintCanvas, 0, 0);
        paintCanvas.width = width; paintCanvas.height = height;
        paintCtx = paintCanvas.getContext('2d');
        if (previous.width && previous.height) paintCtx.drawImage(previous, 0, 0, previous.width, previous.height, 0, 0, width, height);
    }

    function initializeMask() {
        var maxSide = 1000;
        var ratio = Math.min(maxSide / image.naturalWidth, maxSide / image.naturalHeight, 1);
        maskCanvas.width = Math.max(1, Math.round(image.naturalWidth * ratio));
        maskCanvas.height = Math.max(1, Math.round(image.naturalHeight * ratio));
        maskCtx = maskCanvas.getContext('2d', { willReadFrequently: true });
        maskCtx.clearRect(0, 0, maskCanvas.width, maskCanvas.height);
        maskCtx.fillStyle = '#ffffff';
        maskCtx.fillRect(0, 0, maskCanvas.width, maskCanvas.height);
    }

    function transformContext(targetCtx, scale) {
        targetCtx.translate(state.width / 2 + state.offsetX, state.height / 2 + state.offsetY);
        targetCtx.rotate(state.rotation * Math.PI / 180);
        targetCtx.scale(state.flipX, state.flipY);
        return scale;
    }

    function draw(scheduleEstimate) {
        if (!image || !state) return;
        canvas.width = state.width;
        canvas.height = state.height;
        ensurePaintSize(state.width, state.height);
        compositeCanvas.width = state.width;
        compositeCanvas.height = state.height;
        ctx.imageSmoothingEnabled = true; ctx.imageSmoothingQuality = 'high';
        compositeCtx.imageSmoothingEnabled = true; compositeCtx.imageSmoothingQuality = 'high';
        paintCtx.imageSmoothingEnabled = true; paintCtx.imageSmoothingQuality = 'high';
        compositeCtx.clearRect(0, 0, state.width, state.height);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (state.background !== 'transparent') {
            ctx.fillStyle = state.background;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        var scale = baseScale(state.mode) * state.zoom;
        compositeCtx.save();
        transformContext(compositeCtx, scale);
        compositeCtx.drawImage(image, -image.naturalWidth * scale / 2, -image.naturalHeight * scale / 2, image.naturalWidth * scale, image.naturalHeight * scale);
        compositeCtx.globalCompositeOperation = 'destination-in';
        compositeCtx.drawImage(maskCanvas, -image.naturalWidth * scale / 2, -image.naturalHeight * scale / 2, image.naturalWidth * scale, image.naturalHeight * scale);
        compositeCtx.restore();
        compositeCtx.globalCompositeOperation = 'source-over';
        ctx.drawImage(compositeCanvas, 0, 0);
        ctx.drawImage(paintCanvas, 0, 0);

        if (lassoPoints.length > 0) {
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(lassoPoints[0].x, lassoPoints[0].y);
            lassoPoints.slice(1).forEach(function (point) { ctx.lineTo(point.x, point.y); });
            if (activeTool === 'polygon' && lassoPoints.length > 2) ctx.lineTo(lassoPoints[0].x, lassoPoints[0].y);
            ctx.strokeStyle = '#0d6efd'; ctx.lineWidth = Math.max(2, state.width / 500);
            ctx.setLineDash([10, 7]); ctx.stroke(); ctx.restore();
            if (activeTool === 'polygon') {
                ctx.save(); ctx.fillStyle = '#ffffff'; ctx.strokeStyle = '#0d6efd'; ctx.lineWidth = Math.max(2, state.width / 700);
                lassoPoints.forEach(function (point) { ctx.beginPath(); ctx.arc(point.x, point.y, Math.max(4, state.width / 220), 0, Math.PI * 2); ctx.fill(); ctx.stroke(); });
                ctx.restore();
            }
        }

        canvas.style.aspectRatio = state.width + ' / ' + state.height;
        modalElement.querySelector('[data-image-output-label]').textContent = state.width + ' × ' + state.height + ' px';
        modalElement.querySelector('[data-image-final-size]').textContent = state.width + ' × ' + state.height + ' px';
        modalElement.querySelector('[data-image-zoom-value]').textContent = Math.round(state.zoom * 100) + '%';
        modalElement.querySelector('[data-image-quality-value]').textContent = Math.round(state.quality * 100) + '%';
        widthInput.value = state.width;
        heightInput.value = state.height;
        zoomInput.value = Math.round(state.zoom * 100);
        qualityInput.value = Math.round(state.quality * 100);
        modalElement.querySelectorAll('[data-image-mode]').forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.imageMode === state.mode);
        });
        modalElement.querySelectorAll('[data-image-background]').forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.imageBackground === state.background);
        });
        modalElement.querySelector('[data-image-action="undo"]').disabled = history.length === 0;
        modalElement.querySelector('[data-image-action="redo"]').disabled = future.length === 0;
        syncGrid();
        if (scheduleEstimate !== false) scheduleSizeEstimate();
    }

    function canvasBlob(quality) {
        return new Promise(function (resolve, reject) {
            try {
                canvas.toBlob(function (blob) {
                    if (blob) resolve(blob);
                    else reject(new Error('Não foi possível gerar a imagem.'));
                }, 'image/webp', quality);
            } catch (error) {
                reject(error);
            }
        });
    }

    function scheduleSizeEstimate() {
        clearTimeout(estimateTimer);
        var label = modalElement.querySelector('[data-image-estimated-size]');
        label.textContent = 'Calculando…';
        estimateTimer = setTimeout(function () {
            canvasBlob(state.quality).then(function (blob) {
                label.textContent = 'aprox. ' + formatBytes(blob.size);
            }).catch(function () { label.textContent = 'WebP otimizado'; });
        }, 260);
    }

    function snapshot() {
        return {
            state: copyState(state),
            mask: maskCanvas.width ? maskCanvas.toDataURL('image/png') : null,
            paint: paintCanvas.width ? paintCanvas.toDataURL('image/png') : null
        };
    }

    function commit() {
        if (!state) return;
        history.push(snapshot());
        if (history.length > 24) history.shift();
        future = [];
    }

    function restore(snapshotValue) {
        state = copyState(snapshotValue.state || snapshotValue);
        ensurePaintSize(state.width, state.height);
        var pending = [];
        if (snapshotValue.mask) {
            pending.push(loadImage(snapshotValue.mask).then(function (savedMask) {
                maskCtx.clearRect(0, 0, maskCanvas.width, maskCanvas.height);
                maskCtx.drawImage(savedMask, 0, 0, maskCanvas.width, maskCanvas.height);
            }));
        }
        if (snapshotValue.paint) {
            pending.push(loadImage(snapshotValue.paint).then(function (savedPaint) {
                paintCtx.clearRect(0, 0, paintCanvas.width, paintCanvas.height);
                paintCtx.drawImage(savedPaint, 0, 0, paintCanvas.width, paintCanvas.height);
            }));
        }
        Promise.all(pending).then(function () { draw(); });
        if (!pending.length) draw();
    }

    function setDefaultFit(mode) {
        state.mode = mode || editor.mode;
        state.zoom = 1;
        state.offsetX = 0;
        state.offsetY = 0;
        state.rotation = 0;
        state.flipX = 1;
        state.flipY = 1;
    }

    function detectSubject(sourceImage) {
        var sample = document.createElement('canvas');
        var maxSide = 240;
        var scale = Math.min(maxSide / sourceImage.naturalWidth, maxSide / sourceImage.naturalHeight, 1);
        sample.width = Math.max(1, Math.round(sourceImage.naturalWidth * scale));
        sample.height = Math.max(1, Math.round(sourceImage.naturalHeight * scale));
        var sampleCtx = sample.getContext('2d', { willReadFrequently: true });
        sampleCtx.drawImage(sourceImage, 0, 0, sample.width, sample.height);
        var pixels;
        try { pixels = sampleCtx.getImageData(0, 0, sample.width, sample.height).data; }
        catch (error) { return null; }

        var corners = [[1, 1], [sample.width - 2, 1], [1, sample.height - 2], [sample.width - 2, sample.height - 2]];
        var bg = [0, 0, 0];
        corners.forEach(function (point) {
            var i = (Math.max(0, point[1]) * sample.width + Math.max(0, point[0])) * 4;
            bg[0] += pixels[i]; bg[1] += pixels[i + 1]; bg[2] += pixels[i + 2];
        });
        bg = bg.map(function (value) { return value / corners.length; });

        var minX = sample.width, minY = sample.height, maxX = -1, maxY = -1, count = 0;
        for (var y = 0; y < sample.height; y++) {
            for (var x = 0; x < sample.width; x++) {
                var index = (y * sample.width + x) * 4;
                var alpha = pixels[index + 3];
                var distance = Math.sqrt(Math.pow(pixels[index] - bg[0], 2) + Math.pow(pixels[index + 1] - bg[1], 2) + Math.pow(pixels[index + 2] - bg[2], 2));
                if (alpha > 20 && (alpha < 245 || distance > 38)) {
                    minX = Math.min(minX, x); maxX = Math.max(maxX, x);
                    minY = Math.min(minY, y); maxY = Math.max(maxY, y); count++;
                }
            }
        }
        if (count < sample.width * sample.height * .008 || maxX <= minX || maxY <= minY) return null;
        return {
            x: minX / scale,
            y: minY / scale,
            width: (maxX - minX + 1) / scale,
            height: (maxY - minY + 1) / scale
        };
    }

    function removeBackground() {
        commit();
        var sample = document.createElement('canvas');
        sample.width = maskCanvas.width; sample.height = maskCanvas.height;
        var sampleCtx = sample.getContext('2d', { willReadFrequently: true });
        sampleCtx.drawImage(image, 0, 0, sample.width, sample.height);
        var source;
        try { source = sampleCtx.getImageData(0, 0, sample.width, sample.height); }
        catch (error) { toast('error', 'O navegador não permitiu analisar esta imagem.'); return; }

        var pixels = source.data;
        var cornerSize = Math.max(2, Math.round(Math.min(sample.width, sample.height) * .018));
        var bg = [0, 0, 0], bgCount = 0;
        [[0, 0], [sample.width - cornerSize, 0], [0, sample.height - cornerSize], [sample.width - cornerSize, sample.height - cornerSize]].forEach(function (origin) {
            for (var cy = 0; cy < cornerSize; cy++) {
                for (var cx = 0; cx < cornerSize; cx++) {
                    var ci = ((origin[1] + cy) * sample.width + origin[0] + cx) * 4;
                    if (pixels[ci + 3] < 12) continue;
                    bg[0] += pixels[ci]; bg[1] += pixels[ci + 1]; bg[2] += pixels[ci + 2]; bgCount++;
                }
            }
        });
        if (!bgCount) { bg = [255, 255, 255]; bgCount = 1; }
        bg = bg.map(function (value) { return value / bgCount; });
        var backgroundChroma = Math.max(bg[0], bg[1], bg[2]) - Math.min(bg[0], bg[1], bg[2]);

        var sensitivity = number(modalElement.querySelector('[data-image-background-tolerance]').value, 14);
        var removalPass = state.backgroundRemovalPass || 0;
        var threshold = 10 + sensitivity * 1.45 + Math.min(removalPass, 4) * 4;
        var feather = 20;
        var edgeLimit = 30 + sensitivity * .35;
        var total = sample.width * sample.height;
        var visited = new Uint8Array(total);
        var queue = new Int32Array(total);
        var head = 0, tail = 0;

        function distanceAt(position) {
            var i = position * 4;
            if (pixels[i + 3] < 15) return 0;
            return Math.sqrt(Math.pow(pixels[i] - bg[0], 2) + Math.pow(pixels[i + 1] - bg[1], 2) + Math.pow(pixels[i + 2] - bg[2], 2));
        }
        function chromaAt(position) {
            var i = position * 4;
            return Math.max(pixels[i], pixels[i + 1], pixels[i + 2]) - Math.min(pixels[i], pixels[i + 1], pixels[i + 2]);
        }
        function colorTransition(first, second) {
            var a = first * 4; var b = second * 4;
            return Math.sqrt(Math.pow(pixels[a] - pixels[b], 2) + Math.pow(pixels[a + 1] - pixels[b + 1], 2) + Math.pow(pixels[a + 2] - pixels[b + 2], 2));
        }
        function localContrast(position) {
            var px = position % sample.width; var py = Math.floor(position / sample.width); var contrast = 0;
            if (px > 0) contrast = Math.max(contrast, colorTransition(position, position - 1));
            if (px + 1 < sample.width) contrast = Math.max(contrast, colorTransition(position, position + 1));
            if (py > 0) contrast = Math.max(contrast, colorTransition(position, position - sample.width));
            if (py + 1 < sample.height) contrast = Math.max(contrast, colorTransition(position, position + sample.width));
            return contrast;
        }
        function enqueue(position, previous) {
            if (visited[position] || distanceAt(position) > threshold + feather) return;
            if (pixels[position * 4 + 3] > 15 && Math.abs(chromaAt(position) - backgroundChroma) > 15 + sensitivity * .25 && distanceAt(position) > threshold * .35) return;
            if (previous !== null && pixels[position * 4 + 3] > 15 && colorTransition(position, previous) > edgeLimit) return;
            if (previous !== null && pixels[position * 4 + 3] > 15 && localContrast(position) > edgeLimit * 1.12) return;
            visited[position] = 1; queue[tail++] = position;
        }
        for (var edgeX = 0; edgeX < sample.width; edgeX++) { enqueue(edgeX, null); enqueue((sample.height - 1) * sample.width + edgeX, null); }
        for (var edgeY = 0; edgeY < sample.height; edgeY++) { enqueue(edgeY * sample.width, null); enqueue(edgeY * sample.width + sample.width - 1, null); }
        while (head < tail) {
            var position = queue[head++];
            var x = position % sample.width; var y = Math.floor(position / sample.width);
            if (x > 0) enqueue(position - 1, position); if (x + 1 < sample.width) enqueue(position + 1, position);
            if (y > 0) enqueue(position - sample.width, position); if (y + 1 < sample.height) enqueue(position + sample.width, position);
        }

        var previousMask = maskCtx.getImageData(0, 0, sample.width, sample.height).data;
        var maskImage = maskCtx.createImageData(sample.width, sample.height);
        var refinement = number(modalElement.querySelector('[data-image-edge-refine]').value, 2);
        for (var p = 0; p < total; p++) {
            var alpha = pixels[p * 4 + 3];
            if (visited[p]) {
                alpha = 0;
            } else if (refinement > 0) {
                var px = p % sample.width; var py = Math.floor(p / sample.width); var touchesBackground = false;
                for (var ny = Math.max(0, py - 1); ny <= Math.min(sample.height - 1, py + 1) && !touchesBackground; ny++) {
                    for (var nx = Math.max(0, px - 1); nx <= Math.min(sample.width - 1, px + 1); nx++) {
                        if (visited[ny * sample.width + nx]) { touchesBackground = true; break; }
                    }
                }
                if (touchesBackground) {
                    var edgeAlpha = clamp((distanceAt(p) - threshold * .35) / (feather + refinement * 3), 0, 1);
                    alpha = Math.round(alpha * edgeAlpha);
                }
            }
            alpha = Math.min(alpha, previousMask[p * 4 + 3]);
            maskImage.data[p * 4] = 255; maskImage.data[p * 4 + 1] = 255; maskImage.data[p * 4 + 2] = 255; maskImage.data[p * 4 + 3] = alpha;
        }
        maskCtx.putImageData(maskImage, 0, 0);
        refineMask(false);
        state.backgroundRemovalPass = removalPass + 1;
        state.background = 'transparent';
        draw();
        toast('success', removalPass
            ? 'Mais resíduos foram removidos. Você pode repetir ou usar a Varinha nas áreas restantes.'
            : 'Fundo removido. Repita para remover mais ou use a Varinha em regiões específicas.');
    }

    function recoverObjectDetails() {
        var width = maskCanvas.width; var height = maskCanvas.height; var total = width * height;
        var sample = document.createElement('canvas'); sample.width = width; sample.height = height;
        var sampleCtx = sample.getContext('2d', { willReadFrequently: true });
        sampleCtx.drawImage(image, 0, 0, width, height);
        var pixels; var mask;
        try {
            pixels = sampleCtx.getImageData(0, 0, width, height).data;
            mask = maskCtx.getImageData(0, 0, width, height);
        } catch (error) {
            toast('error', 'O navegador não permitiu analisar esta imagem.'); return;
        }

        var cornerSize = Math.max(3, Math.round(Math.min(width, height) * .02));
        var backgrounds = [];
        [[0, 0], [width - cornerSize, 0], [0, height - cornerSize], [width - cornerSize, height - cornerSize]].forEach(function (origin) {
            var color = [0, 0, 0]; var count = 0;
            for (var y = 0; y < cornerSize; y++) {
                for (var x = 0; x < cornerSize; x++) {
                    var index = ((origin[1] + y) * width + origin[0] + x) * 4;
                    if (pixels[index + 3] < 12) continue;
                    color[0] += pixels[index]; color[1] += pixels[index + 1]; color[2] += pixels[index + 2]; count++;
                }
            }
            if (count) backgrounds.push([color[0] / count, color[1] / count, color[2] / count]);
        });
        if (!backgrounds.length) backgrounds.push([255, 255, 255]);

        var sensitivity = number(modalElement.querySelector('[data-image-background-tolerance]').value, 14);
        var refineRadius = number(modalElement.querySelector('[data-image-edge-refine]').value, 2);
        var backgroundLimit = 18 + sensitivity * 1.15;
        var neighborLimit = 38 + sensitivity * 1.55;
        var maxDepth = 24 + refineRadius * 12;
        var accepted = new Uint8Array(total); var depth = new Uint16Array(total);
        var queue = new Int32Array(total); var head = 0; var tail = 0; var recovered = 0;

        function rgbDistance(position, color) {
            var index = position * 4;
            return Math.sqrt(Math.pow(pixels[index] - color[0], 2) + Math.pow(pixels[index + 1] - color[1], 2) + Math.pow(pixels[index + 2] - color[2], 2));
        }
        function backgroundDistance(position) {
            var best = Infinity;
            backgrounds.forEach(function (color) { best = Math.min(best, rgbDistance(position, color)); });
            return best;
        }
        function neighborDistance(first, second) {
            var a = first * 4; var b = second * 4;
            return Math.sqrt(Math.pow(pixels[a] - pixels[b], 2) + Math.pow(pixels[a + 1] - pixels[b + 1], 2) + Math.pow(pixels[a + 2] - pixels[b + 2], 2));
        }
        function tryCandidate(candidate, parent) {
            if (accepted[candidate] || pixels[candidate * 4 + 3] < 12) return;
            var distanceFromBackground = backgroundDistance(candidate);
            var existingAlpha = mask.data[candidate * 4 + 3];
            var similarToObject = neighborDistance(candidate, parent) <= neighborLimit;
            if (distanceFromBackground <= backgroundLimit || (!similarToObject && existingAlpha < 24)) return;

            var confidence = clamp((distanceFromBackground - backgroundLimit) / Math.max(1, backgroundLimit * 1.35), .28, 1);
            var restoredAlpha = Math.round(pixels[candidate * 4 + 3] * confidence);
            mask.data[candidate * 4 + 3] = Math.max(existingAlpha, restoredAlpha);
            accepted[candidate] = 1; depth[candidate] = depth[parent] + 1;
            queue[tail++] = candidate; recovered++;
        }

        for (var position = 0; position < total; position++) {
            if (mask.data[position * 4 + 3] >= 96) {
                accepted[position] = 1; queue[tail++] = position;
            }
        }
        while (head < tail) {
            var parent = queue[head++];
            if (depth[parent] >= maxDepth) continue;
            var px = parent % width; var py = Math.floor(parent / width);
            if (px > 0) tryCandidate(parent - 1, parent);
            if (px + 1 < width) tryCandidate(parent + 1, parent);
            if (py > 0) tryCandidate(parent - width, parent);
            if (py + 1 < height) tryCandidate(parent + width, parent);
        }

        if (!recovered) {
            toast('error', 'Não encontrei detalhes seguros para recuperar. Aumente um pouco a suavização ou use Restaurar.');
            return;
        }
        commit();
        maskCtx.putImageData(mask, 0, 0); draw();
        toast('success', 'Detalhes do objeto recuperados sem restaurar o fundo geral. Se precisar, clique novamente.');
    }

    function refineMask(shouldCommit) {
        if (shouldCommit !== false) commit();
        var radius = number(modalElement.querySelector('[data-image-edge-refine]').value, 2);
        if (radius <= 0) { draw(); return; }
        var width = maskCanvas.width; var height = maskCanvas.height;
        var source = maskCtx.getImageData(0, 0, width, height);
        var cleaned = new Uint8ClampedArray(width * height);
        for (var y = 0; y < height; y++) {
            for (var x = 0; x < width; x++) {
                var count = 0; var samples = 0;
                for (var oy = Math.max(0, y - 1); oy <= Math.min(height - 1, y + 1); oy++) {
                    for (var ox = Math.max(0, x - 1); ox <= Math.min(width - 1, x + 1); ox++) {
                        samples++; if (source.data[(oy * width + ox) * 4 + 3] >= 128) count++;
                    }
                }
                var originalAlpha = source.data[(y * width + x) * 4 + 3];
                cleaned[y * width + x] = count <= Math.floor(samples * .28) ? 0 : originalAlpha;
            }
        }
        var stride = width + 1; var integral = new Uint32Array((width + 1) * (height + 1));
        for (var iy = 1; iy <= height; iy++) {
            var rowSum = 0;
            for (var ix = 1; ix <= width; ix++) {
                rowSum += cleaned[(iy - 1) * width + ix - 1];
                integral[iy * stride + ix] = integral[(iy - 1) * stride + ix] + rowSum;
            }
        }
        var result = maskCtx.createImageData(width, height);
        for (var ry = 0; ry < height; ry++) {
            var top = Math.max(0, ry - radius); var bottom = Math.min(height - 1, ry + radius);
            for (var rx = 0; rx < width; rx++) {
                var left = Math.max(0, rx - radius); var right = Math.min(width - 1, rx + radius);
                var sum = integral[(bottom + 1) * stride + right + 1] - integral[top * stride + right + 1] - integral[(bottom + 1) * stride + left] + integral[top * stride + left];
                var average = Math.round(sum / ((right - left + 1) * (bottom - top + 1)));
                average = Math.min(cleaned[ry * width + rx], average);
                if (average < 10) average = 0;
                var index = (ry * width + rx) * 4;
                result.data[index] = 255; result.data[index + 1] = 255; result.data[index + 2] = 255; result.data[index + 3] = average;
            }
        }
        maskCtx.putImageData(result, 0, 0); draw();
    }

    function invertMask() {
        commit();
        var imageData = maskCtx.getImageData(0, 0, maskCanvas.width, maskCanvas.height);
        for (var i = 3; i < imageData.data.length; i += 4) imageData.data[i] = 255 - imageData.data[i];
        maskCtx.putImageData(imageData, 0, 0); draw();
    }

    function restoreMask() {
        commit(); maskCtx.globalCompositeOperation = 'source-over'; maskCtx.fillStyle = '#fff';
        maskCtx.fillRect(0, 0, maskCanvas.width, maskCanvas.height);
        state.backgroundRemovalPass = 0; draw();
    }

    function selectTool(tool) {
        if (activeTool === 'polygon' && tool !== 'polygon' && lassoPoints.length) lassoPoints = [];
        activeTool = tool;
        modalElement.querySelectorAll('[data-image-tool]').forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.imageTool === activeTool);
        });
        canvas.classList.toggle('is-brush', activeTool !== 'move');
        modalElement.querySelector('.sax-image-editor__drag-hint').innerHTML = activeTool === 'move'
            ? '<i class="fa-solid fa-arrows-up-down-left-right"></i> Arraste a imagem'
            : (activeTool === 'lasso' ? '<i class="fa-solid fa-signature"></i> Desenhe ao redor do que deseja manter'
                : (activeTool === 'polygon' ? '<i class="fa-solid fa-draw-polygon"></i> Pontilhe e escolha tirar dentro ou fora'
                    : (activeTool === 'magic' ? '<i class="fa-solid fa-wand-magic-sparkles"></i> Clique na região que deseja remover' : '<i class="fa-solid fa-paintbrush"></i> Pinte diretamente sobre a imagem')));
    }

    function canvasPoint(event) {
        var rect = canvas.getBoundingClientRect();
        return { x: (event.clientX - rect.left) * canvas.width / rect.width, y: (event.clientY - rect.top) * canvas.height / rect.height };
    }

    function sourcePoint(outputPoint) {
        var dx = outputPoint.x - (state.width / 2 + state.offsetX);
        var dy = outputPoint.y - (state.height / 2 + state.offsetY);
        var angle = state.rotation * Math.PI / 180;
        var rotatedX = Math.cos(angle) * dx + Math.sin(angle) * dy;
        var rotatedY = -Math.sin(angle) * dx + Math.cos(angle) * dy;
        rotatedX /= state.flipX; rotatedY /= state.flipY;
        var scale = baseScale(state.mode) * state.zoom;
        return {
            x: (rotatedX / scale + image.naturalWidth / 2) * maskCanvas.width / image.naturalWidth,
            y: (rotatedY / scale + image.naturalHeight / 2) * maskCanvas.height / image.naturalHeight
        };
    }

    function maskBrush(fromOutput, toOutput, restore) {
        var from = sourcePoint(fromOutput); var to = sourcePoint(toOutput);
        var scale = baseScale(state.mode) * state.zoom;
        var size = number(modalElement.querySelector('[data-image-brush-size]').value, 48) / scale * maskCanvas.width / image.naturalWidth;
        maskCtx.save();
        maskCtx.globalCompositeOperation = restore ? 'source-over' : 'destination-out';
        maskCtx.strokeStyle = '#fff'; maskCtx.fillStyle = '#fff'; maskCtx.lineWidth = Math.max(1, size); maskCtx.lineCap = 'round'; maskCtx.lineJoin = 'round';
        maskCtx.beginPath(); maskCtx.moveTo(from.x, from.y); maskCtx.lineTo(to.x, to.y); maskCtx.stroke();
        maskCtx.beginPath(); maskCtx.arc(to.x, to.y, Math.max(1, size / 2), 0, Math.PI * 2); maskCtx.fill(); maskCtx.restore();
    }

    function magicRemove(outputPoint) {
        var seed = sourcePoint(outputPoint);
        var width = maskCanvas.width; var height = maskCanvas.height;
        var seedX = Math.floor(seed.x); var seedY = Math.floor(seed.y);
        if (seedX < 0 || seedY < 0 || seedX >= width || seedY >= height) {
            toast('error', 'Clique dentro da área visível da imagem.'); return;
        }

        var sample = document.createElement('canvas'); sample.width = width; sample.height = height;
        var sampleCtx = sample.getContext('2d', { willReadFrequently: true });
        sampleCtx.drawImage(image, 0, 0, width, height);
        var pixels;
        try { pixels = sampleCtx.getImageData(0, 0, width, height).data; }
        catch (error) { toast('error', 'O navegador não permitiu analisar esta imagem.'); return; }

        var sensitivity = number(modalElement.querySelector('[data-image-background-tolerance]').value, 14);
        var seedPosition = seedY * width + seedX; var seedIndex = seedPosition * 4;
        var seedColor = [pixels[seedIndex], pixels[seedIndex + 1], pixels[seedIndex + 2]];
        var globalLimit = 14 + sensitivity * 2.05;
        var localLimit = 22 + sensitivity * 1.25;
        var total = width * height; var visited = new Uint8Array(total); var queue = new Int32Array(total);
        var head = 0; var tail = 0;

        function colorDistance(position, color) {
            var index = position * 4;
            return Math.sqrt(Math.pow(pixels[index] - color[0], 2) + Math.pow(pixels[index + 1] - color[1], 2) + Math.pow(pixels[index + 2] - color[2], 2));
        }
        function pixelDistance(first, second) {
            var a = first * 4; var b = second * 4;
            return Math.sqrt(Math.pow(pixels[a] - pixels[b], 2) + Math.pow(pixels[a + 1] - pixels[b + 1], 2) + Math.pow(pixels[a + 2] - pixels[b + 2], 2));
        }
        function enqueue(position, previous) {
            if (position < 0 || position >= total || visited[position]) return;
            if (colorDistance(position, seedColor) > globalLimit) return;
            if (previous !== null && pixelDistance(position, previous) > localLimit) return;
            visited[position] = 1; queue[tail++] = position;
        }

        enqueue(seedPosition, null);
        while (head < tail) {
            var position = queue[head++]; var x = position % width; var y = Math.floor(position / width);
            if (x > 0) enqueue(position - 1, position);
            if (x + 1 < width) enqueue(position + 1, position);
            if (y > 0) enqueue(position - width, position);
            if (y + 1 < height) enqueue(position + width, position);
        }
        if (!tail) { toast('error', 'Nenhuma região semelhante foi encontrada.'); return; }

        var current = maskCtx.getImageData(0, 0, width, height);
        for (var p = 0; p < total; p++) {
            if (visited[p]) current.data[p * 4 + 3] = 0;
        }
        maskCtx.putImageData(current, 0, 0);
        refineMask(false); state.background = 'transparent'; draw();
        toast('success', 'Região removida. Clique em outras partes restantes para continuar.');
    }

    function applySelection(removeInside) {
        if (lassoPoints.length < 3) { lassoPoints = []; draw(false); return; }
        var polygon = document.createElement('canvas'); polygon.width = maskCanvas.width; polygon.height = maskCanvas.height;
        var polygonCtx = polygon.getContext('2d'); polygonCtx.fillStyle = '#fff'; polygonCtx.beginPath();
        var first = sourcePoint(lassoPoints[0]); polygonCtx.moveTo(first.x, first.y);
        lassoPoints.slice(1).forEach(function (point) { var source = sourcePoint(point); polygonCtx.lineTo(source.x, source.y); });
        polygonCtx.closePath(); polygonCtx.fill();
        maskCtx.save(); maskCtx.globalCompositeOperation = removeInside ? 'destination-out' : 'destination-in';
        maskCtx.drawImage(polygon, 0, 0); maskCtx.restore();
        lassoPoints = []; state.background = 'transparent'; draw();
        toast('success', removeInside ? 'A área de dentro dos pontos foi removida.' : 'A área de fora dos pontos foi removida.');
    }

    function automaticFit() {
        commit();
        setDefaultFit(editor.mode);
        if (editor.smartSubject && state.rotation === 0) {
            var subject = detectSubject(image);
            if (subject && subject.width < image.naturalWidth * .98 && subject.height < image.naturalHeight * .98) {
                state.mode = 'contain';
                var normalBase = baseScale('contain');
                var desired = Math.min((state.width * .86) / subject.width, (state.height * .86) / subject.height);
                state.zoom = clamp(desired / normalBase, .4, 3);
                var actual = normalBase * state.zoom;
                var subjectCenterX = subject.x + subject.width / 2 - image.naturalWidth / 2;
                var subjectCenterY = subject.y + subject.height / 2 - image.naturalHeight / 2;
                state.offsetX = -subjectCenterX * actual;
                state.offsetY = -subjectCenterY * actual;
            }
        }
        draw();
    }

    async function openEditor(options) {
        if (!options.file && !options.url) {
            toast('error', 'Selecione uma imagem primeiro.');
            return;
        }
        applyButton.disabled = true;
        editor = {
            file: options.file || null,
            url: options.url || null,
            name: options.name || (options.file && options.file.name) || 'imagem',
            width: clamp(parseInt(options.width || 1080, 10), 240, 2000),
            height: clamp(parseInt(options.height || 1080, 10), 240, 2000),
            mode: options.mode === 'cover' ? 'cover' : 'contain',
            smartSubject: Boolean(options.smartSubject),
            onApply: options.onApply
        };
        modal.show();
        modalElement.classList.add('is-loading');
        try {
            image = await loadImage(sourceFor(editor.file, editor.url));
            state = {
                width: editor.width, height: editor.height, mode: editor.mode,
                zoom: 1, offsetX: 0, offsetY: 0, rotation: 0,
                flipX: 1, flipY: 1, background: '#ffffff', quality: .85, backgroundRemovalPass: 0
            };
            initializeMask();
            paintCanvas.width = state.width; paintCanvas.height = state.height;
            paintCtx = paintCanvas.getContext('2d');
            history = []; future = []; ratioLocked = true;
            activeTool = 'move'; lassoPoints = []; painting = false;
            initialState = snapshot();
            modalElement.querySelector('[data-image-file-name]').textContent = fileBaseName(editor.name) + '.webp';
            modalElement.querySelector('[data-image-original-info]').textContent = image.naturalWidth + ' × ' + image.naturalHeight + ' px · original';
            modalElement.querySelector('[data-image-ratio-lock]').classList.add('is-active');
            modalElement.querySelector('[data-image-background-tolerance]').value = 14;
            modalElement.querySelector('[data-image-brush-size]').value = 48;
            modalElement.querySelector('[data-image-edge-refine]').value = 2;
            modalElement.querySelector('[data-image-background-tolerance-value]').textContent = '14%';
            modalElement.querySelector('[data-image-brush-size-value]').textContent = '48 px';
            modalElement.querySelector('[data-image-edge-refine-value]').textContent = '2 px';
            selectTool('move');
            draw();
            if (options.runAutomatic) automaticFit();
            applyButton.disabled = false;
        } catch (error) {
            modal.hide();
            toast('error', error.message || 'Não foi possível abrir esta imagem.');
        } finally {
            modalElement.classList.remove('is-loading');
        }
    }

    function changeZoom(value, shouldCommit) {
        if (shouldCommit) commit();
        state.zoom = clamp(value, .4, 3);
        draw();
    }

    modalElement.addEventListener('click', function (event) {
        var modeButton = event.target.closest('[data-image-mode]');
        if (modeButton && state) {
            commit(); state.mode = modeButton.dataset.imageMode; state.zoom = 1; state.offsetX = 0; state.offsetY = 0; draw(); return;
        }
        var backgroundButton = event.target.closest('[data-image-background]');
        if (backgroundButton && state) {
            commit(); state.background = backgroundButton.dataset.imageBackground; draw(); return;
        }
        var toolButton = event.target.closest('[data-image-tool]');
        if (toolButton && state) { selectTool(toolButton.dataset.imageTool); return; }
        var action = event.target.closest('[data-image-action]');
        if (!action || !state) return;
        switch (action.dataset.imageAction) {
            case 'auto': automaticFit(); break;
            case 'zoom-in': changeZoom(state.zoom + .1, true); break;
            case 'zoom-out': changeZoom(state.zoom - .1, true); break;
            case 'rotate-left': commit(); state.rotation = (state.rotation - 90) % 360; state.offsetX = 0; state.offsetY = 0; draw(); break;
            case 'rotate-right': commit(); state.rotation = (state.rotation + 90) % 360; state.offsetX = 0; state.offsetY = 0; draw(); break;
            case 'flip-horizontal': commit(); state.flipX *= -1; draw(); break;
            case 'flip-vertical': commit(); state.flipY *= -1; draw(); break;
            case 'remove-background': removeBackground(); break;
            case 'recover-details': recoverObjectDetails(); break;
            case 'polygon-remove-outside':
                if (activeTool !== 'polygon' || lassoPoints.length < 3) toast('error', 'Marque pelo menos três pontos ao redor do objeto.');
                else applySelection(false);
                break;
            case 'polygon-remove-inside':
                if (activeTool !== 'polygon' || lassoPoints.length < 3) toast('error', 'Marque pelo menos três pontos ao redor da área.');
                else applySelection(true);
                break;
            case 'refine-mask': refineMask(true); break;
            case 'invert-mask': invertMask(); break;
            case 'restore-mask': restoreMask(); break;
            case 'clear-drawing': commit(); paintCtx.clearRect(0, 0, paintCanvas.width, paintCanvas.height); draw(); break;
            case 'add-text':
                var textInput = modalElement.querySelector('[data-image-text-input]');
                var text = textInput.value.trim();
                if (!text) { toast('error', 'Digite um texto antes de adicionar.'); break; }
                commit();
                var fontSize = clamp(number(modalElement.querySelector('[data-image-text-size]').value, 64), 14, 240);
                paintCtx.save(); paintCtx.fillStyle = modalElement.querySelector('[data-image-text-color]').value;
                paintCtx.textAlign = 'center'; paintCtx.textBaseline = 'middle'; paintCtx.font = '700 ' + fontSize + 'px Inter, Arial, sans-serif';
                var maximumWidth = state.width * .9;
                if (paintCtx.measureText(text).width > maximumWidth) fontSize *= maximumWidth / paintCtx.measureText(text).width;
                paintCtx.font = '700 ' + Math.round(fontSize) + 'px Inter, Arial, sans-serif';
                paintCtx.fillText(text, state.width / 2, state.height / 2, maximumWidth); paintCtx.restore();
                textInput.value = ''; draw(); break;
            case 'reset': commit(); restore(initialState); break;
            case 'undo':
                if (history.length) { future.push(snapshot()); restore(history.pop()); }
                break;
            case 'redo':
                if (future.length) { history.push(snapshot()); restore(future.pop()); }
                break;
        }
    });

    zoomInput.addEventListener('input', function () { state.zoom = number(this.value, 100) / 100; draw(false); });
    zoomInput.addEventListener('change', function () { scheduleSizeEstimate(); });
    qualityInput.addEventListener('input', function () { state.quality = number(this.value, 85) / 100; draw(); });
    modalElement.querySelector('[data-image-background-tolerance]').addEventListener('input', function () {
        modalElement.querySelector('[data-image-background-tolerance-value]').textContent = this.value + '%';
    });
    modalElement.querySelector('[data-image-brush-size]').addEventListener('input', function () {
        modalElement.querySelector('[data-image-brush-size-value]').textContent = this.value + ' px';
    });
    modalElement.querySelector('[data-image-edge-refine]').addEventListener('input', function () {
        modalElement.querySelector('[data-image-edge-refine-value]').textContent = this.value + ' px';
    });
    modalElement.querySelector('[data-image-background-custom]').addEventListener('input', function () {
        state.background = this.value; draw();
    });
    modalElement.querySelector('[data-image-ratio-lock]').addEventListener('click', function () {
        ratioLocked = !ratioLocked; this.classList.toggle('is-active', ratioLocked);
    });
    widthInput.addEventListener('change', function () {
        commit();
        var oldWidth = state.width;
        state.width = clamp(parseInt(this.value || state.width, 10), 240, 2000);
        if (ratioLocked) state.height = clamp(Math.round(state.height * state.width / oldWidth), 240, 2000);
        state.offsetX = 0; state.offsetY = 0; draw();
    });
    heightInput.addEventListener('change', function () {
        commit();
        var oldHeight = state.height;
        state.height = clamp(parseInt(this.value || state.height, 10), 240, 2000);
        if (ratioLocked) state.width = clamp(Math.round(state.width * state.height / oldHeight), 240, 2000);
        state.offsetX = 0; state.offsetY = 0; draw();
    });

    canvas.addEventListener('pointerdown', function (event) {
        if (!state) return;
        if (activeTool === 'polygon') {
            if (!lassoPoints.length) commit();
            lassoPoints.push(canvasPoint(event)); draw(false); return;
        }
        if (activeTool === 'magic') {
            commit(); magicRemove(canvasPoint(event)); return;
        }
        commit(); canvas.setPointerCapture(event.pointerId);
        if (activeTool === 'move') {
            dragging = true; dragPoint = { x: event.clientX, y: event.clientY };
            canvas.classList.add('is-dragging'); grid.classList.add('is-visible'); return;
        }
        painting = true; dragPoint = canvasPoint(event);
        if (activeTool === 'erase' || activeTool === 'restore') {
            maskBrush(dragPoint, dragPoint, activeTool === 'restore'); draw(false);
        } else if (activeTool === 'lasso') {
            lassoPoints = [dragPoint]; draw(false);
        } else if (activeTool === 'draw') {
            paintCtx.save(); paintCtx.fillStyle = modalElement.querySelector('[data-image-draw-color]').value;
            paintCtx.beginPath(); paintCtx.arc(dragPoint.x, dragPoint.y, number(modalElement.querySelector('[data-image-brush-size]').value, 48) / 2, 0, Math.PI * 2); paintCtx.fill(); paintCtx.restore(); draw(false);
        }
    });
    canvas.addEventListener('pointermove', function (event) {
        if (dragging) {
            var factorX = canvas.width / canvas.clientWidth;
            var factorY = canvas.height / canvas.clientHeight;
            state.offsetX += (event.clientX - dragPoint.x) * factorX;
            state.offsetY += (event.clientY - dragPoint.y) * factorY;
            dragPoint = { x: event.clientX, y: event.clientY }; draw(false); return;
        }
        if (!painting) return;
        var point = canvasPoint(event);
        if (activeTool === 'erase' || activeTool === 'restore') {
            maskBrush(dragPoint, point, activeTool === 'restore');
        } else if (activeTool === 'lasso') {
            if (Math.hypot(point.x - dragPoint.x, point.y - dragPoint.y) > 4) lassoPoints.push(point);
        } else if (activeTool === 'draw') {
            paintCtx.save(); paintCtx.strokeStyle = modalElement.querySelector('[data-image-draw-color]').value;
            paintCtx.lineWidth = number(modalElement.querySelector('[data-image-brush-size]').value, 48); paintCtx.lineCap = 'round'; paintCtx.lineJoin = 'round';
            paintCtx.beginPath(); paintCtx.moveTo(dragPoint.x, dragPoint.y); paintCtx.lineTo(point.x, point.y); paintCtx.stroke(); paintCtx.restore();
        }
        dragPoint = point; draw(false);
    });
    function stopDrag() {
        if (!dragging && !painting) return;
        if (activeTool === 'lasso' && painting) applySelection(false);
        dragging = false; painting = false; canvas.classList.remove('is-dragging'); grid.classList.remove('is-visible'); scheduleSizeEstimate();
    }
    canvas.addEventListener('pointerup', stopDrag);
    canvas.addEventListener('pointercancel', stopDrag);
    canvas.addEventListener('dblclick', function (event) {
        if (activeTool !== 'polygon' || lassoPoints.length < 3) return;
        event.preventDefault(); applySelection(false);
    });
    canvas.addEventListener('wheel', function (event) {
        if (!state) return;
        event.preventDefault();
        changeZoom(state.zoom + (event.deltaY < 0 ? .08 : -.08), true);
    }, { passive: false });

    applyButton.addEventListener('click', async function () {
        if (!editor || !state) return;
        applyButton.disabled = true;
        var original = applyButton.innerHTML;
        applyButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando…';
        try {
            draw(false);
            var blob = await canvasBlob(state.quality);
            var file = new File([blob], fileBaseName(editor.name) + '-' + state.width + 'x' + state.height + '.webp', { type: 'image/webp', lastModified: Date.now() });
            await editor.onApply(file, { width: state.width, height: state.height, quality: state.quality });
            modal.hide();
        } catch (error) {
            toast('error', error.message || 'Não foi possível aplicar a edição.');
        } finally {
            applyButton.disabled = false;
            applyButton.innerHTML = original;
        }
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        clearTimeout(estimateTimer);
        if (imageUrl) URL.revokeObjectURL(imageUrl);
        imageUrl = null; image = null; state = null; editor = null;
    });

    function settingsFrom(element) {
        return {
            width: element.dataset.editorWidth || 1080,
            height: element.dataset.editorHeight || 1080,
            mode: element.dataset.editorMode || 'contain',
            smartSubject: element.dataset.editorSmartSubject === '1'
        };
    }

    function assignSingleFile(input, file) {
        var transfer = new DataTransfer(); transfer.items.add(file); input.files = transfer.files; input._saxEditedFile = file;
        var preview = document.getElementById(input.dataset.editorPreview || input.dataset.prev || '');
        if (preview) {
            if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
            var url = URL.createObjectURL(file); preview.src = url; preview.dataset.objectUrl = url;
            document.getElementById('photoPreviewBox')?.style.removeProperty('display');
        }
        var status = document.getElementById(input.dataset.editorStatus || '');
        if (status) status.textContent = file.name + ' · ' + (file.size / 1048576).toFixed(2) + ' MB';
    }

    function openSingle(input, runAutomatic) {
        var selected = input._saxEditedFile || input.files[0] || null;
        var current = input.dataset.editorCurrent || null;
        var settings = settingsFrom(input);
        openEditor(Object.assign({}, settings, {
            file: selected, url: selected ? null : current, name: selected ? selected.name : (current || 'imagem'), runAutomatic: runAutomatic,
            onApply: function (file) { assignSingleFile(input, file); }
        }));
    }

    document.querySelectorAll('[data-sax-image-editor="single"]').forEach(function (input) {
        input.addEventListener('change', function (event) {
            if (!input.files[0]) return;
            event.stopImmediatePropagation();
            assignSingleFile(input, input.files[0]);
            openSingle(input, false);
        }, true);
    });

    document.addEventListener('click', function (event) {
        var open = event.target.closest('[data-image-editor-open-for]');
        var auto = event.target.closest('[data-image-editor-auto-for]');
        var button = open || auto;
        if (!button) return;
        var input = document.getElementById(button.dataset.imageEditorOpenFor || button.dataset.imageEditorAutoFor);
        if (input) openSingle(input, Boolean(auto));
    });

    function GalleryManager(root) {
        this.root = root;
        this.input = document.getElementById(root.dataset.galleryInput);
        this.preview = document.getElementById(root.dataset.galleryPreview);
        this.counter = document.getElementById(root.dataset.galleryCount || '');
        this.max = parseInt(root.dataset.galleryMax || '20', 10);
        this.settings = settingsFrom(root);
        this.files = [];
        this.urls = [];
        this.bind();
        this.render();
    }

    GalleryManager.prototype.existingCount = function () {
        return this.preview.querySelectorAll('[data-gallery-existing], .gallery-preview-item.is-existing').length;
    };

    GalleryManager.prototype.syncInput = function () {
        var transfer = new DataTransfer();
        this.files.forEach(function (file) { transfer.items.add(file); });
        this.input.files = transfer.files;
    };

    GalleryManager.prototype.render = function () {
        var self = this;
        this.urls.forEach(function (url) { URL.revokeObjectURL(url); }); this.urls = [];
        this.preview.querySelectorAll('[data-gallery-pending]').forEach(function (item) { item.remove(); });
        this.files.forEach(function (file, index) {
            var url = URL.createObjectURL(file); self.urls.push(url);
            var item = document.createElement('div');
            item.dataset.galleryPending = '1'; item.dataset.fileIndex = index;
            item.className = self.root.dataset.galleryStyle === 'product' ? 'product-gallery-pending' : 'gallery-preview-item is-pending shadow-sm border';
            item.innerHTML = '<img src="' + url + '" class="w-100 h-100 object-fit-cover" alt="Prévia da imagem">' +
                '<div class="sax-image-pending-actions"><button type="button" data-gallery-edit title="Editar/cortar"><i class="fas fa-crop-simple"></i></button>' +
                '<button type="button" data-gallery-auto title="Ajuste automático"><i class="fas fa-wand-magic-sparkles"></i></button>' +
                '<button type="button" data-gallery-remove title="Remover"><i class="fas fa-times"></i></button></div>';
            self.preview.appendChild(item);
        });
        this.preview.style.display = (this.files.length || this.existingCount()) ? '' : 'none';
        var total = this.existingCount() + this.files.length;
        if (this.counter) {
            if (this.counter.id === 'gallerySelectionCount') this.counter.textContent = this.files.length ? this.files.length + ' nova(s) imagem(ns) selecionada(s)' : 'Nenhuma nova imagem selecionada';
            else this.counter.textContent = total;
        }
    };

    GalleryManager.prototype.add = function (incoming) {
        var self = this;
        var room = Math.max(0, this.max - this.existingCount() - this.files.length);
        if (incoming.length > room) toast('error', 'Você pode ter no máximo ' + this.max + ' imagens nesta galeria.');
        incoming.slice(0, room).forEach(function (file) {
            var duplicate = self.files.some(function (saved) { return saved.name === file.name && saved.size === file.size; });
            if (!duplicate) self.files.push(file);
        });
        this.syncInput(); this.render();
    };

    GalleryManager.prototype.editIndex = function (index, automatic) {
        var self = this; var file = this.files[index]; if (!file) return;
        openEditor(Object.assign({}, this.settings, {
            file: file, name: file.name, runAutomatic: automatic,
            onApply: function (edited) { self.files[index] = edited; self.syncInput(); self.render(); }
        }));
    };

    GalleryManager.prototype.autoFile = async function (file) {
        var imgUrl = URL.createObjectURL(file);
        try {
            var img = await loadImage(imgUrl);
            var output = document.createElement('canvas'); output.width = this.settings.width; output.height = this.settings.height;
            var outputCtx = output.getContext('2d'); outputCtx.fillStyle = '#ffffff'; outputCtx.fillRect(0, 0, output.width, output.height);
            var scale = this.settings.mode === 'cover' ? Math.max(output.width / img.naturalWidth, output.height / img.naturalHeight) : Math.min(output.width / img.naturalWidth, output.height / img.naturalHeight);
            var drawX = (output.width - img.naturalWidth * scale) / 2;
            var drawY = (output.height - img.naturalHeight * scale) / 2;
            if (this.settings.smartSubject) {
                var subject = detectSubject(img);
                if (subject && subject.width < img.naturalWidth * .98 && subject.height < img.naturalHeight * .98) {
                    scale = Math.min((output.width * .86) / subject.width, (output.height * .86) / subject.height);
                    drawX = output.width / 2 - (subject.x + subject.width / 2) * scale;
                    drawY = output.height / 2 - (subject.y + subject.height / 2) * scale;
                }
            }
            outputCtx.drawImage(img, drawX, drawY, img.naturalWidth * scale, img.naturalHeight * scale);
            var blob = await new Promise(function (resolve, reject) {
                output.toBlob(function (result) {
                    if (result) resolve(result);
                    else reject(new Error('Não foi possível gerar a imagem automática.'));
                }, 'image/webp', .85);
            });
            return new File([blob], fileBaseName(file.name) + '-' + output.width + 'x' + output.height + '.webp', { type: 'image/webp', lastModified: Date.now() });
        } finally { URL.revokeObjectURL(imgUrl); }
    };

    GalleryManager.prototype.autoAll = async function (button) {
        if (!this.files.length) { toast('error', 'Selecione imagens novas para ajustar automaticamente.'); return; }
        var original = button.innerHTML; button.disabled = true; var self = this;
        try {
            for (var i = 0; i < this.files.length; i++) {
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Ajustando ' + (i + 1) + '/' + this.files.length;
                this.files[i] = await this.autoFile(this.files[i]);
            }
            this.syncInput(); this.render(); toast('success', 'Imagens ajustadas e otimizadas.');
        } catch (error) { toast('error', 'Não foi possível ajustar uma das imagens.'); }
        finally { button.disabled = false; button.innerHTML = original; }
    };

    GalleryManager.prototype.editExisting = function (button) {
        var self = this; var item = button.closest('[data-gallery-existing], .gallery-preview-item.is-existing, .gallery-item');
        var source = button.dataset.imageSource; var path = button.dataset.imagePath || '';
        var parentModal = button.closest('.modal');
        if (parentModal) bootstrap.Modal.getInstance(parentModal)?.hide();
        openEditor(Object.assign({}, this.settings, {
            url: source, name: path || source, runAutomatic: false,
            onApply: function (file) {
                if (self.existingCount() + self.files.length > self.max) throw new Error('Limite da galeria atingido.');
                self.files.push(file); self.syncInput();
                if (path && self.root.dataset.galleryRemoveName) {
                    var hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = self.root.dataset.galleryRemoveName; hidden.value = path; self.root.appendChild(hidden);
                }
                item?.remove(); self.render();
            }
        }));
    };

    GalleryManager.prototype.bind = function () {
        var self = this;
        this.input.addEventListener('change', function (event) {
            event.stopImmediatePropagation(); self.add(Array.from(self.input.files));
        }, true);
        this.root.addEventListener('click', function (event) {
            var item = event.target.closest('[data-gallery-pending]');
            if (item) {
                var index = parseInt(item.dataset.fileIndex, 10);
                if (event.target.closest('[data-gallery-remove]')) { event.stopImmediatePropagation(); self.files.splice(index, 1); self.syncInput(); self.render(); }
                else if (event.target.closest('[data-gallery-edit]')) self.editIndex(index, false);
                else if (event.target.closest('[data-gallery-auto]')) self.editIndex(index, true);
                return;
            }
            var existing = event.target.closest('[data-image-edit-existing]');
            if (existing) { event.stopImmediatePropagation(); self.editExisting(existing); }
        }, true);
        this.root.querySelector('[data-gallery-auto-all]')?.addEventListener('click', function () { self.autoAll(this); });
    };

    var galleryManagers = [];
    document.querySelectorAll('[data-sax-image-gallery]').forEach(function (root) {
        var manager = new GalleryManager(root); root._saxGalleryManager = manager; galleryManagers.push(manager);
    });
    window.resetBlogGalleryStore = function () {
        galleryManagers.forEach(function (manager) {
            if (manager.input.id === 'blogGalleryInput') { manager.files = []; manager.syncInput(); manager.render(); }
        });
    };

    window.SaxImageEditor = { open: openEditor };
})();
