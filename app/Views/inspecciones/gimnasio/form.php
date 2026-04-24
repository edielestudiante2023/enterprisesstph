<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/gimnasio/update/') . $inspeccion['id'] : base_url('/inspecciones/gimnasio/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="gymForm">
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
        <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="alert alert-info mt-2 mb-3" style="font-size:13px;">
            <strong><i class="fas fa-dumbbell"></i> FT-SST-250 — Inspeccion de Gimnasio (riesgos locativos)</strong>
        </div>

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
                    <label class="form-label">Fecha inspeccion *</label>
                    <input type="date" name="fecha_inspeccion" class="form-control"
                        value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Aforo maximo</label>
                        <input type="number" min="0" name="aforo_maximo" class="form-control form-control-sm" value="<?= esc($inspeccion['aforo_maximo'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Horario operacion</label>
                        <input type="text" name="horario_operacion" class="form-control form-control-sm" placeholder="5am-10pm" value="<?= esc($inspeccion['horario_operacion'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- CHECKLIST RIESGOS LOCATIVOS -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CHECKLIST RIESGOS LOCATIVOS</h6>
                <?php foreach ($checks as $col => $info):
                    $val = $inspeccion[$col] ?? 'NA';
                ?>
                <div class="border rounded p-2 mb-2">
                    <div style="font-size:13px;"><strong><?= $info['codigo'] ?></strong> — <?= esc($info['label']) ?></div>
                    <div style="font-size:11px; color:#888;"><?= esc($info['fundamento']) ?></div>
                    <div class="btn-group btn-group-sm mt-1" role="group">
                        <?php foreach (['SI','NO','NA'] as $opt):
                            $cls = $val === $opt ? 'btn-primary' : 'btn-outline-secondary';
                        ?>
                        <input type="radio" class="btn-check" name="<?= $col ?>" id="<?= $col ?>_<?= $opt ?>" value="<?= $opt ?>" <?= $val === $opt ? 'checked' : '' ?>>
                        <label class="btn btn-sm <?= $cls ?>" for="<?= $col ?>_<?= $opt ?>"><?= $opt ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- EVIDENCIAS 6 SLOTS -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">EVIDENCIAS FOTOGRAFICAS (6 slots)</h6>
                <?php for ($slot = 1; $slot <= $totalSlots; $slot++):
                    $ev = $evidenciaMapa[$slot] ?? null;
                ?>
                <div class="border rounded p-2 mb-3" style="background:#fafbfc;">
                    <div style="font-size:13px; font-weight:600; color:#1c2437;">Slot <?= $slot ?></div>
                    <?php if ($ev && !empty($ev['ruta_foto'])): ?>
                    <div class="my-1">
                        <div style="font-size:10px; color:#28a745; margin-bottom:2px;"><i class="fas fa-check-circle"></i> Guardada en servidor:</div>
                        <img src="/<?= esc($ev['ruta_foto']) ?>" class="img-thumbnail" style="max-height:100px;">
                    </div>
                    <?php endif; ?>
                    <div class="my-1" id="preview_container_<?= $slot ?>" style="display:none;">
                        <div style="font-size:10px; color:#bd9751; margin-bottom:2px;"><i class="fas fa-clock"></i> Vista previa (aun no guardada):</div>
                        <img id="preview_<?= $slot ?>" src="" class="img-thumbnail" style="max-height:120px; border:2px dashed #bd9751;">
                    </div>
                    <div class="mb-1">
                        <label class="form-label" style="font-size:12px;">Foto</label>
                        <input type="file" name="slot_foto_<?= $slot ?>" class="form-control form-control-sm slot-foto-input" accept="image/*" data-slot="<?= $slot ?>" data-multi-name="1">
                    </div>
                    <div class="row g-1">
                        <div class="col-5">
                            <label class="form-label" style="font-size:12px;">Categoria</label>
                            <select name="slot_categoria_<?= $slot ?>" class="form-select form-select-sm">
                                <option value="">--</option>
                                <?php foreach ($categorias ?? [] as $cat): ?>
                                <option value="<?= esc($cat['codigo']) ?>" <?= ($ev['categoria'] ?? '') === $cat['codigo'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label" style="font-size:12px;">Descripcion</label>
                            <input type="text" name="slot_descripcion_<?= $slot ?>" class="form-control form-control-sm" value="<?= esc($ev['descripcion'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- OBSERVACIONES Y RECOMENDACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES Y RECOMENDACIONES</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Observaciones generales</label>
                    <textarea name="observaciones_generales" class="form-control form-control-sm" rows="3"><?= esc($inspeccion['observaciones_generales'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Recomendaciones generales</label>
                    <textarea name="recomendaciones_generales" class="form-control form-control-sm" rows="3"><?= esc($inspeccion['recomendaciones_generales'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="d-grid gap-3 mt-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar
            </button>
        </div>

        <?php if ($isEdit && !empty($inspeccion['id_cliente'])): ?>
        <!-- PROCEDIMIENTO DE EMERGENCIA -->
        <div class="card mt-4 mb-5" style="border-color:#dc3545;">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> PROCEDIMIENTO DE EMERGENCIA</h6>
                <p style="font-size:12px; color:#666;">Crea un procedimiento de emergencia especifico para esta area (gimnasio) prellenando cliente y tipo de area.</p>
                <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/create/' . $inspeccion['id_cliente'] . '?area=GYM') ?>"
                   class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-plus"></i> Crear procedimiento de emergencia (Gimnasio)
                </a>
            </div>
        </div>
        <?php endif; ?>
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

    // Preview instantaneo de foto en slots
    document.querySelectorAll('.slot-foto-input').forEach(function(inp) {
        inp.addEventListener('change', function() {
            const slot = this.dataset.slot;
            const prevContainer = document.getElementById('preview_container_' + slot);
            const prevImg = document.getElementById('preview_' + slot);
            if (!this.files || !this.files[0]) { prevContainer.style.display = 'none'; return; }
            const file = this.files[0];
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function(e) { prevImg.src = e.target.result; prevContainer.style.display = 'block'; };
            reader.readAsDataURL(file);
        });
    });

    // Confirmacion antes de finalizar
    const btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function(e) {
            if (!confirm('Finalizar inspeccion? Se generara el PDF y no podra editarse.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Autoguardado servidor
    if (typeof initAutosave === 'function') {
        const STORAGE_KEY = 'gym_draft_<?= $inspeccion['id'] ?? 'new' ?>';
        initAutosave({
            formId: 'gymForm',
            storeUrl: '<?= base_url('/inspecciones/gimnasio/store') ?>',
            updateUrlBase: '<?= base_url('/inspecciones/gimnasio/update/') ?>',
            editUrlBase: '<?= base_url('/inspecciones/gimnasio/edit/') ?>',
            recordId: <?= $inspeccion['id'] ?? 'null' ?>,
            isEdit: <?= $isEdit ? 'true' : 'false' ?>,
            storageKey: STORAGE_KEY,
            intervalSeconds: 60,
        });
    }
});
</script>
