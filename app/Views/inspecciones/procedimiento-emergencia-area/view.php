<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Procedimiento de Emergencia — <?= esc($areasLabels[$procedimiento['area']] ?? $procedimiento['area']) ?></h6>
        <span class="badge <?= $procedimiento['estado'] === 'completo' ? 'badge-completo' : 'badge-borrador' ?>">
            <?= $procedimiento['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td style="width:35%" class="text-muted">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Area</td><td><?= esc($areasLabels[$procedimiento['area']] ?? '') ?> — <?= esc($procedimiento['nombre_area_descriptivo'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Fecha elaboracion</td><td><?= date('d/m/Y', strtotime($procedimiento['fecha_elaboracion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Responsable</td><td><?= esc($procedimiento['responsable_area_nombre'] ?? '') ?> — <?= esc($procedimiento['responsable_area_cargo'] ?? '') ?> — <?= esc($procedimiento['responsable_area_contacto'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Horario</td><td><?= esc($procedimiento['horario_operacion'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Aforo maximo</td><td><?= esc($procedimiento['aforo_maximo'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Telefonos emergencia</td><td><?= nl2br(esc($procedimiento['telefonos_emergencia'] ?? '')) ?></td></tr>
                <tr><td class="text-muted">Recursos disponibles</td><td><?= nl2br(esc($procedimiento['recursos_disponibles'] ?? '')) ?></td></tr>
            </table>
        </div>
    </div>

    <h6 class="mt-3 mb-2">Escenarios</h6>
    <?php foreach ($escenarios as $i => $esc): ?>
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-1"><?= $i+1 ?>. <?= esc($esc['escenario_nombre']) ?></h6>
                <?php if ((int)$esc['aprobado_por_consultor'] === 1): ?>
                <span class="badge" style="background:#27ae60;color:#fff;">Aprobado</span>
                <?php else: ?>
                <span class="badge" style="background:#888;color:#fff;">Sin aprobar</span>
                <?php endif; ?>
            </div>
            <?php if (empty($esc['que_hacer'])): ?>
            <em style="font-size:12px; color:#888;">(Sin contenido — generar con IA o redactar manualmente)</em>
            <?php else: ?>
            <div style="font-size:12px;">
                <p><strong>Que hacer:</strong><br><?= nl2br(esc($esc['que_hacer'])) ?></p>
                <p><strong>Que NO hacer:</strong><br><?= nl2br(esc($esc['que_no_hacer'])) ?></p>
                <p><strong>Cuando:</strong><br><?= nl2br(esc($esc['cuando'])) ?></p>
                <p><strong>Quien:</strong><br><?= nl2br(esc($esc['quien'])) ?></p>
                <p><strong>Recursos:</strong><br><?= nl2br(esc($esc['recursos'])) ?></p>
                <?php if (!empty($esc['observaciones'])): ?><p><strong>Observaciones:</strong> <?= nl2br(esc($esc['observaciones'])) ?></p><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="d-grid gap-2 mb-4">
        <?php if ($procedimiento['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/pdf/' . $procedimiento['id']) ?>" target="_blank" class="btn btn-pwa-primary py-2"><i class="fas fa-file-pdf"></i> Ver PDF</a>
        <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/regenerar-pdf/' . $procedimiento['id']) ?>" class="btn btn-outline-secondary py-2"><i class="fas fa-sync"></i> Regenerar PDF</a>
        <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/enviar-email/' . $procedimiento['id']) ?>" class="btn btn-outline-primary py-2"><i class="fas fa-envelope"></i> Reenviar email</a>
        <?php else: ?>
        <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area/edit/' . $procedimiento['id']) ?>" class="btn btn-pwa-primary py-2"><i class="fas fa-edit"></i> Editar</a>
        <?php endif; ?>
        <a href="<?= base_url('/inspecciones/procedimiento-emergencia-area') ?>" class="btn btn-outline-dark py-2"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
</div>
