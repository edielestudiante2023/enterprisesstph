<?php
$totalHechas    = 0;
$totalPend      = 0;
$totalAtrasadas = 0;
$totalNoAplica  = 0;
foreach ($filas as $f) {
    if ($f['estado'] === 'hecha')         $totalHechas++;
    elseif ($f['estado'] === 'pendiente') $totalPend++;
    elseif ($f['estado'] === 'atrasada')  $totalAtrasadas++;
    else                                  $totalNoAplica++;
}
$totalTodos = count($filas);
$aplicables = $totalTodos - $totalNoAplica;
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

    <!-- Cards clickeables para filtrar la tabla -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro active" data-filtro="todas" style="background:#eef2f7; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#1c2437; font-weight:600;"><i class="fas fa-list"></i> Todas</div>
                    <div style="font-size:20px; font-weight:700; color:#1c2437;"><?= $totalTodos ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="hecha" style="background:#d4edda; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#155724; font-weight:600;"><i class="fas fa-check-circle"></i> Hechas</div>
                    <div style="font-size:20px; font-weight:700; color:#155724;"><?= $totalHechas ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="pendiente" style="background:#fff3cd; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#856404; font-weight:600;"><i class="fas fa-clock"></i> Pendientes</div>
                    <div style="font-size:20px; font-weight:700; color:#856404;"><?= $totalPend ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="atrasada" style="background:#f8d7da; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#721c24; font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Atrasadas</div>
                    <div style="font-size:20px; font-weight:700; color:#721c24;"><?= $totalAtrasadas ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 text-center card-filtro" data-filtro="no_aplica" style="background:#e2e3e5; border-radius:10px; cursor:pointer;">
                <div class="card-body py-2 px-1">
                    <div style="font-size:11px; color:#383d41; font-weight:600;"><i class="fas fa-ban"></i> No Aplica</div>
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
        <table id="tablaMatriz" class="table table-sm table-hover" style="width:100%; background:#fff;">
            <thead style="background:#1c2437; color:#fff;">
                <tr>
                    <th style="font-size:12px;">Grupo</th>
                    <th style="font-size:12px;">Tipo de Inspección</th>
                    <th style="font-size:12px;">Fechas en <?= (int) $anio ?></th>
                    <th style="font-size:12px;">Estado</th>
                    <th style="font-size:12px; text-align:right;">Acciones</th>
                </tr>
                <tr style="background:#2a3449;">
                    <th><input type="text" class="form-control form-control-sm col-filter" data-col="0" placeholder="Filtrar grupo" style="font-size:11px;"></th>
                    <th><input type="text" class="form-control form-control-sm col-filter" data-col="1" placeholder="Filtrar tipo" style="font-size:11px;"></th>
                    <th></th>
                    <th>
                        <select class="form-select form-select-sm col-filter" data-col="3" style="font-size:11px;">
                            <option value="">Todos</option>
                            <option value="Hecha">Hechas</option>
                            <option value="Pendiente">Pendientes</option>
                            <option value="Atrasada">Atrasadas</option>
                            <option value="No Aplica">No Aplica</option>
                        </select>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $f): ?>
                <?php
                $badgeClass = [
                    'hecha'     => 'background:#d4edda; color:#155724;',
                    'pendiente' => 'background:#fff3cd; color:#856404;',
                    'atrasada'  => 'background:#f8d7da; color:#721c24;',
                    'no_aplica' => 'background:#e2e3e5; color:#383d41;',
                ][$f['estado']];
                $badgeLabel = [
                    'hecha'     => '<i class="fas fa-check-circle"></i> Hecha' . ($f['total'] > 1 ? ' (' . $f['total'] . ')' : ''),
                    'pendiente' => '<i class="fas fa-clock"></i> Pendiente',
                    'atrasada'  => '<i class="fas fa-exclamation-triangle"></i> Atrasada',
                    'no_aplica' => '<i class="fas fa-ban"></i> No Aplica',
                ][$f['estado']];
                $estadoTexto = ['hecha' => 'Hecha', 'pendiente' => 'Pendiente', 'atrasada' => 'Atrasada', 'no_aplica' => 'No Aplica'][$f['estado']];
                ?>
                <tr data-estado="<?= esc($f['estado']) ?>" <?= $f['estado'] === 'no_aplica' ? 'style="opacity:0.6;"' : '' ?>>
                    <td style="font-size:11px; color:#555;"><?= esc($f['group']) ?></td>
                    <td style="font-size:13px;">
                        <i class="fas <?= esc($f['icon']) ?>" style="color:#bd9751; width:18px;"></i>
                        <?= esc($f['label']) ?>
                    </td>
                    <td style="font-size:12px;" data-order="<?= esc($f['ultima'] ?? $f['proxima_planeada'] ?? $f['ultima_vencida'] ?? '9999-99-99') ?>">
                        <?php if ($f['estado'] === 'no_aplica'): ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <?php if ($f['total'] === 0): ?>
                                <?php if ($f['estado'] === 'atrasada' && !empty($f['ultima_vencida'])): ?>
                                    <span style="color:#721c24; font-weight:600;">
                                        <i class="fas fa-exclamation-triangle"></i> Vencida desde <?= date('d/m/Y', strtotime($f['ultima_vencida'])) ?>
                                    </span>
                                <?php elseif (!empty($f['proxima_planeada'])): ?>
                                    <span style="color:#856404; font-weight:600;">
                                        <i class="fas fa-calendar-day"></i> Próxima: <?= date('d/m/Y', strtotime($f['proxima_planeada'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin registros</span>
                                <?php endif; ?>
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
                            <?php
                            $ptaVisibles = array_values(array_filter($f['pta_vinculados'], fn($v) => !empty($v['fecha_propuesta'])));
                            $ptaCount = count($ptaVisibles);
                            ?>
                            <?php if ($ptaCount > 0): ?>
                                <details class="pta-details mt-1">
                                    <summary class="pta-summary">
                                        <i class="fas fa-calendar-alt"></i> <?= $ptaCount ?> planeada<?= $ptaCount > 1 ? 's' : '' ?>
                                    </summary>
                                    <div class="pta-badges mt-1">
                                        <?php foreach ($ptaVisibles as $v): ?>
                                            <span class="badge me-1 mb-1" style="background:#e8f1fb; color:#0b5ed7; font-weight:500; padding:3px 6px; font-size:10px;"
                                                  title="PTA <?= esc($v['numeral_plandetrabajo'] ?? '') ?>: <?= esc(mb_substr($v['actividad_plandetrabajo'] ?? '', 0, 120)) ?>">
                                                <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($v['fecha_propuesta'])) ?><?= $v['estado_actividad'] === 'CERRADA' ? ' ✓' : '' ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge" style="<?= $badgeClass ?> font-size:11px; padding:5px 8px;">
                            <?= $badgeLabel ?>
                        </span>
                        <span class="d-none"><?= esc($estadoTexto) ?></span>
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
                            <button type="button" class="btn btn-xs btn-outline-primary btn-vincular-pta"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                data-count="<?= count($f['pta_vinculados']) ?>"
                                title="Vincular actividades del Plan de Trabajo"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-link"></i><?= count($f['pta_vinculados']) > 0 ? ' ' . count($f['pta_vinculados']) : '' ?>
                            </button>
                            <?php if (empty($f['pta_vinculados'])): ?>
                            <button type="button" class="btn btn-xs btn-outline-success btn-crear-pta"
                                data-slug="<?= esc($f['slug']) ?>"
                                data-label="<?= esc($f['label']) ?>"
                                title="Crear actividad en el Plan de Trabajo"
                                style="padding:2px 7px;font-size:11px;">
                                <i class="fas fa-calendar-plus"></i> PTA
                            </button>
                            <?php endif; ?>
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

<!-- Modal vincular PTA -->
<div class="modal fade" id="modalVincularPta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1c2437; color:#fff;">
                <h6 class="modal-title"><i class="fas fa-link"></i> Vincular actividades del Plan de Trabajo</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="font-size:12px;">
                    <strong>Tipo:</strong> <span id="modalSlugLabel" style="color:#bd9751;"></span>
                </div>
                <div class="small text-muted mb-2" style="font-size:11px;">
                    Marca las actividades del PTA que correspondan a este tipo de inspección. El vínculo sirve para mostrar la fecha propuesta en la matriz.
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <input type="text" id="modalBuscador" class="form-control form-control-sm" placeholder="Buscar por numeral, actividad o PHVA..." style="font-size:12px;">
                    <div class="form-check form-switch mb-0" style="white-space:nowrap;">
                        <input class="form-check-input" type="checkbox" id="modalToggleCerradas">
                        <label class="form-check-label small" for="modalToggleCerradas" style="font-size:11px;">Incluir cerradas</label>
                    </div>
                </div>
                <div id="modalPtaList" style="font-size:12px;">
                    <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                </div>
            </div>
            <div class="modal-footer">
                <span id="modalCountSelected" class="me-auto small text-muted"></span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary" id="modalBtnGuardar" style="background:#bd9751; border-color:#bd9751;">
                    <i class="fas fa-save"></i> Guardar vínculos
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.card-filtro { transition: transform .15s, box-shadow .15s, outline .15s; outline: 2px solid transparent; }
.card-filtro:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
.card-filtro.active { outline: 2px solid #bd9751; box-shadow: 0 4px 10px rgba(189,151,81,0.25); }
.pta-row { padding:6px 8px; border-bottom:1px solid #eef2f7; display:flex; align-items:flex-start; gap:8px; }
.pta-row:hover { background:#f8f9fa; }
.pta-row.pta-closed { opacity:0.7; background:#f8f9fa; }
.pta-row.pta-checked { background:#e8f1fb; }
.pta-row input[type=checkbox] { margin-top:3px; flex-shrink:0; }
.pta-row .pta-meta { font-size:11px; color:#666; }
.pta-row .pta-activity { font-size:12px; }

.pta-details { display:inline-block; }
.pta-details > .pta-summary {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 8px; background:#e8f1fb; color:#0b5ed7;
    border-radius:10px; font-size:10px; font-weight:600;
    cursor:pointer; list-style:none; user-select:none;
    transition: background .15s;
}
.pta-details > .pta-summary::-webkit-details-marker { display:none; }
.pta-details > .pta-summary::after {
    content:"\f107"; /* fa-chevron-down */
    font-family:"Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome;
    font-weight:900; margin-left:4px; font-size:9px;
    transition: transform .15s;
}
.pta-details[open] > .pta-summary::after { transform: rotate(180deg); }
.pta-details > .pta-summary:hover { background:#d6e7fb; }
.pta-details .pta-badges { display:flex; flex-wrap:wrap; gap:2px; max-width:100%; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ID_CLIENTE = <?= (int) $cliente['id_cliente'] ?>;
    const ANIO = <?= (int) $anio ?>;
    const URL_MARCAR = '<?= base_url('inspecciones/matriz/no-aplica') ?>';
    const URL_QUITAR = '<?= base_url('inspecciones/matriz/quitar-no-aplica') ?>';
    const URL_PTA_LIST = '<?= base_url('inspecciones/matriz/pta-list/' . (int) $cliente['id_cliente']) ?>';
    const URL_PTA_LINK = '<?= base_url('inspecciones/matriz/vincular-pta') ?>';
    const URL_PTA_CREAR = '<?= base_url('inspecciones/matriz/crear-pta') ?>';
    const URL_PTA_IA = '<?= base_url('inspecciones/matriz/generar-pta-ia') ?>';

    const estadoLabelMap = { 'hecha': 'Hecha', 'pendiente': 'Pendiente', 'atrasada': 'Atrasada', 'no_aplica': 'No Aplica' };

    const tabla = $('#tablaMatriz').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todas']],
        order: [[0, 'asc'], [1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [4] }
        ],
        orderCellsTop: true,
        stateSave: true,
        stateDuration: -1,
        initComplete: function () {
            document.querySelectorAll('.col-filter').forEach(function (el) {
                const saved = tabla.column(el.dataset.col).search();
                if (saved) el.value = saved;
            });
            const estadoText = tabla.column(3).search();
            const textToFiltro = { '': 'todas', 'Hecha': 'hecha', 'Pendiente': 'pendiente', 'Atrasada': 'atrasada', 'No Aplica': 'no_aplica' };
            const activeFiltro = textToFiltro[estadoText] || 'todas';
            document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
            const activeCard = document.querySelector('.card-filtro[data-filtro="' + activeFiltro + '"]');
            if (activeCard) activeCard.classList.add('active');
        }
    });

    document.querySelectorAll('.col-filter').forEach(function (el) {
        el.addEventListener('input', function () {
            tabla.column(this.dataset.col).search(this.value).draw();
        });
        el.addEventListener('change', function () {
            tabla.column(this.dataset.col).search(this.value).draw();
        });
    });

    document.querySelectorAll('.card-filtro').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.card-filtro').forEach(c => c.classList.remove('active'));
            this.classList.add('active');

            const filtro = this.dataset.filtro;
            const selectEstado = document.querySelector('.col-filter[data-col="3"]');

            if (filtro === 'todas') {
                selectEstado.value = '';
                tabla.column(3).search('').draw();
            } else {
                const label = estadoLabelMap[filtro] || '';
                selectEstado.value = label;
                tabla.column(3).search(label).draw();
            }
        });
    });

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
                confirmButtonColor: '#6c757d'
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

    // ================ VINCULAR PTA ================
    let modalSlug = null;
    let modalLabel = null;
    let modalPtas = [];
    let modalVinculados = new Set();
    const modalEl = document.getElementById('modalVincularPta');
    const modalBs = new bootstrap.Modal(modalEl);

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    }
    function fmtDate(s) {
        if (!s) return '—';
        const d = new Date(s + 'T00:00:00');
        if (isNaN(d)) return s;
        return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
    }

    function renderPtaList(filter) {
        const container = document.getElementById('modalPtaList');
        const term = (filter || '').toLowerCase().trim();
        const filtered = term
            ? modalPtas.filter(p => (p.numeral_plandetrabajo || '').toLowerCase().includes(term)
                || (p.actividad_plandetrabajo || '').toLowerCase().includes(term)
                || (p.phva_plandetrabajo || '').toLowerCase().includes(term))
            : modalPtas;

        if (filtered.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-3">Sin resultados.</div>';
            updateCountSelected();
            return;
        }

        let html = '';
        filtered.forEach(p => {
            const checked = modalVinculados.has(p.id_ptacliente);
            const closed = p.estado_actividad === 'CERRADA';
            html += `<label class="pta-row ${closed ? 'pta-closed' : ''} ${checked ? 'pta-checked' : ''}">
                <input type="checkbox" value="${p.id_ptacliente}" ${checked ? 'checked' : ''}>
                <div style="flex:1; min-width:0;">
                    <div class="pta-activity">
                        <strong>${escHtml(p.numeral_plandetrabajo)}</strong>
                        <span class="badge" style="background:#eef2f7; color:#555; font-size:9px; padding:2px 5px;">${escHtml(p.phva_plandetrabajo)}</span>
                        <span class="badge" style="background:${closed ? '#d4edda' : (p.estado_actividad === 'GESTIONANDO' ? '#fff3cd' : '#cfe2ff')}; color:#000; font-size:9px; padding:2px 5px;">${escHtml(p.estado_actividad)}</span>
                        ${escHtml((p.actividad_plandetrabajo || '').substring(0, 180))}${(p.actividad_plandetrabajo || '').length > 180 ? '…' : ''}
                    </div>
                    <div class="pta-meta">
                        Propuesta: ${fmtDate(p.fecha_propuesta)}${p.fecha_cierre ? ' · Cierre: ' + fmtDate(p.fecha_cierre) : ''}
                    </div>
                </div>
            </label>`;
        });
        container.innerHTML = html;
        container.querySelectorAll('input[type=checkbox]').forEach(cb => {
            cb.addEventListener('change', function () {
                const id = parseInt(this.value, 10);
                if (this.checked) modalVinculados.add(id); else modalVinculados.delete(id);
                this.closest('.pta-row').classList.toggle('pta-checked', this.checked);
                updateCountSelected();
            });
        });
        updateCountSelected();
    }

    function updateCountSelected() {
        document.getElementById('modalCountSelected').textContent =
            modalVinculados.size + ' seleccionada(s)';
    }

    document.querySelectorAll('.btn-vincular-pta').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modalSlug = this.dataset.slug;
            modalLabel = this.dataset.label;
            document.getElementById('modalSlugLabel').textContent = modalLabel;
            document.getElementById('modalBuscador').value = '';
            document.getElementById('modalToggleCerradas').checked = false;
            document.getElementById('modalPtaList').innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
            modalPtas = [];
            modalVinculados = new Set();
            modalBs.show();

            cargarPtas(false);
        });
    });

    function cargarPtas(incluirCerradas) {
        const params = new URLSearchParams({ slug: modalSlug, anio: ANIO });
        if (incluirCerradas) params.append('cerradas', '1');
        fetch(URL_PTA_LIST + '?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    document.getElementById('modalPtaList').innerHTML =
                        '<div class="text-center text-danger py-3">' + (data.msg || 'Error al cargar.') + '</div>';
                    return;
                }
                modalPtas = (data.ptas || []).map(p => ({ ...p, id_ptacliente: parseInt(p.id_ptacliente, 10) }));
                modalVinculados = new Set((data.vinculados || []).map(i => parseInt(i, 10)));
                renderPtaList('');
            })
            .catch(() => {
                document.getElementById('modalPtaList').innerHTML =
                    '<div class="text-center text-danger py-3">Error de red.</div>';
            });
    }

    document.getElementById('modalBuscador').addEventListener('input', function () {
        renderPtaList(this.value);
    });

    document.getElementById('modalToggleCerradas').addEventListener('change', function () {
        cargarPtas(this.checked);
    });

    document.getElementById('modalBtnGuardar').addEventListener('click', function () {
        const fd = new FormData();
        fd.append('id_cliente', ID_CLIENTE);
        fd.append('slug_inspeccion', modalSlug);
        fd.append('anio', ANIO);
        Array.from(modalVinculados).forEach(id => fd.append('ids_ptacliente[]', id));

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        fetch(URL_PTA_LINK, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    modalBs.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Vínculos guardados',
                        text: (res.added > 0 ? '+' + res.added + ' nuevo(s). ' : '') + (res.removed > 0 ? '-' + res.removed + ' quitado(s).' : ''),
                        timer: 1600,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.msg || 'No se pudo guardar.', 'error');
                    document.getElementById('modalBtnGuardar').disabled = false;
                    document.getElementById('modalBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Guardar vínculos';
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Error de red.', 'error');
                document.getElementById('modalBtnGuardar').disabled = false;
                document.getElementById('modalBtnGuardar').innerHTML = '<i class="fas fa-save"></i> Guardar vínculos';
            });
    });
    // ================ FIN VINCULAR PTA ================

    // ================ CREAR PTA ================
    document.querySelectorAll('.btn-crear-pta').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const slug = this.dataset.slug;
            const label = this.dataset.label;
            const today = new Date().toISOString().slice(0, 10);

            Swal.fire({
                title: 'Crear actividad en el Plan de Trabajo',
                width: 620,
                html: `
                    <div style="text-align:left; font-size:13px;">
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold m-0">Actividad</label>
                                <button type="button" id="ptaBtnIA" class="btn btn-sm"
                                    style="background:linear-gradient(135deg,#7B2D3B,#bd9751);color:#fff;padding:2px 10px;font-size:11px;border:none;border-radius:12px;">
                                    <i class="fas fa-wand-magic-sparkles"></i> IA: autocompletar
                                </button>
                            </div>
                            <textarea id="ptaAct" class="form-control form-control-sm" rows="2" style="font-size:12px;">Inspección de ${label}</textarea>
                            <div id="ptaIAStatus" class="small text-muted mt-1" style="font-size:10px; min-height:14px;"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Fecha propuesta</label>
                                <input id="ptaFecha" type="date" class="form-control form-control-sm" value="${today}" style="font-size:12px;">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">PHVA</label>
                                <select id="ptaPhva" class="form-select form-select-sm" style="font-size:12px;">
                                    <option value="PLANEAR">PLANEAR</option>
                                    <option value="HACER" selected>HACER</option>
                                    <option value="VERIFICAR">VERIFICAR</option>
                                    <option value="ACTUAR">ACTUAR</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small fw-bold mb-1">Numeral <span class="text-muted">(D. 1072)</span></label>
                            <input id="ptaNumeral" type="text" class="form-control form-control-sm" placeholder="Ej: 1.2.3" style="font-size:12px;">
                        </div>
                        <div class="mt-2">
                            <label class="form-label small fw-bold mb-1">Responsable sugerido</label>
                            <input id="ptaResp" type="text" class="form-control form-control-sm" value="CONSULTOR CYCLOID" style="font-size:12px;">
                        </div>
                        <div class="mt-2">
                            <label class="form-label small fw-bold mb-1">Observaciones <span class="text-muted">(opcional)</span></label>
                            <textarea id="ptaObs" class="form-control form-control-sm" rows="1" style="font-size:12px;"></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Crear y vincular',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                focusConfirm: false,
                didOpen: () => {
                    const btnIA = document.getElementById('ptaBtnIA');
                    btnIA.addEventListener('click', function () {
                        const act = document.getElementById('ptaAct').value.trim();
                        if (!act) {
                            document.getElementById('ptaIAStatus').innerHTML =
                                '<span style="color:#dc3545;"><i class="fas fa-exclamation-circle"></i> Escribe la actividad primero.</span>';
                            return;
                        }
                        btnIA.disabled = true;
                        const orig = btnIA.innerHTML;
                        btnIA.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                        document.getElementById('ptaIAStatus').innerHTML = '';

                        const fd = new FormData();
                        fd.append('actividad', act);
                        fd.append('slug_inspeccion', slug);

                        fetch(URL_PTA_IA, { method: 'POST', body: fd })
                            .then(r => r.json())
                            .then(res => {
                                if (!res.ok) {
                                    document.getElementById('ptaIAStatus').innerHTML =
                                        '<span style="color:#dc3545;"><i class="fas fa-exclamation-circle"></i> ' + (res.msg || 'Falló la IA.') + '</span>';
                                    return;
                                }
                                if (res.numeral) document.getElementById('ptaNumeral').value = res.numeral;
                                if (res.phva) document.getElementById('ptaPhva').value = res.phva;
                                if (res.responsable_sugerido) document.getElementById('ptaResp').value = res.responsable_sugerido;
                                if (res.observaciones) document.getElementById('ptaObs').value = res.observaciones;
                                document.getElementById('ptaIAStatus').innerHTML =
                                    '<span style="color:#155724;"><i class="fas fa-check-circle"></i> Campos autocompletados por IA (Claude Haiku). Puedes editarlos.</span>';
                            })
                            .catch(() => {
                                document.getElementById('ptaIAStatus').innerHTML =
                                    '<span style="color:#dc3545;"><i class="fas fa-exclamation-circle"></i> Error de red.</span>';
                            })
                            .finally(() => {
                                btnIA.disabled = false;
                                btnIA.innerHTML = orig;
                            });
                    });
                },
                preConfirm: () => {
                    const act = document.getElementById('ptaAct').value.trim();
                    const fecha = document.getElementById('ptaFecha').value;
                    const phva = document.getElementById('ptaPhva').value;
                    const numeral = document.getElementById('ptaNumeral').value.trim();
                    const resp = document.getElementById('ptaResp').value.trim();
                    const obs = document.getElementById('ptaObs').value.trim();
                    if (!act) { Swal.showValidationMessage('La actividad es obligatoria.'); return false; }
                    if (!fecha) { Swal.showValidationMessage('La fecha es obligatoria.'); return false; }
                    return { act, fecha, phva, numeral, resp, obs };
                }
            }).then(function (r) {
                if (!r.isConfirmed) return;
                const fd = new FormData();
                fd.append('id_cliente', ID_CLIENTE);
                fd.append('slug_inspeccion', slug);
                fd.append('actividad', r.value.act);
                fd.append('fecha_propuesta', r.value.fecha);
                fd.append('phva', r.value.phva);
                fd.append('numeral', r.value.numeral);
                fd.append('responsable_sugerido', r.value.resp);
                fd.append('observaciones', r.value.obs);
                fetch(URL_PTA_CREAR, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Actividad creada y vinculada',
                                text: 'id_ptacliente=' + res.id_ptacliente,
                                timer: 1800, showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.msg || 'No se pudo crear.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
            });
        });
    });
    // ================ FIN CREAR PTA ================

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
                confirmButtonColor: '#ffc107'
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
});
</script>
