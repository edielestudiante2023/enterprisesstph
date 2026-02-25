<div class="page-header">
    <h1><i class="fas fa-running me-2"></i> Evaluación Simulacro</h1>
    <a href="<?= base_url('client/inspecciones/simulacro') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Identificación -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-building me-1"></i> IDENTIFICACIÓN
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">NIT</td><td><?= esc($cliente['nit_cliente'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($eval['fecha'])) ?></td></tr>
            <tr><td class="text-muted">Dirección</td><td><?= esc($eval['direccion'] ?? 'N/A') ?></td></tr>
        </table>
    </div>
</div>

<!-- Información General -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-info-circle me-1"></i> INFORMACIÓN GENERAL
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:45%;">Evento simulado</td><td><?= esc($eval['evento_simulado'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Alcance</td><td><?= esc($eval['alcance_simulacro'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Tipo evacuación</td><td><?= esc($eval['tipo_evacuacion'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Personal no evacua</td><td><?= esc($eval['personal_no_evacua'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Tipo alarma</td><td><?= esc($eval['tipo_alarma'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Puntos de encuentro</td><td><?= esc($eval['puntos_encuentro'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Recurso humano</td><td><?= esc($eval['recurso_humano'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Equipos emergencia</td><td><?= esc($eval['equipos_emergencia'] ?? 'N/A') ?></td></tr>
        </table>
    </div>
</div>

<!-- Brigadista Líder -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-user-shield me-1"></i> BRIGADISTA LÍDER
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Nombre</td><td><?= esc($eval['nombre_brigadista_lider'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Email</td><td><?= esc($eval['email_brigadista_lider'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">WhatsApp</td><td><?= esc($eval['whatsapp_brigadista_lider'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Distintivos</td><td><?= esc($eval['distintivos_brigadistas'] ?? 'N/A') ?></td></tr>
        </table>
    </div>
</div>

<!-- Cronograma -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-stopwatch me-1"></i> CRONOGRAMA DEL SIMULACRO
        </h6>
        <?php
        $cronoPasos = [
            'hora_inicio' => 'Hora de Inicio', 'alistamiento_recursos' => 'Alistamiento de Recursos',
            'asumir_roles' => 'Asumir roles', 'suena_alarma' => 'Suena alarma',
            'distribucion_roles' => 'Distribución de roles', 'llegada_punto_encuentro' => 'Llegada punto de encuentro',
            'agrupacion_por_afinidad' => 'Agrupación por afinidad', 'conteo_personal' => 'Conteo de personal',
            'agradecimiento_y_cierre' => 'Agradecimiento y cierre',
        ];
        foreach ($cronoPasos as $key => $label):
            $valor = $eval[$key] ?? null;
        ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="font-size:13px; border-bottom:1px solid #f0f0f0;">
            <span><?= esc($label) ?></span>
            <strong><?= $valor ? date('H:i:s', strtotime($valor)) : '--:--:--' ?></strong>
        </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between align-items-center pt-2 mt-1" style="border-top:2px solid #ddd;">
            <strong style="font-size:14px;">Tiempo Total</strong>
            <strong style="font-size:18px; color:#0d6efd;"><?= esc($eval['tiempo_total'] ?? '--:--:--') ?></strong>
        </div>
    </div>
</div>

<!-- Conteo de Evacuados -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-calculator me-1"></i> CONTEO DE EVACUADOS
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted">Hombres (18-59)</td><td class="text-end"><strong><?= (int)($eval['hombre'] ?? 0) ?></strong></td></tr>
            <tr><td class="text-muted">Mujeres (18-59)</td><td class="text-end"><strong><?= (int)($eval['mujer'] ?? 0) ?></strong></td></tr>
            <tr><td class="text-muted">Niños (&lt;18)</td><td class="text-end"><strong><?= (int)($eval['ninos'] ?? 0) ?></strong></td></tr>
            <tr><td class="text-muted">Adultos mayores (60+)</td><td class="text-end"><strong><?= (int)($eval['adultos_mayores'] ?? 0) ?></strong></td></tr>
            <tr><td class="text-muted">Discapacidad</td><td class="text-end"><strong><?= (int)($eval['discapacidad'] ?? 0) ?></strong></td></tr>
            <tr><td class="text-muted">Mascotas</td><td class="text-end"><strong><?= (int)($eval['mascotas'] ?? 0) ?></strong></td></tr>
            <tr style="background:#e8f0fe;"><td><strong>Total</strong></td><td class="text-end"><strong style="font-size:16px;"><?= (int)($eval['total'] ?? 0) ?></strong></td></tr>
        </table>
    </div>
</div>

<!-- Evaluación -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-star me-1"></i> EVALUACIÓN DEL SIMULACRO
        </h6>
        <?php
        $criterios = [
            'alarma_efectiva' => 'Alarma efectiva', 'orden_evacuacion' => 'Orden de evacuación',
            'liderazgo_brigadistas' => 'Liderazgo brigadistas', 'organizacion_punto_encuentro' => 'Organización punto de encuentro',
            'participacion_general' => 'Participación general',
        ];
        foreach ($criterios as $key => $label):
            $val = (int)($eval[$key] ?? 0);
            $barColor = $val >= 8 ? '#28a745' : ($val >= 5 ? '#ffc107' : '#dc3545');
        ?>
        <div class="mb-2">
            <div class="d-flex justify-content-between" style="font-size:13px;">
                <span><?= esc($label) ?></span>
                <strong><?= $val ?>/10</strong>
            </div>
            <div class="progress" style="height:6px; border-radius:3px;">
                <div class="progress-bar" style="width:<?= $val * 10 ?>%; background:<?= $barColor ?>; border-radius:3px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="mt-3 p-2" style="background:#f8f9fa; border-radius:8px;">
            <div style="font-size:14px;"><strong>Cuantitativa:</strong>
                <span class="badge bg-info" style="font-size:13px;"><?= esc($eval['evaluacion_cuantitativa'] ?? 'N/A') ?></span>
            </div>
            <div class="mt-1" style="font-size:14px;"><strong>Cualitativa:</strong> <?= esc($eval['evaluacion_cualitativa'] ?? 'N/A') ?></div>
        </div>
    </div>
</div>

<!-- Evidencias -->
<?php if (!empty($eval['observaciones']) || !empty($eval['imagen_1']) || !empty($eval['imagen_2'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-camera me-1"></i> EVIDENCIAS
        </h6>
        <?php if (!empty($eval['observaciones'])): ?>
        <p style="font-size:14px; margin:0 0 10px;"><?= nl2br(esc($eval['observaciones'])) ?></p>
        <?php endif; ?>
        <div class="row g-2">
            <?php foreach (['imagen_1', 'imagen_2'] as $campo): ?>
            <?php if (!empty($eval[$campo])): ?>
            <div class="col-6">
                <img src="<?= base_url($eval[$campo]) ?>" class="img-fluid rounded"
                     style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
                     onclick="openPhoto(this.src)">
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
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
    <?php if (!empty($eval['ruta_pdf'])): ?>
    <a href="/inspecciones/simulacro/pdf/<?= $eval['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
