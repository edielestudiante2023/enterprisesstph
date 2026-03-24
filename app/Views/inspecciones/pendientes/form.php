<?php
$isEdit = !empty($edit) && !empty($pendiente);
$action = $isEdit
    ? base_url('/inspecciones/pendientes/update/') . $pendiente['id_pendientes']
    : base_url('/inspecciones/pendientes/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="formPendiente">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Cliente -->
        <div class="mb-3 mt-2">
            <label class="form-label">Cliente *</label>
            <?php if ($isEdit): ?>
                <input type="text" class="form-control" value="<?= esc($cliente['nombre_cliente'] ?? '') ?>" readonly>
            <?php else: ?>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccionar cliente...</option>
                </select>
            <?php endif; ?>
        </div>

        <!-- Tarea/Actividad -->
        <div class="mb-3">
            <label class="form-label">Tarea / Actividad *</label>
            <textarea name="tarea_actividad" class="form-control" rows="3"
                placeholder="Descripcion de la tarea o compromiso..." required><?= esc($pendiente['tarea_actividad'] ?? '') ?></textarea>
        </div>

        <!-- Responsable -->
        <div class="mb-3">
            <label class="form-label">Responsable</label>
            <input type="text" name="responsable" class="form-control"
                value="<?= esc($pendiente['responsable'] ?? '') ?>" placeholder="Nombre del responsable">
        </div>

        <!-- Fecha cierre -->
        <div class="mb-3">
            <label class="form-label">Fecha limite de cierre</label>
            <input type="date" name="fecha_cierre" class="form-control"
                value="<?= esc($pendiente['fecha_cierre'] ?? '') ?>">
        </div>

        <?php if ($isEdit): ?>
        <!-- Estado -->
        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <?php
                $estados = ['ABIERTA', 'CERRADA', 'SIN RESPUESTA DEL CLIENTE'];
                foreach ($estados as $est):
                ?>
                <option value="<?= $est ?>" <?= ($pendiente['estado'] ?? '') === $est ? 'selected' : '' ?>>
                    <?= $est ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Estado de avance -->
        <div class="mb-3">
            <label class="form-label">Estado de avance</label>
            <textarea name="estado_avance" class="form-control" rows="2"
                placeholder="Notas de progreso..."><?= esc($pendiente['estado_avance'] ?? '') ?></textarea>
        </div>

        <!-- Evidencia -->
        <div class="mb-3">
            <label class="form-label">Evidencia para cerrarla</label>
            <textarea name="evidencia_para_cerrarla" class="form-control" rows="2"
                placeholder="Evidencia o soporte..."><?= esc($pendiente['evidencia_para_cerrarla'] ?? '') ?></textarea>
        </div>
        <?php endif; ?>

        <!-- Botones -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar
            </button>
            <a href="<?= base_url('/inspecciones/pendientes') ?><?= $idCliente ? '/cliente/' . $idCliente : '' ?>" class="btn btn-outline-secondary py-3" style="font-size:17px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$isEdit): ?>
    // Select2 cliente via AJAX
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
            <?php if ($idCliente ?? null): ?>
            $('#selectCliente').val('<?= $idCliente ?>').trigger('change');
            <?php endif; ?>
        }
    });
    <?php endif; ?>
});
</script>
