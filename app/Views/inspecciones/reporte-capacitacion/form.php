<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/reporte-capacitacion/update/') . $inspeccion['id'] : base_url('/inspecciones/reporte-capacitacion/store');
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
                    <label class="form-label">Capacitacion del cronograma <small class="text-muted">(opcional)</small></label>
                    <select name="id_cronograma_capacitacion" id="selectCronograma" class="form-select">
                        <option value="">-- Sin vincular a cronograma --</option>
                    </select>
                    <small class="text-muted">Al seleccionar, se llenan los campos automaticamente.</small>
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

        <!-- RESULTADOS EVALUACIÓN INDUCCIÓN SST (read-only, se carga automáticamente) -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">RESULTADOS EVALUACIÓN INDUCCIÓN SST</h6>
                <small class="text-muted d-block mb-2">Se trae automáticamente si existe evaluación para el mismo cliente y fecha.</small>
                <div id="evalResultadosContainer">
                    <p class="text-muted" style="font-size:13px;"><i class="fas fa-info-circle"></i> Seleccione cliente y fecha.</p>
                </div>
            </div>
        </div>

        <!-- LISTADO DE ASISTENCIA (desde Asistencia Induccion) -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">LISTADO DE ASISTENCIA</h6>
                <small class="text-muted d-block mb-2">Se trae automaticamente de Asistencia/Induccion para el mismo cliente y fecha.</small>
                <div id="asistentesContainer">
                    <p class="text-muted" style="font-size:13px;"><i class="fas fa-info-circle"></i> Seleccione cliente y fecha para ver asistentes.</p>
                </div>
            </div>
        </div>

        <!-- REGISTRO FOTOGRAFICO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
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

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- BOTONES -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;"
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

            // Cargar asistentes despues de que Select2 tenga el valor (modo edicion)
            if (selectedCliente && document.querySelector('[name="fecha_capacitacion"]').value) {
                cargarAsistentes(selectedCliente, document.querySelector('[name="fecha_capacitacion"]').value);
            }
        }
    });

    // ============================================================
    // LISTADO DE ASISTENCIA (AJAX)
    // ============================================================
    function cargarAsistentes(idClienteOverride, fechaOverride) {
        var idCliente = idClienteOverride || document.querySelector('[name="id_cliente"]').value;
        var fecha = fechaOverride || document.querySelector('[name="fecha_capacitacion"]').value;
        var container = document.getElementById('asistentesContainer');

        if (!idCliente || !fecha) {
            container.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-info-circle"></i> Seleccione cliente y fecha para ver asistentes.</p>';
            return;
        }

        container.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>';

        $.ajax({
            url: '<?= base_url('/inspecciones/reporte-capacitacion/api-asistentes') ?>',
            data: { id_cliente: idCliente, fecha: fecha },
            dataType: 'json',
            success: function(data) {
                if (!data || data.length === 0) {
                    container.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-exclamation-triangle"></i> No hay registros de asistencia para esta fecha y cliente.</p>';
                    return;
                }
                var html = '<table class="table table-sm table-bordered" style="font-size:13px;">';
                html += '<thead><tr><th>#</th><th>Nombre</th><th>Cedula</th><th>Cargo</th></tr></thead><tbody>';
                data.forEach(function(a, i) {
                    html += '<tr><td>' + (i+1) + '</td><td>' + (a.nombre || '') + '</td><td>' + (a.cedula || '') + '</td><td>' + (a.cargo || '') + '</td></tr>';
                });
                html += '</tbody></table>';
                html += '<small class="text-muted">' + data.length + ' asistente(s) encontrado(s)</small>';
                container.innerHTML = html;
            },
            error: function() {
                container.innerHTML = '<p class="text-danger" style="font-size:13px;"><i class="fas fa-times-circle"></i> Error al cargar asistentes.</p>';
            }
        });
    }

    // ============================================================
    // CRONOGRAMAS PENDIENTES (AJAX)
    // ============================================================
    var cronogramasData = [];
    var selectedCronograma = '<?= $inspeccion['id_cronograma_capacitacion'] ?? '' ?>';

    function cargarCronogramasPendientes(idCliente) {
        var select = document.getElementById('selectCronograma');
        select.innerHTML = '<option value="">-- Sin vincular a cronograma --</option>';
        cronogramasData = [];

        if (!idCliente) return;

        $.ajax({
            url: '<?= base_url('/inspecciones/reporte-capacitacion/api-cronogramas-pendientes') ?>',
            data: { id_cliente: idCliente },
            dataType: 'json',
            success: function(data) {
                cronogramasData = data;
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id_cronograma_capacitacion;
                    var fechaProg = c.fecha_programada ? ' (' + c.fecha_programada + ')' : '';
                    opt.textContent = (c.nombre_capacitacion || 'Sin nombre') + fechaProg;
                    if (c.id_cronograma_capacitacion == selectedCronograma) opt.selected = true;
                    select.appendChild(opt);
                });
                // Auto-seleccionar por fecha si no hay uno ya elegido
                autoMatchCronogramaPorFecha();
            }
        });
    }

    // Auto-fill al seleccionar un cronograma
    document.getElementById('selectCronograma').addEventListener('change', function() {
        var id = this.value;
        if (!id) return;

        var cronog = cronogramasData.find(function(c) { return c.id_cronograma_capacitacion == id; });
        if (!cronog) return;

        // Llenar campos del formulario
        var fields = {
            'nombre_capacitacion': cronog.nombre_capacitacion || '',
            'objetivo_capacitacion': cronog.objetivo_capacitacion || '',
            'nombre_capacitador': cronog.nombre_del_capacitador || '',
            'horas_duracion': cronog.horas_de_duracion_de_la_capacitacion || '',
            'numero_programados': cronog.numero_total_de_personas_programadas || '',
        };

        Object.keys(fields).forEach(function(name) {
            var el = document.querySelector('[name="' + name + '"]');
            if (el) el.value = fields[name];
        });

        // Marcar perfiles de asistentes
        if (cronog.perfil_de_asistentes) {
            var perfiles = cronog.perfil_de_asistentes.toLowerCase().split(',').map(function(s) { return s.trim(); });
            document.querySelectorAll('[name="perfil_asistentes[]"]').forEach(function(cb) {
                cb.checked = perfiles.indexOf(cb.value) !== -1;
            });
        }

        // Si hay fecha programada, usarla como fecha por defecto si no hay fecha ya
        var fechaInput = document.querySelector('[name="fecha_capacitacion"]');
        if (cronog.fecha_programada && (!fechaInput.value || fechaInput.value === '<?= date('Y-m-d') ?>')) {
            fechaInput.value = new Date().toISOString().split('T')[0]; // hoy
        }
    });

    // Cargar cronogramas al inicio si hay cliente seleccionado
    if (selectedCliente) {
        cargarCronogramasPendientes(selectedCliente);
    }

    // Auto-match: selecciona el cronograma cuya fecha_programada coincida en mes y año
    function autoMatchCronogramaPorFecha() {
        var select = document.getElementById('selectCronograma');
        if (select.value) return; // ya hay uno seleccionado manualmente
        var fecha = document.querySelector('[name="fecha_capacitacion"]').value;
        if (!fecha || !cronogramasData.length) return;
        var parts = fecha.split('-'); // YYYY-MM-DD
        var yyyy = parts[0], mm = parts[1];
        // Primero buscar coincidencia exacta de día; si no, coincidencia de mes+año
        var match = cronogramasData.find(function(c) { return c.fecha_programada === fecha; })
                 || cronogramasData.find(function(c) {
                        if (!c.fecha_programada) return false;
                        var p = c.fecha_programada.split('-');
                        return p[0] === yyyy && p[1] === mm;
                    });
        if (match) {
            select.value = match.id_cronograma_capacitacion;
            select.dispatchEvent(new Event('change')); // disparar auto-fill de campos
        }
    }

    // Escuchar cambios en cliente y fecha
    document.querySelector('[name="id_cliente"]').addEventListener('change', function() {
        cargarAsistentes();
        cargarResultadosEval();
        cargarCronogramasPendientes(this.value);
    });
    document.querySelector('[name="fecha_capacitacion"]').addEventListener('change', function() {
        autoMatchCronogramaPorFecha();
        cargarAsistentes();
        cargarResultadosEval();
    });

    // ============================================================
    // RESULTADOS EVALUACIÓN INDUCCIÓN SST (read-only, automático)
    // ============================================================
    var evalContainer = document.getElementById('evalResultadosContainer');

    function cargarResultadosEval(idClienteOverride, fechaOverride) {
        var idCliente = idClienteOverride || document.querySelector('[name="id_cliente"]').value;
        var fecha     = fechaOverride || document.querySelector('[name="fecha_capacitacion"]').value;
        if (!idCliente || !fecha) {
            evalContainer.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-info-circle"></i> Seleccione cliente y fecha.</p>';
            return;
        }
        evalContainer.innerHTML = '<p class="text-muted" style="font-size:13px;"><i class="fas fa-spinner fa-spin"></i> Buscando...</p>';
        $.ajax({
            url: '<?= base_url('/inspecciones/evaluacion-induccion/api-resultados-fecha') ?>',
            data: { id_cliente: idCliente, fecha: fecha },
            dataType: 'json',
            success: function(resp) {
                if (!resp.success) {
                    evalContainer.innerHTML = '<p class="text-muted" style="font-size:13px;">' + (resp.msg || 'Sin evaluación para este cliente y fecha.') + '</p>';
                    return;
                }
                var html = '<table class="table table-sm table-bordered" style="font-size:12px;">';
                html += '<thead><tr><th>#</th><th>Nombre</th><th>Cédula</th><th>Cargo</th><th class="text-center">Calificación</th></tr></thead><tbody>';
                resp.respuestas.forEach(function(r, i) {
                    var cls = parseFloat(r.calificacion) >= 70 ? 'text-success' : 'text-danger';
                    html += '<tr><td>' + (i+1) + '</td><td>' + esc(r.nombre) + '</td><td>' + esc(r.cedula) + '</td><td>' + esc(r.cargo) + '</td><td class="text-center fw-bold ' + cls + '">' + parseFloat(r.calificacion).toFixed(1) + '%</td></tr>';
                });
                html += '</tbody><tfoot class="table-light"><tr><td colspan="4" class="text-end fw-bold">Promedio:</td><td class="text-center fw-bold">' + resp.promedio + '%</td></tr></tfoot>';
                html += '</table><small class="text-muted">' + resp.respuestas.length + ' evaluado(s)</small>';
                evalContainer.innerHTML = html;
            },
            error: function() {
                evalContainer.innerHTML = '<p class="text-danger" style="font-size:13px;"><i class="fas fa-times-circle"></i> Error al cargar resultados.</p>';
            }
        });
    }

    function esc(str) { return str ? String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''; }

    // Cargar automáticamente si ya hay cliente y fecha
    if ('<?= $idCliente ?? '' ?>' && document.querySelector('[name="fecha_capacitacion"]').value) {
        cargarResultadosEval('<?= $idCliente ?? '' ?>', document.querySelector('[name="fecha_capacitacion"]').value);
    }

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE (restaurar borradores)
    // ============================================================
    const STORAGE_KEY = 'rep_cap_draft_<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        const form = document.getElementById('repCapForm');
        Object.keys(data).forEach(name => {
            if (name === '_savedAt') return;
            if (name === 'perfil_asistentes') {
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
        formId: 'repCapForm',
        storeUrl: base_url('/inspecciones/reporte-capacitacion/store'),
        updateUrlBase: base_url('/inspecciones/reporte-capacitacion/update/'),
        editUrlBase: base_url('/inspecciones/reporte-capacitacion/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
        minFieldsCheck: function() {
            var cliente = document.querySelector('[name="id_cliente"]');
            var fecha = document.querySelector('[name="fecha_capacitacion"]');
            return cliente && cliente.value && fecha && fecha.value;
        },
    });
});
</script>
