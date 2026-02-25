<div class="page-header">
    <h1><i class="fas fa-hard-hat me-2"></i> Recursos de Seguridad</h1>
    <a href="<?= base_url('client/inspecciones/recursos-seguridad') ?>" class="btn-back">
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

<!-- Recursos de seguridad -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">RECURSOS DE SEGURIDAD</h6>
        <?php foreach ($recursos as $key => $info):
            $obs = $inspeccion['obs_' . $key] ?? '';
            $foto = (!empty($info['tiene_foto'])) ? ($inspeccion['foto_' . $key] ?? '') : '';
        ?>
        <div style="border:1px solid #dee2e6; border-radius:8px; padding:12px; margin-bottom:10px;">
            <div class="d-flex align-items-center mb-1">
                <i class="fas <?= $info['icon'] ?>" style="font-size:16px; color:#795548; margin-right:8px;"></i>
                <strong style="font-size:13px;"><?= $info['label'] ?></strong>
            </div>
            <?php if (!empty($info['hint'])): ?>
            <p style="font-size:11px; color:#999; margin:0 0 6px;">(<?= $info['hint'] ?>)</p>
            <?php endif; ?>
            <?php if (!empty($obs)): ?>
            <p style="font-size:12px; color:#666; margin:6px 0;">
                <i class="fas fa-comment-alt" style="color:#999;"></i> <?= nl2br(esc($obs)) ?>
            </p>
            <?php endif; ?>
            <?php if (!empty($foto)): ?>
            <div class="mt-1">
                <img src="/<?= esc($foto) ?>" class="img-fluid rounded"
                     style="max-height:150px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                     onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
            <?php if (empty($obs) && empty($foto)): ?>
            <p style="font-size:12px; color:#999; font-style:italic; margin:0;">Sin información</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Observaciones generales -->
<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">OBSERVACIONES GENERALES</h6>
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
    <a href="/inspecciones/recursos-seguridad/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
