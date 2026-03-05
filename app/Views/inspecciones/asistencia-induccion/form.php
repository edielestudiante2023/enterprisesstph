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

        <!-- EVALUACIÓN (solo inducción/reinducción) -->
        <div class="card mb-3" id="cardEvaluacion" style="display:none;">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">EVALUACIÓN INDUCCIÓN SST</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="evaluacion_habilitada"
                        id="chkEvaluacion" value="1"
                        <?= !empty($inspeccion['evaluacion_habilitada']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="chkEvaluacion" style="font-size:13px;">
                        Habilitar formulario de evaluación para los asistentes
                    </label>
                </div>
                <?php if (!empty($inspeccion['evaluacion_token'])): ?>
                <div id="evalLinkBox" style="<?= !empty($inspeccion['evaluacion_habilitada']) ? '' : 'display:none;' ?>">
                    <small class="text-muted d-block mb-1">Enlace para compartir con los asistentes:</small>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control form-control-sm" id="evalLinkInput"
                            value="<?= base_url('evaluar/' . $inspeccion['evaluacion_token']) ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyEvalLink()" title="Copiar enlace">
                            <i class="fas fa-copy"></i>
                        </button>
                        <a class="btn btn-outline-primary" href="/inspecciones/evaluacion-induccion/resultados/<?= esc($evaluacion['id'] ?? '') ?>"
                            target="_blank" title="Ver resultados">
                            <i class="fas fa-chart-bar"></i>
                        </a>
                    </div>
                    <!-- QR Code -->
                    <div class="text-center mt-1">
                        <img src="/inspecciones/evaluacion-induccion/qr/<?= esc($inspeccion['evaluacion_token']) ?>"
                            alt="QR Evaluación"
                            style="width:160px; height:160px; border:1px solid #e0e0e0; border-radius:8px; padding:6px; background:#fff;">
                        <div style="font-size:11px; color:#999; margin-top:4px;">Escanear para acceder a la evaluación</div>
                    </div>
                </div>
                <?php else: ?>
                <div id="evalLinkBox" style="display:none;">
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Guarda el registro para generar el enlace.</small>
                </div>
                <?php endif; ?>
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

    // Mostrar/ocultar card de evaluación según tipo_charla
    function toggleEvaluacionCard() {
        var tipo = document.querySelector('[name="tipo_charla"]').value;
        var card = document.getElementById('cardEvaluacion');
        card.style.display = (tipo === 'induccion_reinduccion') ? '' : 'none';
    }
    document.querySelector('[name="tipo_charla"]').addEventListener('change', toggleEvaluacionCard);
    toggleEvaluacionCard(); // ejecutar al cargar

    // Mostrar/ocultar link de evaluación según checkbox
    document.getElementById('chkEvaluacion').addEventListener('change', function() {
        document.getElementById('evalLinkBox').style.display = this.checked ? '' : 'none';
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

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE (restaurar borradores)
    // ============================================================
    const STORAGE_KEY = 'asist_ind_draft_<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        Object.keys(data).forEach(name => {
            if (name === '_asistentes' || name === '_savedAt') return;
            const el = document.getElementById('asistIndForm').querySelector('[name="' + name + '"]');
            if (el && el.type !== 'file' && !el.value) el.value = data[name];
        });
        // Restore asistentes
        if (data._asistentes && data._asistentes.nombres) {
            document.getElementById('asistentesBody').innerHTML = '';
            rowCount = 0;
            data._asistentes.nombres.forEach((n, i) => {
                addAsistenteRow(n, data._asistentes.cedulas[i] || '', data._asistentes.cargos[i] || '');
            });
        }
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
        formId: 'asistIndForm',
        storeUrl: '/inspecciones/asistencia-induccion/store',
        updateUrlBase: '/inspecciones/asistencia-induccion/update/',
        editUrlBase: '/inspecciones/asistencia-induccion/edit/',
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
        minFieldsCheck: function() {
            var cliente = document.querySelector('[name="id_cliente"]');
            var fecha = document.querySelector('[name="fecha_sesion"]');
            return cliente && cliente.value && fecha && fecha.value;
        },
    });
});

function copyEvalLink() {
    var input = document.getElementById('evalLinkInput');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(function() {
        Swal.fire({ icon: 'success', title: 'Copiado', text: 'Enlace copiado al portapapeles.', timer: 1500, showConfirmButton: false });
    });
}

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
