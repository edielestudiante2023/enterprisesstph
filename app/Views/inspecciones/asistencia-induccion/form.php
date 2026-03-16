<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/asistencia-induccion/update/') . $inspeccion['id'] : base_url('/inspecciones/asistencia-induccion/store');
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
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0" style="font-size:12px;">Objetivo</label>
                        <button type="button" id="btnGenerarObjetivo" class="btn btn-sm" onclick="generarObjetivoIA()"
                            style="font-size:11px; padding:2px 8px; background:#6c63ff; color:#fff; border:none; border-radius:4px;">
                            ✨ Generar con IA
                        </button>
                    </div>
                    <textarea name="objetivo" id="objetivo" class="form-control form-control-sm" rows="2"
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

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- BOTONES -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Guardar y registrar asistentes' : 'Crear y registrar asistentes' ?>
            </button>
            <?php if ($isEdit): ?>
            <a href="<?= base_url('/inspecciones/asistencia-induccion/registrar/') ?><?= $inspeccion['id'] ?>"
               class="btn btn-pwa py-3" style="font-size:17px;">
                <i class="fas fa-users me-1"></i> Ir a registro de asistentes
            </a>
            <?php endif; ?>
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
        storeUrl: base_url('/inspecciones/asistencia-induccion/store'),
        updateUrlBase: base_url('/inspecciones/asistencia-induccion/update/'),
        editUrlBase: base_url('/inspecciones/asistencia-induccion/edit/'),
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

<?php if (session()->getFlashdata('show_firmas_prompt') && $isEdit): ?>
// ============================================================
// PROMPT PARA IR A FIRMAS (doble validación aritmética)
// ============================================================
(function() {
    const idInspeccion = <?= $inspeccion['id'] ?>;
    const firmasUrl = '<?= base_url('/inspecciones/asistencia-induccion/firmas/') ?>' + idInspeccion;

    function randOp() {
        const ops = [
            function(){ const a = Math.floor(Math.random()*20)+1, b = Math.floor(Math.random()*20)+1; return {q: a+' + '+b, r: a+b}; },
            function(){ const a = Math.floor(Math.random()*20)+5, b = Math.floor(Math.random()*a)+1; return {q: a+' - '+b, r: a-b}; },
            function(){ const a = Math.floor(Math.random()*10)+2, b = Math.floor(Math.random()*10)+2; return {q: a+' × '+b, r: a*b}; }
        ];
        return ops[Math.floor(Math.random()*ops.length)]();
    }

    setTimeout(function() {
        Swal.fire({
            icon: 'success',
            title: 'Guardado exitosamente',
            html: '<p>¿Desea pasar a la pantalla de <strong>firmas</strong> ahora?</p>' +
                  '<p style="font-size:12px; color:#666;">Se requiere doble validación para continuar</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-signature"></i> Ir a Firmas',
            cancelButtonText: 'Seguir editando',
            confirmButtonColor: '#bd9751',
            cancelButtonColor: '#6c757d',
        }).then(result => {
            if (!result.isConfirmed) return;

            const op1 = randOp();
            Swal.fire({
                title: 'Validación 1 de 2',
                html: '<p style="font-size:20px; font-weight:bold;">¿Cuánto es ' + op1.q + '?</p>',
                input: 'number',
                inputPlaceholder: 'Resultado',
                showCancelButton: true,
                confirmButtonText: 'Verificar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#bd9751',
                inputValidator: (val) => {
                    if (!val) return 'Debe ingresar un número';
                    if (parseInt(val) !== op1.r) return 'Respuesta incorrecta. Intente de nuevo.';
                }
            }).then(r1 => {
                if (!r1.isConfirmed) return;

                const op2 = randOp();
                Swal.fire({
                    title: 'Validación 2 de 2',
                    html: '<p style="font-size:20px; font-weight:bold;">¿Cuánto es ' + op2.q + '?</p>',
                    input: 'number',
                    inputPlaceholder: 'Resultado',
                    showCancelButton: true,
                    confirmButtonText: 'Ir a Firmas',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#28a745',
                    inputValidator: (val) => {
                        if (!val) return 'Debe ingresar un número';
                        if (parseInt(val) !== op2.r) return 'Respuesta incorrecta. Intente de nuevo.';
                    }
                }).then(r2 => {
                    if (r2.isConfirmed) {
                        window.location.href = firmasUrl;
                    }
                });
            });
        });
    }, 500);
})();
<?php endif; ?>

// ============================================================
// GENERAR OBJETIVO CON IA
// ============================================================
function generarObjetivoIA() {
    var tema = document.querySelector('[name="tema"]').value.trim();
    if (!tema) {
        Swal.fire({ icon: 'warning', title: 'Falta el tema', text: 'Escribe primero el tema de la sesión.', confirmButtonColor: '#6c63ff' });
        return;
    }
    var btn = document.getElementById('btnGenerarObjetivo');
    btn.disabled = true;
    btn.textContent = '⏳ Generando...';

    fetch('<?= base_url('/inspecciones/asistencia-induccion/generar-objetivo') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ tema: tema })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = '✨ Generar con IA';
        if (data.objetivo) {
            document.getElementById('objetivo').value = data.objetivo;
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo generar el objetivo.', confirmButtonColor: '#6c63ff' });
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = '✨ Generar con IA';
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#6c63ff' });
    });
}
</script>
