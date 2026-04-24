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

        <!-- Vencimiento pendiente (siempre visible) -->
        <div id="seccionVencimiento" class="mb-3">
            <div id="vencPendiente" class="p-3" style="background:#fff3cd; border-radius:8px; border:1px solid #ffc107; display:none;">
                <input type="hidden" name="id_vencimiento" id="hiddenIdVenc" value="">
                <div style="font-size:14px;">
                    <i class="fas fa-clock text-warning"></i>
                    <strong>Vencimiento pendiente en el sistema:</strong>
                    <span id="textoVencFecha" class="fw-bold"></span>
                </div>
                <div class="text-muted" style="font-size:12px;">Al guardar se cerrará este vencimiento y se creará el nuevo.</div>
            </div>
            <div id="vencNoRegistros" class="p-3" style="background:#e2e3e5; border-radius:8px; border:1px solid #adb5bd; display:none;">
                <div style="font-size:14px;">
                    <i class="fas fa-info-circle text-secondary"></i>
                    <strong>No hay vencimientos pendientes</strong> de <?= esc($cfg['nombre']) ?> para este cliente.
                </div>
                <div class="text-muted" style="font-size:12px;">Se creará un nuevo vencimiento con la fecha indicada abajo.</div>
            </div>
            <div id="vencSeleccione" class="p-3" style="background:#f8f9fa; border-radius:8px; border:1px solid #dee2e6;">
                <div style="font-size:14px;" class="text-muted">
                    <i class="fas fa-hand-pointer"></i> Seleccione un cliente para verificar vencimientos.
                </div>
            </div>
        </div>

        <!-- Fecha de inspección (reporte) -->
        <div class="mb-3">
            <label class="form-label">Fecha de la inspección (reporte) *</label>
            <input type="date" name="fecha_inspeccion" id="fechaInspeccion" class="form-control"
                value="<?= date('Y-m-d') ?>" required>
            <div class="form-text">Fecha en que se realiza este reporte/registro.</div>
        </div>

        <!-- Fecha del servicio / intervención -->
        <div class="mb-3">
            <label class="form-label">Fecha del servicio / intervención *</label>
            <input type="date" name="fecha_servicio" id="fechaServicio" class="form-control"
                value="<?= date('Y-m-d') ?>" required>
            <div class="form-text">Fecha en que se ejecutó la actividad (lavado, fumigación, etc.).</div>
        </div>

        <!-- Nuevo vencimiento -->
        <div class="mb-3">
            <label class="form-label">Nueva fecha de vencimiento *</label>
            <input type="date" name="nueva_fecha_vencimiento" id="nuevaFechaVenc" class="form-control"
                value="<?= date('Y-m-d', strtotime('+6 months')) ?>" required>
            <div class="form-text">Sugerida a 6 meses desde la fecha del servicio. Puede editarla.</div>
        </div>

        <!-- Certificado -->
        <div class="mb-3">
            <label class="form-label">Certificado (PDF o imagen)</label>
            <input type="file" name="archivo" class="foto-input-pwa" accept=".pdf,.jpg,.jpeg,.png" data-label="Certificado (PDF o imagen)">
            <div class="form-text">Opcional. PDF o imagen del certificado del proveedor.</div>
        </div>

        <!-- Observaciones -->
        <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"
                placeholder="Empresa ejecutora, número de contrato..."><?= esc($observaciones ?? '') ?></textarea>
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
    var fechaServicioInput = document.getElementById('fechaServicio');
    var nuevaFechaVencInput = document.getElementById('nuevaFechaVenc');

    // Recalcular nueva fecha vencimiento al cambiar fecha servicio (+6 meses)
    fechaServicioInput.addEventListener('change', function() {
        if (this.value) {
            var d = new Date(this.value + 'T00:00:00');
            d.setMonth(d.getMonth() + 6);
            nuevaFechaVencInput.value = d.toISOString().split('T')[0];
        }
    });

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
            mostrarEstadoVencimiento(null, false);
            return;
        }
        $.getJSON('<?= base_url('/inspecciones/certificado-servicio/vencimiento/') ?>' + tipo + '?id_cliente=' + idCliente,
            function(resp) {
                mostrarEstadoVencimiento(resp.vencimiento, true);
            });
    });
    <?php else: ?>
    // Cliente fijo — cargar vencimiento directamente
    mostrarEstadoVencimiento(<?= json_encode($vencimiento) ?>, true);
    <?php endif; ?>

    function mostrarEstadoVencimiento(venc, clienteSeleccionado) {
        var pendiente    = document.getElementById('vencPendiente');
        var noRegistros  = document.getElementById('vencNoRegistros');
        var seleccione   = document.getElementById('vencSeleccione');
        var txtFecha     = document.getElementById('textoVencFecha');
        var hiddenId     = document.getElementById('hiddenIdVenc');

        pendiente.style.display   = 'none';
        noRegistros.style.display = 'none';
        seleccione.style.display  = 'none';

        if (!clienteSeleccionado) {
            seleccione.style.display = 'block';
            hiddenId.value = '';
            return;
        }

        if (venc) {
            var fecha = new Date(venc.fecha_vencimiento + 'T00:00:00')
                .toLocaleDateString('es-CO', {day:'2-digit', month:'long', year:'numeric'});
            txtFecha.textContent = fecha;
            hiddenId.value = venc.id_vencimientos_mmttos || venc.id || '';
            pendiente.style.display = 'block';
        } else {
            hiddenId.value = '';
            noRegistros.style.display = 'block';
        }
    }

});
</script>
