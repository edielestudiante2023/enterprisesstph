<div class="page-header">
    <h1><i class="fas fa-id-card-alt me-2"></i> HV Brigadista</h1>
    <a href="<?= base_url('client/inspecciones/hv-brigadista') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos Personales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-user me-1"></i> DATOS PERSONALES
        </h6>
        <?php if (!empty($hv['foto_brigadista'])): ?>
        <div class="text-center mb-3">
            <img src="<?= base_url($hv['foto_brigadista']) ?>" alt="Foto" style="max-height:150px; border-radius:8px; object-fit:contain; border:1px solid #ddd;">
        </div>
        <?php endif; ?>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Copropiedad</td><td><strong><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Nombre</td><td><strong><?= esc($hv['nombre_completo'] ?? 'N/A') ?></strong></td></tr>
            <tr><td class="text-muted">Documento</td><td><?= esc($hv['documento_identidad'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Fecha nacimiento</td><td><?= !empty($hv['f_nacimiento']) ? date('d/m/Y', strtotime($hv['f_nacimiento'])) : 'N/A' ?></td></tr>
            <tr><td class="text-muted">Edad</td><td><?= esc($hv['edad'] ?? 'N/A') ?> años</td></tr>
            <tr><td class="text-muted">Email</td><td><?= esc($hv['email'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Teléfono</td><td><?= esc($hv['telefono'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Dirección</td><td><?= esc($hv['direccion_residencia'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">EPS</td><td><?= esc($hv['eps'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">RH</td><td><?= esc($hv['rh'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Peso</td><td><?= esc($hv['peso'] ?? 'N/A') ?> kg</td></tr>
            <tr><td class="text-muted">Estatura</td><td><?= esc($hv['estatura'] ?? 'N/A') ?> cm</td></tr>
        </table>
    </div>
</div>

<!-- Estudios -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-graduation-cap me-1"></i> ESTUDIOS
        </h6>
        <?php
        $tieneEstudios = false;
        for ($i = 1; $i <= 3; $i++):
            $est = $hv["estudios_$i"] ?? '';
            $lugar = $hv["lugar_estudio_$i"] ?? '';
            $anio = $hv["anio_estudio_$i"] ?? '';
            if ($est || $lugar || $anio):
                $tieneEstudios = true;
        ?>
        <div style="border-bottom:1px solid #eee; padding-bottom:8px; margin-bottom:8px;">
            <div style="font-size:14px;"><strong>Estudio <?= $i ?>:</strong> <?= esc($est ?: 'N/A') ?></div>
            <div style="font-size:12px; color:#777;">Institución: <?= esc($lugar ?: 'N/A') ?> | Año: <?= esc($anio ?: 'N/A') ?></div>
        </div>
        <?php
            endif;
        endfor;
        if (!$tieneEstudios): ?>
            <p class="text-muted mb-0" style="font-size:13px;">Sin estudios registrados</p>
        <?php endif; ?>
    </div>
</div>

<!-- Información de Salud -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-heartbeat me-1"></i> INFORMACIÓN DE SALUD
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Enfermedades</td><td><?= esc($hv['enfermedades_importantes'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Medicamentos</td><td><?= esc($hv['medicamentos'] ?? 'N/A') ?></td></tr>
        </table>
    </div>
</div>

<!-- Cuestionario Médico -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-notes-medical me-1"></i> CUESTIONARIO MÉDICO
        </h6>
        <?php
        $preguntas = [
            'cardiaca' => 'Enfermedad cardíaca',
            'pechoactividad' => 'Dolor pecho en actividad',
            'dolorpecho' => 'Dolor pecho en reposo',
            'conciencia' => 'Pérdida de conciencia/mareo',
            'huesos' => 'Problemas huesos/articulaciones',
            'medicamentos_bool' => 'Medicamentos presión/diabetes/corazón',
            'actividadfisica' => 'Razón para no hacer actividad',
            'convulsiones' => 'Convulsiones/epilepsia',
            'vertigo' => 'Vértigo',
            'oidos' => 'Enfermedades oídos',
            'lugarescerrados' => 'Miedo lugares cerrados',
            'miedoalturas' => 'Miedo alturas',
            'haceejercicio' => 'Hace ejercicio semanal',
            'miedo_ver_sangre' => 'Miedo a ver sangre',
        ];
        $n = 1;
        foreach ($preguntas as $key => $label):
            $val = $hv[$key] ?? '-';
            $badgeColor = $val === 'SI' ? 'background:#ffc107; color:#333;' : ($val === 'NO' ? 'background:#28a745; color:#fff;' : 'background:#6c757d; color:#fff;');
        ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="font-size:13px; border-bottom:1px solid #f0f0f0;">
            <span><?= $n ?>. <?= esc($label) ?></span>
            <span class="badge" style="<?= $badgeColor ?> font-size:11px; padding:4px 8px;"><?= esc($val) ?></span>
        </div>
        <?php $n++; endforeach; ?>
    </div>
</div>

<!-- Restricciones y Actividad -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-running me-1"></i> RESTRICCIONES Y ACTIVIDAD
        </h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:45%;">Restricciones médicas</td><td><?= esc($hv['restricciones_medicas'] ?? 'N/A') ?></td></tr>
            <tr><td class="text-muted">Deportes/horas semana</td><td><?= esc($hv['deporte_semana'] ?? 'N/A') ?></td></tr>
        </table>
    </div>
</div>

<!-- Firma -->
<?php if (!empty($hv['firma'])): ?>
<div class="card mb-3">
    <div class="card-body text-center">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">
            <i class="fas fa-signature me-1"></i> FIRMA
        </h6>
        <img src="<?= base_url($hv['firma']) ?>" alt="Firma" style="max-height:120px; border:1px solid #ddd; border-radius:8px;">
    </div>
</div>
<?php endif; ?>

<!-- Acciones -->
<div class="mb-4">
    <?php if (!empty($hv['ruta_pdf'])): ?>
    <a href="/inspecciones/hv-brigadista/pdf/<?= $hv['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
