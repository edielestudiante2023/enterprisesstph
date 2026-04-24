<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/productos-quimicos/update/') . $inspeccion['id'] : base_url('/inspecciones/productos-quimicos/store');

$calificaciones = [
    'C'  => ['label' => 'Cumple',         'color' => '#28a745'],
    'CP' => ['label' => 'Cumple Parcial', 'color' => '#ffc107'],
    'NC' => ['label' => 'No Cumple',      'color' => '#dc3545'],
    'NA' => ['label' => 'No Aplica',      'color' => '#6c757d'],
];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="pqForm">
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

        <!-- BADGE DE SCORE EN VIVO -->
        <div id="scoreBox" class="card mt-2 mb-2" style="border:none; background:#f8f9fa;">
            <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted" style="font-size:11px;">CUMPLIMIENTO EN VIVO</small>
                    <div style="font-size:22px; font-weight:700; color:#1c2437;">
                        <span id="scorePct">-</span>
                        <small style="font-size:12px; color:#999;">(<span id="scoreAplicables">0</span> aplicables)</small>
                    </div>
                </div>
                <div id="scoreBadge" style="padding:8px 16px; border-radius:8px; color:#fff; font-weight:600; font-size:13px; background:#6c757d;">
                    <span id="scoreNivel">SIN CALIFICAR</span>
                </div>
            </div>
        </div>

        <div class="accordion mt-2" id="accordionPq">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionPq">
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
                                <label class="form-label">Ubicacion</label>
                                <input type="text" name="ubicacion" class="form-control"
                                    placeholder="Ej: Cuarto de aseo"
                                    value="<?= esc($inspeccion['ubicacion'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Toggle guadaniadora -->
                        <div class="form-check form-switch" style="margin-top:8px;">
                            <input class="form-check-input" type="checkbox" id="toggleGuadaniadora" name="tiene_guadaniadora" value="1" <?= !empty($inspeccion['tiene_guadaniadora']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="toggleGuadaniadora" style="font-size:13px;">
                                <i class="fas fa-gas-pump text-warning"></i> Hay guadaniadora / combustible almacenado
                                <small class="d-block text-muted" style="font-size:11px;">Activa 2 preguntas adicionales sobre gasolina</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHECKLIST -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secChecklist">
                        Lista de Chequeo (<span id="countItems">15</span>)
                    </button>
                </h2>
                <div id="secChecklist" class="accordion-collapse collapse show" data-bs-parent="#accordionPq">
                    <div class="accordion-body p-2">

                        <?php foreach ($items as $num => $cfg):
                            $col = 'cal_item_' . str_pad($num, 2, '0', STR_PAD_LEFT);
                            $valorActual = $inspeccion[$col] ?? null;
                            $esCondicional = $cfg['grupo'] === 'condicional';
                        ?>
                        <div class="card mb-2 checklist-item <?= $esCondicional ? 'item-condicional' : '' ?>"
                             data-item-num="<?= $num ?>" data-grupo="<?= $cfg['grupo'] ?>"
                             style="border-left:3px solid #6c757d; <?= $esCondicional ? 'display:none;' : '' ?>">
                            <div class="card-body p-2">
                                <div class="d-flex align-items-start mb-1">
                                    <span class="badge bg-dark me-2" style="font-size:11px; padding:4px 7px;"><?= $num ?></span>
                                    <span style="font-size:13px; line-height:1.3;"><?= esc($cfg['label']) ?></span>
                                </div>
                                <div class="d-flex gap-1 flex-wrap mt-1">
                                    <?php foreach ($calificaciones as $val => $info):
                                        $checked = $valorActual === $val;
                                    ?>
                                    <label class="cal-btn" style="flex:1 1 22%; min-width:70px; cursor:pointer;">
                                        <input type="radio" name="<?= $col ?>" value="<?= $val ?>" <?= $checked ? 'checked' : '' ?> style="display:none;">
                                        <div class="cal-label" style="text-align:center; padding:7px 4px; border-radius:6px; border:1.5px solid #dee2e6; font-size:11px; font-weight:600; transition:all .15s; background:<?= $checked ? $info['color'] : '#fff' ?>; color:<?= $checked ? '#fff' : '#555' ?>; border-color:<?= $checked ? $info['color'] : '#dee2e6' ?>;">
                                            <?= $val ?><br><small style="font-weight:400; font-size:9px; opacity:0.9;"><?= $info['label'] ?></small>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>

            <!-- FOTOS DE EVIDENCIA -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secFotos">
                        Fotos de Evidencia (<span id="countFotos"><?= count($fotos ?? []) ?></span>)
                    </button>
                </h2>
                <div id="secFotos" class="accordion-collapse collapse" data-bs-parent="#accordionPq">
                    <div class="accordion-body p-2">
                        <div id="fotosContainer">
                            <?php if (!empty($fotos)): ?>
                                <?php foreach ($fotos as $i => $f): ?>
                                <div class="card mb-2 foto-row" style="border-left:3px solid #17a2b8;">
                                    <div class="card-body p-2">
                                        <input type="hidden" name="foto_id[]" value="<?= esc($f['id']) ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong style="font-size:13px;"><i class="fas fa-camera text-info"></i> Foto #<span class="foto-num"><?= $i+1 ?></span></strong>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-foto" style="min-height:32px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:11px;">Foto</label>
                                                <input type="file" name="foto_file[]" class="foto-input-pwa" accept="image/*" data-label="Foto producto"<?= !empty($f['foto']) ? ' data-previous-url="/' . esc($f['foto']) . '"' : '' ?>>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label" style="font-size:11px;">Observacion</label>
                                                <textarea name="foto_obs[]" class="form-control form-control-sm" rows="3" placeholder="Observacion de la foto..." style="font-size:12px;"><?= esc($f['observacion'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btnAddFoto">
                            <i class="fas fa-plus"></i> Agregar foto
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <!-- Observaciones finales -->
        <div class="card mt-3">
            <div class="card-body p-2">
                <label class="form-label" style="font-size:13px;">Observaciones finales / Recomendaciones</label>
                <textarea name="observaciones_finales" class="form-control" rows="3" placeholder="Observaciones..."><?= esc($inspeccion['observaciones_finales'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Indicador autoguardado -->
        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <!-- Botones -->
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

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

const CALIFICACIONES = <?= json_encode($calificaciones) ?>;
const FACTORES = {C: 1.0, CP: 0.5, NC: 0.0, NA: null};

function buildFotoRow(num, data) {
    data = data || {};
    const preview = data.foto
        ? '<img src="/' + data.foto + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">'
        : '';
    const obs = (data.observacion || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return `
    <div class="card mb-2 foto-row" style="border-left:3px solid #17a2b8;">
        <div class="card-body p-2">
            <input type="hidden" name="foto_id[]" value="${data.id || ''}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong style="font-size:13px;"><i class="fas fa-camera text-info"></i> Foto #<span class="foto-num">${num}</span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-foto" style="min-height:32px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Foto</label>
                    <input type="file" name="foto_file[]" class="foto-input-pwa" accept="image/*" data-label="Foto producto">
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:11px;">Observacion</label>
                    <textarea name="foto_obs[]" class="form-control form-control-sm" rows="3" placeholder="Observacion..." style="font-size:12px;">${obs}</textarea>
                </div>
            </div>
        </div>
    </div>`;
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // Select2 clientes
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

    // Toggle guadaniadora -> muestra/oculta preguntas condicionales
    function updateCondicionales() {
        const tiene = document.getElementById('toggleGuadaniadora').checked;
        const condicionales = document.querySelectorAll('.item-condicional');
        condicionales.forEach(el => { el.style.display = tiene ? '' : 'none'; });
        document.getElementById('countItems').textContent = tiene ? '17' : '15';
        recalcCumplimiento();
    }
    document.getElementById('toggleGuadaniadora').addEventListener('change', updateCondicionales);
    updateCondicionales();

    // Calificacion: click en cualquier boton visual marca el radio oculto
    document.addEventListener('click', function(e) {
        const label = e.target.closest('.cal-btn');
        if (!label) return;
        const radio = label.querySelector('input[type=radio]');
        if (!radio) return;
        radio.checked = true;
        // repintar TODOS los botones del mismo grupo
        const name = radio.name;
        document.querySelectorAll('input[name="' + name + '"]').forEach(r => {
            const lab = r.closest('.cal-btn');
            if (!lab) return;
            const div = lab.querySelector('.cal-label');
            if (!div) return;
            const val = r.value;
            const color = CALIFICACIONES[val].color;
            if (r.checked) {
                div.style.background = color;
                div.style.color = '#fff';
                div.style.borderColor = color;
            } else {
                div.style.background = '#fff';
                div.style.color = '#555';
                div.style.borderColor = '#dee2e6';
            }
        });
        recalcCumplimiento();
    });

    // Calculo del % en vivo
    function recalcCumplimiento() {
        let aplicables = 0, suma = 0;
        const tiene = document.getElementById('toggleGuadaniadora').checked;
        document.querySelectorAll('.checklist-item').forEach(row => {
            const grupo = row.dataset.grupo;
            if (grupo === 'condicional' && !tiene) return;
            const checked = row.querySelector('input[type=radio]:checked');
            if (!checked) return;
            const factor = FACTORES[checked.value];
            if (factor === null) return;
            aplicables++;
            suma += factor;
        });
        const pctEl = document.getElementById('scorePct');
        const aplicEl = document.getElementById('scoreAplicables');
        const nivelEl = document.getElementById('scoreNivel');
        const badge = document.getElementById('scoreBadge');
        aplicEl.textContent = aplicables;
        if (aplicables === 0) {
            pctEl.textContent = '-';
            nivelEl.textContent = 'SIN CALIFICAR';
            badge.style.background = '#6c757d';
            return;
        }
        const pct = (suma / aplicables) * 100;
        pctEl.textContent = pct.toFixed(1) + '%';
        let nivel, color;
        if (pct >= 90) { nivel = 'ALTO'; color = '#28a745'; }
        else if (pct >= 70) { nivel = 'MEDIO'; color = '#ffc107'; }
        else { nivel = 'BAJO'; color = '#dc3545'; }
        nivelEl.textContent = nivel;
        badge.style.background = color;
    }
    recalcCumplimiento();

    // Numeracion fotos
    function updateFotos() {
        const rows = document.querySelectorAll('.foto-row');
        document.getElementById('countFotos').textContent = rows.length;
        rows.forEach((row, i) => {
            const num = row.querySelector('.foto-num');
            if (num) num.textContent = i + 1;
        });
    }

    // Eliminar foto
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-foto')) {
            e.target.closest('.foto-row').remove();
            updateFotos();
        }
    });

    // Agregar foto
    document.getElementById('btnAddFoto').addEventListener('click', function() {
        const num = document.querySelectorAll('.foto-row').length + 1;
        const container = document.getElementById('fotosContainer');
        container.insertAdjacentHTML('beforeend', buildFotoRow(num));
        const newRow = container.lastElementChild;
        if (window.fotoInputPwa && newRow) window.fotoInputPwa.scan(newRow);
        updateFotos();
        const secFotos = document.getElementById('secFotos');
        if (!secFotos.classList.contains('show')) {
            new bootstrap.Collapse(secFotos, { toggle: true });
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
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Finalizar validacion
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
            html: 'Se calculara el % de cumplimiento y se generara el PDF. No podras editar despues.',
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
                document.getElementById('pqForm').appendChild(input);
                document.getElementById('pqForm').submit();
            }
        });
    });

    // ============================================================
    // AUTOGUARDADO SERVIDOR (cada 60s)
    // ============================================================
    const STORAGE_KEY = 'pq_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    initAutosave({
        formId: 'pqForm',
        storeUrl: base_url('/inspecciones/productos-quimicos/store'),
        updateUrlBase: base_url('/inspecciones/productos-quimicos/update/'),
        editUrlBase: base_url('/inspecciones/productos-quimicos/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        detailRowSelector: '.foto-row',
        detailIdInputName: 'foto_id[]',
        intervalSeconds: 60,
    });
});
</script>
