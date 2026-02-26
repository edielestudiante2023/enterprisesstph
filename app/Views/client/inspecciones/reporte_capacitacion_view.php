<?php
$perfilesSeleccionados = [];
if (!empty($inspeccion['perfil_asistentes'])) {
    $perfilesSeleccionados = explode(',', $inspeccion['perfil_asistentes']);
}
$cobertura = 0;
if (!empty($inspeccion['numero_programados']) && $inspeccion['numero_programados'] > 0) {
    $cobertura = round(($inspeccion['numero_asistentes'] / $inspeccion['numero_programados']) * 100, 1);
}
?>
<div class="page-header">
    <h1><i class="fas fa-graduation-cap me-2"></i> Reporte de Capacitación</h1>
    <a href="<?= base_url('client/inspecciones/reporte-capacitacion') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-building me-1"></i> DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_capacitacion'])) ?></td></tr>
        </table>
    </div>
</div>

<!-- Info Capacitación -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-chalkboard me-1"></i> INFORMACIÓN DE LA CAPACITACIÓN
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <?php if (!empty($inspeccion['nombre_capacitacion'])): ?>
            <tr><td class="text-muted" style="width:40%;">Nombre</td><td><?= esc($inspeccion['nombre_capacitacion']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['objetivo_capacitacion'])): ?>
            <tr><td class="text-muted">Objetivo</td><td><?= nl2br(esc($inspeccion['objetivo_capacitacion'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($perfilesSeleccionados)): ?>
            <tr>
                <td class="text-muted">Perfil asistentes</td>
                <td>
                    <?php foreach ($perfilesSeleccionados as $p):
                        $label = $perfilesAsistentes[trim($p)] ?? trim($p);
                    ?>
                    <span class="badge bg-info me-1" style="font-size:11px;"><?= esc($label) ?></span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['nombre_capacitador'])): ?>
            <tr><td class="text-muted">Capacitador</td><td><?= esc($inspeccion['nombre_capacitador']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['horas_duracion'])): ?>
            <tr><td class="text-muted">Duración</td><td><?= esc($inspeccion['horas_duracion']) ?> horas</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Asistencia y Evaluación -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-chart-bar me-1"></i> ASISTENCIA Y EVALUACIÓN
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Asistentes</td><td><strong><?= (int)($inspeccion['numero_asistentes'] ?? 0) ?></strong></td></tr>
            <tr><td class="text-muted">Programados</td><td><?= (int)($inspeccion['numero_programados'] ?? 0) ?></td></tr>
            <tr><td class="text-muted">Evaluados</td><td><?= (int)($inspeccion['numero_evaluados'] ?? 0) ?></td></tr>
            <tr style="background:#e8f0fe;"><td class="text-muted">% Cobertura</td><td><strong style="font-size:16px; color:#0d6efd;"><?= $cobertura ?>%</strong></td></tr>
            <tr><td class="text-muted">Promedio calificaciones</td><td><?= esc($inspeccion['promedio_calificaciones'] ?? '-') ?></td></tr>
        </table>
    </div>
</div>

<!-- Fotos -->
<?php
$fotos = [
    'foto_listado_asistencia' => 'Listado de asistencia',
    'foto_capacitacion'       => 'Capacitación',
    'foto_evaluacion'         => 'Evaluación',
    'foto_otros_1'            => 'Otros 1',
    'foto_otros_2'            => 'Otros 2',
];
$hayFotos = false;
foreach ($fotos as $campo => $label) {
    if (!empty($inspeccion[$campo])) { $hayFotos = true; break; }
}
?>
<?php if ($hayFotos): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-camera me-1"></i> REGISTRO FOTOGRÁFICO</h6>
        <div class="row g-2">
            <?php foreach ($fotos as $campo => $label): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6 col-md-4">
                    <small class="text-muted d-block mb-1"><?= $label ?></small>
                    <img src="<?= base_url($inspeccion[$campo]) ?>" class="img-fluid rounded"
                         style="max-height:150px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
                         onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

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
    <a href="/inspecciones/reporte-capacitacion/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;"><i class="fas fa-file-pdf"></i> Ver PDF</a>
    <?php endif; ?>
</div>
