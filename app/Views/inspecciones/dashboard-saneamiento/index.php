<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Dashboard Plan de Saneamiento</h5>
</div>

<!-- Selector de cliente -->
<div class="card mb-3">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('/inspecciones/dashboard-saneamiento') ?>">
            <label class="form-label fw-bold" style="font-size:13px;">Seleccionar Cliente</label>
            <select name="id" class="form-select form-select-sm select2-cliente" onchange="this.form.submit()">
                <option value="">-- Seleccione --</option>
                <?php foreach ($clientes as $c): ?>
                <option value="<?= $c['id_cliente'] ?>" <?= ((int)($idCliente ?? 0) === (int)$c['id_cliente']) ? 'selected' : '' ?>>
                    <?= esc($c['nombre_cliente']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if ($clienteSeleccionado && $resultados): ?>
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
        <i class="fas fa-building me-1"></i> <?= esc($clienteSeleccionado['nombre_cliente']) ?> — Consolidado de Indicadores
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped mb-0" style="font-size:12px;">
                <thead class="table-dark">
                    <tr>
                        <th style="width:5%;">#</th>
                        <th style="width:20%;">Programa</th>
                        <th style="width:25%;">Indicador</th>
                        <th style="width:10%;">Meta</th>
                        <th style="width:10%;">Resultado</th>
                        <th style="width:10%;">Calificación</th>
                        <th style="width:10%;">Fecha</th>
                        <th style="width:10%;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $i => $r): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= esc($r['programa']) ?></td>
                        <td><?= esc($r['indicador']) ?></td>
                        <td class="text-center"><?= esc($r['meta_texto']) ?></td>
                        <td class="text-center">
                            <?php if ($r['cumplimiento'] !== null): ?>
                                <strong><?= number_format($r['cumplimiento'], 1) ?>%</strong>
                            <?php else: ?>
                                <span class="text-muted">Sin medición</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r['calificacion']): ?>
                                <span class="badge <?= $r['calificacion'] === 'CUMPLE' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= esc($r['calificacion']) ?>
                                </span>
                            <?php elseif ($r['cumplimiento'] !== null): ?>
                                <span class="badge <?= $r['cumplimiento'] >= $r['meta'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $r['cumplimiento'] >= $r['meta'] ? 'CUMPLE' : 'NO CUMPLE' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($r['fecha']): ?>
                                <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['observaciones'])): ?>
                                <?= esc($r['observaciones']) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Resumen rápido -->
<?php
$total = count($resultados);
$medidos = array_filter($resultados, fn($r) => $r['cumplimiento'] !== null);
$cumplen = array_filter($resultados, fn($r) => $r['calificacion'] === 'CUMPLE' || ($r['calificacion'] === null && $r['cumplimiento'] !== null && $r['cumplimiento'] >= $r['meta']));
$noCumplen = count($medidos) - count($cumplen);
?>
<div class="row g-2 mb-4">
    <div class="col-4">
        <div class="card text-center border-primary">
            <div class="card-body p-2">
                <div style="font-size:22px; font-weight:bold;" class="text-primary"><?= count($medidos) ?>/<?= $total ?></div>
                <div style="font-size:11px;">Indicadores medidos</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center border-success">
            <div class="card-body p-2">
                <div style="font-size:22px; font-weight:bold;" class="text-success"><?= count($cumplen) ?></div>
                <div style="font-size:11px;">Cumplen</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center border-danger">
            <div class="card-body p-2">
                <div style="font-size:22px; font-weight:bold;" class="text-danger"><?= $noCumplen ?></div>
                <div style="font-size:11px;">No cumplen</div>
            </div>
        </div>
    </div>
</div>
<?php elseif ($idCliente && !$resultados): ?>
<div class="alert alert-info">No se encontraron datos para este cliente.</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2-cliente').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '-- Seleccione --' });
    }
});
</script>
