<?php
$colorMap = [
    'bueno' => '#28a745', 'regular' => '#ffc107', 'malo' => '#fd7e14',
    'deficiente' => '#dc3545', 'no_tiene' => '#6c757d', 'no_aplica' => '#adb5bd',
];
?>
<div class="page-header">
    <h1><i class="fas fa-recycle me-2"></i> Auditoría Zona de Residuos</h1>
    <a href="<?= base_url('client/inspecciones/auditoria-zona-residuos') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-building me-1"></i> DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
        </table>
    </div>
</div>

<!-- Items de inspección -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-clipboard-list me-1"></i> ITEMS DE INSPECCIÓN
        </h6>
        <?php foreach ($itemsZona as $key => $info): ?>
        <div class="mb-3 p-2" style="background:#f8f9fa; border-radius:8px;">
            <div class="d-flex justify-content-between align-items-center">
                <span style="font-size:13px; font-weight:600;">
                    <i class="fas <?= $info['icon'] ?? 'fa-check-circle' ?> me-1" style="color:#bd9751;"></i>
                    <?= esc($info['label']) ?>
                </span>
                <?php if ($info['tipo'] === 'enum'):
                    $estado = $inspeccion['estado_' . $key] ?? '';
                    $bgColor = $colorMap[$estado] ?? '#6c757d';
                    $label = $estadosZona[$estado] ?? 'Sin evaluar';
                ?>
                <span class="badge" style="background:<?= $bgColor ?>; color:#fff; font-size:11px; padding:4px 8px;"><?= esc($label) ?></span>
                <?php else: ?>
                <span style="font-size:12px; color:#555;"><?= esc($inspeccion[$key] ?? 'Sin información') ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($inspeccion['foto_' . $key])): ?>
            <div class="mt-2">
                <img src="<?= base_url($inspeccion['foto_' . $key]) ?>" class="img-fluid rounded"
                     style="max-height:150px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-comment-alt me-1"></i> OBSERVACIONES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="photoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content" style="background:#000;"><div class="modal-body p-1 text-center"><img id="photoFull" src="" class="img-fluid" style="max-height:80vh;"></div></div></div></div>
<script>function openPhoto(src){document.getElementById('photoFull').src=src;new bootstrap.Modal(document.getElementById('photoModal')).show();}</script>

<div class="mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/auditoria-zona-residuos/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;"><i class="fas fa-file-pdf"></i> Ver PDF</a>
    <?php endif; ?>
</div>
