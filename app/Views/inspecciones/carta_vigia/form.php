<div class="container-fluid px-3">
    <form method="post" action="/inspecciones/carta-vigia/store" id="formCartaVigia">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Cliente -->
        <div class="mb-3 mt-2">
            <label class="form-label">Cliente *</label>
            <select name="id_cliente" id="selectCliente" class="form-select" required>
                <option value="">Seleccionar cliente...</option>
            </select>
        </div>

        <!-- Nombre vigía -->
        <div class="mb-3">
            <label class="form-label">Nombre completo del vigia *</label>
            <input type="text" name="nombre_vigia" class="form-control" required
                placeholder="Ej: Juan Carlos Perez Lopez">
        </div>

        <!-- Documento -->
        <div class="mb-3">
            <label class="form-label">Documento de identidad *</label>
            <input type="text" name="documento_vigia" class="form-control" required
                placeholder="Ej: 1234567890" inputmode="numeric">
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email_vigia" class="form-control" required
                placeholder="vigia@ejemplo.com">
        </div>

        <!-- Teléfono -->
        <div class="mb-3">
            <label class="form-label">Telefono</label>
            <input type="tel" name="telefono_vigia" class="form-control"
                placeholder="Ej: 3001234567" inputmode="tel">
        </div>

        <div class="alert alert-info" style="font-size: 13px;">
            <i class="fas fa-info-circle"></i>
            Al guardar, se generara la carta de asignacion y se enviara un email al vigia con el enlace para firmarla digitalmente.
        </div>

        <div id="autoguardadoIndicador" class="text-center text-muted mb-3" style="font-size: 12px; display: none;">
            <i class="fas fa-save"></i> Guardado local: <span id="autoguardadoHora"></span>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-paper-plane"></i> Generar y Enviar
            </button>
            <a href="/inspecciones/carta-vigia<?= $idCliente ? '/cliente/' . $idCliente : '' ?>" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#selectCliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        ajax: {
            url: '/inspecciones/api/clientes',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return { results: data.map(function(c) { return { id: c.id_cliente, text: c.nombre_cliente }; }) };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    <?php if ($idCliente && $cliente): ?>
    var optCliente = new Option('<?= esc($cliente['nombre_cliente']) ?>', '<?= $idCliente ?>', true, true);
    $('#selectCliente').append(optCliente).trigger('change');
    <?php endif; ?>

    // -- Autoguardado localStorage --
    var STORAGE_KEY = 'carta_vigia_draft_new';

    function collectFormData() {
        return {
            id_cliente: document.querySelector('[name="id_cliente"]').value,
            nombre_vigia: document.querySelector('[name="nombre_vigia"]').value,
            documento_vigia: document.querySelector('[name="documento_vigia"]').value,
            email_vigia: document.querySelector('[name="email_vigia"]').value,
            telefono_vigia: document.querySelector('[name="telefono_vigia"]').value,
            timestamp: Date.now()
        };
    }

    function saveToLocal() {
        var data = collectFormData();
        if (!data.nombre_vigia && !data.documento_vigia) return;
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        var hora = new Date().toLocaleTimeString();
        document.getElementById('autoguardadoHora').textContent = hora;
        document.getElementById('autoguardadoIndicador').style.display = '';
    }

    function restoreFromLocal() {
        var saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        try {
            var data = JSON.parse(saved);
            if (Date.now() - data.timestamp > 24 * 60 * 60 * 1000) {
                localStorage.removeItem(STORAGE_KEY);
                return;
            }
            Swal.fire({
                title: 'Borrador encontrado',
                text: 'Se encontró un borrador guardado. ¿Desea restaurar los datos?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#bd9751',
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'No, empezar de cero'
            }).then(function(result) {
                if (result.isConfirmed) {
                    if (data.nombre_vigia) document.querySelector('[name="nombre_vigia"]').value = data.nombre_vigia;
                    if (data.documento_vigia) document.querySelector('[name="documento_vigia"]').value = data.documento_vigia;
                    if (data.email_vigia) document.querySelector('[name="email_vigia"]').value = data.email_vigia;
                    if (data.telefono_vigia) document.querySelector('[name="telefono_vigia"]').value = data.telefono_vigia;
                    if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            });
        } catch (e) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    restoreFromLocal();

    setInterval(saveToLocal, 30000);
    var debounceTimer;
    document.getElementById('formCartaVigia').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(saveToLocal, 2000);
    });

    document.getElementById('formCartaVigia').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
