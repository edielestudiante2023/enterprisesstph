<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Acta de Visita</h6>
        <span class="badge badge-<?= esc($acta['estado']) ?>">
            <?php
            switch ($acta['estado']) {
                case 'borrador': echo 'Borrador'; break;
                case 'pendiente_firma': echo 'Pend. Firma'; break;
                case 'completo': echo 'Completo'; break;
            }
            ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td></tr>
                <tr><td class="text-muted">Hora</td><td><?= date('g:i A', strtotime($acta['hora_visita'])) ?></td></tr>
                <tr><td class="text-muted">Motivo</td><td><?= esc($acta['motivo']) ?></td></tr>
                <tr><td class="text-muted">Modalidad</td><td><?= esc($acta['modalidad'] ?? 'Presencial') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Integrantes -->
    <?php if (!empty($integrantes)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">INTEGRANTES</h6>
            <?php foreach ($integrantes as $int): ?>
            <div class="d-flex justify-content-between py-1" style="font-size:14px; border-bottom:1px solid #eee;">
                <span><?= esc($int['nombre']) ?></span>
                <span class="badge bg-secondary"><?= esc($int['rol']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Temas -->
    <?php if (!empty($temas)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">TEMAS TRATADOS</h6>
            <?php foreach ($temas as $i => $tema): ?>
            <div style="font-size:14px; padding:4px 0; border-bottom:1px solid #eee;">
                <strong>Tema <?= $i + 1 ?>:</strong> <?= esc($tema['descripcion']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($acta['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cartera -->
    <?php if (!empty($acta['cartera'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CARTERA</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($acta['cartera'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Compromisos -->
    <?php if (!empty($compromisos)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">COMPROMISOS</h6>
            <?php foreach ($compromisos as $comp): ?>
            <div style="font-size:14px; padding:6px 0; border-bottom:1px solid #eee;">
                <strong><?= esc($comp['tarea_actividad']) ?></strong>
                <div class="text-muted" style="font-size:12px;">
                    Responsable: <?= esc($comp['responsable'] ?? '-') ?>
                    | Fecha cierre: <?= !empty($comp['fecha_cierre']) ? date('d/m/Y', strtotime($comp['fecha_cierre'])) : '-' ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($acta['estado'] === 'completo' && !empty($acta['ruta_pdf'])): ?>
        <a href="/inspecciones/acta-visita/pdf/<?= $acta['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>

        <?php if ($acta['estado'] !== 'completo'): ?>
        <a href="/inspecciones/acta-visita/edit/<?= $acta['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
