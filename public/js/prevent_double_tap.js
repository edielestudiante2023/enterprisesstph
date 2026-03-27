/**
 * prevent_double_tap.js
 * Previene doble-tap en links y botones que navegan a crear/editar inspecciones.
 * Se inyecta en layout_pwa.php para cubrir todos los 41 modulos.
 */
(function() {
    var COOLDOWN_MS = 2000;
    var lastTapTime = 0;
    var lastTapHref = '';
    var activeOverlay = null;

    // Mostrar overlay de carga sobre el elemento tocado
    function showLoadingOverlay() {
        if (activeOverlay) return;
        activeOverlay = document.createElement('div');
        activeOverlay.id = 'tapLoadingOverlay';
        activeOverlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(28,36,55,0.3);z-index:9999;display:flex;align-items:center;justify-content:center;';
        activeOverlay.innerHTML = '<div style="background:white;border-radius:12px;padding:20px 30px;box-shadow:0 4px 20px rgba(0,0,0,0.3);text-align:center;"><i class="fas fa-spinner fa-spin" style="font-size:24px;color:#bd9751;"></i><div style="margin-top:8px;font-size:13px;color:#666;">Cargando...</div></div>';
        document.body.appendChild(activeOverlay);

        // Auto-remover despues de 5s como fallback
        setTimeout(function() { removeOverlay(); }, 5000);
    }

    function removeOverlay() {
        if (activeOverlay && activeOverlay.parentNode) {
            activeOverlay.parentNode.removeChild(activeOverlay);
        }
        activeOverlay = null;
    }

    // Limpiar overlay cuando la pagina termina de cargar (navegacion exitosa o vuelta atras)
    window.addEventListener('pageshow', function() {
        removeOverlay();
        lastTapTime = 0;
        lastTapHref = '';
    });

    document.addEventListener('click', function(e) {
        var link = e.target.closest('a[href]');
        if (!link) return;

        var href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;

        // Ignorar links que abren en nueva ventana
        if (link.target === '_blank') return;

        var now = Date.now();

        // Si es el mismo link dentro del cooldown, bloquear
        if (href === lastTapHref && (now - lastTapTime) < COOLDOWN_MS) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        // Si hay cualquier navegacion en progreso (overlay activo), bloquear todo
        if (activeOverlay) {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        lastTapTime = now;
        lastTapHref = href;

        // Detectar si es offline y es una ruta que requiere servidor
        if (!navigator.onLine && isServerRoute(href)) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin conexion',
                    text: 'No hay internet. Esta accion requiere conexion al servidor.',
                    confirmButtonColor: '#bd9751',
                    timer: 3000,
                    showConfirmButton: true
                });
            }
            lastTapTime = 0;
            return;
        }

        // Mostrar overlay para feedback visual
        showLoadingOverlay();
    }, true);

    // Rutas que requieren servidor (create, store, edit, delete, finalizar)
    function isServerRoute(href) {
        return /\/(create|store|edit|delete|finalizar|update|registrar)/.test(href);
    }
})();
