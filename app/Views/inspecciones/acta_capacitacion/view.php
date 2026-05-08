<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Reporte de Capacitación</h6>
        <span class="badge badge-<?= esc($acta['estado']) ?>">
            <?= $acta['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS DE LA CAPACITACIÓN</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Tema</td><td><?= esc($acta['tema']) ?></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($acta['fecha_capacitacion'])) ?></td></tr>
                <?php if (!empty($acta['hora_inicio'])): ?>
                <tr><td class="text-muted">Hora</td><td><?= date('g:i A', strtotime($acta['hora_inicio'])) ?> – <?= !empty($acta['hora_fin']) ? date('g:i A', strtotime($acta['hora_fin'])) : '' ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Modalidad</td><td><?= ucfirst($acta['modalidad']) ?></td></tr>
                <tr><td class="text-muted">Dictada por</td><td><?= esc($acta['dictada_por']) ?><?= !empty($acta['entidad_capacitadora']) ? ' — ' . esc($acta['entidad_capacitadora']) : '' ?></td></tr>
                <?php if (!empty($acta['nombre_capacitador'])): ?>
                <tr><td class="text-muted">Capacitador</td><td><?= esc($acta['nombre_capacitador']) ?></td></tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Registrada por</td>
                    <td>
                        <?php if (!empty($realizadoPor)): ?>
                            <?= esc($realizadoPor) ?> <span class="badge bg-info" style="font-size:10px;">Comité</span>
                        <?php elseif (!empty($consultor)): ?>
                            <?= esc($consultor['nombre_consultor'] ?? '') ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <?php if (!empty($vinculos)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CAPACITACIONES DICTADAS (<?= count($vinculos) ?>)</h6>
            <small class="text-muted d-block mb-2">Cada capacitación genera su propio PDF y queda vinculada al cronograma del cliente.</small>
            <?php foreach ($vinculos as $v): ?>
            <div class="mb-2 pb-2" style="border-bottom:1px solid #eee;">
                <div class="d-flex justify-content-between align-items-start" style="gap:8px;">
                    <div style="flex:1;">
                        <strong style="font-size:14px;"><?= esc($v['nombre_capacitacion'] ?? '(cronograma eliminado)') ?></strong>
                        <?php if (!empty($v['fecha_programada'])): ?>
                            <div class="text-muted" style="font-size:11px;">Programada: <?= esc($v['fecha_programada']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($v['promedio_calificaciones']) || !empty($v['numero_evaluados'])): ?>
                            <div class="text-muted" style="font-size:11px;">
                                <?= !empty($v['numero_evaluados']) ? 'Evaluados: <strong>' . (int)$v['numero_evaluados'] . '</strong>' : '' ?>
                                <?php if (!empty($v['promedio_calificaciones'])): ?>
                                    · Promedio: <strong><?= esc($v['promedio_calificaciones']) ?></strong>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($v['objetivo_ia'])): ?>
                            <details style="font-size:12px; margin-top:4px;">
                                <summary class="text-muted" style="cursor:pointer;">Ver objetivo (IA)</summary>
                                <div style="background:#f8f9fa; padding:6px 8px; border-radius:4px; margin-top:4px;"><?= nl2br(esc($v['objetivo_ia'])) ?></div>
                            </details>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($v['ruta_pdf'])): ?>
                    <a href="<?= base_url($v['ruta_pdf']) ?>" target="_blank" class="btn btn-sm btn-outline-success" style="font-size:11px; white-space:nowrap;">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <?php else: ?>
                    <span class="badge bg-secondary" style="font-size:10px;">Pendiente finalizar</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($acta['tema'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">TEMA <small class="text-muted">(nota interna)</small></h6>
            <p style="font-size:13px; margin:0; color:#666;"><?= esc($acta['tema']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ASISTENTES (<?= count($asistentes) ?>)</h6>
            <?php if (empty($asistentes)): ?>
                <p class="text-muted" style="font-size:13px;">Sin asistentes registrados.</p>
            <?php else: ?>
                <?php foreach ($asistentes as $i => $a): ?>
                <div class="mb-2 pb-2" style="border-bottom:1px solid #eee;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="font-size:14px;"><?= ($i + 1) ?>. <?= esc($a['nombre_completo']) ?></strong>
                            <div class="text-muted" style="font-size:12px;">
                                <?php if (!empty($a['numero_documento'])): ?><?= esc($a['tipo_documento']) ?> <?= esc($a['numero_documento']) ?> &middot; <?php endif; ?>
                                <?= esc($a['cargo'] ?? '') ?>
                                <?= !empty($a['area_dependencia']) ? ' &middot; <strong>Contratista:</strong> ' . esc($a['area_dependencia']) : '' ?>
                            </div>
                        </div>
                        <span style="font-size:11px;">
                            <?php if (!empty($a['firma_path'])): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Firmado <?= !empty($a['firmado_at']) ? date('d/m H:i', strtotime($a['firmado_at'])) : '' ?></span>
                            <?php elseif (!empty($a['token_firma'])): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pendiente</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Sin enlace</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($a['firma_path'])): ?>
                    <img src="<?= base_url($a['firma_path']) ?>" style="max-height:50px; margin-top:4px;">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $tieneFotosView = !empty($acta['foto_capacitacion']) || !empty($acta['foto_otros_1']) || !empty($acta['foto_otros_2']);
    ?>
    <?php if ($tieneFotosView): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRÁFICO</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $f): ?>
                    <?php if (!empty($acta[$f])): ?>
                    <a href="<?= base_url($acta[$f]) ?>" target="_blank" style="display:inline-block;">
                        <img src="<?= base_url($acta[$f]) ?>" alt="<?= esc($f) ?>" style="max-width:160px; max-height:120px; border:1px solid #dee2e6; border-radius:6px;">
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($acta['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($acta['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-4 d-grid gap-2">
        <?php
        // Modo nuevo: cada vínculo tiene su propio PDF. Si hay vínculos con ruta_pdf, mostrar botón
        // que abre el primero (los demás están listados en el bloque "Capacitaciones dictadas").
        $primerPdf = '';
        $totalPdfs = 0;
        foreach (($vinculos ?? []) as $vTmp) {
            if (!empty($vTmp['ruta_pdf'])) {
                if (!$primerPdf) $primerPdf = $vTmp['ruta_pdf'];
                $totalPdfs++;
            }
        }
        ?>
        <?php if ($acta['estado'] === 'completo' && $totalPdfs > 0): ?>
        <a href="<?= base_url($primerPdf) ?>" target="_blank" class="btn btn-pwa btn-pwa-primary">
            <i class="fas fa-file-pdf"></i> Ver PDF<?= $totalPdfs > 1 ? ' (' . $totalPdfs . ' capacitaciones)' : '' ?>
        </a>
        <?php elseif ($acta['estado'] === 'completo' && !empty($acta['ruta_pdf'])): ?>
        <a href="<?= site_url('inspecciones/acta-capacitacion/pdf/' . $acta['id']) ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($acta['estado'] === 'borrador'): ?>
        <a href="<?= site_url('inspecciones/acta-capacitacion/edit/' . $acta['id']) ?>" class="btn btn-pwa btn-pwa-primary">
            <i class="fas fa-edit"></i> Continuar editando
        </a>
        <?php endif; ?>
        <a href="<?= site_url('inspecciones/acta-capacitacion') ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
