<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estándares Mínimos SST - Consultor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-logos { background: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); padding: 10px 0; position: fixed; top: 0; width: 100%; z-index: 1000; }
        .header-section { background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%); color: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); }
        .header-section h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .header-metric { text-align: center; }
        .header-metric .value { font-size: 2.25rem; font-weight: bold; color: #FFD700; line-height: 1; }
        .header-metric .label { font-size: 0.85rem; margin: 0; }
        .chart-container { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1.5rem; min-height: 350px; }
        .chart-container h5 { text-align: center; color: #1c2437; font-weight: 600; margin-bottom: 1rem; }
        .chart-container .chart-hint { display: block; text-align: center; color: #6c757d; font-size: 0.75rem; margin-top: 0.25rem; }
        .table-container { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        canvas { max-height: 300px; }
        .btn-volver { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 0.75rem 2rem; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .btn-volver:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); color: white; }
    </style>
</head>

<body>
    <nav class="navbar-logos">
        <div class="container-fluid d-flex justify-content-around align-items-center">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST" height="60">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST" height="60">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid" height="60">
        </div>
    </nav>

    <div style="height: 100px;"></div>

    <div class="container-fluid px-4">
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-chart-pie"></i> Dashboard Estándares Mínimos SST - Consultor</h1>
                    <p class="mb-0">Vista consolidada de todos los clientes</p>
                    <p class="mb-0"><i class="fas fa-user me-2"></i>Sesión: <strong><?= session()->get('nombre_usuario') ?? 'Usuario' ?></strong></p>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-3 header-metric">
                            <div class="value"><span id="metricCalificado"><?= $totalCalificado ?></span> / <span id="metricPosible"><?= $totalPosible ?></span></div>
                            <p class="label">Puntaje Total</p>
                        </div>
                        <div class="col-3 header-metric">
                            <div class="value"><span id="metricPctCumplimiento"><?= $porcentajeCumplimiento ?></span>%</div>
                            <p class="label">% Cumplimiento</p>
                        </div>
                        <div class="col-3 header-metric">
                            <div class="value"><span id="metricClientes"><?= $totalClientes ?></span></div>
                            <p class="label">Total Clientes</p>
                        </div>
                        <div class="col-3 header-metric">
                            <div class="value"><span id="metricItems"><?= $totalItems ?></span></div>
                            <p class="label">Total Ítems</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <a href="<?= base_url('dashboardconsultant') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-building"></i> Seleccione Cliente</label>
                <select class="form-select" id="filterCliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id_cliente'] ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-user-tie"></i> Seleccione Consultor</label>
                <select class="form-select" id="filterConsultor">
                    <option value="">Todos los consultores</option>
                    <?php foreach ($consultoresUnicos as $c): ?>
                        <option value="<?= $c['id_consultor'] ?>"><?= esc($c['nombre_consultor']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-user-friends"></i> Seleccione Consultor Externo</label>
                <select class="form-select" id="filterConsultorExterno">
                    <option value="">Todos los consultores externos</option>
                    <?php foreach ($consultoresExternosUnicos as $ce): ?>
                        <option value="<?= esc($ce['consultor_externo']) ?>"><?= esc($ce['consultor_externo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-layer-group"></i> Seleccione Frecuencia de Visita</label>
                <select class="form-select" id="filterEstandaresFrec">
                    <option value="">Todas las frecuencias</option>
                    <?php foreach ($estandaresFrecUnicos as $es): ?>
                        <option value="<?= esc($es['estandares']) ?>"><?= esc($es['estandares']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-filter"></i> Seleccione Dimensión</label>
                <select class="form-select" id="filterDimension">
                    <option value="">Todas las dimensiones</option>
                    <?php foreach ($dimensionesUnicas as $dim): ?>
                        <option value="<?= esc($dim) ?>"><?= esc($dim) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-check-circle"></i> Seleccione Calificación</label>
                <select class="form-select" id="filterCalificacion">
                    <option value="">Todas las calificaciones</option>
                    <?php foreach ($calificacionesUnicas as $calif): ?>
                        <option value="<?= esc($calif) ?>"><?= esc($calif) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-sync-alt"></i> Seleccione Ciclo PHVA</label>
                <select class="form-select" id="filterPHVA">
                    <option value="">Todos los ciclos</option>
                    <?php foreach ($ciclosUnicos as $ciclo): ?>
                        <option value="<?= esc($ciclo) ?>"><?= esc($ciclo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Gráficos: 6/3/3 -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-layer-group"></i> Gestión por Dimensión</h5>
                    <span class="chart-hint">Click en una barra para filtrar por esa dimensión</span>
                    <canvas id="chartDimension"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-container">
                    <h5><i class="fas fa-sync-alt"></i> Ciclo PHVA</h5>
                    <span class="chart-hint">Click en un segmento para filtrar</span>
                    <canvas id="chartPhva"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-container">
                    <h5><i class="fas fa-check-circle"></i> Calificación</h5>
                    <span class="chart-hint">Click en un segmento para filtrar</span>
                    <canvas id="chartCalificacion"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Evaluaciones</h5>
                    <table id="estandaresTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>CLIENTE</th>
                                <th>ÍTEM</th>
                                <th>CICLO PHVA</th>
                                <th>ESTÁNDAR</th>
                                <th>CALIFICACIÓN</th>
                                <th>CALIFICADO</th>
                                <th>MÁX. POSIBLE</th>
                                <th>NUMERAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluaciones as $ev): ?>
                                <tr>
                                    <td><?= esc($ev['nombre_cliente']) ?></td>
                                    <td><?= esc($ev['item_del_estandar']) ?></td>
                                    <td><?= esc($ev['ciclo']) ?></td>
                                    <td><?= esc($ev['estandar']) ?></td>
                                    <td><?= empty($ev['evaluacion_inicial']) ? 'SIN EVALUAR' : esc($ev['evaluacion_inicial']) ?></td>
                                    <td class="text-end"><?= esc($ev['valor']) ?></td>
                                    <td class="text-end"><?= esc($ev['puntaje_cuantitativo']) ?></td>
                                    <td><?= esc($ev['numeral']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 50px;"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        Chart.register(ChartDataLabels);

        var originalData = <?= json_encode($evaluaciones) ?>;
        var clientesCascade = <?= json_encode($clientesCascade) ?>;

        var DIMENSION_PALETTE = [
            '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
            '#28a745', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'
        ];

        var chartPhva, chartDimension, chartCalificacion;
        var dataTable;
        var suspendCascade = false;

        var FILTERS_STORAGE_KEY = 'dashboardEstandaresFilters';

        function saveFiltersToStorage() {
            try {
                var state = {
                    cliente:          $('#filterCliente').val(),
                    consultor:        $('#filterConsultor').val(),
                    consultorExterno: $('#filterConsultorExterno').val(),
                    estandaresFrec:   $('#filterEstandaresFrec').val(),
                    dimension:        $('#filterDimension').val(),
                    calificacion:     $('#filterCalificacion').val(),
                    phva:             $('#filterPHVA').val()
                };
                localStorage.setItem(FILTERS_STORAGE_KEY, JSON.stringify(state));
            } catch (e) {}
        }

        function loadFiltersFromStorage() {
            try {
                var raw = localStorage.getItem(FILTERS_STORAGE_KEY);
                if (!raw) return null;
                return JSON.parse(raw);
            } catch (e) { return null; }
        }

        function applySavedFiltersToUI(state) {
            if (!state) return;
            suspendCascade = true;
            if (state.cliente !== undefined)          $('#filterCliente').val(state.cliente);
            if (state.consultor !== undefined)        $('#filterConsultor').val(state.consultor);
            if (state.consultorExterno !== undefined) $('#filterConsultorExterno').val(state.consultorExterno);
            if (state.estandaresFrec !== undefined)   $('#filterEstandaresFrec').val(state.estandaresFrec);
            if (state.dimension !== undefined)        $('#filterDimension').val(state.dimension);
            if (state.calificacion !== undefined)     $('#filterCalificacion').val(state.calificacion);
            if (state.phva !== undefined)             $('#filterPHVA').val(state.phva);
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandaresFrec, #filterDimension, #filterCalificacion, #filterPHVA')
                .trigger('change.select2');
            suspendCascade = false;
        }

        $(document).ready(function() {
            var select2Lang = {
                noResults: function() { return "No se encontraron resultados"; },
                searching: function() { return "Buscando..."; }
            };

            $('#filterCliente').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un cliente', allowClear: true, width: '100%', language: select2Lang });
            $('#filterConsultor').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un consultor', allowClear: true, width: '100%', language: select2Lang });
            $('#filterConsultorExterno').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un consultor externo', allowClear: true, width: '100%', language: select2Lang });
            $('#filterEstandaresFrec').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione una frecuencia', allowClear: true, width: '100%', language: select2Lang });
            $('#filterDimension').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione dimensión', allowClear: true, width: '100%', language: select2Lang });
            $('#filterCalificacion').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione calificación', allowClear: true, width: '100%', language: select2Lang });
            $('#filterPHVA').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione ciclo', allowClear: true, width: '100%', language: select2Lang });

            dataTable = $('#estandaresTable').DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json" },
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Estándares Mínimos SST - Consultor',
                    exportOptions: { columns: ':visible' }
                }],
                pageLength: 25,
                order: [[5, 'desc']],
                responsive: true
            });

            initCharts();

            // Cascadeo
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandaresFrec').on('change', function() {
                if (suspendCascade) return;
                updateCascadeDropdowns();
                applyFilters();
            });

            // Filtros independientes (filtran solo evaluaciones)
            $('#filterDimension, #filterCalificacion, #filterPHVA').on('change', function() {
                applyFilters();
            });

            $('#btnLimpiarFiltros').on('click', function() {
                suspendCascade = true;
                $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandaresFrec, #filterDimension, #filterCalificacion, #filterPHVA').val('').trigger('change');
                suspendCascade = false;
                updateCascadeDropdowns();
                applyFilters();
                try { localStorage.removeItem(FILTERS_STORAGE_KEY); } catch (e) {}
            });

            var savedFilters = loadFiltersFromStorage();
            if (savedFilters) {
                applySavedFiltersToUI(savedFilters);
            }
            updateCascadeDropdowns();
            applyFilters();
        });

        // Cascadeo bidireccional
        function filterClientesPool(opts) {
            return clientesCascade.filter(function(c) {
                if (opts.cliente && String(c.id_cliente) !== String(opts.cliente)) return false;
                if (opts.consultor && String(c.id_consultor || '') !== String(opts.consultor)) return false;
                if (opts.externo && (c.consultor_externo || '') !== opts.externo) return false;
                if (opts.estandaresFrec && (c.estandares || '') !== opts.estandaresFrec) return false;
                return true;
            });
        }

        function rebuildSelect($sel, options, currentVal, placeholderText) {
            var html = '<option value="">' + placeholderText + '</option>';
            var stillValid = false;
            options.forEach(function(opt) {
                var sel = (String(opt.value) === String(currentVal)) ? ' selected' : '';
                if (sel) stillValid = true;
                html += '<option value="' + $('<div>').text(opt.value).html() + '"' + sel + '>' +
                        $('<div>').text(opt.label).html() + '</option>';
            });
            $sel.html(html);
            if (!stillValid && currentVal) {
                $sel.val('');
            }
        }

        function updateCascadeDropdowns() {
            var sCliente = $('#filterCliente').val();
            var sConsultor = $('#filterConsultor').val();
            var sExterno = $('#filterConsultorExterno').val();
            var sFrec = $('#filterEstandaresFrec').val();

            var poolCliente = filterClientesPool({ consultor: sConsultor, externo: sExterno, estandaresFrec: sFrec });
            var clienteOptions = []; var seenCli = {};
            poolCliente.forEach(function(c) {
                if (!seenCli[c.id_cliente]) { seenCli[c.id_cliente] = true; clienteOptions.push({ value: c.id_cliente, label: c.nombre_cliente }); }
            });
            clienteOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            var poolConsultor = filterClientesPool({ cliente: sCliente, externo: sExterno, estandaresFrec: sFrec });
            var consultorOptions = []; var seenCons = {};
            poolConsultor.forEach(function(c) {
                if (c.id_consultor && c.nombre_consultor && !seenCons[c.id_consultor]) {
                    seenCons[c.id_consultor] = true;
                    consultorOptions.push({ value: c.id_consultor, label: c.nombre_consultor });
                }
            });
            consultorOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            var poolExterno = filterClientesPool({ cliente: sCliente, consultor: sConsultor, estandaresFrec: sFrec });
            var externoOptions = []; var seenExt = {};
            poolExterno.forEach(function(c) {
                var ext = c.consultor_externo || '';
                if (ext && !seenExt[ext]) { seenExt[ext] = true; externoOptions.push({ value: ext, label: ext }); }
            });
            externoOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            var poolFrec = filterClientesPool({ cliente: sCliente, consultor: sConsultor, externo: sExterno });
            var frecOptions = []; var seenFrec = {};
            poolFrec.forEach(function(c) {
                var e = c.estandares || '';
                if (e && !seenFrec[e]) { seenFrec[e] = true; frecOptions.push({ value: e, label: e }); }
            });
            frecOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            suspendCascade = true;
            rebuildSelect($('#filterCliente'), clienteOptions, sCliente, 'Todos los clientes');
            rebuildSelect($('#filterConsultor'), consultorOptions, sConsultor, 'Todos los consultores');
            rebuildSelect($('#filterConsultorExterno'), externoOptions, sExterno, 'Todos los consultores externos');
            rebuildSelect($('#filterEstandaresFrec'), frecOptions, sFrec, 'Todas las frecuencias');
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandaresFrec').trigger('change.select2');
            suspendCascade = false;
        }

        function initCharts() {
            // Donut PHVA (clickeable)
            var ctxPhva = document.getElementById('chartPhva').getContext('2d');
            var phvaData = <?= json_encode($phvaCounts) ?>;
            chartPhva = new Chart(ctxPhva, {
                type: 'doughnut',
                data: { labels: Object.keys(phvaData), datasets: [{ data: Object.values(phvaData), backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'] }] },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedLabel = chartPhva.data.labels[idx];
                        if (!clickedLabel) return;
                        var current = $('#filterPHVA').val();
                        var newVal = (current === clickedLabel) ? '' : clickedLabel;
                        $('#filterPHVA').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff', font: { weight: 'bold', size: 12 },
                            formatter: function(value, context) {
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var pct = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                return value > 0 ? pct : '';
                            }
                        }
                    }
                }
            });

            // Barras Dimensión (clickeable)
            var ctxDim = document.getElementById('chartDimension').getContext('2d');
            var dimData = <?= json_encode($dimensionCounts) ?>;
            chartDimension = new Chart(ctxDim, {
                type: 'bar',
                data: {
                    labels: Object.keys(dimData),
                    datasets: [{ label: 'Puntaje', data: Object.values(dimData),
                        backgroundColor: Object.keys(dimData).map(function(_, i) { return DIMENSION_PALETTE[i % DIMENSION_PALETTE.length]; }) }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: true,
                    layout: { padding: { right: 30 } },
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedLabel = chartDimension.data.labels[idx];
                        if (!clickedLabel) return;
                        var current = $('#filterDimension').val();
                        var newVal = (current === clickedLabel) ? '' : clickedLabel;
                        $('#filterDimension').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: { color: '#333', anchor: 'end', align: 'end', font: { weight: 'bold', size: 11 }, formatter: function(value) { return value > 0 ? value : ''; } }
                    },
                    scales: {
                        x: { beginAtZero: true },
                        y: { ticks: { autoSkip: false, font: { size: 10 }, callback: function(value) { var l = this.getLabelForValue(value); return l && l.length > 35 ? l.substring(0, 33) + '…' : l; } } }
                    }
                }
            });

            // Donut Calificación (clickeable)
            var ctxCal = document.getElementById('chartCalificacion').getContext('2d');
            var calData = <?= json_encode($calificacionCounts) ?>;
            chartCalificacion = new Chart(ctxCal, {
                type: 'doughnut',
                data: { labels: Object.keys(calData), datasets: [{ data: Object.values(calData), backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'] }] },
                options: {
                    responsive: true, maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedLabel = chartCalificacion.data.labels[idx];
                        if (!clickedLabel) return;
                        var current = $('#filterCalificacion').val();
                        var newVal = (current === clickedLabel) ? '' : clickedLabel;
                        $('#filterCalificacion').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff', font: { weight: 'bold', size: 12 },
                            formatter: function(value, context) {
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var pct = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                return value > 0 ? pct : '';
                            }
                        }
                    }
                }
            });
        }

        function applyFilters() {
            var fCliente = $('#filterCliente').val();
            var fConsultor = $('#filterConsultor').val();
            var fExterno = $('#filterConsultorExterno').val();
            var fFrec = $('#filterEstandaresFrec').val();
            var fDimension = $('#filterDimension').val();
            var fCalificacion = $('#filterCalificacion').val();
            var fPHVA = $('#filterPHVA').val();

            var filteredData = originalData.filter(function(item) {
                if (fCliente && String(item.id_cliente) !== String(fCliente)) return false;
                if (fConsultor && String(item.id_consultor || '') !== String(fConsultor)) return false;
                if (fExterno && (item.consultor_externo || '') !== fExterno) return false;
                if (fFrec && (item.estandares || '') !== fFrec) return false;
                if (fDimension && item.estandar !== fDimension) return false;

                if (fCalificacion) {
                    var itemCalif = item.evaluacion_inicial ? item.evaluacion_inicial : 'SIN EVALUAR';
                    if (itemCalif !== fCalificacion) return false;
                }
                if (fPHVA && item.ciclo !== fPHVA) return false;
                return true;
            });

            var totalCalificado = 0;
            var totalPosible = 0;
            var clientesDistintos = {};
            filteredData.forEach(function(item) {
                totalCalificado += parseFloat(item.valor || 0);
                totalPosible += parseFloat(item.puntaje_cuantitativo || 0);
                if (item.id_cliente !== undefined && item.id_cliente !== null) clientesDistintos[item.id_cliente] = true;
            });
            var pct = totalPosible > 0 ? ((totalCalificado / totalPosible) * 100).toFixed(1) : 0;

            $('#metricCalificado').text(totalCalificado.toFixed(2));
            $('#metricPosible').text(totalPosible.toFixed(2));
            $('#metricPctCumplimiento').text(pct);
            $('#metricClientes').text(Object.keys(clientesDistintos).length);
            $('#metricItems').text(filteredData.length);

            var phvaCounts = {};
            var dimensionCounts = {};
            var calificacionCounts = {};
            filteredData.forEach(function(item) {
                if (item.ciclo) phvaCounts[item.ciclo] = (phvaCounts[item.ciclo] || 0) + 1;
                if (item.estandar) dimensionCounts[item.estandar] = (dimensionCounts[item.estandar] || 0) + parseFloat(item.valor || 0);
                var calif = item.evaluacion_inicial ? item.evaluacion_inicial : 'SIN EVALUAR';
                calificacionCounts[calif] = (calificacionCounts[calif] || 0) + 1;
            });

            updateCharts(phvaCounts, dimensionCounts, calificacionCounts);

            dataTable.clear();
            filteredData.forEach(function(item) {
                dataTable.row.add([
                    item.nombre_cliente,
                    item.item_del_estandar,
                    item.ciclo,
                    item.estandar,
                    item.evaluacion_inicial ? item.evaluacion_inicial : 'SIN EVALUAR',
                    item.valor,
                    item.puntaje_cuantitativo,
                    item.numeral
                ]);
            });
            dataTable.draw();

            saveFiltersToStorage();
        }

        function updateCharts(phvaCounts, dimensionCounts, calificacionCounts) {
            chartPhva.data.labels = Object.keys(phvaCounts);
            chartPhva.data.datasets[0].data = Object.values(phvaCounts);
            chartPhva.update();

            chartDimension.data.labels = Object.keys(dimensionCounts);
            chartDimension.data.datasets[0].data = Object.values(dimensionCounts);
            chartDimension.data.datasets[0].backgroundColor = Object.keys(dimensionCounts).map(function(_, i) { return DIMENSION_PALETTE[i % DIMENSION_PALETTE.length]; });
            chartDimension.update();

            chartCalificacion.data.labels = Object.keys(calificacionCounts);
            chartCalificacion.data.datasets[0].data = Object.values(calificacionCounts);
            chartCalificacion.update();
        }
    </script>
</body>

</html>
