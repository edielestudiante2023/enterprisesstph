<?php
$tipoLabel = $tiposCharla[$inspeccion['tipo_charla'] ?? ''] ?? $inspeccion['tipo_charla'] ?? '';
?>
<div class="page-header">
    <h1><i class="fas fa-chalkboard-teacher me-2"></i> Asistencia Inducción</h1>
    <a href="<?= base_url('client/inspecciones/asistencia-induccion') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-building me-1"></i> DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha sesión</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td></tr>
        </table>
    </div>
</div>

<!-- Info Sesión -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-info-circle me-1"></i> INFORMACIÓN DE LA SESIÓN
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <?php if (!empty($inspeccion['tema'])): ?>
            <tr><td class="text-muted" style="width:40%;">Tema</td><td><?= esc($inspeccion['tema']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['lugar'])): ?>
            <tr><td class="text-muted">Lugar</td><td><?= esc($inspeccion['lugar']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['objetivo'])): ?>
            <tr><td class="text-muted">Objetivo</td><td><?= nl2br(esc($inspeccion['objetivo'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['capacitador'])): ?>
            <tr><td class="text-muted">Capacitador</td><td><?= esc($inspeccion['capacitador']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($tipoLabel)): ?>
            <tr><td class="text-muted">Tipo de charla</td><td><span class="badge bg-info" style="font-size:12px;"><?= esc($tipoLabel) ?></span></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['material'])): ?>
            <tr><td class="text-muted">Material</td><td><?= esc($inspeccion['material']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['tiempo_horas'])): ?>
            <tr><td class="text-muted">Tiempo (horas)</td><td><?= esc($inspeccion['tiempo_horas']) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Asistentes -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-users me-1"></i> ASISTENTES (<?= count($asistentes) ?>)
        </h6>
        <?php if (!empty($asistentes)): ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
                <thead style="background:#f8f9fa;">
                    <tr><th style="width:5%;">#</th><th>Nombre</th><th>Cédula</th><th>Cargo</th><th style="width:15%;">Firma</th></tr>
                </thead>
                <tbody>
                    <?php $num = 1; foreach ($asistentes as $a): ?>
                    <tr>
                        <td class="text-center"><?= $num++ ?></td>
                        <td><?= esc($a['nombre']) ?></td>
                        <td><?= esc($a['cedula']) ?></td>
                        <td><?= esc($a['cargo']) ?></td>
                        <td class="text-center">
                            <?php if (!empty($a['firma'])): ?>
                            <img src="<?= base_url($a['firma']) ?>" style="max-width:60px; max-height:30px; border:1px solid #ddd;">
                            <?php else: ?>
                            <span class="text-muted" style="font-size:10px;">Sin firma</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0" style="font-size:13px;">No hay asistentes registrados</p>
        <?php endif; ?>
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

<div class="mb-4">
    <?php if (!empty($inspeccion['ruta_pdf_asistencia'])): ?>
    <a href="/inspecciones/asistencia-induccion/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF Asistencia
    </a>
    <?php endif; ?>
</div>
