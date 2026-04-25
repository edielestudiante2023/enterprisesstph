<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Capacitaciones - Consultor</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-logos {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .metric-card h6 {
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .metric-card h2 {
            color: #667eea;
            font-size: 2.25rem;
            font-weight: bold;
            margin: 0;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            min-height: 350px;
        }

        .chart-container h5 {
            text-align: center;
            color: #1c2437;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .chart-container .chart-hint {
            display: block;
            text-align: center;
            color: #6c757d;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        canvas {
            max-height: 300px;
        }

        .btn-volver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar con logos -->
    <nav class="navbar-logos">
        <div class="container-fluid d-flex justify-content-around align-items-center">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST" height="60">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST" height="60">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid" height="60">
        </div>
    </nav>

    <div style="height: 100px;"></div>

    <!-- Contenido principal -->
    <div class="container-fluid px-4">
        <!-- Header -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h1><i class="fas fa-graduation-cap"></i> Dashboard Capacitaciones - Consultor</h1>
                    <p class="mb-0">Vista consolidada de todos los clientes</p>
                    <p class="mb-0"><i class="fas fa-user me-2"></i>Sesión: <strong><?= session()->get('nombre_usuario') ?? 'Usuario' ?></strong></p>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="mb-3">
            <a href="<?= base_url('dashboardconsultant') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Tarjetas de métricas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-list-ol"></i> Total Capacitaciones</h6>
                    <h2 id="metricTotal"><?= $totalCapacitaciones ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-building"></i> Total Clientes</h6>
                    <h2 id="metricClientes"><?= $totalClientes ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-users"></i> Asistentes Promedio</h6>
                    <h2 id="metricAsistentes"><?= $promedioAsistentes ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <h6><i class="fas fa-star"></i> Promedio Calificaciones</h6>
                    <h2 id="metricCalificaciones"><?= $promedioCalificaciones ?></h2>
                </div>
            </div>
        </div>

        <!-- Filtros/Selectores -->
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
                <label class="form-label fw-bold"><i class="fas fa-toggle-on"></i> Seleccione Estado</label>
                <select class="form-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estadosUnicos as $estado): ?>
                        <?php if (!empty($estado)): ?>
                            <option value="<?= esc($estado) ?>"><?= esc($estado) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-layer-group"></i> Seleccione Frecuencia de Visita</label>
                <select class="form-select" id="filterEstandares">
                    <option value="">Todas las frecuencias</option>
                    <?php foreach ($estandaresUnicos as $es): ?>
                        <option value="<?= esc($es['estandares']) ?>"><?= esc($es['estandares']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar"></i> Seleccione Año</label>
                <select class="form-select" id="filterAnio">
                    <option value="">Todos los años</option>
                    <?php foreach ($aniosDisponibles as $y): ?>
                        <option value="<?= $y ?>" <?= $y === $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar-day"></i> Seleccione Mes</label>
                <select class="form-select" id="filterMes">
                    <option value="">Todos los meses</option>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar"></i> Fecha Desde</label>
                <input type="date" class="form-control" id="filterFechaDesde" value="<?= $anioActual ?>-01-01">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar"></i> Fecha Hasta</label>
                <input type="date" class="form-control" id="filterFechaHasta" value="<?= $anioActual ?>-12-31">
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico Capacitaciones por Cliente (Barras Horizontales, clickeable) -->
            <div class="col-md-8">
                <div class="chart-container">
                    <h5><i class="fas fa-building"></i> Capacitaciones por Cliente</h5>
                    <span class="chart-hint">Click en una barra para filtrar por ese cliente</span>
                    <canvas id="chartClientes"></canvas>
                </div>
            </div>

            <!-- Gráfico Estado (Donut, clickeable) -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5><i class="fas fa-toggle-on"></i> Estado de Capacitaciones</h5>
                    <span class="chart-hint">Click en un segmento para filtrar por estado</span>
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Capacitaciones</h5>
                    <table id="capacitacionesTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>CLIENTE</th>
                                <th>NOMBRE CAPACITACIÓN</th>
                                <th>FECHA PROGRAMADA</th>
                                <th>FECHA REALIZACIÓN</th>
                                <th>ESTADO</th>
                                <th>ASISTENTES</th>
                                <th>CALIFICACIÓN</th>
                                <th>OBSERVACIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capacitaciones as $cap): ?>
                                <tr>
                                    <td><?= esc($cap['nombre_cliente']) ?></td>
                                    <td><?= esc($cap['nombre_capacitacion'] ?? 'N/A') ?></td>
                                    <td><?= esc($cap['fecha_programada'] ?? 'N/A') ?></td>
                                    <td><?= esc($cap['fecha_de_realizacion'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($cap['estado'] ?? '') === 'EJECUTADA' ? 'success' : (($cap['estado'] ?? '') === 'PROGRAMADA' ? 'warning' : 'secondary') ?>">
                                            <?= esc($cap['estado'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= esc($cap['numero_de_asistentes_a_capacitacion'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= esc($cap['promedio_de_calificaciones'] ?? 'N/A') ?></td>
                                    <td><?= esc($cap['observaciones'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 50px;"></div>

    <!-- Scripts -->
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

        // Datos originales
        var originalData = <?= json_encode($capacitaciones) ?>;
        var clientesCascade = <?= json_encode($clientesCascade) ?>;

        var CLIENTES_PALETTE = [
            '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
            '#28a745', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'
        ];

        var chartClientes, chartEstado;
        var dataTable;
        var chartClientesIds = [];
        var suspendCascade = false;

        // === Persistencia de filtros (localStorage) ===
        var FILTERS_STORAGE_KEY = 'dashboardCapacitacionesFilters';

        function saveFiltersToStorage() {
            try {
                var state = {
                    cliente:          $('#filterCliente').val(),
                    consultor:        $('#filterConsultor').val(),
                    consultorExterno: $('#filterConsultorExterno').val(),
                    estandares:       $('#filterEstandares').val(),
                    estado:           $('#filterEstado').val(),
                    anio:             $('#filterAnio').val(),
                    mes:              $('#filterMes').val(),
                    fechaDesde:       $('#filterFechaDesde').val(),
                    fechaHasta:       $('#filterFechaHasta').val()
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
            if (state.estandares !== undefined)       $('#filterEstandares').val(state.estandares);
            if (state.estado !== undefined)           $('#filterEstado').val(state.estado);
            if (state.anio !== undefined)             $('#filterAnio').val(state.anio);
            if (state.mes !== undefined)              $('#filterMes').val(state.mes);
            if (state.fechaDesde !== undefined)       $('#filterFechaDesde').val(state.fechaDesde);
            if (state.fechaHasta !== undefined)       $('#filterFechaHasta').val(state.fechaHasta);
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares, #filterEstado, #filterAnio, #filterMes')
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
            $('#filterEstandares').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione una frecuencia', allowClear: true, width: '100%', language: select2Lang });
            $('#filterEstado').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione estado', allowClear: true, width: '100%', language: select2Lang });
            $('#filterAnio').select2({ theme: 'bootstrap-5', placeholder: 'Todos los años', allowClear: true, width: '100%', language: select2Lang });
            $('#filterMes').select2({ theme: 'bootstrap-5', placeholder: 'Todos los meses', allowClear: true, width: '100%', language: select2Lang });

            dataTable = $('#capacitacionesTable').DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json" },
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Capacitaciones - Consultor',
                    exportOptions: { columns: ':visible' }
                }],
                pageLength: 25,
                order: [[2, 'desc']],
                responsive: true
            });

            initCharts();

            // Filtros que cascadean (afectan el pool de clientes)
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares').on('change', function() {
                if (suspendCascade) return;
                updateCascadeDropdowns();
                applyFilters();
            });

            // Filtros que solo filtran capacitaciones
            $('#filterEstado, #filterFechaDesde, #filterFechaHasta').on('change', function() {
                applyFilters();
            });

            // Año/Mes auto-rellenan Desde/Hasta
            $('#filterAnio, #filterMes').on('change', function() {
                autoFillFechas();
                applyFilters();
            });

            $('#btnLimpiarFiltros').on('click', function() {
                suspendCascade = true;
                $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares, #filterEstado, #filterAnio, #filterMes').val('').trigger('change');
                $('#filterFechaDesde').val('');
                $('#filterFechaHasta').val('');
                suspendCascade = false;
                updateCascadeDropdowns();
                applyFilters();
                try { localStorage.removeItem(FILTERS_STORAGE_KEY); } catch (e) {}
            });

            // Cargar filtros guardados; si no hay, defaults PHP ya aplicados
            var savedFilters = loadFiltersFromStorage();
            if (savedFilters) {
                applySavedFiltersToUI(savedFilters);
            }
            updateCascadeDropdowns();
            applyFilters();
        });

        function autoFillFechas() {
            var year = $('#filterAnio').val();
            var month = $('#filterMes').val();
            if (!year) {
                $('#filterFechaDesde').val('');
                $('#filterFechaHasta').val('');
                return;
            }
            if (!month) {
                $('#filterFechaDesde').val(year + '-01-01');
                $('#filterFechaHasta').val(year + '-12-31');
                return;
            }
            var y = parseInt(year, 10);
            var m = parseInt(month, 10);
            var lastDay = new Date(y, m, 0).getDate();
            var mm = String(m).padStart(2, '0');
            var dd = String(lastDay).padStart(2, '0');
            $('#filterFechaDesde').val(year + '-' + mm + '-01');
            $('#filterFechaHasta').val(year + '-' + mm + '-' + dd);
        }

        // === Cascadeo bidireccional ===
        function filterClientesPool(opts) {
            return clientesCascade.filter(function(c) {
                if (opts.cliente && String(c.id_cliente) !== String(opts.cliente)) return false;
                if (opts.consultor && String(c.id_consultor || '') !== String(opts.consultor)) return false;
                if (opts.externo && (c.consultor_externo || '') !== opts.externo) return false;
                if (opts.estandares && (c.estandares || '') !== opts.estandares) return false;
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
            var sEstandares = $('#filterEstandares').val();

            var poolCliente = filterClientesPool({ consultor: sConsultor, externo: sExterno, estandares: sEstandares });
            var clienteOptions = [];
            var seenCli = {};
            poolCliente.forEach(function(c) {
                if (!seenCli[c.id_cliente]) {
                    seenCli[c.id_cliente] = true;
                    clienteOptions.push({ value: c.id_cliente, label: c.nombre_cliente });
                }
            });
            clienteOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            var poolConsultor = filterClientesPool({ cliente: sCliente, externo: sExterno, estandares: sEstandares });
            var consultorOptions = [];
            var seenCons = {};
            poolConsultor.forEach(function(c) {
                if (c.id_consultor && c.nombre_consultor && !seenCons[c.id_consultor]) {
                    seenCons[c.id_consultor] = true;
                    consultorOptions.push({ value: c.id_consultor, label: c.nombre_consultor });
                }
            });
            consultorOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            var poolExterno = filterClientesPool({ cliente: sCliente, consultor: sConsultor, estandares: sEstandares });
            var externoOptions = [];
            var seenExt = {};
            poolExterno.forEach(function(c) {
                var ext = c.consultor_externo || '';
                if (ext && !seenExt[ext]) {
                    seenExt[ext] = true;
                    externoOptions.push({ value: ext, label: ext });
                }
            });
            externoOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            var poolEst = filterClientesPool({ cliente: sCliente, consultor: sConsultor, externo: sExterno });
            var estandaresOptions = [];
            var seenEst = {};
            poolEst.forEach(function(c) {
                var e = c.estandares || '';
                if (e && !seenEst[e]) {
                    seenEst[e] = true;
                    estandaresOptions.push({ value: e, label: e });
                }
            });
            estandaresOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            suspendCascade = true;
            rebuildSelect($('#filterCliente'), clienteOptions, sCliente, 'Todos los clientes');
            rebuildSelect($('#filterConsultor'), consultorOptions, sConsultor, 'Todos los consultores');
            rebuildSelect($('#filterConsultorExterno'), externoOptions, sExterno, 'Todos los consultores externos');
            rebuildSelect($('#filterEstandares'), estandaresOptions, sEstandares, 'Todas las frecuencias');
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares').trigger('change.select2');
            suspendCascade = false;
        }

        function initCharts() {
            // Bar chart: Capacitaciones por Cliente
            var ctxClientes = document.getElementById('chartClientes').getContext('2d');
            var clientesInit = computeClienteCounts(originalData);
            chartClientesIds = clientesInit.map(function(x) { return x.id_cliente; });
            chartClientes = new Chart(ctxClientes, {
                type: 'bar',
                data: {
                    labels: clientesInit.map(function(x) { return x.nombre_cliente; }),
                    datasets: [{
                        label: 'Capacitaciones',
                        data: clientesInit.map(function(x) { return x.count; }),
                        backgroundColor: clientesInit.map(function(_, i) { return CLIENTES_PALETTE[i % CLIENTES_PALETTE.length]; })
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    layout: { padding: { right: 30 } },
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedId = chartClientesIds[idx];
                        if (clickedId === undefined || clickedId === null) return;
                        var current = $('#filterCliente').val();
                        var newVal = (String(current) === String(clickedId)) ? '' : String(clickedId);
                        $('#filterCliente').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            color: '#333', anchor: 'end', align: 'end',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value) { return value > 0 ? value : ''; }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true },
                        y: {
                            ticks: {
                                autoSkip: false,
                                font: { size: 10 },
                                callback: function(value) {
                                    var label = this.getLabelForValue(value);
                                    if (!label) return label;
                                    return label.length > 35 ? label.substring(0, 33) + '…' : label;
                                }
                            }
                        }
                    }
                }
            });

            // Donut Estado
            var ctxEstado = document.getElementById('chartEstado').getContext('2d');
            var estadoData = <?= json_encode($estadoCounts) ?>;
            chartEstado = new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(estadoData),
                    datasets: [{
                        data: Object.values(estadoData),
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedLabel = chartEstado.data.labels[idx];
                        if (!clickedLabel) return;
                        var current = $('#filterEstado').val();
                        var newVal = (current === clickedLabel) ? '' : clickedLabel;
                        $('#filterEstado').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bold', size: 12 },
                            formatter: function(value, context) {
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                return value > 0 ? percentage : '';
                            }
                        }
                    }
                }
            });
        }

        function computeClienteCounts(items) {
            var byId = {};
            items.forEach(function(a) {
                var id = a.id_cliente;
                if (id === undefined || id === null) return;
                if (!byId[id]) {
                    byId[id] = { id_cliente: id, nombre_cliente: a.nombre_cliente || 'SIN NOMBRE', count: 0 };
                }
                byId[id].count++;
            });
            var arr = Object.keys(byId).map(function(k) { return byId[k]; });
            arr.sort(function(a, b) { return b.count - a.count; });
            return arr;
        }

        function applyFilters() {
            var filterCliente = $('#filterCliente').val();
            var filterConsultor = $('#filterConsultor').val();
            var filterConsultorExterno = $('#filterConsultorExterno').val();
            var filterEstandares = $('#filterEstandares').val();
            var filterEstado = $('#filterEstado').val();
            var filterFechaDesde = $('#filterFechaDesde').val();
            var filterFechaHasta = $('#filterFechaHasta').val();

            var filteredData = originalData.filter(function(item) {
                if (filterCliente && String(item.id_cliente) !== String(filterCliente)) return false;
                if (filterConsultor && String(item.id_consultor || '') !== String(filterConsultor)) return false;
                if (filterConsultorExterno && (item.consultor_externo || '') !== filterConsultorExterno) return false;
                if (filterEstandares && (item.estandares || '') !== filterEstandares) return false;
                if (filterEstado && item.estado !== filterEstado) return false;

                if (filterFechaDesde || filterFechaHasta) {
                    if (!item.fecha_programada) return false;
                    var fechaSlice = String(item.fecha_programada).substring(0, 10);
                    if (filterFechaDesde && fechaSlice < filterFechaDesde) return false;
                    if (filterFechaHasta && fechaSlice > filterFechaHasta) return false;
                }
                return true;
            });

            // Métricas
            var totalCapacitaciones = filteredData.length;
            var asistentesTotal = 0;
            var calificacionesTotal = 0;
            var countAsistentes = 0;
            var countCalificaciones = 0;
            var clientesDistintos = {};

            filteredData.forEach(function(item) {
                if (item.id_cliente !== undefined && item.id_cliente !== null) {
                    clientesDistintos[item.id_cliente] = true;
                }
                if (item.numero_de_asistentes_a_capacitacion && !isNaN(item.numero_de_asistentes_a_capacitacion)) {
                    asistentesTotal += parseInt(item.numero_de_asistentes_a_capacitacion);
                    countAsistentes++;
                }
                if (item.promedio_de_calificaciones && !isNaN(item.promedio_de_calificaciones)) {
                    calificacionesTotal += parseFloat(item.promedio_de_calificaciones);
                    countCalificaciones++;
                }
            });

            var promedioAsistentes = countAsistentes > 0 ? (asistentesTotal / countAsistentes).toFixed(2) : 0;
            var promedioCalificaciones = countCalificaciones > 0 ? (calificacionesTotal / countCalificaciones).toFixed(2) : 0;

            $('#metricTotal').text(totalCapacitaciones);
            $('#metricClientes').text(Object.keys(clientesDistintos).length);
            $('#metricAsistentes').text(promedioAsistentes);
            $('#metricCalificaciones').text(promedioCalificaciones);

            // Datos para gráficos
            var estadoCounts = {};
            filteredData.forEach(function(item) {
                var estado = item.estado || 'SIN ESTADO';
                estadoCounts[estado] = (estadoCounts[estado] || 0) + 1;
            });
            var clienteCounts = computeClienteCounts(filteredData);

            updateCharts(clienteCounts, estadoCounts);

            // Tabla
            dataTable.clear();
            filteredData.forEach(function(item) {
                var estadoBadge = '';
                if (item.estado === 'EJECUTADA') {
                    estadoBadge = '<span class="badge bg-success">' + (item.estado || 'N/A') + '</span>';
                } else if (item.estado === 'PROGRAMADA') {
                    estadoBadge = '<span class="badge bg-warning">' + (item.estado || 'N/A') + '</span>';
                } else {
                    estadoBadge = '<span class="badge bg-secondary">' + (item.estado || 'N/A') + '</span>';
                }

                dataTable.row.add([
                    item.nombre_cliente,
                    item.nombre_capacitacion || 'N/A',
                    item.fecha_programada || 'N/A',
                    item.fecha_de_realizacion || 'N/A',
                    estadoBadge,
                    item.numero_de_asistentes_a_capacitacion || 'N/A',
                    item.promedio_de_calificaciones || 'N/A',
                    item.observaciones || ''
                ]);
            });
            dataTable.draw();

            saveFiltersToStorage();
        }

        function updateCharts(clienteCounts, estadoCounts) {
            chartClientes.data.labels = clienteCounts.map(function(x) { return x.nombre_cliente; });
            chartClientes.data.datasets[0].data = clienteCounts.map(function(x) { return x.count; });
            chartClientes.data.datasets[0].backgroundColor = clienteCounts.map(function(_, i) { return CLIENTES_PALETTE[i % CLIENTES_PALETTE.length]; });
            chartClientesIds = clienteCounts.map(function(x) { return x.id_cliente; });
            chartClientes.update();

            chartEstado.data.labels = Object.keys(estadoCounts);
            chartEstado.data.datasets[0].data = Object.values(estadoCounts);
            chartEstado.update();
        }
    </script>
</body>

</html>
