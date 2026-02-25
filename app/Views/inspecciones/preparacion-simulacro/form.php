<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/preparacion-simulacro/update/' . $inspeccion['id'] : '/inspecciones/preparacion-simulacro/store';
$alarmaSeleccionados = $isEdit && !empty($inspeccion['tipo_alarma']) ? explode(',', $inspeccion['tipo_alarma']) : [];
$distintivosSeleccionados = $isEdit && !empty($inspeccion['distintivos_brigadistas']) ? explode(',', $inspeccion['distintivos_brigadistas']) : [];
$equiposSeleccionados = $isEdit && !empty($inspeccion['equipos_emergencia']) ? explode(',', $inspeccion['equipos_emergencia']) : [];
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="prepSimForm">
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

        <!-- 1. DATOS GENERALES -->
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
                    <label class="form-label">Fecha simulacro *</label>
                    <input type="date" name="fecha_simulacro" class="form-control"
                        value="<?= $inspeccion['fecha_simulacro'] ?? date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>

        <!-- 2. UBICACION -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">UBICACION</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Ubicacion (coordenadas GPS)</label>
                    <input type="text" name="ubicacion" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['ubicacion'] ?? '') ?>" placeholder="Ej: 4.6097, -74.0817">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Direccion</label>
                    <input type="text" name="direccion" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['direccion'] ?? '') ?>" placeholder="Direccion del simulacro">
                </div>
            </div>
        </div>

        <!-- 3. CONFIGURACION DEL SIMULACRO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CONFIGURACION DEL SIMULACRO</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Evento simulado</label>
                    <select name="evento_simulado" class="form-select form-select-sm">
                        <option value="">Seleccionar...</option>
                        <option value="sismo" <?= ($inspeccion['evento_simulado'] ?? '') === 'sismo' ? 'selected' : '' ?>>Sismo</option>
                        <option value="incendio" <?= ($inspeccion['evento_simulado'] ?? '') === 'incendio' ? 'selected' : '' ?>>Incendio</option>
                        <option value="inundacion" <?= ($inspeccion['evento_simulado'] ?? '') === 'inundacion' ? 'selected' : '' ?>>Inundacion</option>
                        <option value="explosion" <?= ($inspeccion['evento_simulado'] ?? '') === 'explosion' ? 'selected' : '' ?>>Explosion</option>
                        <option value="derrame" <?= ($inspeccion['evento_simulado'] ?? '') === 'derrame' ? 'selected' : '' ?>>Derrame</option>
                        <option value="otro" <?= ($inspeccion['evento_simulado'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Alcance del simulacro</label>
                    <select name="alcance_simulacro" class="form-select form-select-sm">
                        <option value="">Seleccionar...</option>
                        <option value="total" <?= ($inspeccion['alcance_simulacro'] ?? '') === 'total' ? 'selected' : '' ?>>Total</option>
                        <option value="parcial" <?= ($inspeccion['alcance_simulacro'] ?? '') === 'parcial' ? 'selected' : '' ?>>Parcial</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Tipo de evacuacion</label>
                    <select name="tipo_evacuacion" class="form-select form-select-sm">
                        <option value="">Seleccionar...</option>
                        <option value="horizontal" <?= ($inspeccion['tipo_evacuacion'] ?? '') === 'horizontal' ? 'selected' : '' ?>>Horizontal</option>
                        <option value="vertical" <?= ($inspeccion['tipo_evacuacion'] ?? '') === 'vertical' ? 'selected' : '' ?>>Vertical</option>
                        <option value="mixta" <?= ($inspeccion['tipo_evacuacion'] ?? '') === 'mixta' ? 'selected' : '' ?>>Mixta</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Personal que no evacua</label>
                    <textarea name="personal_no_evacua" class="form-control form-control-sm" rows="2"
                        placeholder="Personal que no evacua..."><?= esc($inspeccion['personal_no_evacua'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 4. ALARMA Y DISTINTIVOS -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ALARMA Y DISTINTIVOS</h6>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Tipo de alarma</label>
                    <?php foreach ($opcionesAlarma as $key => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="tipo_alarma[]" value="<?= $key ?>" id="alarma_<?= $key ?>"
                            <?= in_array($key, $alarmaSeleccionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="alarma_<?= $key ?>" style="font-size:13px;"><?= $label ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Distintivos de brigadistas</label>
                    <?php foreach ($opcionesDistintivos as $key => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="distintivos_brigadistas[]" value="<?= $key ?>" id="dist_<?= $key ?>"
                            <?= in_array($key, $distintivosSeleccionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="dist_<?= $key ?>" style="font-size:13px;"><?= $label ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 5. LOGISTICA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">LOGISTICA</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Puntos de encuentro</label>
                    <textarea name="puntos_encuentro" class="form-control form-control-sm" rows="2"
                        placeholder="Puntos de encuentro..."><?= esc($inspeccion['puntos_encuentro'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Recurso humano</label>
                    <textarea name="recurso_humano" class="form-control form-control-sm" rows="2"
                        placeholder="Recurso humano disponible..."><?= esc($inspeccion['recurso_humano'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Equipos de emergencia</label>
                    <?php foreach ($opcionesEquipos as $key => $label): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="equipos_emergencia[]" value="<?= $key ?>" id="equipo_<?= $key ?>"
                            <?= in_array($key, $equiposSeleccionados) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="equipo_<?= $key ?>" style="font-size:13px;"><?= $label ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 6. BRIGADISTA LIDER -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">BRIGADISTA LIDER</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Nombre brigadista lider</label>
                    <input type="text" name="nombre_brigadista_lider" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['nombre_brigadista_lider'] ?? '') ?>" placeholder="Nombre completo">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Email brigadista lider</label>
                    <input type="email" name="email_brigadista_lider" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['email_brigadista_lider'] ?? '') ?>" placeholder="email@ejemplo.com">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">WhatsApp brigadista lider</label>
                    <input type="text" name="whatsapp_brigadista_lider" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['whatsapp_brigadista_lider'] ?? '') ?>" placeholder="Numero de WhatsApp">
                </div>
            </div>
        </div>

        <!-- 7. REGISTRO FOTOGRAFICO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Imagen 1</label>
                    <?php if ($isEdit && !empty($inspeccion['imagen_1'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['imagen_1']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="imagen_1" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Imagen 2</label>
                    <?php if ($isEdit && !empty($inspeccion['imagen_2'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['imagen_2']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="imagen_2" class="form-control form-control-sm" accept="image/*">
                </div>
            </div>
        </div>

        <!-- 8. CRONOGRAMA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CRONOGRAMA</h6>
                <?php foreach ($cronogramaItems as $key => $label): ?>
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center" style="border-color:#dee2e6 !important;">
                    <span style="font-size:13px;"><?= $label ?></span>
                    <input type="time" name="<?= $key ?>" class="form-control form-control-sm" style="width:130px;"
                        value="<?= esc($inspeccion[$key] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 9. EVALUACION Y OBSERVACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">EVALUACION Y OBSERVACIONES</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Entrega formato de evaluacion</label>
                    <select name="entrega_formato_evaluacion" class="form-select form-select-sm">
                        <option value="">Seleccionar...</option>
                        <option value="si" <?= ($inspeccion['entrega_formato_evaluacion'] ?? '') === 'si' ? 'selected' : '' ?>>Si</option>
                        <option value="no" <?= ($inspeccion['entrega_formato_evaluacion'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Observaciones</label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                        placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 10. BOTONES -->
        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-outline flex-fill">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary flex-fill"
                onclick="return confirm('Finalizar inspeccion? Se generara el PDF y no podra editarse.')">
                <i class="fas fa-check-circle"></i> Finalizar
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectedCliente = '<?= $idCliente ?? '' ?>';

    $.ajax({
        url: '/inspecciones/api/clientes',
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

    // Autoguardado localStorage
    const formId = '<?= $isEdit ? $inspeccion['id'] : 'new' ?>';
    const STORAGE_KEY = 'prep_sim_draft_' + formId;
    const form = document.getElementById('prepSimForm');
    let debounceTimer;

    function saveDraft() {
        const data = {};
        form.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="date"], input[type="time"], textarea, select').forEach(el => {
            if (el.name && el.type !== 'file') data[el.name] = el.value;
        });
        // Save checkboxes
        form.querySelectorAll('input[type="checkbox"]').forEach(el => {
            if (!data['_chk_' + el.name]) data['_chk_' + el.name] = [];
            if (el.checked) data['_chk_' + el.name].push(el.value);
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function loadDraft() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(name => {
                if (name.startsWith('_chk_')) {
                    const realName = name.replace('_chk_', '');
                    const vals = data[name];
                    if (Array.isArray(vals)) {
                        form.querySelectorAll('input[name="' + realName + '"]').forEach(el => {
                            el.checked = vals.includes(el.value);
                        });
                    }
                } else {
                    const el = form.querySelector('[name="' + name + '"]');
                    if (el && el.type !== 'file' && !el.value) el.value = data[name];
                }
            });
        } catch(e) {}
    }

    form.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    form.addEventListener('change', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    setInterval(saveDraft, 30000);
    form.addEventListener('submit', () => { localStorage.removeItem(STORAGE_KEY); });
    if (!<?= $isEdit ? 'true' : 'false' ?>) loadDraft();
});
</script>
