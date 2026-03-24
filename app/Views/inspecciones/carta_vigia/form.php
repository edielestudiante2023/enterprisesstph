<div class="container-fluid px-3">
    <?php
    $isEdit = !empty($carta);
    $action = $isEdit ? base_url('/inspecciones/carta-vigia/update/') . $carta['id'] : base_url('/inspecciones/carta-vigia/store');
    ?>
    <form method="post" action="<?= $action ?>" id="formCartaVigia">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Cliente -->
        <div class="mb-3 mt-2">
            <label class="form-label">Cliente *</label>
            <select name="id_cliente" id="selectCliente" class="form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                <option value="">Seleccionar cliente...</option>
            </select>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id_cliente" value="<?= esc($carta['id_cliente']) ?>">
            <?php endif; ?>
        </div>

        <!-- Nombre vigía -->
        <div class="mb-3">
            <label class="form-label">Nombre completo del vigia *</label>
            <input type="text" name="nombre_vigia" class="form-control" required
                placeholder="Ej: Juan Carlos Perez Lopez"
                value="<?= $isEdit ? esc($carta['nombre_vigia']) : '' ?>">
        </div>

        <!-- Documento -->
        <div class="mb-3">
            <label class="form-label">Documento de identidad *</label>
            <input type="text" name="documento_vigia" class="form-control" required
                placeholder="Ej: 1234567890" inputmode="numeric"
                value="<?= $isEdit ? esc($carta['documento_vigia']) : '' ?>">
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email_vigia" class="form-control" required
                placeholder="vigia@ejemplo.com"
                value="<?= $isEdit ? esc($carta['email_vigia']) : '' ?>">
        </div>

        <!-- Teléfono -->
        <div class="mb-3">
            <label class="form-label">Telefono</label>
            <input type="tel" name="telefono_vigia" class="form-control"
                placeholder="Ej: 3001234567" inputmode="tel"
                value="<?= $isEdit ? esc($carta['telefono_vigia']) : '' ?>">
        </div>

        <?php if (!$isEdit): ?>
        <div class="alert alert-info" style="font-size: 13px;">
            <i class="fas fa-info-circle"></i>
            Al guardar, se generara la carta de asignacion y se enviara un email al vigia con el enlace para firmarla digitalmente.
        </div>
        <?php endif; ?>

        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;"></div>

        <!-- Botones -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-<?= $isEdit ? 'save' : 'paper-plane' ?>"></i> <?= $isEdit ? 'Guardar Cambios' : 'Generar y Enviar' ?>
            </button>
            <a href="<?= base_url('/inspecciones/carta-vigia') ?><?= $idCliente ? '/cliente/' . $idCliente : '' ?>" class="btn btn-outline-secondary py-3" style="font-size:17px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var select = document.getElementById('selectCliente');
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Buscar cliente...', allowClear: true, width: '100%' });
            <?php if ($idCliente ?? null): ?>
            $('#selectCliente').val('<?= $idCliente ?>').trigger('change.select2');
            <?php endif; ?>
        }
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE (restauracion inicial)
    // ============================================================
    var STORAGE_KEY = 'carta_vigia_draft_<?= $isEdit ? $carta['id'] : 'new' ?>';
    var isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        if (data.nombre_vigia) document.querySelector('[name="nombre_vigia"]').value = data.nombre_vigia;
        if (data.documento_vigia) document.querySelector('[name="documento_vigia"]').value = data.documento_vigia;
        if (data.email_vigia) document.querySelector('[name="email_vigia"]').value = data.email_vigia;
        if (data.telefono_vigia) document.querySelector('[name="telefono_vigia"]').value = data.telefono_vigia;
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
    }

    if (!isEditLocal) {
        try {
            var saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                var data = JSON.parse(saved);
                if (data._savedAt && (Date.now() - new Date(data._savedAt).getTime()) > 24*3600*1000) {
                    localStorage.removeItem(STORAGE_KEY);
                } else {
                    Swal.fire({
                        title: 'Borrador encontrado',
                        text: 'Se encontro un borrador guardado. Desea restaurar los datos?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#bd9751',
                        confirmButtonText: 'Si, restaurar',
                        cancelButtonText: 'No, empezar de cero'
                    }).then(function(result) {
                        if (result.isConfirmed) restoreFromLocal(data);
                        else localStorage.removeItem(STORAGE_KEY);
                    });
                }
            }
        } catch (e) {
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    // ============================================================
    // AUTOGUARDADO SERVIDOR (cada 60s)
    // ============================================================
    initAutosave({
        formId: 'formCartaVigia',
        storeUrl: base_url('/inspecciones/carta-vigia/store'),
        updateUrlBase: base_url('/inspecciones/carta-vigia/update/'),
        editUrlBase: base_url('/inspecciones/carta-vigia/edit/'),
        recordId: <?= isset($carta) ? $carta['id'] : 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
        minFieldsCheck: function() {
            var cliente = document.querySelector('[name="id_cliente"]');
            var nombre = document.querySelector('[name="nombre_vigia"]');
            return cliente && cliente.value && nombre && nombre.value;
        },
    });
});
</script>
