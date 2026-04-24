<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/turco-sauna/update/') . $inspeccion['id'] : base_url('/inspecciones/turco-sauna/store');
$recintoLabels = ['TURCO' => 'Baño turco', 'SAUNA' => 'Sauna', 'JACUZZI' => 'Jacuzzi'];
$recintoIcons = ['TURCO' => 'fa-cloud', 'SAUNA' => 'fa-fire-flame-simple', 'JACUZZI' => 'fa-hot-tub-person'];
$recintoFlags = ['TURCO' => 'aplica_turco', 'SAUNA' => 'aplica_sauna', 'JACUZZI' => 'aplica_jacuzzi'];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="tsjForm">
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
            <strong><i class="fas fa-hot-tub-person"></i> FT-SST-249 — Inspeccion Turco + Sauna + Jacuzzi</strong>
        </div>

        <!-- DATOS GENERALES + FLAGS -->
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
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Horario operacion</label>
                    <input type="text" name="horario_operacion" class="form-control form-control-sm" placeholder="Ej: 6am-10pm" value="<?= esc($inspeccion['horario_operacion'] ?? '') ?>">
                </div>

                <div class="alert alert-warning" style="font-size:12px; padding:8px 10px;">
                    <strong>Marca al menos un recinto presente:</strong>
                </div>
                <?php foreach (['TURCO','SAUNA','JACUZZI'] as $rc):
                    $flag = $recintoFlags[$rc];
                    $checked = !empty($inspeccion[$flag]) ? 'checked' : '';
                ?>
                <div class="form-check mb-2">
                    <input class="form-check-input flag-recinto" type="checkbox" name="<?= $flag ?>" value="1" id="flag_<?= $rc ?>" data-recinto="<?= $rc ?>" <?= $checked ?>>
                    <label class="form-check-label" for="flag_<?= $rc ?>">
                        <i class="fas <?= $recintoIcons[$rc] ?>"></i> <?= $recintoLabels[$rc] ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CHECKLIST MAESTRO (aplica a todos los recintos marcados) -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CHECKLIST COMUN (aplica a los recintos seleccionados)</h6>
                <?php foreach ($checksMaestro as $col => $info):
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

        <!-- SECCIONES POR RECINTO -->
        <?php foreach (['TURCO','SAUNA','JACUZZI'] as $rc):
            $det = $detalleMapa[$rc] ?? null;
            $flag = $recintoFlags[$rc];
            $activa = !empty($inspeccion[$flag]);
            $aforoField = 'aforo_maximo_' . strtolower($rc);
            $rcLower = strtolower($rc);
        ?>
        <div class="card mb-3 seccion-recinto" id="sec_<?= $rc ?>" style="<?= $activa ? '' : 'display:none;' ?>">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#1c2437;">
                    <i class="fas <?= $recintoIcons[$rc] ?>"></i> <?= $recintoLabels[$rc] ?>
                </h6>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Aforo maximo</label>
                        <input type="number" min="0" name="<?= $aforoField ?>" class="form-control form-control-sm" value="<?= esc($inspeccion[$aforoField] ?? '') ?>">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Material interno</label>
                    <input type="text" name="<?= $rc ?>_material_interno" class="form-control form-control-sm" value="<?= esc($det['material_interno'] ?? '') ?>" placeholder="ceramica, madera aislada, acrilico, etc.">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Fuente de calor</label>
                        <input type="text" name="<?= $rc ?>_fuente_calor" class="form-control form-control-sm" value="<?= esc($det['fuente_calor'] ?? '') ?>" placeholder="generador vapor, hornillo, etc.">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Temperatura operacion</label>
                        <input type="text" name="<?= $rc ?>_temperatura_operacion" class="form-control form-control-sm" value="<?= esc($det['temperatura_operacion'] ?? '') ?>" placeholder="43-46 °C / 80-90 °C">
                    </div>
                </div>

                <?php if ($rc === 'JACUZZI'): ?>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Profundidad (m)</label>
                        <input type="number" step="0.01" min="0" name="JACUZZI_profundidad_m" class="form-control form-control-sm" value="<?= esc($det['profundidad_m'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:12px;">Temp. agua (°C)</label>
                        <input type="number" step="0.1" min="0" max="50" name="JACUZZI_temperatura_agua_c" class="form-control form-control-sm" value="<?= esc($det['temperatura_agua_c'] ?? '') ?>">
                    </div>
                </div>
                <?php endif; ?>

                <!-- Checks especificos del recinto -->
                <?php foreach ($checksDetalle as $col => $info):
                    if (!in_array($rc, $info['aplica'], true)) continue;
                    $name = $rc . '_' . $col;
                    $val = $det[$col] ?? 'NA';
                ?>
                <div class="border rounded p-2 mb-2" style="background:#fafbfc;">
                    <div style="font-size:12.5px;"><strong><?= $info['codigo'] ?></strong> — <?= esc($info['label']) ?></div>
                    <div style="font-size:11px; color:#888;"><?= esc($info['fundamento']) ?></div>
                    <div class="btn-group btn-group-sm mt-1" role="group">
                        <?php foreach (['SI','NO','NA'] as $opt):
                            $cls = $val === $opt ? 'btn-primary' : 'btn-outline-secondary';
                        ?>
                        <input type="radio" class="btn-check" name="<?= $name ?>" id="<?= $name ?>_<?= $opt ?>" value="<?= $opt ?>" <?= $val === $opt ? 'checked' : '' ?>>
                        <label class="btn btn-sm <?= $cls ?>" for="<?= $name ?>_<?= $opt ?>"><?= $opt ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Observaciones del recinto</label>
                    <textarea name="<?= $rc ?>_observaciones" class="form-control form-control-sm" rows="2"><?= esc($det['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- EVIDENCIAS 6 SLOTS -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">EVIDENCIAS FOTOGRAFICAS (6 slots)</h6>
                <?php for ($slot = 1; $slot <= $totalSlots; $slot++):
                    $ev = $evidenciaMapa[$slot] ?? null;
                ?>
                <div class="border rounded p-2 mb-3" style="background:#fafbfc;">
                    <div style="font-size:13px; font-weight:600; color:#1c2437;">Slot <?= $slot ?></div>
                    <div class="mb-1">
                        <label class="form-label" style="font-size:12px;">Foto</label>
                        <input type="file" name="slot_foto_<?= $slot ?>" class="foto-input-pwa" accept="image/*" data-slot="<?= $slot ?>" data-multi-name="1" data-label="Foto slot <?= $slot ?>"<?= ($ev && !empty($ev['ruta_foto'])) ? ' data-previous-url="/' . esc($ev['ruta_foto']) . '"' : '' ?>>
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
        <!-- PROCEDIMIENTO DE EMERGENCIA — 1 boton por recinto activo -->
        <div class="card mt-4 mb-5" style="border-color:#dc3545;">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> PROCEDIMIENTOS DE EMERGENCIA</h6>
                <p style="font-size:12px; color:#666;">Crea un procedimiento de emergencia especifico por recinto. Solo se habilitan los recintos que marcaste arriba.</p>
                <?php
                $mapaArea = ['TURCO' => 'BANO_TURCO', 'SAUNA' => 'SAUNA', 'JACUZZI' => 'JACUZZI'];
                foreach (['TURCO','SAUNA','JACUZZI'] as $rc):
                    $flag = $recintoFlags[$rc];
                    if (empty($inspeccion[$flag])) continue;
                ?>
                <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/create/' . $inspeccion['id_cliente'] . '?area=' . $mapaArea[$rc]) ?>"
                   class="btn btn-outline-danger btn-sm me-2 mb-2">
                    <i class="fas <?= $recintoIcons[$rc] ?>"></i> Crear procedimiento (<?= $recintoLabels[$rc] ?>)
                </a>
                <?php endforeach; ?>
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

    // Preview slots: gestionado por foto_input_pwa.js

    // Toggle secciones por recinto segun flags
    document.querySelectorAll('.flag-recinto').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const sec = document.getElementById('sec_' + this.dataset.recinto);
            if (sec) sec.style.display = this.checked ? '' : 'none';
        });
    });

    // Validacion: al menos uno marcado
    document.getElementById('tsjForm').addEventListener('submit', function(e) {
        const alguno = document.querySelectorAll('.flag-recinto:checked').length > 0;
        if (!alguno) {
            e.preventDefault();
            alert('Debes marcar al menos un recinto (turco, sauna o jacuzzi).');
            return false;
        }
    });

    // Confirmar finalizar
    const btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function(e) {
            if (!confirm('Finalizar inspeccion? Se generara el PDF y no podra editarse.')) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Autoguardado
    if (typeof initAutosave === 'function') {
        const STORAGE_KEY = 'tsj_draft_<?= $inspeccion['id'] ?? 'new' ?>';
        initAutosave({
            formId: 'tsjForm',
            storeUrl: '<?= base_url('/inspecciones/turco-sauna/store') ?>',
            updateUrlBase: '<?= base_url('/inspecciones/turco-sauna/update/') ?>',
            editUrlBase: '<?= base_url('/inspecciones/turco-sauna/edit/') ?>',
            recordId: <?= $inspeccion['id'] ?? 'null' ?>,
            isEdit: <?= $isEdit ? 'true' : 'false' ?>,
            storageKey: STORAGE_KEY,
            intervalSeconds: 60,
        });
    }
});
</script>
