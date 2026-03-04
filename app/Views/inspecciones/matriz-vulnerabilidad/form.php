<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/matriz-vulnerabilidad/update/' . $inspeccion['id'] : '/inspecciones/matriz-vulnerabilidad/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="matVulForm">
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

        <!-- CRITERIOS DE EVALUACION -->
        <?php foreach ($criterios as $key => $criterio): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:13px; color:#555;">
                    <?= $criterio['numero'] ?>. <?= strtoupper($criterio['titulo']) ?>
                </h6>
                <?php
                    $currentVal = $inspeccion[$key] ?? '';
                    foreach ($criterio['opciones'] as $opKey => $opText):
                        $radioId = $key . '_' . $opKey;
                        $checked = ($currentVal === $opKey) ? 'checked' : '';
                        $puntaje = ($opKey === 'a') ? '1.0' : (($opKey === 'b') ? '0.5' : '0.0');
                        $badgeColor = ($opKey === 'a') ? 'background:#d4edda;color:#155724;' : (($opKey === 'b') ? 'background:#fff3cd;color:#856404;' : 'background:#f8d7da;color:#721c24;');
                ?>
                <div class="form-check mb-2" style="padding-left:0;">
                    <label class="d-flex align-items-start gap-2" style="cursor:pointer; font-size:13px;" for="<?= $radioId ?>">
                        <input type="radio" name="<?= $key ?>" id="<?= $radioId ?>"
                            value="<?= $opKey ?>" class="form-check-input mt-1" <?= $checked ?>>
                        <span>
                            <span class="badge" style="<?= $badgeColor ?> font-size:10px; margin-right:4px;">
                                <?= strtoupper($opKey) ?> (<?= $puntaje ?>)
                            </span>
                            <?= $opText ?>
                        </span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- OBSERVACIONES DEL CONSULTOR -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES DEL CONSULTOR</h6>
                <textarea name="observaciones" class="form-control" rows="3"
                    placeholder="Observaciones generales..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- BOTONES -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline flex-fill">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary flex-fill"
                onclick="return confirm('Finalizar matriz? Se generara el PDF y no podra editarse.')">
                <i class="fas fa-check-circle"></i> Finalizar
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedCliente = '<?= $idCliente ?? '' ?>';

    $.ajax({
        url: '/inspecciones/api/clientes',
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
    const STORAGE_KEY = 'mat_vul_draft_<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        const form = document.getElementById('matVulForm');
        Object.keys(data).forEach(name => {
            if (name === '_savedAt') return;
            const el = form.querySelector('[name="' + name + '"]');
            if (el && el.type === 'radio') {
                const radio = form.querySelector('[name="' + name + '"][value="' + data[name] + '"]');
                if (radio) radio.checked = true;
            } else if (el && el.type !== 'file' && !el.value) {
                el.value = data[name];
            }
        });
    }

    if (!isEditLocal) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const savedTime = new Date(data._savedAt);
                const hoursAgo = ((Date.now() - savedTime.getTime()) / 3600000).toFixed(1);
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
        formId: 'matVulForm',
        storeUrl: '/inspecciones/matriz-vulnerabilidad/store',
        updateUrlBase: '/inspecciones/matriz-vulnerabilidad/update/',
        editUrlBase: '/inspecciones/matriz-vulnerabilidad/edit/',
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
    });
});
</script>
