<?php
$totalHechas    = 0;
$totalPend      = 0;
$totalNoAplica  = 0;
foreach ($filas as $f) {
    if ($f['estado'] === 'hecha')      $totalHechas++;
    elseif ($f['estado'] === 'pendiente') $totalPend++;
    else $totalNoAplica++;
}
$aplicables = count($filas) - $totalNoAplica;
$cobertura  = $aplicables > 0 ? round(($totalHechas / $aplicables) * 100) : 0;
?>
<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-th-list"></i> Matriz de Inspecciones</h6>
        <a href="<?= base_url('inspecciones/matriz') ?>" class="btn btn-sm btn-outline-secondary" style="padding:4px 10px;font-size:12px;">
            <i class="fas fa-arrow-left"></i> Cambiar cliente
        </a>
    </div>

    <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #1c2437 0%, #2a3449 100%); border-radius:12px;">
        <div class="card-body py-3 px-3">
            <div style="color:#bd9751; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
            <div style="color:#fff; font-size:17px; font-weight:600;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
            <?php if (!empty($cliente['nit_cliente'])): ?>
                <div style="color:rgba(255,255,255,0.6); font-size:12px;">NIT <?= esc($cliente['nit_cliente']) ?></div>
            <?php endif; ?>

            <form method="get" action="<?= base_url('inspecciones/matriz/' . (int) $cliente['id_cliente']) ?>" class="d-flex align-items-center gap-2 mt-3">
                <label for="anio" style="color:#fff; font-size:12px; margin:0;">Año:</label>
                <select name="anio" id="anio" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                    <?php foreach ($aniosDisponibles as $y): ?>
                        <option value="<?= $y ?>" <?= $y === (int) $anio ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-4">
            <div class="card border-0 text-center" style="background:#d4edda; border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#155724; font-weight:600;">Hechas</div>
                    <div style="font-size:20px; font-weight:700; color:#155724;"><?= $totalHechas ?></div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 text-center" style="background:#fff3cd; border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#856404; font-weight:600;">Pendientes</div>
                    <div style="font-size:20px; font-weight:700; color:#856404;"><?= $totalPend ?></div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 text-center" style="background:#e2e3e5; border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#383d41; font-weight:600;">No Aplica</div>
                    <div style="font-size:20px; font-weight:700; color:#383d41;"><?= $totalNoAplica ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 mb-3" style="background:#f8f9fa; border-radius:10px;">
        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
            <span style="font-size:12px; color:#555;"><i class="fas fa-chart-pie"></i> Cobertura (sobre aplicables)</span>
            <span style="font-size:18px; font-weight:700; color:#2e7d4f;"><?= $cobertura ?>%</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover" style="width:100%; background:#fff;">
            <thead style="background:#1c2437; color:#fff;">
                <tr>
                    <th style="font-size:12px;">Tipo de Inspección</th>
                    <th style="font-size:12px;">Fecha(s) en <?= (int) $anio ?></th>
                    <th style="font-size:12px;">Estado</th>
                    <th style="font-size:12px; text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f): ?>
                <?php
                $badgeClass = [
                    'hecha'     => 'background:#d4edda; color:#155724;',
                    'pendiente' => 'background:#fff3cd; color:#856404;',
                    'no_aplica' => 'background:#e2e3e5; color:#383d41;',
                ][$f['estado']];
                $badgeLabel = [
                    'hecha'     => '<i class="fas fa-check-circle"></i> Hecha' . ($f['total'] > 1 ? ' (' . $f['total'] . ')' : ''),
                    'pendiente' => '<i class="fas fa-clock"></i> Pendiente',
                    'no_aplica' => '<i class="fas fa-ban"></i> No Aplica',
                ][$f['estado']];
                ?>
                <tr <?= $f['estado'] === 'no_aplica' ? 'style="opacity:0.6;"' : '' ?>>
                    <td style="font-size:13px;">
                        <i class="fas <?= esc($f['icon']) ?>" style="color:#bd9751; width:18px;"></i>
                        <?= esc($f['label']) ?>
                    </td>
                    <td style="font-size:12px;">
                        <?php if ($f['estado'] === 'no_aplica'): ?>
                            <span class="text-muted">—</span>
                        <?php elseif ($f['total'] === 0): ?>
                            <span class="text-muted">Sin registros</span>
                        <?php else: ?>
                            <?php foreach ($f['inspecciones'] as $i => $insp): if ($i >= 3) break; ?>
                                <a href="<?= base_url($f['view_route'] . '/' . (int) $insp['id']) ?>"
                                   class="badge text-decoration-none me-1"
                                   style="background:#eef2f7; color:#1c2437; font-weight:500; padding:4px 7px;"
                                   title="Ver inspección">
                                    <?= date('d/m/Y', strtotime($insp['fecha'])) ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if ($f['total'] > 3): ?>
                                <a href="<?= base_url($f['list_route']) ?>" class="small text-muted">+<?= $f['total'] - 3 ?> más</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge" style="<?= $badgeClass ?> font-size:11px; padding:5px 8px;">
                            <?= $badgeLabel ?>
                        </span>
                        <?php if ($f['estado'] === 'no_aplica' && !empty($f['no_aplica']['motivo'])): ?>
                            <div class="small text-muted mt-1" style="font-size:10px;"><?= esc($f['no_aplica']['motivo']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <?php if ($f['estado'] !== 'no_aplica'): ?>
                            <a href="<?= base_url($f['create_route'] . '/' . (int) $cliente['id_cliente']) ?>"
                               class="btn btn-xs btn-outline-success" title="Nueva inspección"
                               style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a href="<?= base_url($f['list_route']) ?>"
                               class="btn btn-xs btn-outline-dark" title="Listar todas"
                               style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-list"></i>
                            </a>
                            <button type="button" class="btn btn-xs btn-outline-secondary btn-marcar-na"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                title="Marcar como No Aplica"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-ban"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-xs btn-outline-warning btn-quitar-na"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                title="Quitar No Aplica"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-undo"></i> Quitar N/A
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const ID_CLIENTE = <?= (int) $cliente['id_cliente'] ?>;
    const URL_MARCAR = '<?= base_url('inspecciones/matriz/no-aplica') ?>';
    const URL_QUITAR = '<?= base_url('inspecciones/matriz/quitar-no-aplica') ?>';

    document.querySelectorAll('.btn-marcar-na').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const slug = this.dataset.slug;
            const label = this.dataset.label;

            Swal.fire({
                title: 'Marcar "' + label + '" como No Aplica',
                input: 'text',
                inputPlaceholder: 'Motivo (opcional), ej: El edificio no tiene ascensor',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Marcar N/A',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#6c757d',
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fd.append('tipo_inspeccion', slug);
                fd.append('motivo', r.value || '');
                fetch(URL_MARCAR, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok) location.reload();
                        else Swal.fire('Error', res.msg || 'No se pudo marcar.', 'error');
                    })
                    .catch(() => Swal.fire('Error', 'No se pudo marcar.', 'error'));
            });
        });
    });

    document.querySelectorAll('.btn-quitar-na').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const slug = this.dataset.slug;
            const label = this.dataset.label;

            Swal.fire({
                title: '¿Quitar marca No Aplica?',
                text: '"' + label + '" volverá al listado como pendiente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ffc107',
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fd.append('tipo_inspeccion', slug);
                fetch(URL_QUITAR, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok) location.reload();
                        else Swal.fire('Error', res.msg || 'No se pudo quitar.', 'error');
                    })
                    .catch(() => Swal.fire('Error', 'No se pudo quitar.', 'error'));
            });
        });
    });
})();
</script>
