<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/asistencia-induccion/update/' . $inspeccion['id'] : '/inspecciones/asistencia-induccion/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="asistIndForm">
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
                    <label class="form-label">Fecha sesion *</label>
                    <input type="date" name="fecha_sesion" class="form-control"
                        value="<?= $inspeccion['fecha_sesion'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>

        <!-- INFORMACION DE LA SESION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">INFORMACION DE LA SESION</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tema *</label>
                    <input type="text" name="tema" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['tema'] ?? '') ?>" placeholder="Tema de la sesion" required>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Lugar</label>
                    <input type="text" name="lugar" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['lugar'] ?? '') ?>" placeholder="Lugar de la sesion">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Objetivo</label>
                    <textarea name="objetivo" class="form-control form-control-sm" rows="2"
                        placeholder="Objetivo de la sesion..."><?= esc($inspeccion['objetivo'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Capacitador</label>
                    <input type="text" name="capacitador" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['capacitador'] ?? '') ?>" placeholder="Nombre del capacitador">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tipo de charla *</label>
                    <select name="tipo_charla" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($tiposCharla as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($inspeccion['tipo_charla'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Material</label>
                    <input type="text" name="material" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['material'] ?? '') ?>" placeholder="Material utilizado">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tiempo (horas)</label>
                    <input type="number" name="tiempo_horas" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['tiempo_horas'] ?? '') ?>" placeholder="0.0" step="0.5" min="0">
                </div>
            </div>
        </div>

        <!-- ASISTENTES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ASISTENTES</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:12px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%;">#</th>
                                <th>Nombre</th>
                                <th>Cedula</th>
                                <th>Cargo</th>
                                <th style="width:8%;">Acc.</th>
                            </tr>
                        </thead>
                        <tbody id="asistentesBody">
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="addAsistenteRow()">
                    <i class="fas fa-plus"></i> Agregar asistente
                </button>
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
                onclick="return confirm('Finalizar asistencia? Se generara el PDF y no podra editarse.')">
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

    // Load existing asistentes if editing
    <?php if ($isEdit && !empty($asistentes)): ?>
    <?php foreach ($asistentes as $a): ?>
    addAsistenteRow('<?= esc(addslashes($a['nombre'])) ?>', '<?= esc(addslashes($a['cedula'])) ?>', '<?= esc(addslashes($a['cargo'])) ?>');
    <?php endforeach; ?>
    <?php else: ?>
    // Add one empty row by default for new records
    addAsistenteRow();
    <?php endif; ?>

    // Autoguardado localStorage
    const formId = '<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const STORAGE_KEY = 'asist_ind_draft_' + formId;
    const form = document.getElementById('asistIndForm');
    let debounceTimer;

    function saveDraft() {
        const data = {};
        form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea, select').forEach(el => {
            if (el.name && el.type !== 'file') data[el.name] = el.value;
        });
        // Save asistentes arrays
        const nombres = [];
        const cedulas = [];
        const cargos = [];
        form.querySelectorAll('input[name="asistente_nombre[]"]').forEach(el => nombres.push(el.value));
        form.querySelectorAll('input[name="asistente_cedula[]"]').forEach(el => cedulas.push(el.value));
        form.querySelectorAll('input[name="asistente_cargo[]"]').forEach(el => cargos.push(el.value));
        data._asistentes = { nombres, cedulas, cargos };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function loadDraft() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(name => {
                if (name === '_asistentes') return;
                const el = form.querySelector('[name="' + name + '"]');
                if (el && el.type !== 'file' && !el.value) el.value = data[name];
            });
            // Restore asistentes
            if (data._asistentes && data._asistentes.nombres) {
                // Clear existing rows
                document.getElementById('asistentesBody').innerHTML = '';
                rowCount = 0;
                data._asistentes.nombres.forEach((n, i) => {
                    addAsistenteRow(n, data._asistentes.cedulas[i] || '', data._asistentes.cargos[i] || '');
                });
            }
        } catch(e) {}
    }

    form.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    setInterval(saveDraft, 30000);
    form.addEventListener('submit', () => { localStorage.removeItem(STORAGE_KEY); });
    if (!<?= $isEdit ? 'true' : 'false' ?>) loadDraft();
});

let rowCount = 0;
function addAsistenteRow(nombre, cedula, cargo) {
    rowCount++;
    const tbody = document.getElementById('asistentesBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-center align-middle">${rowCount}</td>
        <td><input type="text" name="asistente_nombre[]" class="form-control form-control-sm" value="${nombre || ''}" required></td>
        <td><input type="text" name="asistente_cedula[]" class="form-control form-control-sm" value="${cedula || ''}"></td>
        <td><input type="text" name="asistente_cargo[]" class="form-control form-control-sm" value="${cargo || ''}"></td>
        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAsistenteRow(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
}

function removeAsistenteRow(btn) {
    btn.closest('tr').remove();
    // Re-number rows
    const rows = document.getElementById('asistentesBody').querySelectorAll('tr');
    rowCount = 0;
    rows.forEach(tr => {
        rowCount++;
        tr.querySelector('td:first-child').textContent = rowCount;
    });
}
</script>
