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
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/plan-saneamiento/update/') . $inspeccion['id'] : base_url('/inspecciones/plan-saneamiento/store');
$storageKey = $isEdit ? 'saneamiento_draft_' . $inspeccion['id'] : 'saneamiento_draft_new';
?>

<h5 class="mb-3">
    <i class="fas fa-shield-alt me-2"></i>
    <?= $isEdit ? 'Editar' : 'Nuevo' ?> Plan de Saneamiento Básico
</h5>

<form id="saneamientoForm" action="<?= $action ?>" method="post">
    <?= csrf_field() ?>

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
                       placeholder="Nombre del responsable">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <p class="text-muted mb-0" style="font-size: 13px;">
                <i class="fas fa-info-circle me-1"></i>
                Al finalizar se generará automáticamente el documento <strong>FT-SST-219</strong> (Plan de Saneamiento Básico)
                que consolida los indicadores de los 4 programas: Limpieza, Residuos, Plagas y Agua Potable.
            </p>
        </div>
    </div>

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
    var form = document.getElementById('saneamientoForm');
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

// ============================================================
// AUTOGUARDADO EN LOCALSTORAGE (restaurar borradores)
// ============================================================
var STORAGE_KEY = '<?= $storageKey ?>';
var isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

function restoreFromLocal(data) {
    if (data.fecha_programa) document.querySelector('[name="fecha_programa"]').value = data.fecha_programa;
    if (data.nombre_responsable) document.querySelector('[name="nombre_responsable"]').value = data.nombre_responsable;
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

// ============================================================
// AUTOGUARDADO SERVIDOR (cada 60s)
// ============================================================
initAutosave({
    formId: 'saneamientoForm',
    storeUrl: base_url('/inspecciones/plan-saneamiento/store'),
    updateUrlBase: base_url('/inspecciones/plan-saneamiento/update/'),
    editUrlBase: base_url('/inspecciones/plan-saneamiento/edit/'),
    recordId: <?= $inspeccion['id'] ?? 'null' ?>,
    isEdit: <?= $isEdit ? 'true' : 'false' ?>,
    storageKey: STORAGE_KEY,
    intervalSeconds: 60,
});
});
</script>
