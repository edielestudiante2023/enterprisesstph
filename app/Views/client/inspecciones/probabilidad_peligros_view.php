<?php
$badgeStyles = [
    'poco_probable' => 'background:#d4edda; color:#155724;',
    'probable'      => 'background:#fff3cd; color:#856404;',
    'muy_probable'  => 'background:#f8d7da; color:#721c24;',
];
$badgeLabels = [
    'poco_probable' => 'Poco Probable',
    'probable'      => 'Probable',
    'muy_probable'  => 'Muy Probable',
];
?>

<div class="page-header">
    <h1><i class="fas fa-exclamation-triangle me-2"></i> Probabilidad de Peligros</h1>
    <a href="<?= base_url('client/inspecciones/probabilidad-peligros') ?>" class="btn-back">
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

<!-- Peligros por origen -->
<?php foreach ($peligros as $grupoKey => $grupo): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas <?= $grupo['icon'] ?> me-1"></i>
            <?= strtoupper($grupo['label']) ?>
        </h6>
        <?php foreach ($grupo['items'] as $key => $label):
            $val = $inspeccion[$key] ?? null;
        ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="font-size:13px; border-bottom:1px solid #f0f0f0;">
            <span><?= $label ?></span>
            <?php if ($val && isset($badgeLabels[$val])): ?>
            <span class="badge" style="<?= $badgeStyles[$val] ?> font-size:11px; padding:4px 10px;">
                <?= $badgeLabels[$val] ?>
            </span>
            <?php else: ?>
            <span style="font-size:11px; color:#999; font-style:italic;">Sin evaluar</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Resumen porcentajes -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">RESULTADOS CONSOLIDADOS</h6>

        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px;">
            <span><span class="badge" style="background:#d4edda; color:#155724;">Poco Probable</span></span>
            <strong><?= number_format($porcentajes['poco_probable'] * 100, 1) ?>%</strong>
        </div>
        <div class="progress mb-3" style="height:8px; border-radius:4px;">
            <div class="progress-bar bg-success" style="width:<?= $porcentajes['poco_probable'] * 100 ?>%; border-radius:4px;"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px;">
            <span><span class="badge" style="background:#fff3cd; color:#856404;">Probable</span></span>
            <strong><?= number_format($porcentajes['probable'] * 100, 1) ?>%</strong>
        </div>
        <div class="progress mb-3" style="height:8px; border-radius:4px;">
            <div class="progress-bar bg-warning" style="width:<?= $porcentajes['probable'] * 100 ?>%; border-radius:4px;"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px;">
            <span><span class="badge" style="background:#f8d7da; color:#721c24;">Muy Probable</span></span>
            <strong><?= number_format($porcentajes['muy_probable'] * 100, 1) ?>%</strong>
        </div>
        <div class="progress mb-3" style="height:8px; border-radius:4px;">
            <div class="progress-bar bg-danger" style="width:<?= $porcentajes['muy_probable'] * 100 ?>%; border-radius:4px;"></div>
        </div>
    </div>
</div>

<!-- Observaciones -->
<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">OBSERVACIONES DEL CONSULTOR</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Acciones -->
<div class="mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/probabilidad-peligros/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
