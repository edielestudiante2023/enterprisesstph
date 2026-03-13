<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/comunicaciones/update/') . $inspeccion['id'] : base_url('/inspecciones/comunicaciones/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="comForm">
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

        <!-- EQUIPOS DE COMUNICACIÓN -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">EQUIPOS DE COMUNICACION</h6>

                <?php foreach ($equipos as $key => $info): ?>
                <div class="border rounded p-2 mb-3" style="border-color:#dee2e6 !important;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas <?= $info['icon'] ?> text-primary me-2" style="font-size:16px;"></i>
                        <strong style="font-size:13px;"><?= $info['label'] ?></strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:12px;">Cantidad</label>
                        <input type="number" name="cant_<?= $key ?>" class="form-control form-control-sm"
                            value="<?= $inspeccion['cant_' . $key] ?? 0 ?>" min="0">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:12px;">Inspeccion del consultor</label>
                        <textarea name="obs_<?= $key ?>" class="form-control form-control-sm" rows="2"
                            placeholder="Observaciones..."><?= esc($inspeccion['obs_' . $key] ?? '') ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- FOTOS EVIDENCIA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">FOTOS EVIDENCIA</h6>
                <?php foreach (['foto_1' => 'Foto 1', 'foto_2' => 'Foto 2'] as $campo => $label): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;"><?= $label ?></label>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="<?= $campo ?>" class="form-control form-control-sm" accept="image/*">
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- OBSERVACIONES FINALES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES FINALES</h6>
                <textarea name="observaciones_finales" class="form-control" rows="3"
                    placeholder="Observaciones generales..."><?= esc($inspeccion['observaciones_finales'] ?? '') ?></textarea>
            </div>
        </div>

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
    // AUTOGUARDADO EN LOCALSTORAGE (restaurar borradores)
    // ============================================================
    const STORAGE_KEY = 'com_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.observaciones_finales) document.querySelector('[name="observaciones_finales"]').value = data.observaciones_finales;

        const keys = ['telefono_fijo','telefonia_celular','radio_onda_corta','software_citofonia','megafonia','cctv_audio','alarma_comunicacion','voip'];
        keys.forEach(k => {
            if (data['cant_'+k] !== undefined) {
                const el = document.querySelector('[name="cant_'+k+'"]');
                if (el) el.value = data['cant_'+k];
            }
            if (data['obs_'+k] !== undefined) {
                const el = document.querySelector('[name="obs_'+k+'"]');
                if (el) el.value = data['obs_'+k];
            }
        });
    }

    if (!isEditLocal) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const hoursAgo = ((Date.now() - new Date(data._savedAt).getTime()) / 3600000).toFixed(1);
                if (hoursAgo < 24) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Borrador recuperado',
                        html: 'Tienes un borrador guardado hace <strong>' + hoursAgo + ' horas</strong>.<br>Deseas restaurarlo?',
                        showCancelButton: true,
                        confirmButtonText: 'Si, restaurar',
                        cancelButtonText: 'No, empezar de cero',
                        confirmButtonColor: '#bd9751',
                    }).then(result => {
                        if (result.isConfirmed) restoreFromLocal(data);
                        else localStorage.removeItem(STORAGE_KEY);
                    });
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            }
        } catch(e) {}
    }

    // ============================================================
    // AUTOGUARDADO SERVIDOR (cada 60s)
    // ============================================================
    initAutosave({
        formId: 'comForm',
        storeUrl: base_url('/inspecciones/comunicaciones/store'),
        updateUrlBase: base_url('/inspecciones/comunicaciones/update/'),
        editUrlBase: base_url('/inspecciones/comunicaciones/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
    });
});
</script>
