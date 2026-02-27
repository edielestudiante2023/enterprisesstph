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
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Reporte de Capacitacion</h6>
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
                <tr><td class="text-muted">Fecha capacitacion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_capacitacion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Informacion de la capacitacion -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">INFORMACION DE LA CAPACITACION</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <?php if (!empty($inspeccion['nombre_capacitacion'])): ?>
                <tr><td class="text-muted" style="width:45%;">Nombre</td><td><?= esc($inspeccion['nombre_capacitacion']) ?></td></tr>
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
                        <span class="badge bg-info text-dark me-1" style="font-size:11px;"><?= esc($label) ?></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['nombre_capacitador'])): ?>
                <tr><td class="text-muted">Capacitador</td><td><?= esc($inspeccion['nombre_capacitador']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['horas_duracion'])): ?>
                <tr><td class="text-muted">Horas duracion</td><td><?= esc($inspeccion['horas_duracion']) ?> horas</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Asistencia y evaluacion -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ASISTENCIA Y EVALUACION</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Asistentes</td><td><?= esc($inspeccion['numero_asistentes'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Programados</td><td><?= esc($inspeccion['numero_programados'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Evaluados</td><td><?= esc($inspeccion['numero_evaluados'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">% Cobertura</td><td><strong><?= $cobertura ?>%</strong></td></tr>
                <tr><td class="text-muted">Promedio calificaciones</td><td><?= esc($inspeccion['promedio_calificaciones'] ?? '-') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Fotos -->
    <?php
    $fotos = [
        'foto_listado_asistencia' => 'Listado de asistencia',
        'foto_capacitacion'       => 'Capacitacion',
        'foto_evaluacion'         => 'Evaluacion',
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
            <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($fotos as $campo => $label): ?>
                    <?php if (!empty($inspeccion[$campo])): ?>
                    <div>
                        <small class="text-muted"><?= $label ?></small>
                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded d-block"
                            style="max-height:120px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
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
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/inspecciones/reporte-capacitacion/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/reporte-capacitacion/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/reporte-capacitacion/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
