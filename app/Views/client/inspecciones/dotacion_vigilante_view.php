<?php
$colorMap = [
    'bueno' => '#28a745', 'regular' => '#ffc107', 'deficiente' => '#dc3545',
    'no_tiene' => '#6c757d', 'no_aplica' => '#adb5bd',
];
?>
<div class="page-header">
    <h1><i class="fas fa-user-tie me-2"></i> Dotación Vigilante</h1>
    <a href="<?= base_url('client/inspecciones/dotacion-vigilante') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos Generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-building me-1"></i> DATOS GENERALES
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <?php if (!empty($inspeccion['contratista'])): ?>
            <tr><td class="text-muted">Contratista</td><td><?= esc($inspeccion['contratista']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['servicio'])): ?>
            <tr><td class="text-muted">Servicio</td><td><?= esc($inspeccion['servicio']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['nombre_cargo'])): ?>
            <tr><td class="text-muted">Nombre / Cargo</td><td><?= esc($inspeccion['nombre_cargo']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['actividades_frecuentes'])): ?>
            <tr><td class="text-muted">Actividades</td><td><?= nl2br(esc($inspeccion['actividades_frecuentes'])) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Fotos -->
<?php if (!empty($inspeccion['foto_cuerpo_completo']) || !empty($inspeccion['foto_cuarto_almacenamiento'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-camera me-1"></i> REGISTRO FOTOGRÁFICO
        </h6>
        <div class="row g-2">
            <?php if (!empty($inspeccion['foto_cuerpo_completo'])): ?>
            <div class="col-6">
                <small class="text-muted d-block mb-1">Cuerpo completo</small>
                <img src="<?= base_url($inspeccion['foto_cuerpo_completo']) ?>" class="img-fluid rounded"
                     style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
                     onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
            <?php if (!empty($inspeccion['foto_cuarto_almacenamiento'])): ?>
            <div class="col-6">
                <small class="text-muted d-block mb-1">Cuarto almacenamiento</small>
                <img src="<?= base_url($inspeccion['foto_cuarto_almacenamiento']) ?>" class="img-fluid rounded"
                     style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
                     onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Estado EPP -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-vest me-1"></i> ESTADO DE DOTACIÓN EPP
        </h6>
        <?php foreach ($itemsEpp as $key => $info):
            $estado = $inspeccion['estado_' . $key] ?? '';
            $bgColor = $colorMap[$estado] ?? '#6c757d';
            $label = $estadosEpp[$estado] ?? 'Sin evaluar';
        ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="font-size:13px; border-bottom:1px solid #f0f0f0;">
            <span><?= esc($info['label'] ?? $info) ?></span>
            <span class="badge" style="background:<?= $bgColor ?>; color:#fff; font-size:11px; padding:4px 8px;"><?= esc($label) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Concepto Final -->
<?php if (!empty($inspeccion['concepto_final'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-clipboard-check me-1"></i> CONCEPTO FINAL
        </h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['concepto_final'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Observaciones -->
<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-comment-alt me-1"></i> OBSERVACIONES
        </h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
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
    <a href="/inspecciones/dotacion-vigilante/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
