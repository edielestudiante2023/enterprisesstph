<?php
/**
 * @var array      $cfg         ['nombre', 'slug', 'icon', 'detailreport']
 * @var int        $tipo
 * @var int|null   $idCliente
 * @var array|null $cliente
 * @var array|null $vencimiento  vencimiento pendiente para este cliente/tipo (si existe)
 */
$action = base_url('/inspecciones/' . $cfg['slug'] . '/store');
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="formCert">
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

        <!-- Fecha del servicio -->
        <div class="mb-3">
            <label class="form-label">Fecha del servicio *</label>
            <input type="date" name="fecha_servicio" class="form-control"
                value="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Certificado -->
        <div class="mb-3">
            <label class="form-label">Certificado (PDF o imagen)</label>
            <input type="file" name="archivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            <div class="form-text">Opcional. PDF o imagen del certificado del proveedor.</div>
        </div>

        <!-- Observaciones -->
        <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"
                placeholder="Empresa ejecutora, número de contrato..."><?= esc($observaciones ?? '') ?></textarea>
        </div>

        <!-- Cerrar vencimiento -->
        <div id="seccionVencimiento" class="mb-3" style="display:none;">
            <div class="form-check p-3" style="background:#fff3cd; border-radius:8px; border:1px solid #ffc107;">
                <input class="form-check-input" type="checkbox" name="cerrar_vencimiento" id="checkVenc" value="1" checked>
                <input type="hidden" name="id_vencimiento" id="hiddenIdVenc" value="">
                <label class="form-check-label" for="checkVenc" style="font-size:14px;">
                    <strong>Cerrar vencimiento pendiente</strong>
                    <div class="text-muted" style="font-size:12px;" id="textoVenc"></div>
                </label>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar
            </button>
            <a href="<?= base_url('/inspecciones/' . $cfg['slug']) ?>" class="btn btn-outline-secondary py-3" style="font-size:17px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var tipo = <?= (int)$tipo ?>;

    <?php if (!$cliente): ?>
    // Cargar clientes
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

    // Al cambiar cliente, buscar vencimiento pendiente
    $('#selectCliente').on('change', function() {
        var idCliente = this.value;
        if (!idCliente) {
            document.getElementById('seccionVencimiento').style.display = 'none';
            return;
        }
        $.getJSON('<?= base_url('/inspecciones/certificado-servicio/vencimiento/') ?>' + tipo + '?id_cliente=' + idCliente,
            function(resp) {
                mostrarVencimiento(resp.vencimiento);
            });
    });
    <?php else: ?>
    // Cliente fijo — cargar vencimiento directamente
    <?php if ($vencimiento): ?>
    mostrarVencimiento(<?= json_encode($vencimiento) ?>);
    <?php endif; ?>
    <?php endif; ?>

    function mostrarVencimiento(venc) {
        var sec = document.getElementById('seccionVencimiento');
        var txt = document.getElementById('textoVenc');
        var hid = document.getElementById('hiddenIdVenc');
        if (venc) {
            var fecha = new Date(venc.fecha_vencimiento + 'T00:00:00').toLocaleDateString('es-CO', {day:'2-digit',month:'2-digit',year:'numeric'});
            txt.textContent = 'Vence: ' + fecha;
            hid.value = venc.id_vencimientos_mmttos || venc.id || '';
            sec.style.display = 'block';
        } else {
            sec.style.display = 'none';
            hid.value = '';
        }
    }

});
</script>
