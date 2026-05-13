<?php
$action = base_url('/inspecciones/encuesta-caracterizacion/store');
?>
<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <a href="<?= base_url('/inspecciones/encuesta-caracterizacion') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;">Nuevo Items Nucleares SG-SST</h6>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Cliente *</label>
                    <select name="id_cliente" id="selectCliente" class="form-select form-select-sm" required>
                        <option value="">Seleccionar cliente...</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label" style="font-size:13px;">Titulo</label>
                    <input type="text" name="titulo" class="form-control form-control-sm"
                        value="<?= esc(old('titulo') ?: 'Items Nucleares SG-SST') ?>"
                        placeholder="Items Nucleares SG-SST">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-pwa btn-pwa-outline w-100">
            <i class="fas fa-save"></i> Crear encuesta
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function cargarClientes() {
        if (typeof $ === 'undefined' || !$.fn.select2) {
            setTimeout(cargarClientes, 50);
            return;
        }

        $.ajax({
            url: '<?= base_url('/inspecciones/api/clientes') ?>',
            dataType: 'json',
            success: function(data) {
                var select = document.getElementById('selectCliente');
                var oldCliente = '<?= esc(old('id_cliente') ?? '') ?>';

                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id_cliente;
                    opt.textContent = c.nombre_cliente;
                    if (oldCliente && String(c.id_cliente) === String(oldCliente)) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                });

                $('#selectCliente').select2({
                    placeholder: 'Buscar cliente...',
                    allowClear: true,
                    width: '100%',
                });
            }
        });
    }

    cargarClientes();
});
</script>
