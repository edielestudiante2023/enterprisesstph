<?php
$alarmaSeleccionados = !empty($inspeccion['tipo_alarma']) ? explode(',', $inspeccion['tipo_alarma']) : [];
$distintivosSeleccionados = !empty($inspeccion['distintivos_brigadistas']) ? explode(',', $inspeccion['distintivos_brigadistas']) : [];
$equiposSeleccionados = !empty($inspeccion['equipos_emergencia']) ? explode(',', $inspeccion['equipos_emergencia']) : [];

$tiempoTotal = '';
if (!empty($inspeccion['hora_inicio']) && !empty($inspeccion['agradecimiento_cierre'])) {
    $inicio = new \DateTime($inspeccion['hora_inicio']);
    $fin = new \DateTime($inspeccion['agradecimiento_cierre']);
    $diff = $inicio->diff($fin);
    $tiempoTotal = $diff->format('%H:%I:%S');
}
?>
<div class="page-header">
    <h1><i class="fas fa-clipboard-list me-2"></i> Preparación Simulacro</h1>
    <a href="<?= base_url('client/inspecciones/preparacion-simulacro') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos Generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-building me-1"></i> DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_simulacro'])) ?></td></tr>
            <?php if (!empty($inspeccion['ubicacion'])): ?>
            <tr><td class="text-muted">Ubicación</td><td><?= esc($inspeccion['ubicacion']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['direccion'])): ?>
            <tr><td class="text-muted">Dirección</td><td><?= esc($inspeccion['direccion']) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Configuración -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-cog me-1"></i> CONFIGURACIÓN DEL SIMULACRO
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <?php if (!empty($inspeccion['evento_simulado'])): ?>
            <tr><td class="text-muted" style="width:40%;">Evento simulado</td><td><?= esc(ucfirst($inspeccion['evento_simulado'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['alcance_simulacro'])): ?>
            <tr><td class="text-muted">Alcance</td><td><?= esc(ucfirst($inspeccion['alcance_simulacro'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['tipo_evacuacion'])): ?>
            <tr><td class="text-muted">Tipo evacuación</td><td><?= esc(ucfirst($inspeccion['tipo_evacuacion'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['personal_no_evacua'])): ?>
            <tr><td class="text-muted">Personal no evacua</td><td><?= nl2br(esc($inspeccion['personal_no_evacua'])) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Alarma y Distintivos -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-bullhorn me-1"></i> ALARMA Y DISTINTIVOS
        </h6>
        <?php if (!empty($alarmaSeleccionados)): ?>
        <div class="mb-2">
            <small class="text-muted d-block mb-1">Tipo de alarma</small>
            <?php foreach ($alarmaSeleccionados as $val): ?>
            <span class="badge bg-primary me-1 mb-1" style="font-size:11px;"><?= esc($opcionesAlarma[trim($val)] ?? $val) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($distintivosSeleccionados)): ?>
        <div class="mb-2">
            <small class="text-muted d-block mb-1">Distintivos brigadistas</small>
            <?php foreach ($distintivosSeleccionados as $val): ?>
            <span class="badge bg-info me-1 mb-1" style="font-size:11px;"><?= esc($opcionesDistintivos[trim($val)] ?? $val) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Logística -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-truck me-1"></i> LOGÍSTICA
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <?php if (!empty($inspeccion['puntos_encuentro'])): ?>
            <tr><td class="text-muted" style="width:40%;">Puntos de encuentro</td><td><?= nl2br(esc($inspeccion['puntos_encuentro'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['recurso_humano'])): ?>
            <tr><td class="text-muted">Recurso humano</td><td><?= nl2br(esc($inspeccion['recurso_humano'])) ?></td></tr>
            <?php endif; ?>
        </table>
        <?php if (!empty($equiposSeleccionados)): ?>
        <div class="mt-2">
            <small class="text-muted d-block mb-1">Equipos de emergencia</small>
            <?php foreach ($equiposSeleccionados as $val): ?>
            <span class="badge bg-warning text-dark me-1 mb-1" style="font-size:11px;"><?= esc($opcionesEquipos[trim($val)] ?? $val) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Brigadista Líder -->
<?php if (!empty($inspeccion['nombre_brigadista_lider'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-user-shield me-1"></i> BRIGADISTA LÍDER
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Nombre</td><td><?= esc($inspeccion['nombre_brigadista_lider']) ?></td></tr>
            <?php if (!empty($inspeccion['email_brigadista_lider'])): ?>
            <tr><td class="text-muted">Email</td><td><?= esc($inspeccion['email_brigadista_lider']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['whatsapp_brigadista_lider'])): ?>
            <tr><td class="text-muted">WhatsApp</td><td><?= esc($inspeccion['whatsapp_brigadista_lider']) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Cronograma -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-stopwatch me-1"></i> CRONOGRAMA
        </h6>
        <?php foreach ($cronogramaItems as $key => $label):
            $valor = $inspeccion[$key] ?? null;
        ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="font-size:13px; border-bottom:1px solid #f0f0f0;">
            <span><?= esc($label) ?></span>
            <strong><?= $valor ? esc($valor) : '--:--' ?></strong>
        </div>
        <?php endforeach; ?>
        <?php if ($tiempoTotal): ?>
        <div class="d-flex justify-content-between align-items-center pt-2 mt-1" style="border-top:2px solid #ddd;">
            <strong style="font-size:14px;">Tiempo Total</strong>
            <strong style="font-size:18px; color:#0d6efd;"><?= $tiempoTotal ?></strong>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Fotos -->
<?php if (!empty($inspeccion['imagen_1']) || !empty($inspeccion['imagen_2'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-camera me-1"></i> EVIDENCIAS</h6>
        <div class="row g-2">
            <?php foreach (['imagen_1', 'imagen_2'] as $campo): ?>
            <?php if (!empty($inspeccion[$campo])): ?>
            <div class="col-6">
                <img src="<?= base_url($inspeccion[$campo]) ?>" class="img-fluid rounded"
                     style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
                     onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Evaluación -->
<?php if (!empty($inspeccion['entrega_formato_evaluacion'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;"><i class="fas fa-clipboard-check me-1"></i> EVALUACIÓN</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:45%;">Entrega formato evaluación</td><td><?= esc(ucfirst($inspeccion['entrega_formato_evaluacion'])) ?></td></tr>
        </table>
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
    <a href="/inspecciones/preparacion-simulacro/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;"><i class="fas fa-file-pdf"></i> Ver PDF</a>
    <?php endif; ?>
</div>
