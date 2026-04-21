<div class="container-fluid px-3 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> Semáforo PTA — Detalle</h6>
        <a href="<?= base_url('inspecciones/pta-semaforo') ?>" class="btn btn-sm btn-outline-secondary" style="padding:4px 10px;font-size:12px;">
            <i class="fas fa-arrow-left"></i> Cambiar cliente
        </a>
    </div>

    <div class="card border-0 mb-3" style="background: linear-gradient(135deg, #1c2437 0%, #2a3449 100%); border-radius:12px;">
        <div class="card-body py-3 px-3">
            <div style="color:#bd9751; font-size:11px; font-weight:600; text-transform:uppercase;">Cliente</div>
            <div style="color:#fff; font-size:17px; font-weight:600;"><?= esc($cliente['nombre_cliente']) ?></div>
            <?php if (!empty($cliente['nit_cliente'])): ?>
                <div style="color:rgba(255,255,255,0.6); font-size:12px;">NIT <?= esc($cliente['nit_cliente']) ?></div>
            <?php endif; ?>

            <form method="get" action="" class="d-flex align-items-center gap-2 mt-3">
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
        <div class="col-4 col-md-2">
            <div class="card border-0 text-center" style="background:#d4edda;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#155724;font-weight:600;"><i class="fas fa-check-circle"></i> Verdes</div>
                    <div style="font-size:20px;font-weight:700;color:#155724;"><?= $kpi['verde'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 text-center" style="background:#fff3cd;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#856404;font-weight:600;"><i class="fas fa-clock"></i> Radar</div>
                    <div style="font-size:20px;font-weight:700;color:#856404;"><?= $kpi['amarillo'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 text-center" style="background:#f8d7da;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#721c24;font-weight:600;"><i class="fas fa-times-circle"></i> Críticas</div>
                    <div style="font-size:20px;font-weight:700;color:#721c24;"><?= $kpi['rojo'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 text-center" style="background:#eef2f7;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#1c2437;font-weight:600;"><i class="fas fa-question-circle"></i> Sin Match</div>
                    <div style="font-size:20px;font-weight:700;color:#1c2437;"><?= $kpi['sin_match'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 text-center" style="background:#e2e3e5;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#383d41;font-weight:600;"><i class="fas fa-ghost"></i> Huérfanas</div>
                    <div style="font-size:20px;font-weight:700;color:#383d41;"><?= $kpi['huerfanas'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 text-center" style="background:#fff;border:2px solid #bd9751;border-radius:10px;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px;color:#8a6d34;font-weight:600;"><i class="fas fa-chart-pie"></i> % Cump.</div>
                    <div style="font-size:20px;font-weight:700;color:#8a6d34;"><?= $kpi['pct_cumplimiento'] ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($huerfanas)): ?>
    <div class="card border-0 mb-3" style="background:#fff;border-radius:10px;border:1px solid #e2e3e5;">
        <div class="card-body py-2 px-3">
            <div style="font-size:12px;font-weight:700;color:#383d41;text-transform:uppercase;">
                <i class="fas fa-ghost"></i> Inspecciones huérfanas (sin PTA correspondiente)
            </div>
            <div class="small mt-2">
            <?php foreach ($huerfanas as $h): ?>
                <span class="badge me-1 mb-1" style="background:#e2e3e5;color:#383d41;font-weight:500;padding:5px 8px;">
                    <i class="fas <?= esc($h['icon']) ?>"></i> <?= esc($h['label']) ?> <strong>(<?= $h['count'] ?>)</strong>
                </span>
            <?php endforeach; ?>
            </div>
            <div class="small text-muted mt-2" style="font-size:11px;">
                Estas inspecciones se ejecutaron pero no tienen actividad PTA asociada. Revisa si deben añadirse al plan o si el match manual está faltando.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table id="tablaPta" class="table table-sm table-hover" style="width:100%;background:#fff;">
            <thead style="background:#1c2437;color:#fff;">
                <tr>
                    <th style="font-size:12px;">PHVA</th>
                    <th style="font-size:12px;">Numeral</th>
                    <th style="font-size:12px;">Actividad PTA</th>
                    <th style="font-size:12px;">Fechas</th>
                    <th style="font-size:12px;">Inspecciones mapeadas</th>
                    <th style="font-size:12px;text-align:center;">Semáforo</th>
                    <th style="font-size:12px;text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f):
                $p = $f['pta'];
                $sem = $f['semaforo'];
                $semStyle = [
                    'verde'     => 'background:#d4edda;color:#155724;',
                    'amarillo'  => 'background:#fff3cd;color:#856404;',
                    'rojo'      => 'background:#f8d7da;color:#721c24;',
                    'sin_match' => 'background:#eef2f7;color:#1c2437;',
                ][$sem];
                $semLabel = [
                    'verde'     => '<i class="fas fa-check-circle"></i> Ejecutado',
                    'amarillo'  => '<i class="fas fa-clock"></i> Radar',
                    'rojo'      => '<i class="fas fa-times-circle"></i> Crítico',
                    'sin_match' => '<i class="fas fa-question-circle"></i> Sin match',
                ][$sem];
            ?>
                <tr data-sem="<?= $sem ?>">
                    <td style="font-size:11px;"><span class="badge bg-secondary"><?= esc($p['phva_plandetrabajo']) ?></span></td>
                    <td style="font-size:11px;"><?= esc($p['numeral_plandetrabajo']) ?></td>
                    <td style="font-size:12px;"><?= esc(mb_substr($p['actividad_plandetrabajo'], 0, 150)) ?></td>
                    <td style="font-size:11px;">
                        <?php if ($p['fecha_propuesta']): ?>Propuesta: <?= date('d/m/Y', strtotime($p['fecha_propuesta'])) ?><br><?php endif; ?>
                        <?php if ($p['fecha_cierre']): ?>Cierre: <?= date('d/m/Y', strtotime($p['fecha_cierre'])) ?><?php endif; ?>
                    </td>
                    <td style="font-size:11px;">
                        <?php if (empty($f['matches'])): ?>
                            <span class="text-muted">Sin clasificar</span>
                        <?php else: ?>
                            <?php foreach ($f['matches'] as $m): ?>
                                <?php $t = \App\Libraries\InspeccionTypes::bySlug($m['slug_inspeccion']); ?>
                                <?php if (!$t) continue; ?>
                                <div style="font-size:11px;">
                                    <span class="badge" style="background:#eef2f7;color:#1c2437;">
                                        <i class="fas <?= esc($t['icon']) ?>"></i> <?= esc($t['label']) ?>
                                    </span>
                                    <span class="small text-muted">(<?= number_format($m['score'], 2) ?>)</span>
                                    <button class="btn btn-xs text-danger btn-quitar-match"
                                            data-idpta="<?= (int) $p['id_ptacliente'] ?>"
                                            data-slug="<?= esc($m['slug_inspeccion']) ?>"
                                            style="border:none;background:none;padding:0 2px;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <span class="badge" style="<?= $semStyle ?> padding:5px 8px;font-size:11px;"><?= $semLabel ?></span>
                        <?php if ($f['inspecciones'] > 0): ?>
                            <div class="small text-muted" style="font-size:10px;"><?= $f['inspecciones'] ?> ejecución(es)</div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        <button class="btn btn-xs btn-outline-primary btn-agregar-match"
                                data-idpta="<?= (int) $p['id_ptacliente'] ?>"
                                data-actividad="<?= esc($p['actividad_plandetrabajo']) ?>"
                                title="Agregar match manual"
                                style="padding:2px 7px;font-size:11px;">
                            <i class="fas fa-plus"></i> Match
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ID_CLIENTE = <?= (int) $cliente['id_cliente'] ?>;
    const URL_ADD = '<?= base_url('inspecciones/pta-semaforo/agregar-match') ?>';
    const URL_DEL = '<?= base_url('inspecciones/pta-semaforo/quitar-match') ?>';
    const CATALOG = <?= json_encode(array_map(fn($t) => ['slug' => $t['slug'], 'label' => $t['label'], 'group' => $t['group']], $catalog), JSON_UNESCAPED_UNICODE) ?>;

    $('#tablaPta').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todas']],
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [4, 6] }],
        stateSave: true,
        stateDuration: -1
    });

    document.querySelectorAll('.btn-agregar-match').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const idPta = this.dataset.idpta;
            const actividad = this.dataset.actividad;
            const opts = CATALOG.map(t => `<option value="${t.slug}">[${t.group}] ${t.label}</option>`).join('');

            Swal.fire({
                title: 'Agregar match manual',
                html: `<div style="text-align:left;"><p style="font-size:12px;color:#666;margin-bottom:10px;">${actividad.substring(0, 200)}${actividad.length > 200 ? '...' : ''}</p><select id="swalSlug" class="form-select" style="width:100%;">${opts}</select></div>`,
                showCancelButton: true,
                confirmButtonText: 'Agregar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#bd9751',
                preConfirm: () => document.getElementById('swalSlug').value
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fd.append('id_ptacliente', idPta);
                fd.append('slug_inspeccion', r.value);
                fetch(URL_ADD, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => { if (res.ok) location.reload(); else Swal.fire('Error', res.msg || 'Falló.', 'error'); });
            });
        });
    });

    document.querySelectorAll('.btn-quitar-match').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const idPta = this.dataset.idpta;
            const slug = this.dataset.slug;

            Swal.fire({
                title: '¿Quitar este match?',
                text: 'Esta clasificación se eliminará.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fd.append('id_ptacliente', idPta);
                fd.append('slug_inspeccion', slug);
                fetch(URL_DEL, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => { if (res.ok) location.reload(); else Swal.fire('Error', 'Falló.', 'error'); });
            });
        });
    });
});
</script>
