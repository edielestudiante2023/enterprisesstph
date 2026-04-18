<?php
$aprobados   = array_filter($respuestas, fn($r) => $r['calificacion'] >= 70);
$reprobados  = array_filter($respuestas, fn($r) => $r['calificacion'] < 70);
?>
<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mb-3 mt-2">
        <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;">Resultados Evaluación</h6>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-1" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:13px; color:#999;">RESUMEN</h6>
            <div class="row text-center">
                <div class="col-4">
                    <div style="font-size:28px; font-weight:800; color:#1a2340;"><?= count($respuestas) ?></div>
                    <div style="font-size:11px; color:#999;">Respondieron</div>
                </div>
                <div class="col-4">
                    <div style="font-size:28px; font-weight:800; color:#bd9751;"><?= number_format($promedio, 1) ?>%</div>
                    <div style="font-size:11px; color:#999;">Promedio</div>
                </div>
                <div class="col-4">
                    <div style="font-size:28px; font-weight:800; color:#28a745;"><?= count($aprobados) ?></div>
                    <div style="font-size:11px; color:#999;">Aprobados</div>
                </div>
            </div>
            <div class="mt-2 text-center">
                <small class="text-muted">Evaluación: <strong><?= esc($evaluacion['titulo']) ?></strong></small><br>
                <small class="text-muted">Cliente: <strong><?= esc($cliente['nombre_cliente'] ?? '-') ?></strong></small>
            </div>
            <div class="mt-2 text-center">
                <a href="/evaluar/<?= esc($evaluacion['token']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:11px;">
                    <i class="fas fa-external-link-alt"></i> Ver formulario público
                </a>
            </div>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="card mb-3">
        <div class="card-body p-2">
            <h6 class="card-title px-2 pt-1" style="font-size:13px; color:#999;">LISTADO DE CALIFICACIONES</h6>
            <?php if (empty($respuestas)): ?>
            <p class="text-muted text-center py-3" style="font-size:13px;">Aún no hay respuestas registradas.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered" style="font-size:12px;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Cargo</th>
                            <th>Conjunto</th>
                            <th>Calificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respuestas as $i => $r):
                            $aprobado = $r['calificacion'] >= 70;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($r['nombre']) ?></td>
                            <td><?= esc($r['cedula']) ?></td>
                            <td><?= esc($r['cargo']) ?></td>
                            <td style="font-size:11px;"><?= esc($r['nombre_cliente'] ?? '-') ?></td>
                            <td class="text-center fw-bold <?= $aprobado ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($r['calificacion'], 1) ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Promedio:</td>
                            <td class="text-center fw-bold"><?= number_format($promedio, 1) ?>%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
