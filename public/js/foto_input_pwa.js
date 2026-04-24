/**
 * foto_input_pwa.js — UI unificada de carga de foto para inspecciones PWA.
 *
 * OPT-IN por clase en el input:
 *   <input type="file" name="foto_1" accept="image/*" class="foto-input-pwa">
 *
 * Opcionales en el input:
 *   data-previous-url="uploads/.../foto.jpg"   URL de foto ya guardada en BD (muestra miniatura inicial)
 *   data-delete-name="foto_1__delete"          Nombre del hidden que marca borrado (default: <name>__delete)
 *   data-label="Foto del extintor"             Texto para estado vacio
 *
 * Inserta automaticamente:
 *   - Wrapper con dropzone clickeable
 *   - Preview (miniatura si hay foto, icono upload si no)
 *   - Boton X para limpiar / marcar borrado
 *
 * Conviene con autosave_server.js:
 *   - Cuando el autosave sube la foto, pone input.value='' y remueve data-dirty.
 *   - Un MutationObserver escucha ese clear y restaura el estado "foto existente" si aplica.
 *
 * Para N-ITEMS dinamicos (buildRow), despues de anadir la fila llamar:
 *   window.fotoInputPwa.scan(rowEl);
 */
(function () {
    'use strict';

    var STYLE_ID = 'foto-input-pwa-styles';
    var WRAPPER_CLASS = 'foto-input-pwa-wrap';
    var INITIALIZED = 'fotoPwaInit';

    function injectStyles() {
        if (document.getElementById(STYLE_ID)) return;
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = [
            '.foto-input-pwa-wrap{position:relative;display:block;width:100%;margin:6px 0;}',
            '.foto-input-pwa-wrap input[type="file"].foto-input-pwa{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;pointer-events:none;}',
            '.foto-input-pwa-wrap .fi-dropzone{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:140px;padding:14px;background:#fafafa;border:2px dashed #cfd2d9;border-radius:10px;cursor:pointer;transition:border-color .15s,background .15s;text-align:center;}',
            '.foto-input-pwa-wrap .fi-dropzone:hover,.foto-input-pwa-wrap .fi-dropzone:active{border-color:#bd9751;background:#fff8ea;}',
            '.foto-input-pwa-wrap .fi-dropzone.has-preview{min-height:auto;padding:0;border-style:solid;border-color:#e3e5ea;background:#fff;}',
            '.foto-input-pwa-wrap .fi-icon{font-size:32px;color:#bd9751;margin-bottom:6px;line-height:1;}',
            '.foto-input-pwa-wrap .fi-label{font-size:14px;color:#555;font-weight:600;}',
            '.foto-input-pwa-wrap .fi-hint{font-size:11px;color:#999;margin-top:2px;}',
            '.foto-input-pwa-wrap .fi-thumb{display:block;width:100%;max-height:260px;object-fit:contain;background:#000;border-radius:10px;}',
            '.foto-input-pwa-wrap .fi-remove{position:absolute;top:6px;right:6px;width:34px;height:34px;border-radius:50%;border:none;background:rgba(0,0,0,.65);color:#fff;font-size:20px;line-height:1;cursor:pointer;display:none;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.25);z-index:2;}',
            '.foto-input-pwa-wrap.has-value .fi-remove{display:flex;}',
            '.foto-input-pwa-wrap .fi-spinner{position:absolute;inset:0;display:none;align-items:center;justify-content:center;background:rgba(255,255,255,.85);border-radius:10px;font-size:22px;color:#bd9751;z-index:3;}',
            '.foto-input-pwa-wrap.is-working .fi-spinner{display:flex;}',
            '.foto-input-pwa-wrap .fi-filename{padding:6px 8px;font-size:12px;color:#555;background:#f5f5f5;border-radius:0 0 10px 10px;word-break:break-all;}',
        ].join('');
        document.head.appendChild(style);
    }

    function wrap(input) {
        if (input[INITIALIZED]) return;
        input[INITIALIZED] = true;

        // Normalizar atributos — nunca usar capture (poco confiable por device).
        input.removeAttribute('capture');
        if (!input.hasAttribute('accept')) input.setAttribute('accept', 'image/*');

        var name = input.getAttribute('name') || '';
        var prevUrl = input.getAttribute('data-previous-url') || '';
        var labelText = input.getAttribute('data-label') || 'Toca para subir foto';
        var deleteName = input.getAttribute('data-delete-name') || (name.replace(/\[\]$/, '') + '__delete');

        var wrapper = document.createElement('div');
        wrapper.className = WRAPPER_CLASS;

        var dropzone = document.createElement('label');
        dropzone.className = 'fi-dropzone';
        dropzone.setAttribute('tabindex', '0');

        var emptyView = document.createElement('div');
        emptyView.className = 'fi-empty';
        emptyView.innerHTML =
            '<div class="fi-icon"><i class="fas fa-camera-retro"></i></div>' +
            '<div class="fi-label">' + escapeHtml(labelText) + '</div>' +
            '<div class="fi-hint">JPG / PNG &mdash; se comprime automaticamente</div>';

        var thumb = document.createElement('img');
        thumb.className = 'fi-thumb';
        thumb.alt = '';
        thumb.style.display = 'none';

        dropzone.appendChild(emptyView);
        dropzone.appendChild(thumb);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'fi-remove';
        removeBtn.setAttribute('aria-label', 'Quitar foto');
        removeBtn.innerHTML = '&times;';

        var spinner = document.createElement('div');
        spinner.className = 'fi-spinner';
        spinner.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Hidden para marcar borrado de foto previa en BD (solo se envia si el user dio click en X y habia foto previa).
        var hiddenDelete = null;
        if (prevUrl) {
            hiddenDelete = document.createElement('input');
            hiddenDelete.type = 'hidden';
            hiddenDelete.name = deleteName;
            hiddenDelete.value = '0';
            wrapper.appendChild(hiddenDelete);
        }

        // Insertar el wrapper donde estaba el input. El input va DENTRO del
        // label (dropzone) para que click/tap en el label dispare el file picker.
        input.parentNode.insertBefore(wrapper, input);
        dropzone.appendChild(input);
        wrapper.appendChild(dropzone);
        wrapper.appendChild(removeBtn);
        wrapper.appendChild(spinner);

        // Estado inicial: foto previa en BD?
        if (prevUrl) {
            showThumb(prevUrl);
        }

        function showThumb(src) {
            thumb.src = src;
            thumb.style.display = 'block';
            emptyView.style.display = 'none';
            dropzone.classList.add('has-preview');
            wrapper.classList.add('has-value');
        }
        function showEmpty() {
            thumb.style.display = 'none';
            thumb.removeAttribute('src');
            emptyView.style.display = '';
            dropzone.classList.remove('has-preview');
            wrapper.classList.remove('has-value');
        }

        // Usuario elige archivo -> preview local con object URL.
        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) {
                // Si se vacio (p.ej. cancelo dialogo), volver al estado previo segun prevUrl.
                if (prevUrl && (!hiddenDelete || hiddenDelete.value !== '1')) showThumb(prevUrl);
                else showEmpty();
                return;
            }
            var f = input.files[0];
            if (!f.type || f.type.indexOf('image/') !== 0) {
                // No es imagen: mostrar nombre.
                thumb.style.display = 'none';
                emptyView.style.display = 'none';
                dropzone.classList.add('has-preview');
                wrapper.classList.add('has-value');
                var nameTag = wrapper.querySelector('.fi-filename');
                if (!nameTag) {
                    nameTag = document.createElement('div');
                    nameTag.className = 'fi-filename';
                    dropzone.appendChild(nameTag);
                }
                nameTag.textContent = f.name;
                if (hiddenDelete) hiddenDelete.value = '0';
                return;
            }
            try {
                var url = URL.createObjectURL(f);
                showThumb(url);
                if (hiddenDelete) hiddenDelete.value = '0';
            } catch (_) { /* iOS antiguo */ }
        });

        // Boton X: limpiar seleccion y/o marcar borrado.
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            try { input.value = ''; } catch (_) {}
            try {
                if (typeof DataTransfer === 'function') {
                    input.files = (new DataTransfer()).files;
                }
            } catch (_) {}
            if (input._compressedFiles) input._compressedFiles = null;
            input.removeAttribute('data-dirty');
            if (hiddenDelete) hiddenDelete.value = '1';
            showEmpty();
        });

        // Spinner mientras image_compressor.js comprime.
        input.addEventListener('change', function () {
            if (input.dataset.compressing === '1') wrapper.classList.add('is-working');
        });
        input.addEventListener('compressed', function () {
            wrapper.classList.remove('is-working');
        });

        // Observer: autosave_server.js limpia input.value + remueve data-dirty despues del upload.
        // Detectamos eso para sincronizar el preview.
        var observer = new MutationObserver(function () {
            // El autosave clear: input.files vacio + sin data-dirty.
            if ((!input.files || !input.files.length) && input.getAttribute('data-dirty') !== '1') {
                // El autosave acaba de limpiar: la foto quedo en BD. Volver al estado vacio
                // (el user tendra que recargar para ver la URL nueva — comportamiento consistente con autosave actual).
                if (prevUrl && (!hiddenDelete || hiddenDelete.value !== '1')) showThumb(prevUrl);
                else showEmpty();
            }
        });
        observer.observe(input, { attributes: true, attributeFilter: ['data-dirty', 'value'] });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function scan(root) {
        var ctx = root || document;
        var nodes = ctx.querySelectorAll('input[type="file"].foto-input-pwa');
        nodes.forEach(wrap);
    }

    window.fotoInputPwa = { scan: scan, wrap: wrap };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { injectStyles(); scan(); });
    } else {
        injectStyles();
        scan();
    }
})();
