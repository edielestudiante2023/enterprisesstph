<?php
$badgeClasses = [
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
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Probabilidad de Peligros</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Peligros por origen -->
    <?php foreach ($peligros as $grupoKey => $grupo): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">
                <i class="fas <?= $grupo['icon'] ?> me-1"></i>
                <?= strtoupper($grupo['label']) ?>
            </h6>
            <?php foreach ($grupo['items'] as $key => $label):
                $val = $inspeccion[$key] ?? null;
            ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-2" style="font-size:13px;">
                <span><?= $label ?></span>
                <?php if ($val && isset($badgeLabels[$val])): ?>
                <span class="badge" style="<?= $badgeClasses[$val] ?> font-size:11px; padding:4px 8px;">
                    <?= $badgeLabels[$val] ?>
                </span>
                <?php else: ?>
                <span class="text-muted" style="font-size:11px; font-style:italic;">Sin evaluar</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Resumen porcentajes -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RESULTADOS CONSOLIDADOS</h6>
            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px;">
                <span><span class="badge" style="background:#d4edda; color:#155724;">Poco Probable</span></span>
                <strong><?= number_format($porcentajes['poco_probable'] * 100, 1) ?>%</strong>
            </div>
            <div class="progress mb-2" style="height:8px;">
                <div class="progress-bar bg-success" style="width:<?= $porcentajes['poco_probable'] * 100 ?>%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px;">
                <span><span class="badge" style="background:#fff3cd; color:#856404;">Probable</span></span>
                <strong><?= number_format($porcentajes['probable'] * 100, 1) ?>%</strong>
            </div>
            <div class="progress mb-2" style="height:8px;">
                <div class="progress-bar bg-warning" style="width:<?= $porcentajes['probable'] * 100 ?>%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px;">
                <span><span class="badge" style="background:#f8d7da; color:#721c24;">Muy Probable</span></span>
                <strong><?= number_format($porcentajes['muy_probable'] * 100, 1) ?>%</strong>
            </div>
            <div class="progress mb-2" style="height:8px;">
                <div class="progress-bar bg-danger" style="width:<?= $porcentajes['muy_probable'] * 100 ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES DEL CONSULTOR</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/inspecciones/probabilidad-peligros/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/probabilidad-peligros/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
