<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> Semáforo PTA — Ejecutivo</h6>
        <a href="<?= base_url('inspecciones') ?>" class="btn btn-sm btn-outline-secondary" style="padding:4px 10px;font-size:12px;">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #1c2437 0%, #2a3449 100%); border-radius:12px;">
        <div class="card-body py-3 px-3">
            <div style="color:#bd9751; font-size:11px; font-weight:600; text-transform:uppercase;">Plan de Trabajo Anual vs Ejecución</div>
            <div style="color:#fff; font-size:15px; font-weight:600;"><?= $clientesConPta ?> copropiedades con PTA mapeado</div>
            <form method="get" class="d-flex align-items-center gap-2 mt-2">
                <label style="color:#fff; font-size:12px; margin:0;">Año:</label>
                <select name="anio" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                    <?php foreach ($aniosDisponibles as $y): ?>
                        <option value="<?= $y ?>" <?= $y === (int) $anio ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 text-center" style="background:#d4edda;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#155724;font-weight:600;"><i class="fas fa-check-circle"></i> Cumplimiento</div>
                    <div style="font-size:20px;font-weight:700;color:#155724;"><?= $pctGlobalCump ?>%</div>
                    <div style="font-size:10px;color:#155724;"><?= $totalVerde ?> actividades OK</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 text-center" style="background:#f8d7da;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#721c24;font-weight:600;"><i class="fas fa-times-circle"></i> Atraso</div>
                    <div style="font-size:20px;font-weight:700;color:#721c24;"><?= $pctGlobalAtraso ?>%</div>
                    <div style="font-size:10px;color:#721c24;"><?= $totalRojo ?> críticas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 text-center" style="background:#fff3cd;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#856404;font-weight:600;"><i class="fas fa-clock"></i> Radar</div>
                    <div style="font-size:20px;font-weight:700;color:#856404;"><?= $totalAmarillo ?></div>
                    <div style="font-size:10px;color:#856404;">por ejecutar</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 text-center" style="background:#e2e3e5;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#383d41;font-weight:600;"><i class="fas fa-ghost"></i> Huérfanas</div>
                    <div style="font-size:20px;font-weight:700;color:#383d41;"><?= $totalHuerfana ?></div>
                    <div style="font-size:10px;color:#383d41;">inspecciones fuera PTA</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($topRojo)): ?>
    <div class="card border-0 mb-3" style="background:#fff;border-radius:10px;border:1px solid #f5c6cb;">
        <div class="card-body py-2 px-3">
            <div style="font-size:12px;font-weight:700;color:#721c24;text-transform:uppercase;">
                <i class="fas fa-exclamation-triangle"></i> Top 10 clientes en rojo
            </div>
            <div class="small mt-2">
            <?php foreach ($topRojo as $t): if ($t['rojo'] === 0) continue; ?>
                <a href="<?= base_url('inspecciones/pta-semaforo/' . $t['id_cliente'] . '?anio=' . $anio) ?>"
                   class="badge text-decoration-none me-1 mb-1"
                   style="background:#f8d7da;color:#721c24;font-weight:500;padding:5px 8px;">
                    <?= esc($t['nombre_cliente']) ?> <strong>(<?= $t['rojo'] ?>)</strong>
                </a>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="tablaSem" class="table table-sm table-hover" style="width:100%;background:#fff;">
            <thead style="background:#1c2437;color:#fff;">
                <tr>
                    <th style="font-size:12px;">Copropiedad</th>
                    <th style="font-size:12px;text-align:center;">Verdes</th>
                    <th style="font-size:12px;text-align:center;">Amarillas</th>
                    <th style="font-size:12px;text-align:center;">Rojas</th>
                    <th style="font-size:12px;text-align:center;">Huérfanas</th>
                    <th style="font-size:12px;text-align:center;">% Cump.</th>
                    <th style="font-size:12px;text-align:right;">Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clienteStats as $c): ?>
                <tr>
                    <td style="font-size:13px;">
                        <strong><?= esc($c['nombre_cliente']) ?></strong>
                        <?php if ($c['nit_cliente']): ?><div class="small text-muted"><?= esc($c['nit_cliente']) ?></div><?php endif; ?>
                    </td>
                    <td style="text-align:center;"><span class="badge" style="background:#d4edda;color:#155724;"><?= $c['verde'] ?></span></td>
                    <td style="text-align:center;"><span class="badge" style="background:#fff3cd;color:#856404;"><?= $c['amarillo'] ?></span></td>
                    <td style="text-align:center;"><span class="badge" style="background:<?= $c['rojo'] > 0 ? '#f8d7da' : '#eef2f7' ?>;color:#721c24;font-weight:700;"><?= $c['rojo'] ?></span></td>
                    <td style="text-align:center;"><span class="badge" style="background:#e2e3e5;color:#383d41;"><?= $c['huerfanas'] ?></span></td>
                    <td style="text-align:center;font-weight:600;color:<?= $c['pct_cumplimiento'] >= 70 ? '#155724' : ($c['pct_cumplimiento'] >= 40 ? '#856404' : '#721c24') ?>;"><?= $c['pct_cumplimiento'] ?>%</td>
                    <td style="text-align:right;">
                        <a href="<?= base_url('inspecciones/pta-semaforo/' . $c['id_cliente'] . '?anio=' . $anio) ?>"
                           class="btn btn-xs btn-outline-dark" style="padding:2px 7px;font-size:11px;">
                            <i class="fas fa-search"></i> Ver detalle
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaSem').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todas']],
        order: [[3, 'desc'], [0, 'asc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        stateSave: true,
        stateDuration: -1
    });
});
</script>
