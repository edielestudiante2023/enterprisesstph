<?php
/**
 * @var array $cfg
 * @var array $registro
 * @var array|null $cliente
 */
$archivo = $registro['archivo'] ?? null;
$ext = $archivo ? strtolower(pathinfo($archivo, PATHINFO_EXTENSION)) : null;
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas <?= esc($cfg['icon']) ?>"></i> <?= esc($cfg['nombre']) ?></h6>
        <a href="<?= base_url('/inspecciones/' . $cfg['slug']) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS DEL SERVICIO</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($registro['fecha_servicio'])) ?></td></tr>
                <?php if (!empty($registro['observaciones'])): ?>
                <tr><td class="text-muted">Observaciones</td><td><?= nl2br(esc($registro['observaciones'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($registro['id_vencimiento'])): ?>
                <tr><td class="text-muted">Vencimiento</td><td><span class="badge bg-success">Cerrado al guardar</span></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if ($archivo): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CERTIFICADO</h6>
            <?php if ($ext === 'pdf'): ?>
            <div class="mb-2">
                <a href="<?= base_url($archivo) ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-external-link-alt"></i> Abrir en nueva pestaña
                </a>
            </div>
            <iframe src="<?= base_url($archivo) ?>" style="width:100%; height:500px; border:1px solid #ddd; border-radius:6px;"></iframe>
            <?php elseif (in_array($ext, ['jpg','jpeg','png','webp'])): ?>
            <img src="<?= base_url($archivo) ?>" class="img-fluid rounded" style="max-width:100%; border:1px solid #ddd;">
            <div class="mt-2">
                <a href="<?= base_url($archivo) ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-external-link-alt"></i> Ver imagen completa
                </a>
            </div>
            <?php else: ?>
            <a href="<?= base_url($archivo) ?>" target="_blank" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-file-download"></i> Descargar certificado
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning" style="font-size:14px;">
        <i class="fas fa-exclamation-circle"></i> No se adjuntó certificado en este registro.
    </div>
    <?php endif; ?>
</div>
