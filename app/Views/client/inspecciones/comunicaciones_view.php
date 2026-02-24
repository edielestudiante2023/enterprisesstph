<div class="page-header">
    <h1><i class="fas fa-broadcast-tower me-2"></i> Equipos de Comunicación</h1>
    <a href="<?= base_url('client/inspecciones/comunicaciones') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha inspección</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
        </table>
    </div>
</div>

<!-- Equipos de comunicación -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">EQUIPOS DE COMUNICACIÓN</h6>
        <?php foreach ($equipos as $key => $info):
            $cant = (int)($inspeccion['cant_' . $key] ?? 0);
            $obs = $inspeccion['obs_' . $key] ?? '';
            $cantColor = $cant > 0 ? 'color:#28a745;' : 'color:#dc3545;';
        ?>
        <div style="border:1px solid #dee2e6; border-radius:8px; padding:12px; margin-bottom:10px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas <?= $info['icon'] ?>" style="font-size:14px; color:#6f42c1; margin-right:6px;"></i>
                    <strong style="font-size:13px;"><?= $info['label'] ?></strong>
                </div>
                <span style="font-size:13px; background:#f8f9fa; padding:3px 10px; border-radius:4px;">
                    Cantidad: <strong style="<?= $cantColor ?>"><?= $cant ?></strong>
                </span>
            </div>
            <?php if (!empty($obs)): ?>
            <p style="margin:8px 0 0; font-size:12px; color:#777;">
                <i class="fas fa-comment-alt"></i> <?= nl2br(esc($obs)) ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Fotos evidencia -->
<?php if (!empty($inspeccion['foto_1']) || !empty($inspeccion['foto_2'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">FOTOS EVIDENCIA</h6>
        <div class="row g-2">
            <?php foreach (['foto_1', 'foto_2'] as $campo): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                         style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
                         onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Observaciones finales -->
<?php if (!empty($inspeccion['observaciones_finales'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">OBSERVACIONES FINALES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones_finales'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Modal foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:#000;">
            <div class="modal-body p-1 text-center">
                <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>
<script>
function openPhoto(src) {
    document.getElementById('photoFull').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>

<!-- Acciones -->
<div class="mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/comunicaciones/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
