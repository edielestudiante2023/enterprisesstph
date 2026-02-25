<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? '/inspecciones/dotacion-aseadora/update/' . $inspeccion['id'] : '/inspecciones/dotacion-aseadora/store';
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="dotAseForm">
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
            </div>
        </div>

        <!-- DATOS DEL CONTRATISTA -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">DATOS DEL CONTRATISTA</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Contratista</label>
                    <input type="text" name="contratista" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['contratista'] ?? '') ?>" placeholder="Nombre del contratista">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Servicio</label>
                    <input type="text" name="servicio" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['servicio'] ?? '') ?>" placeholder="Servicio prestado">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Nombre / Cargo</label>
                    <input type="text" name="nombre_cargo" class="form-control form-control-sm"
                        value="<?= esc($inspeccion['nombre_cargo'] ?? '') ?>" placeholder="Nombre y cargo">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Actividades frecuentes</label>
                    <textarea name="actividades_frecuentes" class="form-control form-control-sm" rows="2"
                        placeholder="Actividades frecuentes..."><?= esc($inspeccion['actividades_frecuentes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- REGISTRO FOTOGRAFICO -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto cuerpo completo</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_cuerpo_completo'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_cuerpo_completo']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_cuerpo_completo" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:12px;">Foto cuarto de almacenamiento</label>
                    <?php if ($isEdit && !empty($inspeccion['foto_cuarto_almacenamiento'])): ?>
                    <div class="mb-1">
                        <img src="/<?= esc($inspeccion['foto_cuarto_almacenamiento']) ?>" class="img-thumbnail" style="max-height:80px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="foto_cuarto_almacenamiento" class="form-control form-control-sm" accept="image/*">
                </div>
            </div>
        </div>

        <!-- ESTADO DE DOTACION EPP -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">ESTADO DE DOTACION EPP</h6>
                <?php foreach ($itemsEpp as $key => $info): ?>
                <div class="border rounded p-2 mb-2" style="border-color:#dee2e6 !important;">
                    <div class="d-flex align-items-center mb-1">
                        <i class="fas <?= $info['icon'] ?> text-primary me-2" style="font-size:16px;"></i>
                        <strong style="font-size:13px;"><?= $info['label'] ?></strong>
                    </div>
                    <select name="estado_<?= $key ?>" class="form-select form-select-sm">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($estadosEpp as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($inspeccion['estado_' . $key] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CONCEPTO FINAL Y OBSERVACIONES -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title" style="font-size:14px; color:#999;">CONCEPTO FINAL Y OBSERVACIONES</h6>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Concepto final</label>
                    <textarea name="concepto_final" class="form-control form-control-sm" rows="3"
                        placeholder="Concepto final..."><?= esc($inspeccion['concepto_final'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Observaciones</label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                        placeholder="Observaciones..."><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
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
    const STORAGE_KEY = 'dot_ase_draft_' + formId;
    const form = document.getElementById('dotAseForm');
    let debounceTimer;

    function saveDraft() {
        const data = {};
        form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea, select').forEach(el => {
            if (el.name && el.type !== 'file') data[el.name] = el.value;
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function loadDraft() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return;
        try {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(name => {
                const el = form.querySelector('[name="' + name + '"]');
                if (el && el.type !== 'file' && !el.value) el.value = data[name];
            });
        } catch(e) {}
    }

    form.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(saveDraft, 2000); });
    setInterval(saveDraft, 30000);
    form.addEventListener('submit', () => { localStorage.removeItem(STORAGE_KEY); });
    if (!<?= $isEdit ? 'true' : 'false' ?>) loadDraft();
});
</script>
