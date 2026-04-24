<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/zona-bbq/update/') . $inspeccion['id'] : base_url('/inspecciones/zona-bbq/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="bbqForm">
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
            <strong><i class="fas fa-fire-burner"></i> FT-SST-251 — Inspeccion Zona BBQ</strong>
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
                        <input type="text" name="horario_operacion" class="form-control form-control-sm" placeholder="7am-7pm" value="<?= esc($inspeccion['horario_operacion'] ?? '') ?>">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Tipo de combustible</label>
                        <select name="tipo_combustible" class="form-select form-select-sm">
                            <?php foreach ($combustibles as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ($inspeccion['tipo_combustible'] ?? 'GAS_LP') === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Numero de asadores</label>
                        <input type="number" min="1" name="numero_asadores" class="form-control form-control-sm" value="<?= esc($inspeccion['numero_asadores'] ?? 1) ?>">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Sistema de reserva</label>
                        <select name="tiene_sistema_reserva" class="form-select form-select-sm">
                            <?php foreach (['SI','NO','NA'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($inspeccion['tiene_sistema_reserva'] ?? 'NA') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Tipo de extintor</label>
                        <input type="text" name="tipo_extintor" class="form-control form-control-sm" placeholder="ABC 10 lb" value="<?= esc($inspeccion['tipo_extintor'] ?? '') ?>">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Dist. vegetacion (m)</label>
                        <input type="number" step="0.1" min="0" name="distancia_vegetacion_m" class="form-control form-control-sm" value="<?= esc($inspeccion['distancia_vegetacion_m'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Dist. vivienda (m)</label>
                        <input type="number" step="0.1" min="0" name="distancia_vivienda_m" class="form-control form-control-sm" value="<?= esc($inspeccion['distancia_vivienda_m'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- CHECKLIST -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CHECKLIST RIESGOS (BBQ-01..17)</h6>
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

        <!-- ASADORES (N items dinamicos) -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title d-flex justify-content-between align-items-center" style="font-size:14px; color:#999;">
                    ASADORES / PARRILLAS
                    <button type="button" id="addAsador" class="btn btn-sm btn-outline-primary" style="font-size:11px;"><i class="fas fa-plus"></i> Agregar</button>
                </h6>
                <div id="asadoresContainer">
                    <?php if (!empty($asadores)): foreach ($asadores as $a): ?>
                    <div class="border rounded p-2 mb-2 asador-row" style="background:#fafbfc;">
                        <div class="row g-1">
                            <div class="col-3">
                                <label style="font-size:11px;">N°</label>
                                <input type="text" name="asador_numero[]" class="form-control form-control-sm" value="<?= esc($a['numero']) ?>" required>
                            </div>
                            <div class="col-4">
                                <label style="font-size:11px;">Estado parrilla</label>
                                <select name="asador_estado_parrilla[]" class="form-select form-select-sm">
                                    <?php foreach (['operativo','danado','requiere_mant'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $a['estado_parrilla'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label style="font-size:11px;">Conexion gas</label>
                                <select name="asador_estado_gas[]" class="form-select form-select-sm">
                                    <?php foreach (['operativo','fuga_detectada','sin_conexion','no_aplica'] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= $a['estado_conexion_gas'] === $opt ? 'selected' : '' ?>><?= str_replace('_',' ', $opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-1 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asador" style="padding:2px 6px;"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="row g-1 mt-1">
                            <div class="col-5">
                                <label style="font-size:11px;">Ultima prueba fuga</label>
                                <input type="date" name="asador_fecha_prueba[]" class="form-control form-control-sm" value="<?= esc($a['fecha_ultima_prueba_fuga'] ?? '') ?>">
                            </div>
                            <div class="col-7">
                                <label style="font-size:11px;">Observaciones</label>
                                <input type="text" name="asador_obs[]" class="form-control form-control-sm" value="<?= esc($a['observaciones'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
                <p class="text-muted" style="font-size:11px;">Agrega 1 fila por cada asador. El numero puede ser "1", "A", etc.</p>
            </div>
        </div>

        <!-- EVIDENCIAS 6 SLOTS CON PREVIEW -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">EVIDENCIAS FOTOGRAFICAS (6 slots)</h6>
                <?php for ($slot = 1; $slot <= $totalSlots; $slot++):
                    $ev = $evidenciaMapa[$slot] ?? null;
                ?>
                <div class="border rounded p-2 mb-3" style="background:#fafbfc;">
                    <div style="font-size:13px; font-weight:600; color:#1c2437;">Slot <?= $slot ?></div>

                    <!-- Miniatura foto YA GUARDADA en servidor (si existe) -->
                    <?php if ($ev && !empty($ev['ruta_foto'])): ?>
                    <div class="my-1" id="saved_<?= $slot ?>">
                        <div style="font-size:10px; color:#28a745; margin-bottom:2px;"><i class="fas fa-check-circle"></i> Guardada en servidor:</div>
                        <img src="/<?= esc($ev['ruta_foto']) ?>" class="img-thumbnail" style="max-height:100px;">
                    </div>
                    <?php endif; ?>

                    <!-- Preview INSTANTANEO de foto recien seleccionada -->
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

        <!-- OBSERVACIONES -->
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
        <div class="card mt-4 mb-5" style="border-color:#dc3545;">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> PROCEDIMIENTO DE EMERGENCIA</h6>
                <p style="font-size:12px; color:#666;">Crea un procedimiento de emergencia especifico para la zona BBQ.</p>
                <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/create/' . $inspeccion['id_cliente'] . '?area=ZONA_BBQ') ?>"
                   class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-plus"></i> Crear procedimiento de emergencia (Zona BBQ)
                </a>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Template para nueva fila de asador (cloneable) -->
<template id="asadorTemplate">
    <div class="border rounded p-2 mb-2 asador-row" style="background:#fafbfc;">
        <div class="row g-1">
            <div class="col-3">
                <label style="font-size:11px;">N°</label>
                <input type="text" name="asador_numero[]" class="form-control form-control-sm" required>
            </div>
            <div class="col-4">
                <label style="font-size:11px;">Estado parrilla</label>
                <select name="asador_estado_parrilla[]" class="form-select form-select-sm">
                    <option value="operativo">operativo</option>
                    <option value="danado">danado</option>
                    <option value="requiere_mant">requiere_mant</option>
                </select>
            </div>
            <div class="col-4">
                <label style="font-size:11px;">Conexion gas</label>
                <select name="asador_estado_gas[]" class="form-select form-select-sm">
                    <option value="no_aplica">no aplica</option>
                    <option value="operativo">operativo</option>
                    <option value="fuga_detectada">fuga detectada</option>
                    <option value="sin_conexion">sin conexion</option>
                </select>
            </div>
            <div class="col-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-asador" style="padding:2px 6px;"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="row g-1 mt-1">
            <div class="col-5">
                <label style="font-size:11px;">Ultima prueba fuga</label>
                <input type="date" name="asador_fecha_prueba[]" class="form-control form-control-sm">
            </div>
            <div class="col-7">
                <label style="font-size:11px;">Observaciones</label>
                <input type="text" name="asador_obs[]" class="form-control form-control-sm">
            </div>
        </div>
    </div>
</template>

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

    // ==== PREVIEW INSTANTANEO DE FOTO EN SLOTS ====
    document.querySelectorAll('.slot-foto-input').forEach(function(inp) {
        inp.addEventListener('change', function() {
            const slot = this.dataset.slot;
            const prevContainer = document.getElementById('preview_container_' + slot);
            const prevImg = document.getElementById('preview_' + slot);
            if (!this.files || !this.files[0]) {
                prevContainer.style.display = 'none';
                return;
            }
            const file = this.files[0];
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                prevImg.src = e.target.result;
                prevContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    });

    // ==== ASADORES DINAMICOS ====
    const container = document.getElementById('asadoresContainer');
    const tpl = document.getElementById('asadorTemplate');
    document.getElementById('addAsador').addEventListener('click', function() {
        const clone = tpl.content.cloneNode(true);
        container.appendChild(clone);
    });
    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-asador')) {
            const row = e.target.closest('.asador-row');
            if (row) row.remove();
        }
    });

    // ==== FINALIZAR CONFIRMACION ====
    const btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function(e) {
            if (!confirm('Finalizar inspeccion? Se generara el PDF y no podra editarse.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // ==== AUTOSAVE ====
    if (typeof initAutosave === 'function') {
        const STORAGE_KEY = 'bbq_draft_<?= $inspeccion['id'] ?? 'new' ?>';
        initAutosave({
            formId: 'bbqForm',
            storeUrl: '<?= base_url('/inspecciones/zona-bbq/store') ?>',
            updateUrlBase: '<?= base_url('/inspecciones/zona-bbq/update/') ?>',
            editUrlBase: '<?= base_url('/inspecciones/zona-bbq/edit/') ?>',
            recordId: <?= $inspeccion['id'] ?? 'null' ?>,
            isEdit: <?= $isEdit ? 'true' : 'false' ?>,
            storageKey: STORAGE_KEY,
            intervalSeconds: 60,
        });
    }
});
</script>
