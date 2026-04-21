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

    // ── 4) Swal loader fullscreen para submit (manual + programático) ──
    //     Se cierra solo al navegar (redirect del server destruye la página).
    //     A los 30s: mensaje actualizado + botón "Cerrar" para desbloquear al usuario.
    //     NO cerramos automáticamente si el fallback del layout rehabilita botones:
    //     el servidor puede estar procesando legítimamente (Finalizar con PDF+email
    //     tarda 15-30s; guardar borrador con fotos en 3G similar).

    function openSavingModal() {
        if (typeof Swal === 'undefined') return;
        if (Swal.isVisible()) return;

        Swal.fire({
            title: 'Guardando...',
            html: 'No cierres la aplicación',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });

        // A los 30s: tranquilizar al usuario + dar vía de escape manual.
        setTimeout(function () {
            if (!Swal.isVisible()) return;
            Swal.update({
                title: 'Sigue guardando...',
                html: 'Si la conexión está lenta puede tardar hasta 1 minuto. Tus datos están en el formulario.',
                showConfirmButton: true,
                confirmButtonText: 'Cerrar aviso'
            });
        }, 30000);
    }

    // Guardar referencia a openSavingModal para que otras piezas (autosave_server.js)
    // puedan llamarla si hiciera falta.
    window.pwaShowSaving = openSavingModal;

    // 4a) Submit por evento (botones "Guardar borrador"): dispara 'submit' del DOM.
    window.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.dataset.noPwaLoader === '1') return;
        setTimeout(function () {
            if (form.dataset.submitted === 'true') openSavingModal();
        }, 50);
    }, false);

    // 4b) Submit programático (form.submit() tras SweetAlert "¿Está seguro?")
    //     NO dispara el evento 'submit' por spec. Override del prototype para que
    //     también abra el modal. El script se carga solo desde layout_pwa.php, por
    //     lo que este override solo corre dentro del módulo de inspecciones.
    var nativeSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        try {
            if (!this.dataset || this.dataset.noPwaLoader !== '1') {
                if (this.dataset) this.dataset.submitted = 'true';
                openSavingModal();
            }
        } catch (_) { /* no bloquear el submit si algo falla */ }
        return nativeSubmit.apply(this, arguments);
    };
})();
