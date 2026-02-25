<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/reporte-capacitacion/update/' . $inspeccion['id'] : '/inspecciones/reporte-capacitacion/store';
$perfilesSeleccionados = [];
if ($isEdit && !empty($inspeccion['perfil_asistentes'])) {
    $perfilesSeleccionados = explode(',', $inspeccion['perfil_asistentes']);
}
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="repCapForm">
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
                    <label class="form-label">Fecha capacitacion *</label>
                    <input type="date" name="fecha_capacitacion" class="form-control"
                        value="<?= $inspeccion['fecha_capacitacion'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>

        <!-- INFORMACION DE LA CAPACITACION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">INFORMACION DE LA CAPACITACION</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Nombre de la capacitacion</label>
                    <input type="text" name="nombre_capacitacion" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['nombre_capacitacion'] ?? '') ?>" placeholder="Nombre de la capacitacion">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Objetivo de la capacitacion</label>
                    <textarea name="objetivo_capacitacion" class="form-control form-control-sm" rows="3"
                        placeholder="Objetivo de la capacitacion..."><?= esc($inspeccion['objetivo_capacitacion'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Perfil de asistentes</label>
                    <?php foreach ($perfilesAsistentes as $key => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="perfil_asistentes[]"
                            value="<?= $key ?>" id="perfil_<?= $key ?>"
                            <?= in_array($key, $perfilesSeleccionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="perfil_<?= $key ?>" style="font-size:13px;">
                            <?= $label ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Nombre del capacitador</label>
                    <input type="text" name="nombre_capacitador" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['nombre_capacitador'] ?? '') ?>" placeholder="Nombre del capacitador">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Horas de duracion</label>
                    <input type="number" name="horas_duracion" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['horas_duracion'] ?? '') ?>" placeholder="Ej: 2.5" step="0.5" min="0">
                </div>
            </div>
        </div>

        <!-- ASISTENCIA Y EVALUACION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ASISTENCIA Y EVALUACION</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Numero de asistentes</label>
                    <input type="number" name="numero_asistentes" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['numero_asistentes'] ?? '') ?>" placeholder="0" min="0">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Numero de programados</label>
                    <input type="number" name="numero_programados" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['numero_programados'] ?? '') ?>" placeholder="0" min="0">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Numero de evaluados</label>
                    <input type="number" name="numero_evaluados" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['numero_evaluados'] ?? '') ?>" placeholder="0" min="0">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Promedio de calificaciones</label>
                    <input type="number" name="promedio_calificaciones" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['promedio_calificaciones'] ?? '') ?>" placeholder="Ej: 4.5" step="0.01" min="0">
                </div>
            </div>
        </div>

        <!-- REGISTRO FOTOGRAFICO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto listado de asistencia</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_listado_asistencia'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_listado_asistencia']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_listado_asistencia" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto capacitacion</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_capacitacion'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_capacitacion']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_capacitacion" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto evaluacion</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_evaluacion'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_evaluacion']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_evaluacion" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto otros 1</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_otros_1'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_otros_1']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_otros_1" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto otros 2</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_otros_2'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_otros_2']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_otros_2" class="form-control form-control-sm" accept="image/*">
                </div>
            </div>
        </div>

        <!-- OBSERVACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
                <div class="mb-2">
                    <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                        placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline flex-fill">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary flex-fill"
                onclick="return confirm('Finalizar reporte? Se generara el PDF y no podra editarse.')">
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

    // Autoguardado localStorage
    const formId = '<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const STORAGE_KEY = 'rep_cap_draft_' + formId;
    const form = document.getElementById('repCapForm');
    let debounceTimer;

    function saveDraft() {
        const data = {};
        form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea, select').forEach(el => {
            if (el.name && el.type !== 'file') data[el.name] = el.value;
        });
        // Save checkboxes
        const perfiles = [];
        form.querySelectorAll('input[name="perfil_asistentes[]"]:checked').forEach(el => {
            perfiles.push(el.value);
        });
        data['perfil_asistentes'] = perfiles;
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function loadDraft() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(name => {
                if (name === 'perfil_asistentes') {
                    // Restore checkboxes
                    if (Array.isArray(data[name])) {
                        data[name].forEach(val => {
                            const cb = form.querySelector('input[name="perfil_asistentes[]"][value="' + val + '"]');
                            if (cb) cb.checked = true;
                        });
                    }
                } else {
                    const el = form.querySelector('[name="' + name + '"]');
                    if (el && el.type !== 'file' && !el.value) el.value = data[name];
                }
            });
        } catch(e) {}
    }

    form.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    form.addEventListener('change', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    setInterval(saveDraft, 30000);
    form.addEventListener('submit', () => { localStorage.removeItem(STORAGE_KEY); });
    if (!<?= $isEdit ? 'true' : 'false' ?>) loadDraft();
});
</script>
