<?php
$backUrl = base_url('client/inspecciones');
$iconos = [
    'acta_visita'     => 'fa-file-signature',
    'locativa'        => 'fa-building',
    'senalizacion'    => 'fa-sign',
    'botiquin'        => 'fa-first-aid',
    'extintores'      => 'fa-fire-extinguisher',
    'comunicaciones'  => 'fa-broadcast-tower',
    'gabinetes'       => 'fa-shower',
];
$icono = $iconos[$tipo] ?? 'fa-clipboard-list';
?>

<div class="page-header">
    <h1><i class="fas <?= $icono ?> me-2"></i> <?= esc($titulo) ?></h1>
    <a href="<?= $backUrl ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (empty($inspecciones)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-inbox" style="font-size:3rem; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">No hay <?= strtolower(esc($titulo)) ?> completadas</h5>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($inspecciones as $insp): ?>
    <div class="col-md-6 col-lg-4">
        <a href="<?= base_url($base_url . '/' . $insp['id']) ?>" style="text-decoration:none;">
            <div class="card h-100" style="transition:all 0.3s ease; cursor:pointer;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span style="font-size:13px; color:#1c2437; font-weight:700;">
                            #<?= $insp['id'] ?>
                        </span>
                        <span class="badge bg-success" style="font-size:11px;">Completo</span>
                    </div>
                    <div style="font-size:14px; color:#555;">
                        <i class="fas fa-calendar-alt me-1" style="color:#bd9751;"></i>
                        <?= date('d/m/Y', strtotime($insp[$campo_fecha])) ?>
                    </div>
                    <?php if ($tipo === 'senalizacion' && isset($insp['calificacion'])): ?>
                    <div class="mt-2">
                        <?php
                        $calif = (float)$insp['calificacion'];
                        $califColor = '#28a745';
                        if ($calif <= 40) $califColor = '#dc3545';
                        elseif ($calif <= 60) $califColor = '#fd7e14';
                        elseif ($calif <= 80) $califColor = '#ffc107';
                        ?>
                        <span style="font-size:18px; font-weight:700; color:<?= $califColor ?>;">
                            <?= number_format($calif, 1) ?>%
                        </span>
                        <small class="text-muted ms-1"><?= esc($insp['descripcion_cualitativa'] ?? '') ?></small>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'acta_visita' && !empty($insp['motivo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-info-circle me-1"></i> <?= esc(mb_strimwidth($insp['motivo'], 0, 60, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'botiquin' && !empty($insp['ubicacion_botiquin'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-map-marker-alt me-1"></i> <?= esc(mb_strimwidth($insp['ubicacion_botiquin'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'extintores' && isset($insp['numero_extintores_totales'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-fire-extinguisher me-1"></i> <?= $insp['numero_extintores_totales'] ?> extintores
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'gabinetes' && isset($insp['cantidad_gabinetes'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-shower me-1"></i> <?= $insp['cantidad_gabinetes'] ?> gabinetes
                    </div>
                    <?php endif; ?>
                    <div class="mt-3 text-end">
                        <span style="font-size:12px; color:#bd9751; font-weight:600;">
                            Ver detalle <i class="fas fa-chevron-right ms-1"></i>
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }
</style>
