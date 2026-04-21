/**
 * pwa_feedback.js — Feedback visual para PWA Inspecciones.
 *
 * - Barra sticky online/offline debajo de la topbar.
 * - Rediseño del pill #autoSaveStatus (más visible).
 * - Swal.fire loader para submit manual.
 * - Mensaje claro cuando el autosave falla (listener sobre el pill).
 */
(function () {
    'use strict';

    // ── 1) Estilos ──
    var css = '' +
        '#pwaNetBar{position:fixed;left:0;right:0;z-index:1040;display:none;' +
        'text-align:center;font-size:13px;line-height:30px;height:30px;color:#fff;' +
        'font-weight:600;letter-spacing:.2px;box-shadow:0 2px 6px rgba(0,0,0,.15)}' +
        '#pwaNetBar.offline{display:block;background:#dc3545;top:0}' +
        '#pwaNetBar.slow{display:block;background:#fd7e14;top:0}' +
        '#autoSaveStatus{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;' +
        'border-radius:999px;font-size:13px;font-weight:500;background:#f1f3f5;color:#495057;' +
        'border:1px solid #dee2e6;box-shadow:0 1px 2px rgba(0,0,0,.05);white-space:nowrap}' +
        '#autoSaveStatus.state-ok{background:#d1e7dd;color:#0f5132;border-color:#badbcc}' +
        '#autoSaveStatus.state-warn{background:#fff3cd;color:#664d03;border-color:#ffecb5}' +
        '#autoSaveStatus.state-err{background:#f8d7da;color:#842029;border-color:#f5c2c7}' +
        'body.pwa-offline{padding-top:30px}';
    var style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);

    // ── 2) Barra online/offline ──
    var bar = document.createElement('div');
    bar.id = 'pwaNetBar';
    document.body.appendChild(bar);

    function renderNet() {
        if (navigator.onLine) {
            bar.className = '';
            bar.style.display = 'none';
            document.body.classList.remove('pwa-offline');
        } else {
            bar.className = 'offline';
            bar.textContent = 'Sin conexión — los cambios se guardan localmente';
            document.body.classList.add('pwa-offline');
        }
    }
    window.addEventListener('online', renderNet);
    window.addEventListener('offline', renderNet);
    renderNet();

    // ── 3) Observer sobre #autoSaveStatus: colorea según el icono inyectado ──
    function refreshStatusClass() {
        var el = document.getElementById('autoSaveStatus');
        if (!el) return;
        var html = el.innerHTML || '';
        el.classList.remove('state-ok', 'state-warn', 'state-err');
        if (html.indexOf('color:#28a745') !== -1) el.classList.add('state-ok');
        else if (html.indexOf('color:#dc3545') !== -1) el.classList.add('state-err');
        else if (html.indexOf('color:#ffc107') !== -1) el.classList.add('state-warn');
    }
    function watchStatus() {
        var el = document.getElementById('autoSaveStatus');
        if (!el) return;
        refreshStatusClass();
        var mo = new MutationObserver(refreshStatusClass);
        mo.observe(el, { childList: true, characterData: true, subtree: true });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', watchStatus);
    } else {
        watchStatus();
    }

    // ── 4) Swal loader para submit manual (se cierra al navegar) ──
    //     El bloque anti-duplicación ya marca form.dataset.submitted='true'.
    //     Solo abrimos Swal; si algo falla, el setTimeout del layout rehabilita
    //     los botones → ahí también cerramos Swal.
    window.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.dataset.noPwaLoader === '1') return;
        // Abrir loader fullscreen
        if (typeof Swal !== 'undefined') {
            setTimeout(function () {
                // Pequeño delay para no interferir con handlers de validación síncrona
                if (form.dataset.submitted === 'true') {
                    Swal.fire({
                        title: 'Guardando...',
                        html: 'No cierres la aplicación',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function () { Swal.showLoading(); }
                    });
                    // Si en 30s no navegamos ni se cerró, avisar al usuario.
                    setTimeout(function () {
                        if (Swal.isVisible()) {
                            Swal.update({
                                title: 'Sigue guardando...',
                                html: 'Si la conexión es lenta puede tardar. No cierres la app.'
                            });
                        }
                    }, 30000);
                }
            }, 50);
        }
    }, false); // bubble phase — después del handler anti-duplicación en capture

    // ── 5) Si el fallback de 8s del layout rehabilita los botones sin navegar,
    //     interpretamos que algo salió mal y cerramos el Swal con mensaje.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('button[type="submit"], input[type="submit"]');
        if (!btn) return;
        var form = btn.form;
        if (!form) return;

        // Observar cuándo el form.dataset.submitted vuelve a vacío (rehabilitación)
        var tries = 0;
        var t = setInterval(function () {
            tries++;
            if (tries > 20) { clearInterval(t); return; }  // 20 * 500ms = 10s
            if (form.dataset.submitted === '' && typeof Swal !== 'undefined' && Swal.isVisible()) {
                clearInterval(t);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    text: navigator.onLine
                        ? 'El servidor no respondió a tiempo. Tus datos siguen en el formulario — intenta de nuevo.'
                        : 'Sin conexión. Conecta a internet y vuelve a intentar.',
                    confirmButtonText: 'Entendido'
                });
            }
        }, 500);
    }, true);
})();
