/**
 * Server-Side Autosave Engine para Inspecciones PWA
 *
 * Uso en cada form view:
 *   initAutosave({
 *       formId: 'extForm',
 *       storeUrl: '/inspecciones/extintores/store',
 *       updateUrlBase: '/inspecciones/extintores/update/',
 *       editUrlBase: '/inspecciones/extintores/edit/',
 *       recordId: <?= $inspeccion['id'] ?? 'null' ?>,
 *       isEdit: <?= $isEdit ? 'true' : 'false' ?>,
 *       storageKey: 'ext_draft_<?= $inspeccion['id'] ?? "new" ?>',
 *       detailRowSelector: '.extintor-row',   // solo N-ITEMS
 *       detailIdInputName: 'ext_id[]',        // solo N-ITEMS
 *       intervalSeconds: 60,
 *   });
 */
(function () {
    'use strict';

    window.initAutosave = function (cfg) {
        var form = document.getElementById(cfg.formId);
        if (!form) return;

        var isEdit = !!cfg.isEdit;
        var recordId = cfg.recordId || null;
        var interval = (cfg.intervalSeconds || 60) * 1000;
        var saving = false;
        var submitted = false; // true después de un submit manual → bloquea todo autosave posterior
        var pendingSubmitBtn = null;
        var statusEl = document.getElementById('autoSaveStatus');
        var debounceTimer = null;
        var intervalId = null;

        // ── Marcar file inputs como dirty cuando el usuario selecciona archivo ──
        // ADICIONALMENTE disparar autosave debounce (3s) porque el evento `input`
        // no se dispara con file inputs, entonces sin este trigger las fotos se
        // quedaban sin guardar hasta el intervalo de 60s o el submit manual.
        form.addEventListener('change', function (e) {
            if (e.target.type === 'file' && e.target.files && e.target.files.length > 0) {
                e.target.setAttribute('data-dirty', '1');
                if (!submitted) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(doAutosave, 3000);
                }
            }
        });

        // ── Construir FormData selectivamente ──
        function buildFormData() {
            var fd = new FormData();
            var els = form.elements;

            for (var i = 0; i < els.length; i++) {
                var el = els[i];
                if (!el.name || el.disabled) continue;
                if (el.type === 'file' || el.type === 'submit') continue;
                if (el.type === 'radio' && !el.checked) continue;
                if (el.type === 'checkbox' && !el.checked) continue;
                fd.append(el.name, el.value);
            }

            // Solo incluir file inputs con data-dirty="1" (fotos nuevas)
            // Usar índice explícito para que el servidor reciba el archivo
            // en la posición correcta del detalle (extintor, gabinete, etc.)
            var fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach(function (input) {
                if (input.getAttribute('data-dirty') !== '1') return;

                // Fallback iOS viejo: image_compressor.js guarda los Blobs comprimidos
                // en input._compressedFiles cuando DataTransfer no está disponible.
                var source = (input._compressedFiles && input._compressedFiles.length)
                    ? input._compressedFiles
                    : (input.files && input.files.length ? input.files : null);
                if (!source) return;

                // Multi-foto: si el input tiene data-multi-name="1", preservar el
                // nombre original (incluye [] para que PHP los acumule como array).
                // Esto soporta el patron de multi-foto por piscina (N inputs con
                // mismo name dentro de la misma fila de detalle).
                if (input.getAttribute('data-multi-name') === '1') {
                    for (var j = 0; j < source.length; j++) {
                        var fm = source[j];
                        fd.append(input.name, fm, fm.name || ('photo' + j + '.jpg'));
                    }
                    return;
                }

                var row = cfg.detailRowSelector ? input.closest(cfg.detailRowSelector) : null;
                if (row) {
                    var allRows = form.querySelectorAll(cfg.detailRowSelector);
                    var rowIdx = Array.from(allRows).indexOf(row);
                    var baseName = input.name.replace('[]', '');
                    var f0 = source[0];
                    fd.append(baseName + '[' + rowIdx + ']', f0, f0.name || 'photo.jpg');
                } else {
                    var f = source[0];
                    fd.append(input.name, f, f.name || 'photo.jpg');
                }
            });

            // Nunca enviar finalizar en autosave
            fd.delete('finalizar');

            return fd;
        }

        // ── Verificar campos mínimos para auto-crear ──
        function hasMinFields() {
            if (typeof cfg.minFieldsCheck === 'function') return cfg.minFieldsCheck();
            var cliente = form.querySelector('[name="id_cliente"]');
            var fecha = form.querySelector('[name="fecha_inspeccion"]');
            return cliente && cliente.value && fecha && fecha.value;
        }

        // ── URL destino ──
        function getUrl() {
            return (isEdit && recordId)
                ? cfg.updateUrlBase + recordId
                : cfg.storeUrl;
        }

        // ── Mostrar estado ──
        function showStatus(icon, text, color) {
            if (!statusEl) return;
            statusEl.innerHTML = '<i class="fas ' + icon + '" style="color:' + color + '"></i> ' + text;
        }

        // ── Guardar en localStorage como fallback ──
        function saveToLocalStorage() {
            try {
                var data = { _savedAt: new Date().toISOString() };
                var els = form.elements;
                for (var i = 0; i < els.length; i++) {
                    var el = els[i];
                    if (!el.name || el.type === 'file' || el.type === 'submit') continue;
                    if (el.type === 'radio' && !el.checked) continue;
                    data[el.name] = el.value;
                }
                localStorage.setItem(cfg.storageKey, JSON.stringify(data));
            } catch (e) { /* quota exceeded, etc */ }
        }

        // ── Core: ejecutar autosave ──
        function doAutosave() {
            // No autosave si ya se hizo submit manual o hay uno en curso
            if (saving || submitted) return;

            if (!hasMinFields()) {
                saveToLocalStorage();
                return;
            }

            saving = true;
            showStatus('fa-spinner fa-spin', 'Guardando...', '#999');

            // NO deshabilitar los botones submit: si el usuario hace submit manual
            // durante el autosave, el handler de submit lo detecta (saving=true) y
            // lo pone en pendingSubmitBtn para ejecutarlo al terminar.
            // Dejamos la referencia vacía para mantener el interfaz con .finally().
            var submits = [];

            var fd = buildFormData();
            var url = getUrl();

            var controller = (typeof AbortController === 'function') ? new AbortController() : null;
            var timeoutId = controller ? setTimeout(function () { controller.abort(); }, 45000) : null;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Autosave': '1',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
                credentials: 'same-origin',
                signal: controller ? controller.signal : undefined
            })
            .then(function (resp) {
                if (timeoutId) { clearTimeout(timeoutId); timeoutId = null; }
                if (resp.status === 422) {
                    return resp.json().then(function (data) {
                        var msg = (data && data.message) ? data.message : 'Faltan campos obligatorios';
                        var err = new Error(msg);
                        err.code = 'VALIDATION';
                        throw err;
                    });
                }
                if (!resp.ok) {
                    var err = new Error('HTTP ' + resp.status);
                    err.code = 'HTTP';
                    throw err;
                }
                return resp.json();
            })
            .then(function (data) {
                if (submitted) return; // El usuario ya hizo submit manual mientras esperábamos respuesta
                if (data.success) {
                    showStatus('fa-cloud-upload-alt', 'Guardado ' + (data.saved_at || new Date().toLocaleTimeString()), '#28a745');

                    // Transición create → edit
                    if (!isEdit && data.id) {
                        recordId = data.id;
                        isEdit = true;
                        form.action = cfg.updateUrlBase + data.id;
                        if (cfg.editUrlBase && window.history && window.history.replaceState) {
                            window.history.replaceState(null, '', cfg.editUrlBase + data.id);
                        }
                        // Limpiar localStorage draft de 'new'
                        localStorage.removeItem(cfg.storageKey);
                        // Actualizar storageKey para el nuevo ID
                        cfg.storageKey = cfg.storageKey.replace('_new', '_' + data.id);
                    }

                    // Actualizar hidden IDs de detail rows (N-ITEMS)
                    if (data.detail_ids && cfg.detailRowSelector && cfg.detailIdInputName) {
                        var rows = form.querySelectorAll(cfg.detailRowSelector);
                        rows.forEach(function (row, i) {
                            var idInput = row.querySelector('input[name="' + cfg.detailIdInputName + '"]');
                            if (idInput && data.detail_ids[i] !== undefined) {
                                idInput.value = data.detail_ids[i];
                            }
                        });
                    }

                    // Limpiar dirty flags y file inputs ya subidos
                    form.querySelectorAll('input[type="file"][data-dirty="1"]').forEach(function (input) {
                        input.removeAttribute('data-dirty');
                        input.value = ''; // Evitar re-subir en el próximo tick

                        // Limpiar preview visual de la foto recién subida para que se vean los slots listos para una nueva foto
                        var prevPreview = input.previousElementSibling;
                        if (prevPreview && prevPreview.classList && prevPreview.classList.contains('file-live-preview')) {
                            prevPreview.remove();
                        }
                    });

                } else {
                    showStatus('fa-exclamation-triangle', 'Error: ' + (data.message || ''), '#dc3545');
                }
            })
            .catch(function (err) {
                if (timeoutId) { clearTimeout(timeoutId); timeoutId = null; }
                if (submitted) return;

                var isAbort = err && (err.name === 'AbortError' || err.code === 20);
                var isValidation = err && err.code === 'VALIDATION';
                var isNetwork = err && err.name === 'TypeError';

                if (isValidation) {
                    showStatus('fa-exclamation-triangle', err.message, '#dc3545');
                    // No guardar localStorage: el error es de datos, no de red.
                } else if (isAbort) {
                    showStatus('fa-clock', 'Conexión lenta — guardado local', '#ffc107');
                    saveToLocalStorage();
                } else if (isNetwork || !navigator.onLine) {
                    showStatus('fa-wifi', 'Sin conexión — guardado local', '#dc3545');
                    saveToLocalStorage();
                } else {
                    showStatus('fa-exclamation-triangle', 'Error al guardar', '#dc3545');
                    saveToLocalStorage();
                }
            })
            .finally(function () {
                if (timeoutId) { clearTimeout(timeoutId); timeoutId = null; }
                saving = false;
                submits.forEach(function (btn) { btn.disabled = false; });

                // Si el usuario intentó submit durante autosave, ejecutar ahora
                if (pendingSubmitBtn) {
                    var btn = pendingSubmitBtn;
                    pendingSubmitBtn = null;
                    btn.click();
                }
            });
        }

        // ── Interceptar submit manual para evitar race conditions ──
        form.addEventListener('submit', function (e) {
            // Bloquear todo autosave futuro: el form está navegando
            submitted = true;
            clearTimeout(debounceTimer);
            if (intervalId) { clearInterval(intervalId); intervalId = null; }

            // Limpiar localStorage en submit manual
            localStorage.removeItem(cfg.storageKey);

            if (saving) {
                e.preventDefault();
                var activeBtn = document.activeElement;
                if (activeBtn && (activeBtn.type === 'submit' || activeBtn.tagName === 'BUTTON')) {
                    pendingSubmitBtn = activeBtn;
                }
                showStatus('fa-hourglass-half', 'Esperando guardado...', '#ffc107');
                return false;
            }
        });

        // ── Timer principal: cada 60s ──
        intervalId = setInterval(doAutosave, interval);

        // ── Debounce: guardar 5s después del último input ──
        form.addEventListener('input', function () {
            if (submitted) return;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doAutosave, 5000);
        });

        // ── Select2: guardar 3s después de cambio ──
        if (window.$ && $.fn.select2) {
            $(form).find('select').on('change', function () {
                if (submitted) return;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(doAutosave, 3000);
            });
        }

        // ── Interceptar form.submit() programático (ej: Finalizar vía SweetAlert) ──
        // form.submit() NO dispara el evento 'submit', por lo que submitted=true
        // nunca se setea sin esta sobreescritura, dejando timers activos.
        var nativeFormSubmit = form.submit.bind(form);
        form.submit = function () {
            submitted = true;
            clearTimeout(debounceTimer);
            if (intervalId) { clearInterval(intervalId); intervalId = null; }
            nativeFormSubmit();
        };

        // ── Estado inicial ──
        showStatus('fa-cloud', 'Autoguardado activado', '#999');
    };
})();
