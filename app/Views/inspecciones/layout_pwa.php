<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#1c2437">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Inspecciones">
    <link rel="manifest" href="<?= base_url('/manifest_inspecciones.json?v=3') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('/icons/icon-192.png?v=3') ?>">
    <title><?= $title ?? 'Inspecciones SST' ?></title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap5 + Responsive -->
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-dark: #1c2437;
            --gold-primary: #bd9751;
            --gold-hover: #a8843f;
            --bg-light: #f5f5f5;
            --text-primary: #333;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg-light);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        /* Top bar */
        .pwa-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: var(--primary-dark);
            color: white;
            display: flex;
            align-items: center;
            padding: 0 12px;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .pwa-topbar .btn-back {
            color: white;
            font-size: 20px;
            padding: 8px 12px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .pwa-topbar .pwa-title {
            flex: 1;
            font-size: 17px;
            font-weight: 600;
            text-align: center;
            margin-right: 44px;
        }

        /* Main content */
        .pwa-main {
            padding-top: 64px;
            padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
            min-height: 100vh;
        }

        /* Bottom nav */
        .pwa-bottomnav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: calc(64px + env(safe-area-inset-bottom, 0px));
            padding-bottom: env(safe-area-inset-bottom, 0px);
            background: var(--primary-dark);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.2);
        }

        .pwa-bottomnav a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            text-align: center;
            font-size: 10px;
            padding: 4px 12px;
            transition: color 0.2s;
        }

        .pwa-bottomnav a.active,
        .pwa-bottomnav a:hover {
            color: var(--gold-primary);
        }

        .pwa-bottomnav a i {
            font-size: 20px;
            display: block;
            margin-bottom: 2px;
        }

        .pwa-bottomnav a.btn-create i {
            font-size: 28px;
            color: var(--gold-primary);
        }

        /* Cards */
        .card-inspeccion {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 12px;
            border: none;
            border-left: 4px solid var(--gold-primary);
            background: white;
        }

        .card-inspeccion.borrador {
            border-left-color: #ffc107;
        }

        .card-inspeccion.pendiente_firma {
            border-left-color: #fd7e14;
        }

        .card-inspeccion.completo {
            border-left-color: #28a745;
        }

        /* Buttons */
        .btn-pwa {
            min-height: 48px;
            font-size: 16px;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-pwa-primary {
            background: var(--gold-primary);
            color: white;
            border: none;
        }

        .btn-pwa-primary:hover, .btn-pwa-primary:active {
            background: var(--gold-hover);
            color: white;
        }

        .btn-pwa-outline {
            background: white;
            color: var(--primary-dark);
            border: 2px solid var(--primary-dark);
        }

        .btn-pwa-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        /* Forms */
        .form-control, .form-select {
            font-size: 16px;
            min-height: 44px;
            border-radius: 8px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }

        /* Accordion */
        .accordion-button {
            font-weight: 600;
            font-size: 15px;
            padding: 14px 16px;
        }

        .accordion-button:not(.collapsed) {
            background: var(--primary-dark);
            color: white;
        }

        /* DataTables PWA overrides */
        .dataTables_wrapper .dataTables_filter input {
            min-height: 40px; font-size: 15px; border-radius: 8px;
            border: 1px solid #ced4da; padding: 6px 12px;
        }
        .dataTables_wrapper .dataTables_length select { min-height: 36px; font-size: 14px; }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { font-size: 13px; }
        table.dataTable td, table.dataTable th { font-size: 13px; vertical-align: middle; }
        table.dataTable thead th { background: #1c2437 !important; color: #bd9751 !important; border-color: #2c3e50 !important; }
        table.dataTable thead th.sorting:after,
        table.dataTable thead th.sorting_asc:after,
        table.dataTable thead th.sorting_desc:after { color: #bd9751; }
        .dataTables_wrapper .page-item.active .page-link { background-color: #bd9751; border-color: #bd9751; }
        .dataTables_wrapper .page-link { color: #1c2437; }

        /* Badge estados */
        .badge-borrador {
            background: #ffc107;
            color: #333;
        }

        .badge-pendiente_firma {
            background: #fd7e14;
            color: white;
        }

        .badge-completo {
            background: #28a745;
            color: white;
        }

        /* Grid de inspecciones */
        .grid-inspecciones {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 0 4px;
        }

        .grid-inspecciones .card-tipo {
            border-radius: 12px;
            padding: 20px 12px;
            text-align: center;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-decoration: none;
            color: var(--text-primary);
            transition: transform 0.2s;
        }

        .grid-inspecciones .card-tipo:active {
            transform: scale(0.96);
        }

        .grid-inspecciones .card-tipo i {
            font-size: 32px;
            color: var(--gold-primary);
            margin-bottom: 8px;
        }

        .grid-inspecciones .card-tipo .count {
            font-size: 13px;
            color: #999;
        }

        .grid-inspecciones .card-tipo.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Section titles */
        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #999;
            letter-spacing: 1px;
            margin: 20px 16px 8px;
        }

        /* Floating home button */
        .fab-home {
            position: fixed;
            bottom: calc(74px + env(safe-area-inset-bottom, 0px));
            left: 16px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gold-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            text-decoration: none;
            z-index: 1001;
            transition: transform 0.2s, background 0.2s;
        }
        .fab-home:hover, .fab-home:active {
            background: var(--gold-hover);
            color: white;
            transform: scale(1.1);
        }

        /* Responsive adjustments */
        @media (max-width: 380px) {
            .grid-inspecciones {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="pwa-topbar">
        <button class="btn-back" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span class="pwa-title"><?= $title ?? 'Inspecciones SST' ?></span>
    </div>

    <!-- Main Content -->
    <div class="pwa-main">
        <?= $content ?>
    </div>

    <!-- Floating Home Button -->
    <a href="<?= site_url('index.php/consultor/dashboard') ?>" class="fab-home" title="Ir al Dashboard">
        <i class="fas fa-house"></i>
    </a>

    <!-- Bottom Navigation -->
    <div class="pwa-bottomnav">
        <a href="<?= site_url('inspecciones') ?>" class="<?= current_url() === base_url('inspecciones') ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
        </a>
        <a href="<?= site_url('inspecciones/acta-visita') ?>" class="<?= strpos(current_url(), 'acta-visita') !== false ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Actas</span>
        </a>
        <a href="<?= site_url('inspecciones/acta-visita/create') ?>" class="btn-create">
            <i class="fas fa-plus-circle"></i>
        </a>
        <a href="<?= site_url('inspecciones/mantenimientos') ?>" class="<?= strpos(current_url(), 'mantenimientos') !== false ? 'active' : '' ?>">
            <i class="fas fa-wrench"></i>
            <span>Mmtos</span>
        </a>
        <a href="<?= site_url('logout') ?>">
            <i class="fas fa-sign-out-alt"></i>
            <span>Salir</span>
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"></script>

    <!-- Flash messages -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('msg')): ?>
        Swal.fire({
            icon: 'success',
            title: '<?= session()->getFlashdata('msg') ?>',
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 2500,
        });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: '<?= session()->getFlashdata('error') ?>',
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 3000,
        });
        <?php endif; ?>
    });
    </script>

    <!-- Anti-duplicación: bloquear botón submit tras primer clic -->
    <script>
    (function() {
        // Rastrear qué botón submit fue tocado (móvil no siempre refleja activeElement)
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('button[type="submit"], input[type="submit"]');
            if (btn && btn.form) {
                btn.form._lastClickedSubmit = btn;
            }
        }, true);

        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // Si ya fue enviado, bloquear (protección anti doble-tap)
            if (form.dataset.submitted === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return;
            }

            // Si image_compressor.js tiene fotos comprimiendo, esperar antes de enviar.
            // Sin esto, los inputs disabled=true durante compresion NO se incluyen en el
            // form data, y las fotos no llegan al servidor.
            var compressing = form.querySelectorAll('input[type="file"][data-compressing="1"]');
            if (compressing.length > 0 && !form.dataset.waitingCompression) {
                e.preventDefault();
                e.stopImmediatePropagation();
                form.dataset.waitingCompression = '1';
                var activeBtn = document.activeElement;
                if (activeBtn && activeBtn.type === 'submit') {
                    var origHtml = activeBtn.innerHTML;
                    activeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Comprimiendo foto...';
                    activeBtn.dataset.origHtmlCompress = origHtml;
                }
                var pending = Array.from(compressing).map(function(input) {
                    return new Promise(function(resolve) {
                        input.addEventListener('compressed', function handler() {
                            input.removeEventListener('compressed', handler);
                            resolve();
                        });
                    });
                });
                Promise.all(pending).then(function() {
                    delete form.dataset.waitingCompression;
                    if (activeBtn && activeBtn.dataset.origHtmlCompress) {
                        activeBtn.innerHTML = activeBtn.dataset.origHtmlCompress;
                        delete activeBtn.dataset.origHtmlCompress;
                    }
                    if (activeBtn && activeBtn.type === 'submit') activeBtn.click();
                });
                return;
            }
            delete form.dataset.waitingCompression;

            // Marcar como enviado
            form.dataset.submitted = 'true';

            // Identificar el botón clickeado y preservar su name/value
            var btns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            var clickedBtn = document.activeElement;
            if (!clickedBtn || (clickedBtn.type !== 'submit' && clickedBtn.tagName !== 'BUTTON')) {
                clickedBtn = form._lastClickedSubmit || btns[0];
            }

            btns.forEach(function(btn) {
                if (btn.name && btn.value && btn === clickedBtn) {
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = btn.name;
                    hidden.value = btn.value;
                    form.appendChild(hidden);
                }
                btn.disabled = true;
            });

            // Solo el botón clickeado muestra spinner
            if (clickedBtn && clickedBtn.type === 'submit') {
                clickedBtn.dataset.originalHtml = clickedBtn.innerHTML;
                clickedBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            }

            // Re-habilitar después de 60s como fallback (por si hay error de red).
            // Finalizar con PDF + email puede tardar 20-30s legítimamente; guardar
            // borrador con fotos en 3G similar. 60s cubre el peor caso esperado.
            setTimeout(function() {
                form.dataset.submitted = '';
                btns.forEach(function(btn) {
                    btn.disabled = false;
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                        delete btn.dataset.originalHtml;
                    }
                });
            }, 60000);
        }, true); // capture phase para interceptar antes que otros handlers
    })();
    </script>

    <!-- Anti doble-tap en links (crear, editar, continuar) -->
    <script src="/js/prevent_double_tap.js"></script>

    <!-- UI unificada de carga de foto (opt-in por clase .foto-input-pwa) -->
    <script src="/js/foto_input_pwa.js?v=3"></script>

    <!-- Compresor de imágenes cliente (antes del FormData) -->
    <script src="/js/image_compressor.js"></script>
    <script>
    // Gancho global: comprime <input type="file"> al seleccionar fotos.
    // Excluir con data-skip-compress="1" en el input si alguna vista no lo requiere.
    (function () {
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input || input.type !== 'file') return;
            if (!input.files || !input.files.length) return;
            if (input.dataset.skipCompress === '1') return;
            if (input.dataset.compressing === '1') return;

            input.dataset.compressing = '1';
            var originalDisabled = input.disabled;
            input.disabled = true;

            (async function () {
                try {
                    var compressed = [];
                    for (var i = 0; i < input.files.length; i++) {
                        compressed.push(await window.compressImage(input.files[i]));
                    }
                    if (typeof DataTransfer === 'function') {
                        var dt = new DataTransfer();
                        compressed.forEach(function (f) { dt.items.add(f); });
                        input.files = dt.files;
                    } else {
                        // Safari iOS viejo: guardar Blobs comprimidos para que autosave los use en FormData.
                        input._compressedFiles = compressed;
                    }
                    input.setAttribute('data-dirty', '1');
                } catch (err) {
                    // Si algo falla, dejar los files originales (el servidor aún comprime).
                    console.warn('compressImage error:', err);
                } finally {
                    input.dataset.compressing = '0';
                    input.disabled = originalDisabled;
                    // Notificar a autosave que los files ya están listos.
                    input.dispatchEvent(new Event('compressed', { bubbles: true }));
                }
            })();
        }, true);
    })();
    </script>

    <!-- Feedback visual PWA (barra online/offline, loader Swal, estilos pill) -->
    <script src="/js/pwa_feedback.js"></script>

    <!-- Autosave Server Engine -->
    <script src="/js/autosave_server.js"></script>

    <!-- Service Worker Registration -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.getRegistrations().then(function(regs) {
                var unregisters = regs
                    .filter(function(r) { return r.scope === location.origin + '/'; })
                    .map(function(r) { return r.unregister(); });
                return Promise.all(unregisters);
            }).then(function() {
                return navigator.serviceWorker.register('/sw_inspecciones.js', { scope: '/inspecciones' });
            }).then(function(reg) {
                console.log('SW registrado, scope:', reg.scope);
            }).catch(function(err) {
                console.log('SW error:', err);
            });
        });
    }
    </script>
</body>
</html>
