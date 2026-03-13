<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/auditoria-zona-residuos/update/') . $inspeccion['id'] : base_url('/inspecciones/auditoria-zona-residuos/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="audResForm">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('msg') ?>
        </div>
        <?php endif; ?>

        <!-- DATOS GENERALES -->
        <div class="card mt-2 mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
                <div class="mb-3">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" id="selectCliente" class="form-select" required>
                        <option value="">Seleccionar cliente...</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha inspeccion *</label>
                    <input type="date" name="fecha_inspeccion" class="form-control"
                        value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>

        <!-- ITEMS DE INSPECCION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ITEMS DE INSPECCION</h6>
                <?php foreach ($itemsZona as $key => $info): ?>
                <div class="border rounded p-2 mb-3" style="border-color:#dee2e6 !important;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas <?= $info['icon'] ?> text-primary me-2" style="font-size:16px;"></i>
                        <strong style="font-size:13px;"><?= $info['label'] ?></strong>
                    </div>
                    <?php if ($info['tipo'] === 'enum'): ?>
                    <select name="estado_<?= $key ?>" class="form-select form-select-sm mb-2">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($estadosZona as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($inspeccion['estado_' . $key] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <input type="text" name="<?= $key ?>" class="form-control form-control-sm mb-2"
                        value="<?= esc($inspeccion[$key] ?? '') ?>" placeholder="Descripcion...">
                    <?php endif; ?>
                    <div>
                        <label class="form-label" style="font-size:11px; color:#666;">Foto <?= $info['label'] ?></label>
                        <?php if ($isEdit && !empty($inspeccion['foto_' . $key])): ?>
                        <div class="mb-1">
                            <img src="/<?= esc($inspeccion['foto_' . $key]) ?>" class="img-thumbnail" style="max-height:80px;">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="foto_<?= $key ?>" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Observaciones</label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                        placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;"></div>

        <!-- BOTONES -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;"
                onclick="return confirm('Finalizar inspeccion? Se generara el PDF y no podra editarse.')">
                <i class="fas fa-check-circle"></i> Finalizar
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedCliente = '<?= $idCliente ?? '' ?>';

    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (c.id_cliente == selectedCliente) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE (restauracion inicial)
    // ============================================================
    const STORAGE_KEY = 'aud_res_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.observaciones) document.querySelector('[name="observaciones"]').value = data.observaciones;
        <?php foreach ($itemsZona as $key => $info): ?>
        <?php if ($info['tipo'] === 'enum'): ?>
        if (data['estado_<?= $key ?>']) document.querySelector('[name="estado_<?= $key ?>"]').value = data['estado_<?= $key ?>'];
        <?php else: ?>
        if (data['<?= $key ?>']) document.querySelector('[name="<?= $key ?>"]').value = data['<?= $key ?>'];
        <?php endif; ?>
        <?php endforeach; ?>
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
                        confirmButtonText: 'Si, restaurar',
                        cancelButtonText: 'No, empezar de cero',
                        confirmButtonColor: '#bd9751',
                    }).then(result => {
                        if (result.isConfirmed) restoreFromLocal(data);
                        else localStorage.removeItem(STORAGE_KEY);
                    });
                }
            }
        } catch(e) {}
    }

    // ============================================================
    // AUTOGUARDADO SERVIDOR (cada 60s)
    // ============================================================
    initAutosave({
        formId: 'audResForm',
        storeUrl: base_url('/inspecciones/auditoria-zona-residuos/store'),
        updateUrlBase: base_url('/inspecciones/auditoria-zona-residuos/update/'),
        editUrlBase: base_url('/inspecciones/auditoria-zona-residuos/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
    });
});
</script>
