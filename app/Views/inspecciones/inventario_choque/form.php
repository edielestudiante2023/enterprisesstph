<?php
$isEdit = !empty($inventario);
$action = $isEdit
    ? base_url('/inspecciones/inventario-choque/update/') . $inventario['id']
    : base_url('/inspecciones/inventario-choque/store');
?>

<div class="container-fluid px-3 mt-2">
    <h6 class="mb-3">
        <i class="fas fa-clipboard-check"></i>
        <?= $isEdit ? 'Editar Inventario de Choque' : 'Nuevo Inventario de Choque' ?>
    </h6>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger" style="font-size:13px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label">Cliente *</label>
            <select name="id_cliente" id="selectCliente" class="form-select" required>
                <option value="">Seleccionar cliente...</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha de captura *</label>
            <input type="date" name="fecha_captura" class="form-control"
                   value="<?= $inventario['fecha_captura'] ?? date('Y-m-d') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3"
                      placeholder="Notas generales del recorrido..."><?= esc($inventario['observaciones'] ?? '') ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-pwa-primary" style="flex:1;">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Crear y comenzar' ?>
            </button>
            <a href="<?= base_url('/inspecciones/inventario-choque') ?>" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';
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
        }
    });
});
</script>
