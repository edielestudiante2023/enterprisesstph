<?php
/**
 * @var int|null   $idCliente
 * @var array|null $cliente
 * @var array|null $registro
 */
$registro = $registro ?? null;
$isEdit   = !empty($registro);
$formAction = $isEdit
    ? base_url('/inspecciones/planilla-seg-social/update/' . $registro['id'])
    : base_url('/inspecciones/planilla-seg-social/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $formAction ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if ($flash = session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;"><?= esc($flash) ?></div>
        <?php endif; ?>

        <!-- Cliente -->
        <div class="mb-3 mt-2">
            <label class="form-label">Cliente *</label>
            <?php if ($cliente): ?>
                <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente']) ?>" readonly>
            <?php else: ?>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccionar cliente...</option>
                </select>
            <?php endif; ?>
        </div>

        <!-- Período -->
        <div class="mb-3">
            <label class="form-label">Período (mes/año) *</label>
            <input type="month" name="periodo" class="form-control"
                value="<?= $isEdit ? esc($registro['periodo']) : date('Y-m') ?>" required>
        </div>

        <!-- Archivo -->
        <div class="mb-3">
            <label class="form-label">Planilla (PDF o imagen)</label>
            <?php if ($isEdit && !empty($registro['archivo'])): ?>
            <div class="mb-2" style="font-size:13px;">
                Archivo actual:
                <a href="<?= base_url($registro['archivo']) ?>" target="_blank">
                    <i class="fas fa-file-alt"></i> Ver actual
                </a>
            </div>
            <?php endif; ?>
            <input type="file" name="archivo" class="foto-input-pwa" accept=".pdf,.jpg,.jpeg,.png" data-label="Soporte planilla (PDF o imagen)">
            <div class="form-text">
                <?= $isEdit
                    ? 'Opcional. Si no cargas archivo nuevo, se conserva el actual.'
                    : 'Opcional. Soporte de pago de planilla.' ?>
            </div>
        </div>

        <!-- Observaciones -->
        <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"
                placeholder="Notas adicionales..."><?= $isEdit ? esc($registro['observaciones']) : '' ?></textarea>
        </div>

        <!-- Botones -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?>
            </button>
            <a href="<?= base_url('/inspecciones/planilla-seg-social') ?>" class="btn btn-outline-secondary py-3" style="font-size:17px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$cliente): ?>
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var select = document.getElementById('selectCliente');
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Buscar cliente...', allowClear: true, width: '100%' });
        }
    });
    <?php endif; ?>
});
</script>
