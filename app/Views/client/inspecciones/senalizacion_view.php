<?php
$califColor = '#28a745';
$calif = (float)($inspeccion['calificacion'] ?? 0);
if ($calif <= 40) $califColor = '#dc3545';
elseif ($calif <= 60) $califColor = '#fd7e14';
elseif ($calif <= 80) $califColor = '#ffc107';
?>

<div class="page-header">
    <h1><i class="fas fa-sign me-2"></i> Inspección de Señalización</h1>
    <a href="<?= base_url('client/inspecciones/senalizacion') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
        </table>
    </div>
</div>

<!-- Resumen calificación -->
<div class="card mb-3" style="border: 2px solid <?= $califColor ?>;">
    <div class="card-body text-center py-3">
        <h6 class="card-title mb-2">CALIFICACIÓN</h6>
        <div style="font-size:2.5rem; font-weight:700; color:<?= $califColor ?>;">
            <?= number_format($calif, 1) ?>%
        </div>
        <div style="font-size:14px; color:#555; margin-top:4px;">
            <?= esc($inspeccion['descripcion_cualitativa'] ?? '') ?>
        </div>
        <div class="mt-2" style="font-size:12px; color:#999;">
            <span class="me-2"><i class="fas fa-ban me-1"></i>NA: <?= $inspeccion['conteo_no_aplica'] ?? 0 ?></span>
            <span class="me-2" style="color:#dc3545;"><i class="fas fa-times me-1"></i>NC: <?= $inspeccion['conteo_no_cumple'] ?? 0 ?></span>
            <span class="me-2" style="color:#fd7e14;"><i class="fas fa-exclamation me-1"></i>CP: <?= $inspeccion['conteo_parcial'] ?? 0 ?></span>
            <span style="color:#28a745;"><i class="fas fa-check me-1"></i>CT: <?= $inspeccion['conteo_total'] ?? 0 ?></span>
        </div>
    </div>
</div>

<!-- Items por grupo (accordion) -->
<?php if (!empty($itemsGrouped)): ?>
<div class="accordion mb-3" id="accordionSenalizacion">
    <?php foreach ($itemsGrouped as $grupo => $items):
        $grupoId = 'sg_' . preg_replace('/[^a-z0-9]/', '_', strtolower($grupo));
        $cumple = 0;
        foreach ($items as $it) {
            if ($it['estado_cumplimiento'] === 'CUMPLE TOTALMENTE') $cumple++;
        }
    ?>
    <div class="accordion-item" style="border:none; margin-bottom:2px;">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $grupoId ?>"
                    style="background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%); color:white; font-weight:600; font-size:14px;">
                <?= esc($grupo) ?>
                <span class="badge bg-light text-dark ms-2" style="font-size:10px;"><?= $cumple ?>/<?= count($items) ?></span>
            </button>
        </h2>
        <div id="<?= $grupoId ?>" class="accordion-collapse collapse" data-bs-parent="#accordionSenalizacion">
            <div class="accordion-body p-2" style="background:white;">
                <?php foreach ($items as $item):
                    $estadoBg = 'bg-danger';
                    if ($item['estado_cumplimiento'] === 'NO APLICA') $estadoBg = 'bg-secondary';
                    elseif ($item['estado_cumplimiento'] === 'CUMPLE PARCIALMENTE') $estadoBg = 'bg-warning text-dark';
                    elseif ($item['estado_cumplimiento'] === 'CUMPLE TOTALMENTE') $estadoBg = 'bg-success';
                ?>
                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid #f0f0f0;">
                    <div style="font-size:13px; flex:1;"><?= esc($item['nombre_item']) ?></div>
                    <span class="badge <?= $estadoBg ?>" style="font-size:10px; white-space:nowrap;"><?= esc($item['estado_cumplimiento']) ?></span>
                </div>
                <?php if (!empty($item['foto'])): ?>
                <div class="mb-1 mt-1">
                    <img src="<?= base_url($item['foto']) ?>" class="img-fluid rounded"
                         style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src, '<?= esc($item['nombre_item'], 'js') ?>')">
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Observaciones generales -->
<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">OBSERVACIONES GENERALES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Botón PDF -->
<?php if (!empty($inspeccion['ruta_pdf'])): ?>
<div class="text-center mb-4">
    <a href="/inspecciones/senalizacion/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pdf" target="_blank">
        <i class="fas fa-file-pdf me-2"></i> Descargar PDF
    </a>
</div>
<?php endif; ?>

<!-- Modal foto ampliada -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0 py-1">
                <small class="text-light" id="photoDesc"></small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-1 text-center">
                <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
<script>
function openPhoto(src, desc) {
    document.getElementById('photoFull').src = src;
    document.getElementById('photoDesc').textContent = desc || '';
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>

<style>
    .accordion-button::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    }
    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%) !important;
    }
</style>
