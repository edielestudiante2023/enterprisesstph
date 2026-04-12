<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/piscinas/update/') . $inspeccion['id'] : base_url('/inspecciones/piscinas/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="pisForm">
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

        <div class="accordion mt-2" id="accordionPis">

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionPis">
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
                                <label class="form-label">Total piscinas</label>
                                <input type="number" name="total_piscinas" min="0" class="form-control"
                                    value="<?= $inspeccion['total_piscinas'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secMtto">
                        Empresa de Mantenimiento y Certificacion Municipal
                    </button>
                </h2>
                <div id="secMtto" class="accordion-collapse collapse" data-bs-parent="#accordionPis">
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
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-7">
                                <label class="form-label mb-0" style="font-size:12px;">Certificado municipal vigente</label>
                            </div>
                            <div class="col-5">
                                <select name="certificado_municipal_vigente" class="form-select form-select-sm" style="font-size:12px;">
                                    <?php $cv = $inspeccion['certificado_municipal_vigente'] ?? 'NA'; foreach (['SI','NO','NA'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $cv === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Fecha vencimiento certificado municipal</label>
                            <input type="date" name="fecha_vencimiento_certificado_mpio" class="form-control form-control-sm" value="<?= $inspeccion['fecha_vencimiento_certificado_mpio'] ?? '' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPiscinas">
                        Piscinas Inspeccionadas (<span id="countPis"><?= count($piscinas ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secPiscinas" class="accordion-collapse collapse" data-bs-parent="#accordionPis">
                    <div class="accordion-body p-2">
                        <div id="piscinasContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddPiscina">
                            <i class="fas fa-plus"></i> Agregar piscina
                        </button>
                    </div>
                </div>
            </div>

        </div>

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
.zona-cerramientos { background:#f7faff; }
.zona-alarmas { background:#fff8f3; }
.zona-drenajes { background:#f4f9f4; }
.zona-senalizacion { background:#fff5f5; }
.zona-emergencia { background:#f5f5fa; }
.zona-avisos { background:#fefce8; }
.zona-agua { background:#f0f7ff; }
.zona-equipos { background:#fef5ff; }
.zona-higiene { background:#f0fff4; }
.zona-resultado { background:#f0fff4; border-color:#bd9751; }
</style>

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

const ZONAS = <?= json_encode($zonas) ?>;
const PISCINAS_INIT = <?= json_encode($piscinas ?? []) ?>;
const TIPOS_PISCINA = ['ADULTOS','NINOS','JACUZZI','CHAPOTEADERO','OTRA'];

function buildPiscinaRow(num, data) {
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
    TIPOS_PISCINA.forEach(t => {
        const sel = (data.tipo === t) ? 'selected' : (!data.tipo && t === 'ADULTOS' ? 'selected' : '');
        tipoOpts += '<option value="' + t + '" ' + sel + '>' + t + '</option>';
    });

    const fotoExistenteHtml = data.foto
        ? `<div class="mb-1"><img src="<?= base_url() ?>${data.foto}" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer;" onclick="openPhoto(this.src)"></div>`
        : '';

    return `
    <div class="card mb-2 piscina-row" style="border-left:3px solid #1c2437;">
        <div class="card-body p-2">
            <input type="hidden" name="item_id[]" value="${data.id || ''}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong style="font-size:13px;"><i class="fas fa-water-ladder"></i> Piscina #<span class="pis-num">${num}</span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-pis" style="min-height:32px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-2 mb-1">
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;">Identificador</label>
                    <input type="text" name="item_identificador[]" class="form-control form-control-sm" placeholder="Ej: Piscina adultos torre 1" value="${(data.identificador || '').replace(/"/g,'&quot;')}">
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;">Tipo</label>
                    <select name="item_tipo[]" class="form-select form-select-sm" style="font-size:12px;">
                        ${tipoOpts}
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Profundidad min (m)</label>
                    <input type="number" step="0.01" name="item_profundidad_minima_m[]" class="form-control form-control-sm" value="${data.profundidad_minima_m || ''}">
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Profundidad max (m)</label>
                    <input type="number" step="0.01" name="item_profundidad_maxima_m[]" class="form-control form-control-sm" value="${data.profundidad_maxima_m || ''}">
                </div>
            </div>

            ${zonasHtml}

            <div class="row g-2 mt-1">
                <div class="col-12">
                    <label class="form-label" style="font-size:11px;">Foto</label>
                    ${fotoExistenteHtml}
                    <div class="photo-input-group">
                        <input type="file" name="item_foto[]" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i> Foto</button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
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

    function updatePiscinas() {
        const rows = document.querySelectorAll('.piscina-row');
        document.getElementById('countPis').textContent = rows.length;
        rows.forEach((row, i) => {
            row.querySelector('.pis-num').textContent = i + 1;
        });
    }

    PISCINAS_INIT.forEach((row, i) => {
        document.getElementById('piscinasContainer').insertAdjacentHTML('beforeend', buildPiscinaRow(i + 1, row));
    });
    updatePiscinas();

    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-pis')) {
            e.target.closest('.piscina-row').remove();
            updatePiscinas();
        }
    });

    document.getElementById('btnAddPiscina').addEventListener('click', function() {
        const num = document.querySelectorAll('.piscina-row').length + 1;
        document.getElementById('piscinasContainer').insertAdjacentHTML('beforeend', buildPiscinaRow(num));
        updatePiscinas();

        const sec = document.getElementById('secPiscinas');
        if (!sec.classList.contains('show')) {
            new bootstrap.Collapse(sec, { toggle: true });
        }
    });

    document.addEventListener('click', function(e) {
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!galleryBtn) return;
        const group = galleryBtn.closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        input.removeAttribute('capture');
        input.click();
    });

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
                document.getElementById('pisForm').appendChild(input);
                document.getElementById('pisForm').submit();
            }
        });
    });

    const STORAGE_KEY = 'pis_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    initAutosave({
        formId: 'pisForm',
        storeUrl: base_url('/inspecciones/piscinas/store'),
        updateUrlBase: base_url('/inspecciones/piscinas/update/'),
        editUrlBase: base_url('/inspecciones/piscinas/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        detailRowSelector: '.piscina-row',
        detailIdInputName: 'item_id[]',
        intervalSeconds: 60,
    });
});
</script>
