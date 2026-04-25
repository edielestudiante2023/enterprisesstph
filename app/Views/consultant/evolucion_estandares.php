<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolucion Estandares - Enterprise SST</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-logos { background: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); padding: 10px 0; position: fixed; top: 0; width: 100%; z-index: 1000; }
        .header-metric { text-align: center; padding: 0 6px; }
        .header-metric small { display: block; color: #6c757d; font-size: 0.7rem; line-height: 1; }
        .header-metric h4 { margin: 0; font-size: 1.4rem; line-height: 1.1; color: #1c2437; }
        .chart-container { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem; min-height: 350px; }
        .chart-container h5 { text-align: center; color: #1c2437; font-weight: 600; margin-bottom: 0.5rem; }
        .chart-container .chart-hint { display: block; text-align: center; color: #6c757d; font-size: 0.75rem; margin-bottom: 0.5rem; }
        .filter-section { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem; }
        .table-container { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem; }
        .btn-clear { background: linear-gradient(135deg, #1c2437, #2c3e50); color: white; border: none; }
        .btn-clear:hover { background: linear-gradient(135deg, #2c3e50, #1c2437); color: white; }
        footer { background: white; padding: 20px 0; border-top: 1px solid #dee2e6; margin-top: 40px; text-align: center; font-size: 14px; color: #6c757d; }
    </style>
</head>

<body>
    <nav class="navbar-logos">
        <div class="d-flex justify-content-between align-items-center px-4" style="max-width: 1500px; margin: 0 auto;">
            <div>
                <a href="<?= base_url('/admin/dashboard') ?>"><img src="<?= base_url('uploads/logocycloidhorizontal.png') ?>" alt="Cycloid" style="height: 50px;"></a>
            </div>
            <div class="header-metric">
                <small>% CUMPLIMIENTO PROMEDIO</small>
                <h4 id="headerPctCumplimiento"><?= $pctCumplimiento ?>%</h4>
            </div>
            <div class="header-metric">
                <small>TOTAL CLIENTES</small>
                <h4 id="headerTotalClientes"><?= $totalClientes ?></h4>
            </div>
            <div>
                <h6 class="mb-0 fw-bold" style="color: #1c2437;">EVOLUCION ESTANDARES</h6>
            </div>
            <div>
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST" style="height: 50px;">
            </div>
        </div>
    </nav>

    <div style="height: 80px;"></div>

    <div class="container-fluid px-4" style="max-width: 1500px; margin: 0 auto;">

        <!-- Filtros principales -->
        <div class="filter-section">
            <div class="row g-3 align-items-end">
                <?php if ($role === 'admin'): ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-user-tie me-1"></i> Consultor</label>
                    <select id="filterConsultor" class="form-select">
                        <option value="">Todos los consultores</option>
                        <?php foreach ($consultoresUnicos as $c): ?>
                            <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-building me-1"></i> Cliente</label>
                    <select id="filterCliente" class="form-select">
                        <option value="">Todos los clientes</option>
                        <?php foreach ($clientesUnicos as $cl): ?>
                            <option value="<?= esc($cl) ?>"><?= esc($cl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-user-friends me-1"></i> Consultor Externo</label>
                    <select id="filterConsultorExterno" class="form-select">
                        <option value="">Todos los consultores externos</option>
                        <?php foreach ($consultoresExternosUnicos as $ce): ?>
                            <option value="<?= esc($ce) ?>"><?= esc($ce) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-layer-group me-1"></i> Frecuencia de Visita</label>
                    <select id="filterEstandaresFrec" class="form-select">
                        <option value="">Todas las frecuencias</option>
                        <?php foreach ($estandaresFrecUnicos as $e): ?>
                            <option value="<?= esc($e) ?>"><?= esc($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Período + toggle activos + slider % -->
        <div class="filter-section">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-calendar me-1"></i> Período Desde</label>
                    <input type="month" class="form-control" id="filterPeriodoDesde" value="<?= $anioActual ?>-01">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-calendar me-1"></i> Período Hasta</label>
                    <input type="month" class="form-control" id="filterPeriodoHasta" value="<?= $anioActual ?>-12">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-percentage me-1"></i> Rango % Cumplimiento</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" class="form-control form-control-sm" id="filterPctMin" min="0" max="100" value="0" style="width: 70px;">
                        <input type="range" class="form-range flex-grow-1" id="rangeMin" min="0" max="100" value="0">
                        <input type="range" class="form-range flex-grow-1" id="rangeMax" min="0" max="100" value="100">
                        <input type="number" class="form-control form-control-sm" id="filterPctMax" min="0" max="100" value="100" style="width: 70px;">
                    </div>
                </div>
                <div class="col-md-3 d-flex flex-column gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filterSoloActivos">
                        <label class="form-check-label fw-bold" for="filterSoloActivos">Solo clientes activos hoy</label>
                    </div>
                    <button id="btnClear" class="btn btn-clear w-100">
                        <i class="fas fa-eraser me-1"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-8">
                <div class="chart-container">
                    <h5>Evolución % Cumplimiento por Consultor</h5>
                    <span class="chart-hint">Click en un punto para filtrar por consultor + mes</span>
                    <canvas id="chartLinea"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5>Distribución por Consultor</h5>
                    <span class="chart-hint">Click en un segmento para filtrar por consultor</span>
                    <canvas id="chartDonut"></canvas>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-container">
            <div class="table-responsive">
                <table id="tablaEst" class="table table-striped table-bordered" style="width: 100%;">
                    <thead class="table-dark">
                        <tr>
                            <th>Consultor</th>
                            <th>Cliente</th>
                            <th>Total Valor</th>
                            <th>Total Puntaje</th>
                            <th>% Cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p class="mb-0"><strong>Cycloid Talent SAS</strong> - Todos los derechos reservados &copy; <?= date('Y') ?></p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const originalData = <?= json_encode($registros) ?>;
        const role = '<?= $role ?>';
        const colorsLine = ['#4facfe', '#f5576c', '#43e97b', '#fa709a', '#667eea', '#ffc107', '#20c997', '#e44d26', '#6f42c1', '#fd7e14'];
        const colorsDonut = ['rgba(79, 172, 254, 0.8)', 'rgba(245, 87, 108, 0.8)', 'rgba(67, 233, 123, 0.8)', 'rgba(250, 112, 154, 0.8)', 'rgba(102, 126, 234, 0.8)', 'rgba(255, 193, 7, 0.8)', 'rgba(32, 201, 151, 0.8)', 'rgba(228, 77, 38, 0.8)'];

        let chartLinea = null;
        let chartDonut = null;
        let lineaConsultoresOrden = [];
        let lineaMesesOrden = [];
        let donutLabels = [];
        let suspendCascade = false;

        const FILTERS_STORAGE_KEY = 'evolucionEstandaresFilters';

        function saveFilters() {
            try {
                const state = {
                    consultor:        $('#filterConsultor').val(),
                    cliente:          $('#filterCliente').val(),
                    consultorExterno: $('#filterConsultorExterno').val(),
                    estandaresFrec:   $('#filterEstandaresFrec').val(),
                    periodoDesde:     $('#filterPeriodoDesde').val(),
                    periodoHasta:     $('#filterPeriodoHasta').val(),
                    pctMin:           $('#filterPctMin').val(),
                    pctMax:           $('#filterPctMax').val(),
                    soloActivos:      $('#filterSoloActivos').is(':checked')
                };
                localStorage.setItem(FILTERS_STORAGE_KEY, JSON.stringify(state));
            } catch (e) {}
        }

        function loadFilters() {
            try {
                const raw = localStorage.getItem(FILTERS_STORAGE_KEY);
                if (!raw) return null;
                return JSON.parse(raw);
            } catch (e) { return null; }
        }

        function applySavedFilters(state) {
            if (!state) return;
            suspendCascade = true;
            if (state.consultor !== undefined && $('#filterConsultor').length) $('#filterConsultor').val(state.consultor);
            if (state.cliente !== undefined)          $('#filterCliente').val(state.cliente);
            if (state.consultorExterno !== undefined) $('#filterConsultorExterno').val(state.consultorExterno);
            if (state.estandaresFrec !== undefined)   $('#filterEstandaresFrec').val(state.estandaresFrec);
            if (state.periodoDesde !== undefined)     $('#filterPeriodoDesde').val(state.periodoDesde);
            if (state.periodoHasta !== undefined)     $('#filterPeriodoHasta').val(state.periodoHasta);
            if (state.pctMin !== undefined)           { $('#filterPctMin').val(state.pctMin); $('#rangeMin').val(state.pctMin); }
            if (state.pctMax !== undefined)           { $('#filterPctMax').val(state.pctMax); $('#rangeMax').val(state.pctMax); }
            if (state.soloActivos !== undefined)      $('#filterSoloActivos').prop('checked', !!state.soloActivos);
            $('#filterConsultor, #filterCliente, #filterConsultorExterno, #filterEstandaresFrec').trigger('change.select2');
            suspendCascade = false;
        }

        if (role === 'admin') $('#filterConsultor').select2({ theme: 'bootstrap-5', placeholder: 'Todos los consultores', allowClear: true, width: '100%' });
        $('#filterCliente').select2({ theme: 'bootstrap-5', placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        $('#filterConsultorExterno').select2({ theme: 'bootstrap-5', placeholder: 'Todos los externos', allowClear: true, width: '100%' });
        $('#filterEstandaresFrec').select2({ theme: 'bootstrap-5', placeholder: 'Todas las frecuencias', allowClear: true, width: '100%' });

        const dt = $('#tablaEst').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            pageLength: 25,
            order: [[4, 'desc']],
            columns: [
                { data: 'nombre_consultor' },
                { data: 'nombre_cliente' },
                { data: 'total_valor' },
                { data: 'total_puntaje' },
                { data: 'porcentaje_cumplimiento' }
            ]
        });

        function getFiltered(excluding) {
            const consultor = $('#filterConsultor').val();
            const cliente = $('#filterCliente').val();
            const externo = $('#filterConsultorExterno').val();
            const frec = $('#filterEstandaresFrec').val();
            const desde = $('#filterPeriodoDesde').val();
            const hasta = $('#filterPeriodoHasta').val();
            const pctMin = parseFloat($('#filterPctMin').val()) || 0;
            const pctMax = parseFloat($('#filterPctMax').val()) || 100;
            const soloActivos = $('#filterSoloActivos').is(':checked');

            return originalData.filter(r => {
                const ym = r.fecha_extraccion ? String(r.fecha_extraccion).substring(0, 7) : null;
                if (desde && (!ym || ym < desde)) return false;
                if (hasta && (!ym || ym > hasta)) return false;

                if (soloActivos && (r.cliente_estado_actual !== 'activo')) return false;

                if (excluding !== 'consultor' && consultor && r.nombre_consultor !== consultor) return false;
                if (excluding !== 'cliente' && cliente && r.nombre_cliente !== cliente) return false;
                if (excluding !== 'externo' && externo && (r.cliente_consultor_externo || '') !== externo) return false;
                if (excluding !== 'frec' && frec && r.estandares !== frec) return false;

                const pct = parseFloat(r.porcentaje_cumplimiento) || 0;
                if (pct < pctMin || pct > pctMax) return false;

                return true;
            });
        }

        function rebuildSelect($sel, options, currentVal, placeholderText) {
            let html = '<option value="">' + placeholderText + '</option>';
            let stillValid = false;
            options.forEach(opt => {
                const sel = (String(opt) === String(currentVal)) ? ' selected' : '';
                if (sel) stillValid = true;
                html += '<option value="' + $('<div>').text(opt).html() + '"' + sel + '>' + $('<div>').text(opt).html() + '</option>';
            });
            $sel.html(html);
            if (!stillValid && currentVal) $sel.val('');
        }

        function updateCascadeDropdowns() {
            const sConsultor = $('#filterConsultor').val();
            const sCliente = $('#filterCliente').val();
            const sExterno = $('#filterConsultorExterno').val();
            const sFrec = $('#filterEstandaresFrec').val();
            const distinctNonEmpty = (arr) => [...new Set(arr.filter(Boolean))].sort();

            if (role === 'admin') {
                const poolConsultor = getFiltered('consultor');
                rebuildSelect($('#filterConsultor'), distinctNonEmpty(poolConsultor.map(r => r.nombre_consultor)), sConsultor, 'Todos los consultores');
            }
            const poolCliente = getFiltered('cliente');
            rebuildSelect($('#filterCliente'), distinctNonEmpty(poolCliente.map(r => r.nombre_cliente)), sCliente, 'Todos los clientes');
            const poolExterno = getFiltered('externo');
            rebuildSelect($('#filterConsultorExterno'), distinctNonEmpty(poolExterno.map(r => r.cliente_consultor_externo)), sExterno, 'Todos los consultores externos');
            const poolFrec = getFiltered('frec');
            rebuildSelect($('#filterEstandaresFrec'), distinctNonEmpty(poolFrec.map(r => r.estandares)), sFrec, 'Todas las frecuencias');

            $('#filterCliente, #filterConsultorExterno, #filterEstandaresFrec').trigger('change.select2');
            if (role === 'admin') $('#filterConsultor').trigger('change.select2');
        }

        function updateAll() {
            const filtered = getFiltered();
            updateLineChart(filtered);
            updateDonutChart(filtered);
            updateTable(filtered);
            updateHeaders(filtered);
            saveFilters();
        }

        function updateLineChart(data) {
            const consultores = [...new Set(data.map(r => r.nombre_consultor))].filter(Boolean).sort();
            const meses = [...new Set(data.map(r => r.fecha_extraccion ? String(r.fecha_extraccion).substring(0, 7) : null).filter(Boolean))].sort();

            lineaConsultoresOrden = consultores;
            lineaMesesOrden = meses;

            const datasets = consultores.map((consultor, idx) => {
                const points = meses.map(mes => {
                    const regs = data.filter(r => r.nombre_consultor === consultor && r.fecha_extraccion && String(r.fecha_extraccion).substring(0, 7) === mes);
                    if (regs.length === 0) return null;
                    const totalVal = regs.reduce((s, r) => s + parseFloat(r.total_valor || 0), 0);
                    const totalPun = regs.reduce((s, r) => s + parseFloat(r.total_puntaje || 0), 0);
                    return totalPun > 0 ? Math.round((totalVal / totalPun) * 1000) / 10 : null;
                });
                return {
                    label: consultor,
                    data: points,
                    borderColor: colorsLine[idx % colorsLine.length],
                    backgroundColor: colorsLine[idx % colorsLine.length],
                    fill: false, tension: 0.3, spanGaps: true,
                    pointRadius: 5, pointHoverRadius: 7
                };
            });

            const labels = meses.map(m => {
                const [y, mo] = m.split('-');
                const names = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                return names[parseInt(mo) - 1] + ' ' + y;
            });

            if (chartLinea) chartLinea.destroy();
            chartLinea = new Chart(document.getElementById('chartLinea').getContext('2d'), {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        const el = elements[0];
                        const consultor = lineaConsultoresOrden[el.datasetIndex];
                        const mes = lineaMesesOrden[el.index];
                        if (role === 'admin') $('#filterConsultor').val(consultor).trigger('change.select2');
                        if (mes) {
                            $('#filterPeriodoDesde').val(mes);
                            $('#filterPeriodoHasta').val(mes);
                        }
                        updateCascadeDropdowns();
                        updateAll();
                    },
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
                    scales: { y: { beginAtZero: true, max: 100, title: { display: true, text: '% Cumplimiento' } } }
                }
            });
        }

        function updateDonutChart(data) {
            const consultores = [...new Set(data.map(r => r.nombre_consultor))].filter(Boolean).sort();
            const counts = consultores.map(c => data.filter(r => r.nombre_consultor === c).length);
            const total = counts.reduce((a, b) => a + b, 0) || 1;

            donutLabels = consultores;

            if (chartDonut) chartDonut.destroy();
            chartDonut = new Chart(document.getElementById('chartDonut').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: consultores,
                    datasets: [{ data: counts, backgroundColor: colorsDonut.slice(0, consultores.length), borderWidth: 2 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length || role !== 'admin') return;
                        const idx = elements[0].index;
                        const consultor = donutLabels[idx];
                        const current = $('#filterConsultor').val();
                        const newVal = (current === consultor) ? '' : consultor;
                        $('#filterConsultor').val(newVal).trigger('change.select2');
                        updateCascadeDropdowns();
                        updateAll();
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.label + ': ' + ((ctx.raw / total) * 100).toFixed(1) + '%'
                            }
                        }
                    }
                }
            });
        }

        function updateTable(data) {
            const latest = {};
            data.forEach(r => {
                const key = r.nombre_cliente;
                if (!latest[key] || r.fecha_extraccion > latest[key].fecha_extraccion) latest[key] = r;
            });
            dt.clear();
            Object.values(latest).forEach(r => {
                dt.row.add({
                    nombre_consultor: r.nombre_consultor,
                    nombre_cliente: r.nombre_cliente,
                    total_valor: r.total_valor,
                    total_puntaje: r.total_puntaje,
                    porcentaje_cumplimiento: r.porcentaje_cumplimiento
                });
            });
            dt.draw();
        }

        function updateHeaders(data) {
            const clientes = [...new Set(data.map(r => r.nombre_cliente))];
            const totalVal = data.reduce((s, r) => s + parseFloat(r.total_valor || 0), 0);
            const totalPun = data.reduce((s, r) => s + parseFloat(r.total_puntaje || 0), 0);
            const pct = totalPun > 0 ? ((totalVal / totalPun) * 100).toFixed(1) : 0;

            document.getElementById('headerTotalClientes').textContent = clientes.length;
            document.getElementById('headerPctCumplimiento').textContent = pct + '%';
        }

        if (role === 'admin') {
            $('#filterConsultor').on('change', () => { if (suspendCascade) return; updateCascadeDropdowns(); updateAll(); });
        }
        $('#filterCliente').on('change', () => { if (suspendCascade) return; updateCascadeDropdowns(); updateAll(); });
        $('#filterConsultorExterno').on('change', () => { if (suspendCascade) return; updateCascadeDropdowns(); updateAll(); });
        $('#filterEstandaresFrec').on('change', () => { if (suspendCascade) return; updateCascadeDropdowns(); updateAll(); });

        $('#filterPeriodoDesde, #filterPeriodoHasta').on('change', () => { updateCascadeDropdowns(); updateAll(); });
        $('#filterSoloActivos').on('change', () => { updateCascadeDropdowns(); updateAll(); });

        $('#filterPctMin, #filterPctMax').on('change', updateAll);
        $('#rangeMin').on('input', function() { $('#filterPctMin').val(this.value); updateAll(); });
        $('#rangeMax').on('input', function() { $('#filterPctMax').val(this.value); updateAll(); });

        $('#btnClear').on('click', () => {
            suspendCascade = true;
            if (role === 'admin') $('#filterConsultor').val('').trigger('change.select2');
            $('#filterCliente').val('').trigger('change.select2');
            $('#filterConsultorExterno').val('').trigger('change.select2');
            $('#filterEstandaresFrec').val('').trigger('change.select2');
            $('#filterPeriodoDesde').val('<?= $anioActual ?>-01');
            $('#filterPeriodoHasta').val('<?= $anioActual ?>-12');
            $('#filterPctMin').val(0); $('#rangeMin').val(0);
            $('#filterPctMax').val(100); $('#rangeMax').val(100);
            $('#filterSoloActivos').prop('checked', false);
            suspendCascade = false;
            updateCascadeDropdowns();
            updateAll();
            try { localStorage.removeItem(FILTERS_STORAGE_KEY); } catch (e) {}
        });

        const saved = loadFilters();
        if (saved) applySavedFilters(saved);
        updateCascadeDropdowns();
        updateAll();
    });
    </script>
</body>

</html>
