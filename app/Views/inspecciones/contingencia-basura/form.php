<?php if (session()->getFlashdata('msg')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('msg') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
$isEdit                  = !empty($inspeccion);
$action                  = $isEdit ? base_url('/inspecciones/contingencia-basura/update/') . $inspeccion['id'] : base_url('/inspecciones/contingencia-basura/store');
$storageKey              = $isEdit ? 'cont_basura_draft_' . $inspeccion['id'] : 'cont_basura_draft_new';
$empresaAseo             = $isEdit ? ($inspeccion['empresa_aseo'] ?? '') : '';
$horarioRecoleccion      = $isEdit ? ($inspeccion['horario_recoleccion_actual'] ?? '') : '';
?>

<h5 class="mb-3">
    <i class="fas fa-trash-alt me-2"></i>
    <?= $isEdit ? 'Editar' : 'Nuevo' ?> Plan de Contingencia — Recolección de Basuras
</h5>

<form id="basuraForm" action="<?= $action ?>" method="post">
    <?= csrf_field() ?>

    <!-- DATOS GENERALES -->
    <div class="card mb-3">
        <div class="card-header" style="background: #1c2437; color: white;">
            <i class="fas fa-info-circle me-1"></i> Datos Generales
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccionar cliente...</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Fecha del Plan <span class="text-danger">*</span></label>
                <input type="date" name="fecha_programa" class="form-control"
                       value="<?= esc($inspeccion['fecha_programa'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Responsable</label>
                <input type="text" name="nombre_responsable" class="form-control"
                       value="<?= esc($inspeccion['nombre_responsable'] ?? '') ?>"
                       placeholder="Nombre del responsable de la administración">
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN DEL PRESTADOR -->
    <div class="card mb-3">
        <div class="card-header" style="background: #1c2437; color: white;">
            <i class="fas fa-truck me-1"></i> Prestador del Servicio de Aseo
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del prestador del servicio de aseo</label>
                <input type="text" name="empresa_aseo" class="form-control"
                       value="<?= esc($empresaAseo) ?>"
                       placeholder="Ej: Empresas Municipales de Cali — EMCALI">
                <small class="text-muted">Empresa pública o privada responsable de la recolección habitual.</small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Horario normal de recolección</label>
                <input type="text" name="horario_recoleccion_actual" class="form-control"
                       value="<?= esc($horarioRecoleccion) ?>"
                       placeholder="Ej: Lunes, Miércoles y Viernes — 6:00 a.m. a 10:00 a.m.">
                <small class="text-muted">Días y horario habitual de recolección en la zona del conjunto.</small>
            </div>
        </div>
    </div>

    <!-- Info del documento -->
    <div class="card mb-3">
        <div class="card-body">
            <p class="text-muted mb-0" style="font-size: 13px;">
                <i class="fas fa-info-circle me-1"></i>
                Al finalizar se generará automáticamente el documento <strong>FT-SST-235</strong>
                (Plan de Contingencia si la Recolección de Basura No Pasa) con el protocolo de actuación completo.
            </p>
        </div>
    </div>

    <!-- Botones -->
    <div class="d-grid gap-2 mb-4">
        <button type="submit" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-save me-2"></i>Guardar borrador
        </button>
        <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary btn-finalizar">
            <i class="fas fa-check-circle me-2"></i>Finalizar y generar PDF
        </button>
    </div>

    <div id="autoguardadoIndicador" class="text-center text-muted mb-3" style="font-size: 12px; display: none;">
        <i class="fas fa-save"></i> Guardado local: <span id="autoguardadoHora"></span>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
var preselectedClient = '<?= esc($idCliente ?? '') ?>';
$.ajax({
    url: '<?= base_url('/inspecciones/api/clientes') ?>',
    dataType: 'json',
    success: function(data) {
        var sel = document.getElementById('selectCliente');
        data.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.id_cliente;
            opt.textContent = c.nombre_cliente;
            if (c.id_cliente == preselectedClient) opt.selected = true;
            sel.appendChild(opt);
        });
        $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        if (window._pendingClientRestore) {
            $('#selectCliente').val(window._pendingClientRestore).trigger('change');
            window._pendingClientRestore = null;
        }
    }
});

document.querySelector('.btn-finalizar').addEventListener('click', function(e) {
    e.preventDefault();
    var form = document.getElementById('basuraForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    Swal.fire({
        title: 'Finalizar plan?',
        text: 'Se generará el PDF y no podrá editarse más.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#bd9751',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            var input = document.createElement('input');
            input.type = 'hidden'; input.name = 'finalizar'; input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    });
});

var STORAGE_KEY = '<?= $storageKey ?>';
var isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

function restoreFromLocal(data) {
    if (data.fecha_programa) document.querySelector('[name="fecha_programa"]').value = data.fecha_programa;
    if (data.nombre_responsable) document.querySelector('[name="nombre_responsable"]').value = data.nombre_responsable;
    if (data.empresa_aseo) document.querySelector('[name="empresa_aseo"]').value = data.empresa_aseo;
    if (data.horario_recoleccion_actual) document.querySelector('[name="horario_recoleccion_actual"]').value = data.horario_recoleccion_actual;
    if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
}

if (!isEditLocal) {
    try {
        var saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            var data = JSON.parse(saved);
            var hoursAgo = ((Date.now() - new Date(data._savedAt).getTime()) / 3600000).toFixed(1);
            if (hoursAgo < 24) {
                Swal.fire({
                    title: 'Borrador encontrado',
                    text: 'Se encontro un borrador de hace ' + hoursAgo + ' horas. Restaurar?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Si, restaurar',
                    cancelButtonText: 'No, empezar de cero',
                    confirmButtonColor: '#bd9751',
                }).then(function(result) {
                    if (result.isConfirmed) restoreFromLocal(data);
                    else localStorage.removeItem(STORAGE_KEY);
                });
            } else {
                localStorage.removeItem(STORAGE_KEY);
            }
        }
    } catch(e) {}
}

initAutosave({
    formId: 'basuraForm',
    storeUrl: base_url('/inspecciones/contingencia-basura/store'),
    updateUrlBase: base_url('/inspecciones/contingencia-basura/update/'),
    editUrlBase: base_url('/inspecciones/contingencia-basura/edit/'),
    recordId: <?= $inspeccion['id'] ?? 'null' ?>,
    isEdit: <?= $isEdit ? 'true' : 'false' ?>,
    storageKey: STORAGE_KEY,
    intervalSeconds: 60,
});
});
</script>
