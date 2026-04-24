<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/ascensores/update/') . $inspeccion['id'] : base_url('/inspecciones/ascensores/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="ascForm">
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

        <div class="accordion mt-2" id="accordionAsc">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionAsc">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Fecha inspeccion *</label>
                                <input type="date" name="fecha_inspeccion" class="form-control"
                                    value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Total ascensores</label>
                                <input type="number" name="total_ascensores" min="0" class="form-control"
                                    value="<?= $inspeccion['total_ascensores'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EMPRESA DE MANTENIMIENTO Y ONAC -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secMtto">
                        Empresa de Mantenimiento y ONAC
                    </button>
                </h2>
                <div id="secMtto" class="accordion-collapse collapse" data-bs-parent="#accordionAsc">
                    <div class="accordion-body">
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Empresa de mantenimiento</label>
                            <input type="text" name="empresa_mantenimiento" class="form-control form-control-sm" value="<?= esc($inspeccion['empresa_mantenimiento'] ?? '') ?>">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">NIT</label>
                                <input type="text" name="nit_empresa_mantenimiento" class="form-control form-control-sm" value="<?= esc($inspeccion['nit_empresa_mantenimiento'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Contacto</label>
                                <input type="text" name="contacto_empresa_mantenimiento" class="form-control form-control-sm" value="<?= esc($inspeccion['contacto_empresa_mantenimiento'] ?? '') ?>">
                            </div>
                        </div>
                        <hr style="margin:8px 0;">
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Organismo certificador (ONAC)</label>
                            <input type="text" name="organismo_certificador_onac" class="form-control form-control-sm" value="<?= esc($inspeccion['organismo_certificador_onac'] ?? '') ?>">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Fecha ultimo certificado</label>
                                <input type="date" name="fecha_ultimo_certificado_onac" class="form-control form-control-sm" value="<?= $inspeccion['fecha_ultimo_certificado_onac'] ?? '' ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Vencimiento certificado</label>
                                <input type="date" name="fecha_vencimiento_certificado_onac" class="form-control form-control-sm" value="<?= $inspeccion['fecha_vencimiento_certificado_onac'] ?? '' ?>">
                            </div>
                        </div>
                        <hr style="margin:8px 0;">
                        <small class="text-muted d-block mb-2" style="font-size:11px;">Checklist documental:</small>
                        <?php
                        $docs = [
                            'certificado_visible_al_publico' => 'Certificado visible al publico',
                            'cronograma_mantenimiento_anual' => 'Cronograma mantenimiento anual',
                            'reportes_tecnicos_disponibles'  => 'Reportes tecnicos disponibles',
                        ];
                        foreach ($docs as $field => $label):
                            $val = $inspeccion[$field] ?? 'NA';
                        ?>
                        <div class="row g-1 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;"><?= $label ?></label></div>
                            <div class="col-5">
                                <select name="<?= $field ?>" class="form-select form-select-sm" style="font-size:12px;">
                                    <?php foreach (['SI','NO','NA'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $val === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ASCENSORES INSPECCIONADOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secAscensores">
                        Ascensores Inspeccionados (<span id="countAsc"><?= count($ascensores ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secAscensores" class="accordion-collapse collapse" data-bs-parent="#accordionAsc">
                    <div class="accordion-body p-2">
                        <div id="ascensoresContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddAscensor">
                            <i class="fas fa-plus"></i> Agregar ascensor
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Recomendaciones generales -->
        <div class="card mt-3">
            <div class="card-body p-2">
                <label class="form-label" style="font-size:13px;">Recomendaciones generales</label>
                <textarea name="recomendaciones_generales" class="form-control" rows="3" placeholder="Recomendaciones..."><?= esc($inspeccion['recomendaciones_generales'] ?? '') ?></textarea>
            </div>
        </div>

        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar inspeccion
            </button>
        </div>
    </form>
</div>

<!-- Modal foto ampliada -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-body p-1 text-center">
                <img id="photoModalImg" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>

<style>
.zona-block { border:1px solid #e6e6e6; border-radius:6px; margin:6px 0; padding:6px; }
.zona-title { font-size:11px; font-weight:700; color:#1c2437; text-transform:uppercase; margin-bottom:4px; border-bottom:1px solid #ddd; padding-bottom:2px; }
.zona-cabina { background:#f7faff; }
.zona-puertas { background:#fff8f3; }
.zona-cuarto_maquinas { background:#f4f9f4; }
.zona-foso { background:#fff5f5; }
.zona-shaft { background:#f5f5fa; }
.zona-electricos { background:#fefce8; }
.zona-contrapeso { background:#f0f7ff; }
.zona-senalizacion { background:#fef5ff; }
.zona-resultado { background:#f0fff4; border-color:#bd9751; }
</style>

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

const ZONAS = <?= json_encode($zonas) ?>;
const ASCENSORES_INIT = <?= json_encode($ascensores ?? []) ?>;
const TIPOS_ASCENSOR = ['ELECTRICO','HIDRAULICO','NA'];

function buildAscensorRow(num, data) {
    data = data || {};

    let zonasHtml = '';
    for (const [zKey, zCfg] of Object.entries(ZONAS)) {
        let critsHtml = '';
        for (const [cKey, cCfg] of Object.entries(zCfg.criterios)) {
            let opts = '';
            cCfg.opciones.forEach(opt => {
                const sel = (data[cKey] === opt) ? 'selected' : (!data[cKey] && opt === cCfg.default ? 'selected' : '');
                opts += '<option value="' + opt + '" ' + sel + '>' + opt + '</option>';
            });
            critsHtml += `
                <div class="col-6 mb-1">
                    <label class="form-label" style="font-size:10px;">${cCfg.label}</label>
                    <select name="item_${cKey}[]" class="form-select form-select-sm" style="font-size:11px;">
                        ${opts}
                    </select>
                </div>`;
        }
        zonasHtml += `
            <div class="zona-block zona-${zKey}">
                <div class="zona-title">${zCfg.label}</div>
                <div class="row g-1">${critsHtml}</div>
            </div>`;
    }

    let tipoOpts = '';
    TIPOS_ASCENSOR.forEach(t => {
        const sel = (data.tipo === t) ? 'selected' : (!data.tipo && t === 'ELECTRICO' ? 'selected' : '');
        tipoOpts += '<option value="' + t + '" ' + sel + '>' + t + '</option>';
    });

    const fotoPrevAttr = data.foto ? ` data-previous-url="<?= base_url() ?>${data.foto}"` : '';

    return `
    <div class="card mb-2 ascensor-row" style="border-left:3px solid #1c2437;">
        <div class="card-body p-2">
            <input type="hidden" name="item_id[]" value="${data.id || ''}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong style="font-size:13px;"><i class="fas fa-elevator"></i> Ascensor #<span class="asc-num">${num}</span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asc" style="min-height:32px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-2 mb-1">
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;">Identificador</label>
                    <input type="text" name="item_identificador[]" class="form-control form-control-sm" placeholder="Ej: Torre 1 - Ascensor A" value="${(data.identificador || '').replace(/"/g,'&quot;')}">
                </div>
                <div class="col-4">
                    <label class="form-label" style="font-size:11px;">Capacidad kg</label>
                    <input type="number" step="any" name="item_capacidad_kg[]" class="form-control form-control-sm" value="${data.capacidad_kg || ''}">
                </div>
                <div class="col-4">
                    <label class="form-label" style="font-size:11px;">Personas</label>
                    <input type="number" name="item_capacidad_personas[]" class="form-control form-control-sm" value="${data.capacidad_personas || ''}">
                </div>
                <div class="col-4">
                    <label class="form-label" style="font-size:11px;">Pisos</label>
                    <input type="text" name="item_pisos_servidos[]" class="form-control form-control-sm" value="${(data.pisos_servidos || '').toString().replace(/"/g,'&quot;')}">
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;">Tipo</label>
                    <select name="item_tipo[]" class="form-select form-select-sm" style="font-size:12px;">
                        ${tipoOpts}
                    </select>
                </div>
            </div>

            ${zonasHtml}

            <div class="row g-2 mt-1">
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;">Foto</label>
                    <input type="file" name="item_foto[]" class="foto-input-pwa" accept="image/*" data-label="Foto ascensor"${fotoPrevAttr}>
                </div>
            </div>
            <div class="mt-1">
                <label class="form-label" style="font-size:11px;">Observaciones</label>
                <input type="text" name="item_observaciones[]" class="form-control form-control-sm" placeholder="Observaciones..." value="${(data.observaciones || '').replace(/"/g,'&quot;')}">
            </div>
        </div>
    </div>`;
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // --- Select2 clientes ---
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteId && c.id_cliente == clienteId) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });

            if (window._pendingClientRestore) {
                $('#selectCliente').val(window._pendingClientRestore).trigger('change');
                window._pendingClientRestore = null;
            }
        }
    });

    function updateAscensores() {
        const rows = document.querySelectorAll('.ascensor-row');
        document.getElementById('countAsc').textContent = rows.length;
        rows.forEach((row, i) => {
            row.querySelector('.asc-num').textContent = i + 1;
        });
    }

    // Render filas existentes (vienen del controller en edit)
    ASCENSORES_INIT.forEach((row, i) => {
        const container = document.getElementById('ascensoresContainer');
        container.insertAdjacentHTML('beforeend', buildAscensorRow(i + 1, row));
        const newRow = container.lastElementChild;
        if (window.fotoInputPwa && newRow) window.fotoInputPwa.scan(newRow);
    });
    updateAscensores();

    // Eliminar ascensor
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-asc')) {
            e.target.closest('.ascensor-row').remove();
            updateAscensores();
        }
    });

    // Agregar ascensor
    document.getElementById('btnAddAscensor').addEventListener('click', function() {
        const num = document.querySelectorAll('.ascensor-row').length + 1;
        const container = document.getElementById('ascensoresContainer');
        container.insertAdjacentHTML('beforeend', buildAscensorRow(num));
        const newRow = container.lastElementChild;
        if (window.fotoInputPwa && newRow) window.fotoInputPwa.scan(newRow);
        updateAscensores();

        const sec = document.getElementById('secAscensores');
        if (!sec.classList.contains('show')) {
            new bootstrap.Collapse(sec, { toggle: true });
        }
    });

    // Boton Galeria
    document.addEventListener('click', function(e) {
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!galleryBtn) return;
        const group = galleryBtn.closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        input.removeAttribute('capture');
        input.click();
    });

    // Preview fotos
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const input = e.target;
        const group = input.closest('.photo-input-group');
        const previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;

        previewDiv.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Finalizar via SweetAlert (usa form.submit() override que setea submitted=true)
    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        const cliente = document.getElementById('selectCliente').value;
        if (!cliente) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#bd9751' });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspeccion?',
            html: 'Se generara el PDF y no podras editar despues.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, finalizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
        }).then(result => {
            if (result.isConfirmed) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'finalizar';
                input.value = '1';
                document.getElementById('ascForm').appendChild(input);
                document.getElementById('ascForm').submit();
            }
        });
    });

    // Autoguardado servidor
    const STORAGE_KEY = 'asc_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    initAutosave({
        formId: 'ascForm',
        storeUrl: base_url('/inspecciones/ascensores/store'),
        updateUrlBase: base_url('/inspecciones/ascensores/update/'),
        editUrlBase: base_url('/inspecciones/ascensores/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        detailRowSelector: '.ascensor-row',
        detailIdInputName: 'item_id[]',
        intervalSeconds: 60,
    });
});
</script>
