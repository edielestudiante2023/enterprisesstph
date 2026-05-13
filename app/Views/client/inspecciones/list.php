<?php
$backUrl = base_url('client/inspecciones');
$iconos = [
    'acta_visita'             => 'fa-file-signature',
    'locativa'                => 'fa-building',
    'senalizacion'            => 'fa-sign',
    'botiquin'                => 'fa-first-aid',
    'extintores'              => 'fa-fire-extinguisher',
    'productos_quimicos'      => 'fa-flask',
    'comunicaciones'          => 'fa-broadcast-tower',
    'gabinetes'               => 'fa-shower',
    'matriz_vulnerabilidad'   => 'fa-shield-alt',
    'probabilidad_peligros'   => 'fa-exclamation-triangle',
    'recursos_seguridad'      => 'fa-hard-hat',
    'brigada_simulacros'      => 'fa-people-carry',
    'ascensores'              => 'fa-sort',
    'piscinas'                => 'fa-swimming-pool',
    'piscinero'               => 'fa-swimmer',
    'hv_brigadista'           => 'fa-id-card-alt',
    'plan_emergencia'         => 'fa-route',
    'simulacro'               => 'fa-running',
    'limpieza'                => 'fa-pump-soap',
    'dotacion_vigilante'      => 'fa-user-tie',
    'dotacion_aseadora'       => 'fa-broom',
    'dotacion_todero'         => 'fa-hard-hat',
    'auditoria_residuos'      => 'fa-recycle',
    'asistencia_induccion'    => 'fa-chalkboard-teacher',
    'evaluacion_capacitacion' => 'fa-pen-fancy',
    'reporte_capacitacion'    => 'fa-graduation-cap',
    'preparacion_simulacro'   => 'fa-clipboard-list',
    'residuos'                => 'fa-recycle',
    'plagas'                  => 'fa-bug',
    'agua_potable'            => 'fa-tint',
    'plan_saneamiento'        => 'fa-shield-alt',
    'contingencia_plagas'     => 'fa-bug',
    'contingencia_limpieza'   => 'fa-spray-can',
    'contingencia_agua'       => 'fa-tint-slash',
    'contingencia_basura'     => 'fa-trash-alt',
    'lavado_tanques'          => 'fa-water',
    'fumigacion'              => 'fa-spray-can',
    'desratizacion'           => 'fa-mouse',
    'planilla_ss'             => 'fa-file-invoice',
    'kpi_limpieza'            => 'fa-chart-line',
    'kpi_residuos'            => 'fa-chart-bar',
    'kpi_plagas'              => 'fa-chart-pie',
    'kpi_agua_potable'        => 'fa-chart-area',
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
                        <?= !empty($insp[$campo_fecha]) ? date('d/m/Y', strtotime($insp[$campo_fecha])) : 'Sin fecha' ?>
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
                    <?php if ($tipo === 'hv_brigadista' && !empty($insp['nombre_completo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-user me-1"></i> <?= esc(mb_strimwidth($insp['nombre_completo'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'simulacro' && !empty($insp['evento_simulado'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-running me-1"></i> <?= esc(mb_strimwidth($insp['evento_simulado'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (in_array($tipo, ['dotacion_vigilante','dotacion_aseadora','dotacion_todero']) && !empty($insp['nombre_cargo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-user me-1"></i> <?= esc(mb_strimwidth($insp['nombre_cargo'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'asistencia_induccion' && !empty($insp['tema'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-chalkboard me-1"></i> <?= esc(mb_strimwidth($insp['tema'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'reporte_capacitacion' && !empty($insp['nombre_capacitacion'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-graduation-cap me-1"></i> <?= esc(mb_strimwidth($insp['nombre_capacitacion'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'preparacion_simulacro' && !empty($insp['evento_simulado'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-clipboard-list me-1"></i> <?= esc(mb_strimwidth($insp['evento_simulado'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'productos_quimicos' && !empty($insp['nivel_riesgo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-flask me-1"></i> <?= esc($insp['nivel_riesgo']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'ascensores' && isset($insp['total_ascensores'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-sort me-1"></i> <?= $insp['total_ascensores'] ?> ascensores
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'piscinas' && isset($insp['total_piscinas'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-swimming-pool me-1"></i> <?= $insp['total_piscinas'] ?> piscinas
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'piscinero' && !empty($insp['nombre_piscinero'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-user me-1"></i> <?= esc(mb_strimwidth($insp['nombre_piscinero'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'evaluacion_capacitacion' && !empty($insp['titulo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-pen-fancy me-1"></i> <?= esc(mb_strimwidth($insp['titulo'], 0, 50, '...')) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (in_array($tipo, ['lavado_tanques','fumigacion','desratizacion']) && !empty($insp['archivo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-paperclip me-1"></i> Certificado adjunto
                    </div>
                    <?php endif; ?>
                    <?php if ($tipo === 'planilla_ss' && !empty($insp['periodo'])): ?>
                    <div class="mt-2" style="font-size:13px; color:#777;">
                        <i class="fas fa-calendar me-1"></i> Periodo: <?= esc($insp['periodo']) ?>
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
