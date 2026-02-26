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
$action = $isEdit ? '/inspecciones/agua-potable/update/' . $inspeccion['id'] : '/inspecciones/agua-potable/store';
$storageKey = $isEdit ? 'agua_draft_' . $inspeccion['id'] : 'agua_draft_new';
?>

<h5 class="mb-3">
    <i class="fas fa-tint me-2"></i>
    <?= $isEdit ? 'Editar' : 'Nuevo' ?> Programa Agua Potable
</h5>

<form id="aguaForm" action="<?= $action ?>" method="post">
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
                <label class="form-label fw-bold">Fecha del Programa <span class="text-danger">*</span></label>
                <input type="date" name="fecha_programa" class="form-control"
                       value="<?= esc($inspeccion['fecha_programa'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Responsable</label>
                <input type="text" name="nombre_responsable" class="form-control"
                       value="<?= esc($inspeccion['nombre_responsable'] ?? '') ?>"
                       placeholder="Nombre del responsable de la inspección">
            </div>
        </div>
    </div>

    <!-- DATOS DE TANQUES -->
    <div class="card mb-3">
        <div class="card-header" style="background: #1c2437; color: white;">
            <i class="fas fa-database me-1"></i> Información de Tanques
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Cantidad de Tanques</label>
                <input type="text" name="cantidad_tanques" class="form-control"
                       value="<?= esc($inspeccion['cantidad_tanques'] ?? '') ?>"
                       placeholder="Ej: 2 tanques">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Capacidad Individual de cada Tanque</label>
                <input type="text" name="capacidad_individual" class="form-control"
                       value="<?= esc($inspeccion['capacidad_individual'] ?? '') ?>"
                       placeholder="Ej: 5000 litros">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Capacidad Total de Almacenamiento</label>
                <input type="text" name="capacidad_total" class="form-control"
                       value="<?= esc($inspeccion['capacidad_total'] ?? '') ?>"
                       placeholder="Ej: 10000 litros">
            </div>
        </div>
    </div>

    <!-- Info del documento -->
    <div class="card mb-3">
        <div class="card-body">
            <p class="text-muted mb-0" style="font-size: 13px;">
                <i class="fas fa-info-circle me-1"></i>
                Al finalizar se generará automáticamente el documento <strong>FT-SST-228</strong> (Programa de Abastecimiento y Control de Agua Potable)
                con el texto legal completo, los datos de tanques y el nombre del cliente seleccionado.
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
var preselectedClient = '<?= esc($idCliente ?? '') ?>';
$.ajax({
    url: '/inspecciones/api/clientes',
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
    var form = document.getElementById('aguaForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    Swal.fire({
        title: 'Finalizar programa?',
        text: 'Se generará el PDF y no podrá editarse más.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#bd9751',
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'finalizar';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    });
});

// ── Autoguardado localStorage ──
var STORAGE_KEY = '<?= $storageKey ?>';

function collectFormData() {
    return {
        id_cliente: document.querySelector('[name="id_cliente"]').value,
        fecha_programa: document.querySelector('[name="fecha_programa"]').value,
        nombre_responsable: document.querySelector('[name="nombre_responsable"]').value,
        cantidad_tanques: document.querySelector('[name="cantidad_tanques"]').value,
        capacidad_individual: document.querySelector('[name="capacidad_individual"]').value,
        capacidad_total: document.querySelector('[name="capacidad_total"]').value,
        timestamp: Date.now()
    };
}

function saveToLocal() {
    var data = collectFormData();
    if (!data.id_cliente && !data.nombre_responsable) return;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    var hora = new Date().toLocaleTimeString();
    document.getElementById('autoguardadoHora').textContent = hora;
    document.getElementById('autoguardadoIndicador').style.display = '';
}

function restoreFromLocal() {
    var saved = localStorage.getItem(STORAGE_KEY);
    if (!saved) return;
    try {
        var data = JSON.parse(saved);
        if (Date.now() - data.timestamp > 24 * 60 * 60 * 1000) {
            localStorage.removeItem(STORAGE_KEY);
            return;
        }
        Swal.fire({
            title: 'Borrador encontrado',
            text: 'Se encontró un borrador guardado. ¿Desea restaurar los datos?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#bd9751',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'No, empezar de cero'
        }).then(result => {
            if (result.isConfirmed) {
                if (data.fecha_programa) document.querySelector('[name="fecha_programa"]').value = data.fecha_programa;
                if (data.nombre_responsable) document.querySelector('[name="nombre_responsable"]').value = data.nombre_responsable;
                if (data.cantidad_tanques) document.querySelector('[name="cantidad_tanques"]').value = data.cantidad_tanques;
                if (data.capacidad_individual) document.querySelector('[name="capacidad_individual"]').value = data.capacidad_individual;
                if (data.capacidad_total) document.querySelector('[name="capacidad_total"]').value = data.capacidad_total;
                if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
            } else {
                localStorage.removeItem(STORAGE_KEY);
            }
        });
    } catch (e) {
        localStorage.removeItem(STORAGE_KEY);
    }
}

<?php if (!$isEdit): ?>
restoreFromLocal();
<?php endif; ?>

setInterval(saveToLocal, 30000);
var debounceTimer;
document.getElementById('aguaForm').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(saveToLocal, 2000);
});

document.getElementById('aguaForm').addEventListener('submit', function() {
    localStorage.removeItem(STORAGE_KEY);
});
</script>
