<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/gabinetes/update/' . $inspeccion['id'] : '/inspecciones/gabinetes/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="gabForm">
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

        <div class="accordion mt-2" id="accordionGab">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionGab">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha inspeccion *</label>
                            <input type="date" name="fecha_inspeccion" class="form-control"
                                value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                        </div>

                        <hr style="margin:8px 0;">
                        <small class="text-muted d-block mb-2" style="font-size:11px;">Gabinetes contra incendio:</small>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Cuenta con gabinetes?</label>
                                <select name="tiene_gabinetes" class="form-select form-select-sm">
                                    <option value="SI" <?= ($inspeccion['tiene_gabinetes'] ?? 'SI') === 'SI' ? 'selected' : '' ?>>SI</option>
                                    <option value="NO" <?= ($inspeccion['tiene_gabinetes'] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Entregados por constructora?</label>
                                <select name="entregados_constructora" class="form-select form-select-sm">
                                    <option value="SI" <?= ($inspeccion['entregados_constructora'] ?? 'SI') === 'SI' ? 'selected' : '' ?>>SI</option>
                                    <option value="NO" <?= ($inspeccion['entregados_constructora'] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Cantidad de gabinetes</label>
                            <input type="number" name="cantidad_gabinetes" class="form-control form-control-sm" min="0"
                                value="<?= $inspeccion['cantidad_gabinetes'] ?? 0 ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Elementos que contiene cada gabinete</label>
                            <textarea name="elementos_gabinete" class="form-control form-control-sm" rows="2" placeholder="Manguera, hacha, valvula..."><?= esc($inspeccion['elementos_gabinete'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Ubicacion de los gabinetes</label>
                            <textarea name="ubicacion_gabinetes" class="form-control form-control-sm" rows="2" placeholder="Ubicacion detallada..."><?= esc($inspeccion['ubicacion_gabinetes'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Estado de la senalizacion</label>
                            <textarea name="estado_senalizacion_gab" class="form-control form-control-sm" rows="2" placeholder="Estado de la senalizacion..."><?= esc($inspeccion['estado_senalizacion_gab'] ?? '') ?></textarea>
                        </div>

                        <!-- Fotos gabinetes generales -->
                        <div class="row g-2 mb-2">
                            <?php foreach (['foto_gab_1' => 'Foto gabinete 1', 'foto_gab_2' => 'Foto gabinete 2'] as $campo => $label): ?>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;"><?= $label ?></label>
                                <div class="photo-input-group">
                                    <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                    </div>
                                    <div class="preview-img mt-1">
                                        <?php if (!empty($inspeccion[$campo])): ?>
                                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Observaciones gabinetes</label>
                            <textarea name="observaciones_gabinetes" class="form-control form-control-sm" rows="2" placeholder="Observaciones..."><?= esc($inspeccion['observaciones_gabinetes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GABINETES INDIVIDUALES (N items) -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secGabinetes">
                        Gabinetes Individuales (<span id="countGab"><?= count($gabinetes ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secGabinetes" class="accordion-collapse collapse" data-bs-parent="#accordionGab">
                    <div class="accordion-body p-2">
                        <div id="gabinetesContainer">
                            <?php if (!empty($gabinetes)): ?>
                                <?php foreach ($gabinetes as $i => $gab): ?>
                                <div class="card mb-2 gabinete-row" style="border-left:3px solid #0d6efd;">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="gab_id[]" value="<?= $gab['id'] ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong style="font-size:13px;"><i class="fas fa-shower text-primary"></i> Gabinete #<span class="gab-num"><?= $i + 1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-gab" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label" style="font-size:11px;">Ubicacion</label>
                                            <input type="text" name="gab_ubicacion[]" class="form-control form-control-sm" placeholder="Ej: Piso 1, pasillo norte" value="<?= esc($gab['ubicacion'] ?? '') ?>">
                                        </div>
                                        <div class="row g-1">
                                            <?php
                                            $sinoFields = ['tiene_manguera'=>'Manguera','tiene_hacha'=>'Hacha','tiene_extintor'=>'Extintor','tiene_valvula'=>'Valvula','tiene_boquilla'=>'Boquilla','tiene_llave_spanner'=>'Llave spanner'];
                                            foreach ($sinoFields as $key => $label):
                                            ?>
                                            <div class="col-4 mb-1">
                                                <label class="form-label" style="font-size:11px;"><?= $label ?></label>
                                                <select name="gab_<?= $key ?>[]" class="form-select form-select-sm" style="font-size:12px;">
                                                    <option value="SI" <?= ($gab[$key] ?? 'SI') === 'SI' ? 'selected' : '' ?>>SI</option>
                                                    <option value="NO" <?= ($gab[$key] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                                                </select>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="row g-1 mt-1">
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:11px;">Estado general</label>
                                                <select name="gab_estado[]" class="form-select form-select-sm" style="font-size:12px;">
                                                    <?php foreach (['BUENO','REGULAR','MALO'] as $opt): ?>
                                                    <option value="<?= $opt ?>" <?= ($gab['estado'] ?? 'BUENO') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:11px;">Senalizacion</label>
                                                <select name="gab_senalizacion[]" class="form-select form-select-sm" style="font-size:12px;">
                                                    <?php foreach (['BUENO','REGULAR','MALO','NO TIENE'] as $opt): ?>
                                                    <option value="<?= $opt ?>" <?= ($gab['senalizacion'] ?? 'BUENO') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row g-2 mt-1">
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:11px;">Foto</label>
                                                <div class="photo-input-group">
                                                    <input type="file" name="gab_foto[]" class="file-preview" accept="image/*" style="display:none;">
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                                    </div>
                                                    <div class="preview-img mt-1">
                                                        <?php if (!empty($gab['foto'])): ?>
                                                        <img src="/<?= esc($gab['foto']) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:11px;">Observaciones</label>
                                                <input type="text" name="gab_observaciones[]" class="form-control form-control-sm" placeholder="Observaciones..." value="<?= esc($gab['observaciones'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddGabinete">
                            <i class="fas fa-plus"></i> Agregar gabinete
                        </button>
                    </div>
                </div>
            </div>

            <!-- DETECTORES DE HUMO -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secDetectores">
                        Detectores de Humo
                    </button>
                </h2>
                <div id="secDetectores" class="accordion-collapse collapse" data-bs-parent="#accordionGab">
                    <div class="accordion-body">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Existen detectores de humo?</label>
                                <select name="tiene_detectores" class="form-select form-select-sm">
                                    <option value="SI" <?= ($inspeccion['tiene_detectores'] ?? 'SI') === 'SI' ? 'selected' : '' ?>>SI</option>
                                    <option value="NO" <?= ($inspeccion['tiene_detectores'] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Entregados por constructora?</label>
                                <select name="detectores_entregados" class="form-select form-select-sm">
                                    <option value="SI" <?= ($inspeccion['detectores_entregados'] ?? 'SI') === 'SI' ? 'selected' : '' ?>>SI</option>
                                    <option value="NO" <?= ($inspeccion['detectores_entregados'] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Cantidad de detectores</label>
                            <input type="number" name="cantidad_detectores" class="form-control form-control-sm" min="0"
                                value="<?= $inspeccion['cantidad_detectores'] ?? 0 ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Ubicacion de los detectores</label>
                            <textarea name="ubicacion_detectores" class="form-control form-control-sm" rows="2" placeholder="Partes de la copropiedad con detectores..."><?= esc($inspeccion['ubicacion_detectores'] ?? '') ?></textarea>
                        </div>

                        <!-- Fotos detectores -->
                        <div class="row g-2 mb-2">
                            <?php foreach (['foto_det_1' => 'Foto detector 1', 'foto_det_2' => 'Foto detector 2'] as $campo => $label): ?>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;"><?= $label ?></label>
                                <div class="photo-input-group">
                                    <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                                    </div>
                                    <div class="preview-img mt-1">
                                        <?php if (!empty($inspeccion[$campo])): ?>
                                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Observaciones detectores</label>
                            <textarea name="observaciones_detectores" class="form-control form-control-sm" rows="2" placeholder="Observaciones..."><?= esc($inspeccion['observaciones_detectores'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- Botones -->
        <div class="mt-3 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary" id="btnFinalizar">
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

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

function buildGabineteRow(num, data) {
    data = data || {};

    const sinoFields = [
        { key: 'tiene_manguera', label: 'Manguera', def: 'SI' },
        { key: 'tiene_hacha', label: 'Hacha', def: 'SI' },
        { key: 'tiene_extintor', label: 'Extintor', def: 'NO' },
        { key: 'tiene_valvula', label: 'Valvula', def: 'SI' },
        { key: 'tiene_boquilla', label: 'Boquilla', def: 'SI' },
        { key: 'tiene_llave_spanner', label: 'Llave spanner', def: 'NO' },
    ];

    let sinoHtml = '';
    sinoFields.forEach(f => {
        const val = data[f.key] || f.def;
        sinoHtml += `
            <div class="col-4 mb-1">
                <label class="form-label" style="font-size:11px;">${f.label}</label>
                <select name="gab_${f.key}[]" class="form-select form-select-sm" style="font-size:12px;">
                    <option value="SI" ${val === 'SI' ? 'selected' : ''}>SI</option>
                    <option value="NO" ${val === 'NO' ? 'selected' : ''}>NO</option>
                </select>
            </div>`;
    });

    const estadoVal = data.estado || 'BUENO';
    const senalVal = data.senalizacion || 'BUENO';

    let estadoOpts = '';
    ['BUENO','REGULAR','MALO'].forEach(o => {
        estadoOpts += `<option value="${o}" ${estadoVal === o ? 'selected' : ''}>${o}</option>`;
    });

    let senalOpts = '';
    ['BUENO','REGULAR','MALO','NO TIENE'].forEach(o => {
        senalOpts += `<option value="${o}" ${senalVal === o ? 'selected' : ''}>${o}</option>`;
    });

    return `
    <div class="card mb-2 gabinete-row" style="border-left:3px solid #0d6efd;">
        <div class="card-body p-2">
            <input type="hidden" name="gab_id[]" value="${data.id || ''}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong style="font-size:13px;"><i class="fas fa-shower text-primary"></i> Gabinete #<span class="gab-num">${num}</span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-gab" style="min-height:32px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-1">
                <label class="form-label" style="font-size:11px;">Ubicacion</label>
                <input type="text" name="gab_ubicacion[]" class="form-control form-control-sm" placeholder="Ej: Piso 1, pasillo norte" value="${(data.ubicacion || '').replace(/"/g, '&quot;')}">
            </div>
            <div class="row g-1">
                ${sinoHtml}
            </div>
            <div class="row g-1 mt-1">
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Estado general</label>
                    <select name="gab_estado[]" class="form-select form-select-sm" style="font-size:12px;">
                        ${estadoOpts}
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Senalizacion</label>
                    <select name="gab_senalizacion[]" class="form-select form-select-sm" style="font-size:12px;">
                        ${senalOpts}
                    </select>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Foto</label>
                    <div class="photo-input-group">
                        <input type="file" name="gab_foto[]" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;"><i class="fas fa-camera"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i></button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Observaciones</label>
                    <input type="text" name="gab_observaciones[]" class="form-control form-control-sm" placeholder="Observaciones..." value="${(data.observaciones || '').replace(/"/g, '&quot;')}">
                </div>
            </div>
        </div>
    </div>`;
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // --- Select2 clientes ---
    $.ajax({
        url: '/inspecciones/api/clientes',
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

    // --- Conteo y numeracion ---
    function updateGabinetes() {
        const rows = document.querySelectorAll('.gabinete-row');
        document.getElementById('countGab').textContent = rows.length;
        rows.forEach((row, i) => {
            row.querySelector('.gab-num').textContent = i + 1;
        });
    }

    // --- Eliminar gabinete ---
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-gab')) {
            e.target.closest('.gabinete-row').remove();
            updateGabinetes();
        }
    });

    // --- Agregar gabinete ---
    document.getElementById('btnAddGabinete').addEventListener('click', function() {
        const num = document.querySelectorAll('.gabinete-row').length + 1;
        document.getElementById('gabinetesContainer').insertAdjacentHTML('beforeend', buildGabineteRow(num));
        updateGabinetes();

        const secGab = document.getElementById('secGabinetes');
        if (!secGab.classList.contains('show')) {
            new bootstrap.Collapse(secGab, { toggle: true });
        }
    });

    // --- Botones Camara / Galeria ---
    document.addEventListener('click', function(e) {
        const cameraBtn = e.target.closest('.btn-photo-camera');
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!cameraBtn && !galleryBtn) return;

        const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');

        if (cameraBtn) {
            input.setAttribute('capture', 'environment');
        } else {
            input.removeAttribute('capture');
        }
        input.click();
    });

    // --- Preview fotos ---
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

    // --- Finalizar validacion ---
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
                document.getElementById('gabForm').appendChild(input);
                document.getElementById('gabForm').submit();
            }
        });
    });

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE
    // ============================================================
    const STORAGE_KEY = 'gab_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEdit = <?= $isEdit ? 'true' : 'false' ?>;

    function collectFormData() {
        const data = {};
        data.id_cliente = document.getElementById('selectCliente').value;
        data.fecha_inspeccion = document.querySelector('[name="fecha_inspeccion"]').value;

        // Campos master
        ['tiene_gabinetes','entregados_constructora','cantidad_gabinetes','elementos_gabinete',
         'ubicacion_gabinetes','estado_senalizacion_gab','observaciones_gabinetes',
         'tiene_detectores','detectores_entregados','cantidad_detectores',
         'ubicacion_detectores','observaciones_detectores'
        ].forEach(f => { data[f] = document.querySelector('[name="'+f+'"]')?.value || ''; });

        // Gabinetes individuales
        data.gabinetes = [];
        document.querySelectorAll('.gabinete-row').forEach(row => {
            const gab = {};
            gab.id = row.querySelector('[name="gab_id[]"]').value;
            gab.ubicacion = row.querySelector('[name="gab_ubicacion[]"]')?.value || '';
            ['tiene_manguera','tiene_hacha','tiene_extintor','tiene_valvula','tiene_boquilla','tiene_llave_spanner'].forEach(k => {
                gab[k] = row.querySelector('[name="gab_'+k+'[]"]')?.value || '';
            });
            gab.estado = row.querySelector('[name="gab_estado[]"]')?.value || '';
            gab.senalizacion = row.querySelector('[name="gab_senalizacion[]"]')?.value || '';
            gab.observaciones = row.querySelector('[name="gab_observaciones[]"]')?.value || '';
            data.gabinetes.push(gab);
        });

        data._savedAt = new Date().toISOString();
        return data;
    }

    function saveToLocal() {
        try {
            const data = collectFormData();
            if (data.id_cliente || data.gabinetes.length) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                document.getElementById('autoSaveStatus').innerHTML =
                    '<i class="fas fa-check-circle text-success"></i> Guardado ' + new Date().toLocaleTimeString();
            }
        } catch(e) {}
    }

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;

        ['tiene_gabinetes','entregados_constructora','cantidad_gabinetes','elementos_gabinete',
         'ubicacion_gabinetes','estado_senalizacion_gab','observaciones_gabinetes',
         'tiene_detectores','detectores_entregados','cantidad_detectores',
         'ubicacion_detectores','observaciones_detectores'
        ].forEach(f => {
            const el = document.querySelector('[name="'+f+'"]');
            if (el && data[f]) el.value = data[f];
        });

        (data.gabinetes || []).forEach((gab, i) => {
            document.getElementById('gabinetesContainer').insertAdjacentHTML('beforeend', buildGabineteRow(i + 1, gab));
        });
        updateGabinetes();
    }

    if (!isEdit) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const hoursAgo = ((Date.now() - new Date(data._savedAt).getTime()) / 3600000).toFixed(1);
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

    setInterval(saveToLocal, 30000);
    document.getElementById('gabForm').addEventListener('input', function() {
        clearTimeout(window._autoSaveTimeout);
        window._autoSaveTimeout = setTimeout(saveToLocal, 2000);
    });
    $('#selectCliente').on('change', function() { setTimeout(saveToLocal, 500); });
    document.getElementById('gabForm').addEventListener('submit', function() {
        localStorage.removeItem(STORAGE_KEY);
    });
});
</script>
