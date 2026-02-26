<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php foreach (session()->getFlashdata('errors') as $e): ?>
        <div><?= esc($e) ?></div>
    <?php endforeach; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
$isEdit = !empty($url);
$action = $isEdit ? '/inspecciones/urls/update/' . $url['id'] : '/inspecciones/urls/store';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-link me-2"></i><?= esc($title) ?></h5>
    <a href="/inspecciones/urls" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<form method="post" action="<?= $action ?>">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
            <i class="fas fa-info-circle me-1"></i> Datos del Acceso Rápido
        </div>
        <div class="card-body p-3">
            <!-- Tipo -->
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">Tipo / Categoría <span class="text-danger">*</span></label>
                <select name="tipo" id="selectTipo" class="form-select form-select-sm" required>
                    <option value="">Seleccione tipo...</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= esc($t) ?>" <?= ($isEdit && $url['tipo'] === $t) ? 'selected' : '' ?>>
                            <?= esc($t) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__OTRO__">+ Nuevo tipo...</option>
                </select>
            </div>

            <!-- Tipo nuevo (oculto por defecto) -->
            <div class="mb-3" id="tipoNuevoGroup" style="display:none;">
                <label class="form-label" style="font-size:12px;">Nombre del nuevo tipo</label>
                <input type="text" name="tipo_nuevo" id="tipoNuevo" class="form-control form-control-sm"
                       placeholder="Ej: CAPACITACIONES" maxlength="100">
                <div class="form-text" style="font-size:11px;">Se guardará en mayúsculas.</div>
            </div>

            <!-- Nombre -->
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control form-control-sm"
                       value="<?= $isEdit ? esc($url['nombre']) : old('nombre') ?>" required maxlength="255"
                       placeholder="Ej: QR INDUCCION SST">
            </div>

            <!-- URL -->
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">URL <span class="text-danger">*</span></label>
                <input type="url" name="url" class="form-control form-control-sm"
                       value="<?= $isEdit ? esc($url['url']) : old('url') ?>" required maxlength="1000"
                       placeholder="https://...">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-pwa btn-pwa-primary w-100 mb-4">
        <i class="fas fa-save me-1"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
    </button>
</form>

<script>
document.getElementById('selectTipo').addEventListener('change', function() {
    var grupo = document.getElementById('tipoNuevoGroup');
    var input = document.getElementById('tipoNuevo');
    if (this.value === '__OTRO__') {
        grupo.style.display = '';
        input.required = true;
        input.focus();
    } else {
        grupo.style.display = 'none';
        input.required = false;
        input.value = '';
    }
});
</script>
