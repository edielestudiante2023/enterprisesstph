<div class="container-fluid px-3">

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;">
        <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('msg') ?>
    </div>
    <?php endif; ?>

    <div class="card mt-2 mb-3">
        <div class="card-body">
            <h5 style="font-size:16px;">
                <i class="fas fa-layer-group" style="color:#bd9751;"></i>
                Capacitaciones del día
            </h5>
            <p class="text-muted mb-0" style="font-size:13px;">
                <?php if ($cliente): ?>
                    Cliente: <strong><?= esc($cliente['nombre_cliente']) ?></strong><br>
                <?php endif; ?>
                Se crearon <strong><?= count($actas) ?></strong> actas hermanas con datos comunes.
                Edite cada una individualmente para ajustar detalles, agregar asistentes, generar el QR de firmas y finalícelas por separado.
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ACTAS DEL LOTE</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle" style="font-size:13px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Capacitación</th>
                            <th class="text-center" style="width:80px;">Estado</th>
                            <th class="text-center" style="width:200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actas as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= esc($a['cronog_nombre'] ?? $a['tema'] ?? '—') ?></strong>
                                <?php if (!empty($a['fecha_programada'])): ?>
                                    <br><small class="text-muted">Programada: <?= esc($a['fecha_programada']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $estado = $a['estado'] ?? 'borrador';
                                    $badgeClass = $estado === 'completo' ? 'bg-success' : 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= esc($estado) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('inspecciones/acta-capacitacion/edit/' . $a['id']) ?>"
                                   class="btn btn-sm btn-outline-primary" style="font-size:11px;">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php if ($estado === 'completo' && !empty($a['ruta_pdf'])): ?>
                                    <a href="<?= site_url('inspecciones/acta-capacitacion/view/' . $a['id']) ?>"
                                       class="btn btn-sm btn-outline-success" style="font-size:11px;">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 mt-3 mb-5 pb-3">
        <a href="<?= site_url('inspecciones/acta-capacitacion') ?>" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:15px;">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>
